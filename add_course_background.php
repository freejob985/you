<?php
require_once 'index/php.php';
require_once 'index/helper_functions.php';

header('Content-Type: application/json');

$playlistId = $_POST['courseLink'];
$courseLanguage = $_POST['courseLanguage'];
$apiKey = "AIzaSyDGPD8_t3EAlU4f_pMOGjECkVQr-p3oRvY"; // استبدل بمفتاح API الخاص بك

try {
    // جلب تفاصيل قائمة التشغيل
    $playlistDetails = getPlaylistDetails($playlistId, $apiKey);
    $playlistInfo = array();
    
    if(isset($playlistDetails['items'][0]['snippet'])) {
        $snippet = $playlistDetails['items'][0]['snippet'];
        $playlistInfo = array(
            'title' => $snippet['title'],
            'description' => $snippet['description'],
            'thumbnail' => isset($snippet['thumbnails']['high']['url']) ? $snippet['thumbnails']['high']['url'] : '',
            'channel_title' => $snippet['channelTitle']
        );
    }

    $playlistItemsResponse = getPlaylistItems($playlistId, $apiKey);
    $totalItems = count($playlistItemsResponse['items']);

    // إضافة الكورس إلى قاعدة البيانات
    $stmt = $db->prepare('INSERT INTO courses (title, lessons_count, duration, thumbnail, language_id) VALUES (:title, :lessons_count, :duration, :thumbnail, :language_id)');
    $stmt->bindValue(':title', $playlistInfo['title'], PDO::PARAM_STR);
    $stmt->bindValue(':lessons_count', $totalItems, PDO::PARAM_INT);
    $stmt->bindValue(':duration', 0, PDO::PARAM_INT); // سيتم تحديثه لاحقًا
    $stmt->bindValue(':thumbnail', $playlistInfo['thumbnail'], PDO::PARAM_STR);
    $stmt->bindValue(':language_id', $courseLanguage, PDO::PARAM_INT);
    $stmt->execute();
    $courseId = $db->lastInsertId();

    // إعداد ملف التقدم
    file_put_contents('course_progress.txt', json_encode([
        'progress' => 0,
        'current' => 0,
        'total' => $totalItems,
        'latest_lesson' => '',
        'course_title' => $playlistInfo['title']
    ]));

    // إرسال استجابة النجاح
    echo json_encode(['success' => true, 'message' => "بدأت عملية إضافة الكورس: " . $playlistInfo['title']]);

    // بدء عملية إضافة الدروس في الخلفية
    ignore_user_abort(true);
    set_time_limit(0);

    $totalDuration = 0;

    foreach ($playlistItemsResponse['items'] as $index => $item) {
        $videoId = $item['snippet']['resourceId']['videoId'];
        $title = $item['snippet']['title'];
        $url = "https://www.youtube.com/watch?v=" . $videoId;
        
        $videoResponse = getVideoDetails($videoId, $apiKey);
        $duration = isset($videoResponse['items'][0]['contentDetails']['duration']) ? ISO8601ToSeconds($videoResponse['items'][0]['contentDetails']['duration']) : 0;
        $totalDuration += $duration;
        $thumbnail = isset($videoResponse['items'][0]['snippet']['thumbnails']['high']['url']) ? $videoResponse['items'][0]['snippet']['thumbnails']['high']['url'] : '';

        $stmt = $db->prepare('INSERT INTO lessons (title, url, course_id, duration, status, thumbnail, views, language_id) VALUES (:title, :url, :course_id, :duration, :status, :thumbnail, :views, :language_id)');
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':url', $url, PDO::PARAM_STR);
        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindValue(':duration', $duration, PDO::PARAM_INT);
        $stmt->bindValue(':status', 'active', PDO::PARAM_STR);
        $stmt->bindValue(':thumbnail', $thumbnail, PDO::PARAM_STR);
        $stmt->bindValue(':views', 0, PDO::PARAM_INT);
        $stmt->bindValue(':language_id', $courseLanguage, PDO::PARAM_INT);
        $stmt->execute();

        // تحديث التقدم
        $progress = round(($index + 1) / $totalItems * 100, 2);
        file_put_contents('course_progress.txt', json_encode([
            'progress' => $progress,
            'current' => $index + 1,
            'total' => $totalItems,
            'latest_lesson' => $title,
            'course_title' => $playlistInfo['title']
        ]));

        // تأخير صغير لتجنب تجاوز حد API
        usleep(100000); // 0.1 ثانية
    }

    // تحديث مدة الكورس الإجمالية
    $stmt = $db->prepare('UPDATE courses SET duration = :duration WHERE id = :id');
    $stmt->bindValue(':duration', $totalDuration, PDO::PARAM_INT);
    $stmt->bindValue(':id', $courseId, PDO::PARAM_INT);
    $stmt->execute();

    // إنهاء العملية
    file_put_contents('course_progress.txt', json_encode([
        'progress' => 100,
        'current' => $totalItems,
        'total' => $totalItems,
        'latest_lesson' => 'تم الانتهاء',
        'course_title' => $playlistInfo['title']
    ]));

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "حدث خطأ: " . $e->getMessage()]);
}
