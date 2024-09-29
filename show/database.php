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
    $stmt = $db->prepare("SELECT * FROM lessons WHERE lesson_id = :lesson_id");
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
        $stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        file_put_contents('debug.log', "getPlaylistItems result: " . print_r($result, true) . "\n", FILE_APPEND);
        return $result ? $result : [];
    } catch (Exception $e) {
        file_put_contents('debug.log', "getPlaylistItems error: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    }
}

function addComment($lessonId, $comment) {
    $db = connectDB();
    $stmt = $db->prepare("INSERT INTO comments (lesson_id, content) VALUES (:lesson_id, :content)");
    $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
    $stmt->bindParam(':content', $comment, PDO::PARAM_STR);
    return $stmt->execute();
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
        throw $e;
    }
}

function addCode($lessonId, $language, $code) {
    $db = connectDB();
    $stmt = $db->prepare("INSERT INTO codes (lesson_id, language, code) VALUES (:lesson_id, :language, :code)");
    $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
    $stmt->bindParam(':language', $language, PDO::PARAM_STR);
    $stmt->bindParam(':code', $code, PDO::PARAM_STR);
    return $stmt->execute();
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
?>