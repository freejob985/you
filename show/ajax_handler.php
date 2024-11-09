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
                $tags = isset($_POST['section_tags']) ? $_POST['section_tags'] : '';
                $result = updateLessonTags($lessonId, $tags);
                ob_end_clean();
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update lesson tags']);
                }
                break;

            case 'get_lesson_sections':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $sections = getLessonSections($lessonId);
                ob_end_clean();
                echo json_encode(['success' => true, 'sections' => $sections]);
                break;

            case 'update_lesson_sections':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $languageId = isset($_POST['language_id']) ? intval($_POST['language_id']) : 0;
                $sections = isset($_POST['sections']) ? $_POST['sections'] : [];
                
                $result = updateLessonSections($lessonId, $languageId, $sections);
                ob_end_clean();
                if ($result) {
                    $updatedSections = getLessonSections($lessonId);
                    echo json_encode(['success' => true, 'sections' => $updatedSections]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update sections']);
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