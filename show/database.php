<?php
/**
 * database.php
 *
 * This file contains functions for database interactions, including connecting to the database,
 * retrieving lesson and course details, and updating lesson information.
 *
 * Dependencies:
 * - PDO extension for SQLite
 * - A SQLite database file located at '../db/courses.db'
 *
 * Note: Ensure that the database file exists and has the correct schema as per your application requirements.
 */

/**
 * Establishes a connection to the SQLite database.
 *
 * @return PDO The PDO instance representing the database connection.
 * @throws PDOException If the connection fails.
 */
function connectDB() {
    try {
        $db = new PDO('sqlite:D:\server\htdocs\you\courses.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        file_put_contents('debug.log', "Database connection error: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    }
}

/**
 * Retrieves lesson details by lesson ID.
 *
 * @param int $lessonId The ID of the lesson.
 * @return array|null An associative array containing lesson details, or null if not found.
 */
function getLessonDetails($lessonId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE id = :lesson_id");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getLessonDetails error: " . $e->getMessage() . "\n", FILE_APPEND);
        return null;
    }
}

/**
 * Retrieves course details by course ID.
 *
 * @param int $courseId The ID of the course.
 * @return array|null An associative array containing course details, or null if not found.
 */
function getCourseDetails($courseId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT * FROM courses WHERE id = :course_id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getCourseDetails error: " . $e->getMessage() . "\n", FILE_APPEND);
        return null;
    }
}

/**
 * Retrieves playlist items for a given course ID.
 *
 * @param int $courseId The ID of the course.
 * @return array An array of playlist items.
 */
function getPlaylistItems($courseId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT id, title, status, duration FROM lessons WHERE course_id = :course_id ORDER BY id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getPlaylistItems error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [];
    }
}

/**
 * Extracts the YouTube video ID from a YouTube URL.
 *
 * @param string $url The YouTube URL.
 * @return string|null The video ID, or null if not found.
 */
function getYoutubeVideoId($url) {
    preg_match('/v=([^&]+)/', $url, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

/**
 * Adds a new comment to a lesson.
 *
 * @param int $lessonId The ID of the lesson.
 * @param string $comment The content of the comment.
 * @return int|false The ID of the new comment, or false on failure.
 */
function addComment($lessonId, $comment) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("INSERT INTO comments (lesson_id, content, created_at) VALUES (:lesson_id, :content, :created_at)");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->bindParam(':content', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->execute();
        return $db->lastInsertId();
    } catch (Exception $e) {
        file_put_contents('debug.log', "addComment error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Retrieves comments for a given lesson ID.
 *
 * @param int $lessonId The ID of the lesson.
 * @return array An array of comments.
 */
function getComments($lessonId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT * FROM comments WHERE lesson_id = :lesson_id ORDER BY created_at DESC");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getComments error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [];
    }
}

/**
 * Deletes a comment by its ID.
 *
 * @param int $commentId The ID of the comment.
 * @return bool True on success, false on failure.
 */
function deleteComment($commentId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("DELETE FROM comments WHERE id = :comment_id");
        $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        file_put_contents('debug.log', "deleteComment error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Adds a new code snippet to a lesson.
 *
 * @param int $lessonId The ID of the lesson.
 * @param string $language The programming language of the code.
 * @param string $code The code content.
 * @return int|false The ID of the new code snippet, or false on failure.
 */
function addCode($lessonId, $language, $code) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("INSERT INTO codes (lesson_id, language, code) VALUES (:lesson_id, :language, :code)");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->bindParam(':language', $language, PDO::PARAM_STR); // Added this line
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $db->lastInsertId();
    } catch (Exception $e) {
        file_put_contents('debug.log', "addCode error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Retrieves code snippets for a given lesson ID.
 *
 * @param int $lessonId The ID of the lesson.
 * @return array An array of code snippets.
 */
function getCodes($lessonId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT * FROM codes WHERE lesson_id = :lesson_id ORDER BY id");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getCodes error: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    }
}

/**
 * Deletes a code snippet by its ID.
 *
 * @param int $codeId The ID of the code snippet.
 * @return bool True on success, false on failure.
 */
function deleteCode($codeId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("DELETE FROM codes WHERE id = :code_id");
        $stmt->bindParam(':code_id', $codeId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        file_put_contents('debug.log', "deleteCode error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Updates the status of a lesson.
 *
 * @param int $lessonId The ID of the lesson.
 * @param string $status The new status of the lesson.
 * @return bool True on success, false on failure.
 */
function updateLessonStatus($lessonId, $status) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("UPDATE lessons SET status = :status WHERE id = :lesson_id");
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        file_put_contents('debug.log', "updateLessonStatus error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * Retrieves statistics for a course.
 *
 * @param int $courseId The ID of the course.
 * @return array An associative array containing course statistics.
 */
function getCourseStatistics($courseId) {
    try {
        $db = connectDB();

        // Total lessons
        $stmt = $db->prepare("SELECT COUNT(*) as total_lessons FROM lessons WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $totalLessons = $stmt->fetch(PDO::FETCH_ASSOC)['total_lessons'];

        // Completed lessons
        $stmt = $db->prepare("SELECT COUNT(*) as completed_lessons FROM lessons WHERE course_id = :course_id AND status = 'completed'");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $completedLessons = $stmt->fetch(PDO::FETCH_ASSOC)['completed_lessons'];

        // Incomplete lessons
        $incompleteLessons = $totalLessons - $completedLessons;

        // Statuses
        $stmt = $db->prepare("SELECT DISTINCT status FROM lessons WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Sections
        $stmt = $db->prepare("SELECT DISTINCT s.name FROM sections s JOIN lessons l ON s.id = l.section_id WHERE l.course_id = :course_id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $sections = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'incomplete_lessons' => $incompleteLessons,
            'statuses' => $statuses,
            'sections' => $sections
        ];
    } catch (Exception $e) {
        file_put_contents('debug.log', "getCourseStatistics error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [
            'total_lessons' => 0,
            'completed_lessons' => 0,
            'incomplete_lessons' => 0,
            'statuses' => [],
            'sections' => []
        ];
    }
}

/**
 * Toggles the view status of a lesson (e.g., watched or not watched).
 *
 * @param int $lessonId The ID of the lesson.
 * @return array An associative array containing the result of the operation.
 */
function toggleLessonViewStatus($lessonId) {
    try {
        $db = connectDB();

        // Get current views
        $stmt = $db->prepare("SELECT views FROM lessons WHERE id = :lesson_id");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        $currentViews = $stmt->fetchColumn();

        // Toggle views between 0 and 1
        $newViews = $currentViews == 0 ? 1 : 0;

        // Update views
        $stmt = $db->prepare("UPDATE lessons SET views = :views WHERE id = :lesson_id");
        $stmt->bindParam(':views', $newViews, PDO::PARAM_INT);
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => true,
            'new_views' => $newViews
        ];
    } catch (Exception $e) {
        file_put_contents('debug.log', "toggleLessonViewStatus error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Retrieves the name of a language by its ID.
 *
 * @param int $languageId The ID of the language.
 * @return string The name of the language.
 */
function getLanguageName($languageId) {
    $db = connectDB();
    $stmt = $db->prepare("SELECT name FROM tags WHERE id = :language_id");
    $stmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn();
}

/**
 * Retrieves the name of a section by its ID.
 *
 * @param int $sectionId The ID of the section.
 * @return string The name of the section.
 */
function getSectionName($sectionId) {
    $db = connectDB();
    $stmt = $db->prepare("SELECT name FROM sections WHERE id = :section_id");
    $stmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn();
}

/**
 * Retrieves all sections.
 *
 * @return array An array of sections with their IDs and names.
 */
function getAllSections() {
    $db = connectDB();
    $stmt = $db->prepare("SELECT id, name FROM sections");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Updates the section of a lesson.
 *
 * @param int $lessonId The ID of the lesson.
 * @param int $sectionId The ID of the new section.
 * @return bool True on success, false on failure.
 */
function updateLessonSection($lessonId, $sectionId) {
    $db = connectDB();
    $stmt = $db->prepare("UPDATE lessons SET section_id = :section_id WHERE id = :lesson_id");
    $stmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
    $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Updates the tags of a lesson.
 *
 * @param int $lessonId The ID of the lesson.
 * @param string $tags The new tags for the lesson.
 * @return bool True on success, false on failure.
 */
function updateLessonTags($lessonId, $tags) {
    $db = connectDB();
    $stmt = $db->prepare("UPDATE lessons SET section_tags = :section_tags WHERE id = :lesson_id");
    $stmt->bindParam(':section_tags', $tags, PDO::PARAM_STR);
    $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * Gets the label for a given status code.
 *
 * @param string $status The status code.
 * @return string The label for the status.
 */
function getStatusLabel_($status) {
    switch ($status) {
        case 'completed': return 'مكتمل';
        case 'watch': return 'مشاهدة';
        case 'problem': return 'مشكلة';
        case 'discussion': return 'نقاش';
        case 'search': return 'بحث';
        case 'retry': return 'إعادة';
        case 'retry_again': return 'إعادة مرة أخرى';
        case 'review': return 'مراجعة';
        case 'excluded': return 'مستبعد';
        case 'project': return 'مشروع';
        default: return 'غير محدد';
    }
}

function getStatuses() {
    $db = connectDB();
    $stmt = $db->query("SELECT DISTINCT status FROM lessons");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getSections() {
    $db = connectDB();
    $stmt = $db->query("SELECT * FROM sections");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateLessonStatusOrSection($lessonId, $type, $value) {
    $db = connectDB();
    $column = ($type === 'status') ? 'status' : 'section_id';
    $stmt = $db->prepare("UPDATE lessons SET $column = :value WHERE id = :lesson_id");
    $stmt->bindParam(':value', $value);
    $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
    return $stmt->execute();
}

/**
 * يجلب أقسام درس معين
 * 
 * @param int $lessonId معرف الدرس
 * @return array مصفوفة تحتوي على الأقسام
 */
function getLessonSections($lessonId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("
            SELECT s.id, s.name 
            FROM sections s
            JOIN lessons l ON l.section_id = s.id
            WHERE l.id = :lesson_id
        ");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getLessonSections error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [];
    }
}

/**
 * تحديث أقسام درس معين
 * 
 * @param int $lessonId معرف الدرس
 * @param int $languageId معرف اللغة
 * @param array $sections مصفوفة تحتوي على الأقسام الجديدة
 * @return bool نجاح أو فشل العملية
 */
function updateLessonSections($lessonId, $languageId, $sections) {
    try {
        $db = connectDB();
        $db->beginTransaction();

        // إضافة الأقسام الجديدة
        foreach ($sections as $section) {
            $sectionName = trim($section['value']);
            
            // التحقق من وجود القسم
            $stmt = $db->prepare("SELECT id FROM sections WHERE name = :name AND language_id = :language_id");
            $stmt->bindParam(':name', $sectionName, PDO::PARAM_STR);
            $stmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
            $stmt->execute();
            $existingSection = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingSection) {
                // إضافة قسم جديد
                $stmt = $db->prepare("INSERT INTO sections (name, language_id) VALUES (:name, :language_id)");
                $stmt->bindParam(':name', $sectionName, PDO::PARAM_STR);
                $stmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
                $stmt->execute();
                $sectionId = $db->lastInsertId();
            } else {
                $sectionId = $existingSection['id'];
            }
            
            // تحديث القسم في جدول الدروس
            $stmt = $db->prepare("UPDATE lessons SET section_id = :section_id WHERE id = :lesson_id");
            $stmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
            $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
            $stmt->execute();
        }

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        file_put_contents('debug.log', "updateLessonSections error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

/**
 * يجلب معرفات الدروس المجاورة (السابق والتالي) لدرس معين
 * 
 * @param int $lessonId معرف الدرس الحالي
 * @return array مصفوفة تحتوي على معرفات الدروس المجاورة
 */
function getAdjacentLessons($lessonId) {
    try {
        $db = connectDB();
        
        // الحصول على معرف الكورس للدرس الحالي
        $stmt = $db->prepare("SELECT course_id FROM lessons WHERE id = :lesson_id");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        $courseId = $stmt->fetchColumn();
        
        // الحصول على الدرس السابق
        $stmt = $db->prepare("
            SELECT id FROM lessons 
            WHERE course_id = :course_id AND id < :lesson_id 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        $prevId = $stmt->fetchColumn();
        
        // الحصول على الدرس التالي
        $stmt = $db->prepare("
            SELECT id FROM lessons 
            WHERE course_id = :course_id AND id > :lesson_id 
            ORDER BY id ASC LIMIT 1
        ");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->execute();
        $nextId = $stmt->fetchColumn();
        
        return [
            'prev' => $prevId ?: null,
            'next' => $nextId ?: null
        ];
    } catch (Exception $e) {
        file_put_contents('debug.log', "getAdjacentLessons error: " . $e->getMessage() . "\n", FILE_APPEND);
        return ['prev' => null, 'next' => null];
    }
}
?>