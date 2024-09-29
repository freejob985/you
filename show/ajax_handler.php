<?php
include_once("database.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get_playlist':
                $courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
                $playlistItems = getPlaylistItems($courseId);
                echo json_encode($playlistItems);
                break;
            case 'get_comments':
                $lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
                $comments = getComments($lessonId);
                echo json_encode($comments);
                break;
            case 'get_codes':
                $lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
                $codes = getCodes($lessonId);
                echo json_encode($codes);
                break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_comment':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                $result = addComment($lessonId, $comment);
                echo json_encode(['success' => $result]);
                break;
            case 'add_code':
                $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
                $language = isset($_POST['language']) ? $_POST['language'] : '';
                $code = isset($_POST['code']) ? $_POST['code'] : '';
                $result = addCode($lessonId, $language, $code);
                echo json_encode(['success' => $result]);
                break;
        }
    }
}
