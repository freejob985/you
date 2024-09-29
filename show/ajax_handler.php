<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

header('Content-Type: application/json');

ob_start(); // بدء التخزين المؤقت للمخرجات

try {
    include_once("database.php");

    file_put_contents('debug.log', "Request: " . print_r($_REQUEST, true) . "\n", FILE_APPEND);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'get_playlist':
                    $courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
                    file_put_contents('debug.log', "Course ID: $courseId\n", FILE_APPEND);
                    $playlistItems = getPlaylistItems($courseId);
                    file_put_contents('debug.log', "Playlist Items: " . print_r($playlistItems, true) . "\n", FILE_APPEND);
                    $json_output = json_encode($playlistItems);
                    if ($json_output === false) {
                        file_put_contents('debug.log', "JSON encode error: " . json_last_error_msg() . "\n", FILE_APPEND);
                        ob_end_clean();
                        echo json_encode(['error' => 'Failed to encode JSON']);
                    } else {
                        ob_end_clean();
                        echo $json_output;
                    }
                    break;
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
                    file_put_contents('debug.log', "add_comment action called with lessonId: $lessonId, comment: $comment\n", FILE_APPEND);
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
} catch (Exception $e) {
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    ob_end_clean();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
