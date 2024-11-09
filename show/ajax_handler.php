<?php
/**
 * ajax_handler.php
 *
 * This file handles AJAX requests from the frontend, performing actions such as
 * adding comments, updating lesson status, and retrieving data.
 *
 * Dependencies:
 * - session_start() if using sessions
 * - Inclusion of 'database.php' for database functions
 */

// Include database functions
include_once("database.php");

// Optionally, start a session if needed
// session_start();

// Set headers for JSON response and allow cross-origin requests if necessary
header('Content-Type: application/json');

// Handle GET and POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get_comments':
                $lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
                $comments = getComments($lessonId);
                ob_end_clean();
                echo json_encode($comments);
                break;

            case 'get_codes':
                $lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
                $codes = getCodes($lessonId);
                ob_end_clean();
                echo json_encode($codes);
                break;

            case 'get_playlist':
                $courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
                $playlistItems = getPlaylistItems($courseId);
                $statistics = getCourseStatistics($courseId);
                ob_end_clean();
                echo json_encode(['success' => true, 'playlistItems' => $playlistItems, 'statistics' => $statistics]);
                break;

            case 'get_sections':
                $lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
                try {
                    $db = connectDB();
                    
                    // الحصول على language_id والأقسام الحالية للدرس
                    $stmt = $db->prepare("
                        SELECT l.section_tags, l.language_id, 
                               (SELECT GROUP_CONCAT(name) FROM sections WHERE language_id = l.language_id) as available_sections 
                        FROM lessons l 
                        WHERE l.id = :lesson_id
                    ");
                    $stmt->execute([':lesson_id' => $lessonId]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $sections = [];
                    $availableSections = [];
                    
                    if ($result) {
                        // الأقسام المحددة حالياً للدرس
                        if ($result['section_tags']) {
                            $sections = json_decode($result['section_tags'], true);
                        }
                        
                        // جميع الأقسام المتاحة للغة
                        if ($result['available_sections']) {
                            $availableSections = explode(',', $result['available_sections']);
                        }
                    }
                    
                    echo json_encode([
                        'success' => true, 
                        'sections' => $sections,
                        'availableSections' => array_values(array_unique($availableSections))
                    ]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;

            default:
                ob_end_clean();
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } else {
        ob_end_clean();
        echo json_encode(['error' => 'No action specified']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_comment':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                $commentId = addComment($lessonId, $comment);
                ob_end_clean();
                if ($commentId) {
                    echo json_encode(['success' => true, 'comment_id' => $commentId]);
                } else {
                    echo json_encode(['success' => false]);
                }
                break;

            case 'delete_comment':
                $commentId = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
                $result = deleteComment($commentId);
                ob_end_clean();
                echo json_encode(['success' => $result]);
                break;

            case 'add_code':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $language = isset($_POST['language']) ? $_POST['language'] : '';
                $code = isset($_POST['code']) ? $_POST['code'] : '';
                $codeId = addCode($lessonId, $language, $code);
                ob_end_clean();
                if ($codeId) {
                    echo json_encode(['success' => true, 'code_id' => $codeId]);
                } else {
                    echo json_encode(['success' => false]);
                }
                break;

            case 'delete_code':
                $codeId = isset($_POST['code_id']) ? intval($_POST['code_id']) : 0;
                $result = deleteCode($codeId);
                ob_end_clean();
                echo json_encode(['success' => $result]);
                break;

            case 'change_lesson_status':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $status = isset($_POST['status']) ? $_POST['status'] : '';
                $courseId = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
                $result = updateLessonStatus($lessonId, $status);
                ob_end_clean();
                if ($result) {
                    $statistics = getCourseStatistics($courseId);
                    echo json_encode(['success' => true, 'statistics' => $statistics]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update lesson status']);
                }
                break;

            case 'toggle_view_status':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $result = toggleLessonViewStatus($lessonId);
                ob_end_clean();
                echo json_encode($result);
                break;

            case 'update_section':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $sectionId = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;
                $result = updateLessonSection($lessonId, $sectionId);
                ob_end_clean();
                if ($result) {
                    $sectionName = getSectionName($sectionId);
                    echo json_encode(['success' => true, 'section_name' => $sectionName]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update lesson section']);
                }
                break;

            case 'update_tags':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $tags = isset($_POST['section_tags']) ? json_decode($_POST['section_tags'], true) : [];
                
                try {
                    $db = connectDB();
                    $db->beginTransaction();
                    
                    // الحصول على language_id الخاص بالدرس
                    $stmt = $db->prepare("SELECT language_id FROM lessons WHERE id = :lesson_id");
                    $stmt->execute([':lesson_id' => $lessonId]);
                    $languageId = $stmt->fetchColumn();
                    
                    if (!$languageId) {
                        throw new Exception('لم يتم العثور على لغة الدرس');
                    }
                    
                    // إضافة الأقسام الجديدة إلى جدول sections
                    foreach ($tags as $tag) {
                        // التحقق من وجود القسم
                        $stmt = $db->prepare("SELECT id FROM sections WHERE name = :name AND language_id = :language_id");
                        $stmt->execute([
                            ':name' => $tag,
                            ':language_id' => $languageId
                        ]);
                        $sectionId = $stmt->fetchColumn();
                        
                        // إذا لم يكن القسم موجوداً، قم بإضافته
                        if (!$sectionId) {
                            $stmt = $db->prepare("INSERT INTO sections (name, language_id) VALUES (:name, :language_id)");
                            $stmt->execute([
                                ':name' => $tag,
                                ':language_id' => $languageId
                            ]);
                        }
                    }
                    
                    // تحديث الأقسام للدرس
                    $stmt = $db->prepare("UPDATE lessons SET section_tags = :tags WHERE id = :lesson_id");
                    $stmt->execute([
                        ':tags' => json_encode($tags),
                        ':lesson_id' => $lessonId
                    ]);
                    
                    $db->commit();
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;

            default:
                ob_end_clean();
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } else {
        ob_end_clean();
        echo json_encode(['error' => 'No action specified']);
    }
} else {
    ob_end_clean();
    echo json_encode(['error' => 'Invalid request method']);
}
?>