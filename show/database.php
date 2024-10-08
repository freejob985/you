<?php
function connectDB() {
    try {
        $db = new PDO('sqlite:D:\server\htdocs\you\courses.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch(PDOException $e) {
        throw new Exception("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    }
}

function dd(...$variables) {
    $debugTrace = debug_backtrace();
    $file = $debugTrace[0]['file'];
    $line = $debugTrace[0]['line'];

    echo "<strong>Called in: $file on line $line</strong><br>";

    $colors = ['red', 'green', 'blue', 'purple', 'cyan'];
    $colorIndex = 0;

    foreach ($variables as $variable) {
        $currentColor = $colors[$colorIndex % count($colors)];
        echo "<pre style='color: $currentColor'>";
        var_dump($variable);
        echo "</pre>";
        $colorIndex++;
    }

    exit();
}

function getYoutubeVideoId($url) {
    $video_id = '';
    $parsed_url = parse_url($url);
    if (isset($parsed_url['query'])) {
        parse_str($parsed_url['query'], $query_params);
        if (isset($query_params['v'])) {
            $video_id = $query_params['v'];
        }
    } elseif (isset($parsed_url['path'])) {
        $path = explode('/', trim($parsed_url['path'], '/'));
        if (count($path) > 0) {
            $video_id = end($path);
        }
    }
    return $video_id;
}

function getLessonDetails($lessonId) {
    $db = connectDB();
    $stmt = $db->prepare("SELECT * FROM lessons WHERE id = :lesson_id");
    $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCourseDetails($courseId) {
    $db = connectDB();
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = :id");
    $stmt->bindParam(':id', $courseId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPlaylistItems($courseId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT id, title, status FROM lessons WHERE course_id = :course_id ORDER BY id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents('debug.log', "getPlaylistItems result: " . print_r($result, true) . "\n", FILE_APPEND);
        return $result ? $result : [];
    } catch (Exception $e) {
        file_put_contents('debug.log', "getPlaylistItems error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

function addComment($lessonId, $comment) {
    try {
        $db = connectDB();
        file_put_contents('debug.log', "addComment called with lessonId: $lessonId, comment: $comment\n", FILE_APPEND);
        $stmt = $db->prepare("INSERT INTO comments (lesson_id, content) VALUES (:lesson_id, :content)");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->bindParam(':content', $comment, PDO::PARAM_STR);
        $stmt->execute();
        return $db->lastInsertId();
    } catch (Exception $e) {
        file_put_contents('debug.log', "addComment error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

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

function addCode($lessonId, $language, $code) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("INSERT INTO codes (lesson_id, language, code) VALUES (:lesson_id, :language, :code)");
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        $stmt->bindParam(':language', $language, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $db->lastInsertId();
    } catch (Exception $e) {
        file_put_contents('debug.log', "addCode error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

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

// إضافة دالة لتحديث حالة الدرس
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

// إضافة دالة للحصول على إحصائيات الدروس
function getCourseStatistics($courseId) {
    try {
        $db = connectDB();

        // إجمالي الدروس
        $stmt = $db->prepare("SELECT COUNT(*) as total_lessons FROM lessons WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $totalLessons = $stmt->fetch(PDO::FETCH_ASSOC)['total_lessons'];

        // الدروس المكتملة
        $stmt = $db->prepare("SELECT COUNT(*) as completed_lessons FROM lessons WHERE course_id = :course_id AND status = 'completed'");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $completedLessons = $stmt->fetch(PDO::FETCH_ASSOC)['completed_lessons'];

        // الدروس غير المكتملة
        $incompleteLessons = $totalLessons - $completedLessons;

        // الحالات
        $stmt = $db->prepare("SELECT DISTINCT status FROM lessons WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // الأقسام
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

function toggleLessonView($lessonId, $newState) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("UPDATE lessons SET views = :new_state WHERE id = :lesson_id");
        $stmt->bindParam(':new_state', $newState, PDO::PARAM_INT);
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        file_put_contents('debug.log', "toggleLessonView error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

function getSectionName($sectionId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT name FROM sections WHERE id = :section_id");
        $stmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : 'غير معروف';
    } catch (Exception $e) {
        file_put_contents('debug.log', "getSectionName error: " . $e->getMessage() . "\n", FILE_APPEND);
        return 'غير معروف';
    }
}

function getStatusesByLanguage($languageId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT * FROM tags WHERE language_id = :language_id");
        $stmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getStatusesByLanguage error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [];
    }
}

function getSectionsByLanguage($languageId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT * FROM sections WHERE language_id = :language_id");
        $stmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        file_put_contents('debug.log', "getSectionsByLanguage error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [];
    }
}

function updateLessonSection($lessonId, $sectionId) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("UPDATE lessons SET section_id = :section_id WHERE id = :lesson_id");
        $stmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
        $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        file_put_contents('debug.log', "updateLessonSection error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'completed': return 'مكتمل';
        case 'watch': return 'مشاهدة';
        case 'problem': return 'مشكلة';
        case 'discussion': return 'نقاش';
        case 'search': return 'بحث';
        case 'retry': return 'إعادة';
        case 'retry_again': return 'إعادة ثانية';
        case 'review': return 'مراجعة';
        case 'excluded': return 'مستبعد';
        case 'project': return 'مشروع تطبيقي';
        default: return 'غير محدد';
    }
}
?>