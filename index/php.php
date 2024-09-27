<?php
// إنشاء اتصال بقاعدة البيانات SQLite باستخدام PDO
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// إنشاء جدول العلاقة بين التاجات والأقسام
try {
    $db->exec('CREATE TABLE IF NOT EXISTS tag_sections (
        tag_id INTEGER,
        section_id INTEGER,
        FOREIGN KEY (tag_id) REFERENCES tags(id),
        FOREIGN KEY (section_id) REFERENCES sections(id),
        PRIMARY KEY (tag_id, section_id)
    )');
//    echo "تم إنشاء جدول tag_sections بنجاح.";
} catch(PDOException $e) {
    echo "حدث خطأ أثناء إنشاء جدول tag_sections: " . $e->getMessage();
}


// إنشاء جدول التاجات
$db->exec('CREATE TABLE IF NOT EXISTS tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
)');

// إنشاء جدول الكورسات
$db->exec('CREATE TABLE IF NOT EXISTS courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    lessons_count INTEGER,
    duration INTEGER,
    thumbnail TEXT,
    language_id INTEGER,
    FOREIGN KEY (language_id) REFERENCES tags(id)
)');

// إنشاء جدول العلاقة بين الكورسات والتاجات
$db->exec('CREATE TABLE IF NOT EXISTS course_tags (
    course_id INTEGER,
    tag_id INTEGER,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id),
    PRIMARY KEY (course_id, tag_id)
)');

// إنشاء جدول الدروس
$db->exec('CREATE TABLE IF NOT EXISTS lessons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    course_id INTEGER,
    duration INTEGER,
    section_id INTEGER,
    status TEXT,
    thumbnail TEXT,
    views INTEGER DEFAULT 0,
    language_id INTEGER,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (language_id) REFERENCES tags(id)
)');

// إنشاء جدول العلاقة بين الدروس والتاجات
$db->exec('CREATE TABLE IF NOT EXISTS lesson_tags (
    lesson_id INTEGER,
    tag_id INTEGER,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id),
    PRIMARY KEY (lesson_id, tag_id)
)');

// إنشاء جدول الأقسام
$db->exec('CREATE TABLE IF NOT EXISTS sections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    language_id INTEGER,
    FOREIGN KEY (language_id) REFERENCES tags(id)
)');

// حذف جدول اللغات القديم إذا كان موجودًا
$db->exec('DROP TABLE IF EXISTS languages');

function getPlaylistItems($playlistId, $apiKey) {
    $url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId={$playlistId}&key={$apiKey}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function getVideoDetails($videoId, $apiKey) {
    $url = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails,snippet&id={$videoId}&key={$apiKey}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function getPlaylistDetails($playlistId, $apiKey) {
    $url = "https://www.googleapis.com/youtube/v3/playlists?part=snippet&id={$playlistId}&key={$apiKey}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function ISO8601ToSeconds($duration) {
    $interval = new DateInterval($duration);
    return ($interval->d * 24 * 60 * 60) +
           ($interval->h * 60 * 60) +
           ($interval->i * 60) +
           $interval->s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_all':
                try {
                    $db->exec('DELETE FROM lessons');
                    $db->exec('DELETE FROM course_tags');
                    $db->exec('DELETE FROM courses');
                    $db->exec('DELETE FROM tags');
                    $db->exec('DELETE FROM sections');
                    echo json_encode(['success' => true, 'message' => 'تم حذف جميع البيانات بنجاح!']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف البيانات: ' . $e->getMessage()]);
                }
                exit;

            case 'add_sections':
                $languageId = $_POST['languageId'];
                $sectionsTags = json_decode($_POST['sectionsTags'], true);
                
                try {
                    $db->beginTransaction();
                    
                    foreach ($sectionsTags as $section) {
                        $sectionName = trim($section['value']);
                        if (!empty($sectionName)) {
                            $stmt = $db->prepare('INSERT INTO sections (name, language_id) VALUES (:name, :language_id)');
                            $stmt->bindValue(':name', $sectionName, PDO::PARAM_STR);
                            $stmt->bindValue(':language_id', $languageId, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
                    
                    $db->commit();
                    echo json_encode(['success' => true, 'message' => 'تمت إضافة الأقسام بنجاح!']);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة الأقسام: ' . $e->getMessage()]);
                }
                exit;

            case 'add_language':
                $languageTags = json_decode($_POST['languageTags'], true);
                
                try {
                    $db->beginTransaction();
                    $addedLanguages = [];
                    
                    foreach ($languageTags as $language) {
                        $languageName = trim($language['value']);
                        if (!empty($languageName)) {
                            $stmt = $db->prepare('INSERT INTO tags (name) VALUES (:name)');
                            $stmt->bindValue(':name', $languageName, PDO::PARAM_STR);
                            $stmt->execute();
                            $addedLanguages[] = ['id' => $db->lastInsertId(), 'name' => $languageName];
                        }
                    }
                    
                    $db->commit();
                    echo json_encode(['success' => true, 'message' => 'تمت إضافة اللغات بنجاح!', 'languages' => $addedLanguages]);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة اللغات: ' . $e->getMessage()]);
                }
                exit;

            case 'get_languages':
                try {
                    $stmt = $db->query('SELECT id, name FROM tags WHERE id IN (SELECT DISTINCT language_id FROM sections)');
                    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode(['success' => true, 'languages' => $languages]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء جلب اللغات: ' . $e->getMessage()]);
                }
                exit;
        }
    }

    $courseLink = $_POST['courseLink'];
    $courseTags = isset($_POST['courseTags']) ? json_decode($_POST['courseTags'], true) : [];
    $courseLanguage = $_POST['courseLanguage'];
    $apiKey = "AIzaSyDGPD8_t3EAlU4f_pMOGjECkVQr-p3oRvY"; // استبدل بمفتاح API الخاص بك

    // استخراج معرف قائمة التشغيل من الرابط
    preg_match('/list=([^&]+)/', $courseLink, $matches);
    $playlistId = $matches[1] ?? null;

    if ($playlistId) {
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
            $totalDuration = 0;
            $lessonsCount = 0;

            if(isset($playlistItemsResponse['items'])) {
                foreach ($playlistItemsResponse['items'] as $item) {
                    $videoId = $item['snippet']['resourceId']['videoId'];
                    $videoResponse = getVideoDetails($videoId, $apiKey);
                    
                    if (isset($videoResponse['items'][0]['contentDetails']['duration'])) {
                        $duration = $videoResponse['items'][0]['contentDetails']['duration'];
                        $seconds = ISO8601ToSeconds($duration);
                        $totalDuration += $seconds;
                        $lessonsCount++;
                    }
                }
            }

            // إضافة الكورس إلى قاعدة البيانات
            $stmt = $db->prepare('INSERT INTO courses (title, lessons_count, duration, thumbnail, language_id) VALUES (:title, :lessons_count, :duration, :thumbnail, :language_id)');
            $stmt->bindValue(':title', $playlistInfo['title'], PDO::PARAM_STR);
            $stmt->bindValue(':lessons_count', $lessonsCount, PDO::PARAM_INT);
            $stmt->bindValue(':duration', $totalDuration, PDO::PARAM_INT);
            $stmt->bindValue(':thumbnail', $playlistInfo['thumbnail'], PDO::PARAM_STR);
            $stmt->bindValue(':language_id', $courseLanguage, PDO::PARAM_INT);
            $result = $stmt->execute();

            if ($result) {
                $courseId = $db->lastInsertId();

                // إضافة الدروس إلى قاعدة البيانات
                foreach ($playlistItemsResponse['items'] as $item) {
                    $videoId = $item['snippet']['resourceId']['videoId'];
                    $title = $item['snippet']['title'];
                    $url = "https://www.youtube.com/watch?v=" . $videoId;
                    
                    $videoResponse = getVideoDetails($videoId, $apiKey);
                    $duration = isset($videoResponse['items'][0]['contentDetails']['duration']) ? ISO8601ToSeconds($videoResponse['items'][0]['contentDetails']['duration']) : 0;
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
                }

                echo json_encode(['success' => true, 'message' => 'تمت إضافة الكورس بنجاح!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة الكورس.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'رابط قائمة التشغيل غير صالح.']);
    }
}

function getLanguages($db) {
    try {
        $stmt = $db->query('SELECT id, name FROM tags ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}