<?php
require_once 'index/php.php';
require_once 'index/helper_functions.php';

$playlistId = $argv[1];
$courseLanguage = $argv[2];
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
    $totalDuration = 0;
    $lessonsCount = 0;

    // إضافة الكورس إلى قاعدة البيانات
    $stmt = $db->prepare('INSERT INTO courses (title, lessons_count, duration, thumbnail, language_id) VALUES (:title, :lessons_count, :duration, :thumbnail, :language_id)');
    $stmt->bindValue(':title', $playlistInfo['title'], PDO::PARAM_STR);
    $stmt->bindValue(':lessons_count', $totalItems, PDO::PARAM_INT);
    $stmt->bindValue(':duration', 0, PDO::PARAM_INT); // سيتم تحديثه لاحقًا
    $stmt->bindValue(':thumbnail', $playlistInfo['thumbnail'], PDO::PARAM_STR);
    $stmt->bindValue(':language_id', $courseLanguage, PDO::PARAM_INT);
    $stmt->execute();
    $courseId = $db->lastInsertId();

    // إضافة الدروس إلى قاعدة البيانات
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
            'latest_lesson' => $title
        ]));

        // تأخير صغير لتجنب تجاوز حد API
        usleep(100000); // 0.1 ثانية
    }

    // تحديث مدة الكورس الإجمالية
    $stmt = $db->prepare('UPDATE courses SET duration = :duration WHERE id = :id');
    $stmt->bindValue(':duration', $totalDuration, PDO::PARAM_INT);
    $stmt->bindValue(':id', $courseId, PDO::PARAM_INT);
    $stmt->execute();

    // إرسال إشعار بنجاح العملية
    file_put_contents('course_added_notification.txt', "تم إضافة الكورس بنجاح: " . $playlistInfo['title']);
} catch (Exception $e) {
    file_put_contents('course_added_notification.txt', "حدث خطأ: " . $e->getMessage());
}
