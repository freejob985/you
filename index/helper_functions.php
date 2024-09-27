<?php

// دالة للحصول على معلومات قائمة التشغيل من YouTube API
function getPlaylistItems($playlistId, $apiKey) {
    $url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId={$playlistId}&key={$apiKey}";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// دالة للحصول على تفاصيل الفيديو من YouTube API
function getVideoDetails($videoId, $apiKey) {
    $url = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$videoId}&key={$apiKey}";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// دالة لتحويل مدة الفيديو من صيغة ISO 8601 إلى صيغة قابلة للقراءة
function formatDuration($duration) {
    $interval = new DateInterval($duration);
    $parts = [];
    
    if ($interval->h > 0) {
        $parts[] = $interval->h . ' ساعة';
    }
    if ($interval->i > 0) {
        $parts[] = $interval->i . ' دقيقة';
    }
    if ($interval->s > 0 || empty($parts)) {
        $parts[] = $interval->s . ' ثانية';
    }
    
    return implode(' و ', $parts);
}

// دالة لإضافة كورس جديد إلى قاعدة البيانات
function addCourse($db, $playlistData, $tags, $languageId) {
    try {
        $db->beginTransaction();

        // إضافة الكورس
        $stmt = $db->prepare("INSERT INTO courses (title, description, thumbnail, playlist_id, language_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $playlistData['items'][0]['snippet']['title'],
            $playlistData['items'][0]['snippet']['description'],
            $playlistData['items'][0]['snippet']['thumbnails']['high']['url'],
            $playlistData['items'][0]['snippet']['playlistId'],
            $languageId
        ]);
        $courseId = $db->lastInsertId();

        // إضافة التاجات
        foreach ($tags as $tag) {
            $stmt = $db->prepare("INSERT OR IGNORE INTO tags (name) VALUES (?)");
            $stmt->execute([$tag]);
            $tagId = $db->lastInsertId();
            if (!$tagId) {
                $stmt = $db->prepare("SELECT id FROM tags WHERE name = ?");
                $stmt->execute([$tag]);
                $tagId = $stmt->fetchColumn();
            }
            $stmt = $db->prepare("INSERT INTO course_tags (course_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$courseId, $tagId]);
        }

        $db->commit();
        return $courseId;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// دالة لإضافة الدروس والأقسام إلى قاعدة البيانات
function addLessonsAndSections($db, $playlistData, $courseId, $apiKey, $languageId) {
    try {
        $db->beginTransaction();

        $currentSectionId = null;
        $lessonOrder = 1;
        $sectionOrder = 1;

        foreach ($playlistData['items'] as $item) {
            $title = $item['snippet']['title'];
            
            // التحقق مما إذا كان العنوان يشير إلى قسم جديد
            if (preg_match('/^القسم\s+(\d+)/i', $title, $matches)) {
                // إنشاء قسم جديد
                $stmt = $db->prepare("INSERT INTO sections (course_id, language_id, title, order_num) VALUES (?, ?, ?, ?)");
                $stmt->execute([$courseId, $languageId, $title, $sectionOrder]);
                $currentSectionId = $db->lastInsertId();
                $sectionOrder++;
                $lessonOrder = 1;
            } else {
                // إذا لم يكن هناك قسم حالي، قم بإنشاء قسم افتراضي
                if ($currentSectionId === null) {
                    $stmt = $db->prepare("INSERT INTO sections (course_id, language_id, title, order_num) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$courseId, $languageId, 'القسم الافتراضي', $sectionOrder]);
                    $currentSectionId = $db->lastInsertId();
                    $sectionOrder++;
                }

                // إضافة الدرس
                $videoId = $item['snippet']['resourceId']['videoId'];
                $videoDetails = getVideoDetails($videoId, $apiKey);
                $duration = formatDuration($videoDetails['items'][0]['contentDetails']['duration']);

                $stmt = $db->prepare("INSERT INTO lessons (section_id, course_id, title, video_id, duration, order_num, language_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$currentSectionId, $courseId, $title, $videoId, $duration, $lessonOrder, $languageId]);
                $lessonOrder++;
            }
        }

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// دالة للحصول على معرف قائمة التشغيل من رابط YouTube
function getPlaylistIdFromUrl($url) {
    $parsedUrl = parse_url($url);
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
        if (isset($queryParams['list'])) {
            return $queryParams['list'];
        }
    }
    return null;
}

// دالة للتحقق من صحة رابط YouTube
function isValidYoutubeUrl($url) {
    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/';
    return preg_match($pattern, $url) === 1;
}

// دالة للحصول على جميع اللغات
function getLanguages($db) {
    $stmt = $db->query("SELECT * FROM tags ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// دالة لإضافة لغة جديدة
function addLanguage($db, $languageName) {
    $stmt = $db->prepare("INSERT INTO tags (name) VALUES (?)");
    $stmt->execute([$languageName]);
    return $db->lastInsertId();
}

// دالة لإضافة أقسام جديدة للغة
function addSectionsToLanguage($db, $languageId, $sectionNames) {
    $stmt = $db->prepare("INSERT INTO sections (language_id, title) VALUES (?, ?)");
    foreach ($sectionNames as $sectionName) {
        $stmt->execute([$languageId, $sectionName]);
    }
}

?>