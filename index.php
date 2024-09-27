<?php
// إنشاء اتصال بقاعدة البيانات SQLite باستخدام PDO
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
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
    thumbnail TEXT
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
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (section_id) REFERENCES sections(id)
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
    $url = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$videoId}&key={$apiKey}";
    
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
    if (isset($_POST['action']) && $_POST['action'] === 'delete_all') {
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
    }

    $courseLink = $_POST['courseLink'];
    $courseTags = json_decode($_POST['courseTags'], true);
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
            $stmt = $db->prepare('INSERT INTO courses (title, lessons_count, duration, thumbnail) VALUES (:title, :lessons_count, :duration, :thumbnail)');
            $stmt->bindValue(':title', $playlistInfo['title'], PDO::PARAM_STR);
            $stmt->bindValue(':lessons_count', $lessonsCount, PDO::PARAM_INT);
            $stmt->bindValue(':duration', $totalDuration, PDO::PARAM_INT);
            $stmt->bindValue(':thumbnail', $playlistInfo['thumbnail'], PDO::PARAM_STR);
            $result = $stmt->execute();

            if ($result) {
                $courseId = $db->lastInsertId();

                // إضافة التاجات
                foreach ($courseTags as $tag) {
                    $tagName = trim($tag['value']);
                    if (!empty($tagName)) {
                        // إضافة التاج إذا لم يكن موجودًا
                        $stmt = $db->prepare('INSERT OR IGNORE INTO tags (name) VALUES (:name)');
                        $stmt->bindValue(':name', $tagName, PDO::PARAM_STR);
                        $stmt->execute();

                        // الحصول على معرف التاج
                        $stmt = $db->prepare('SELECT id FROM tags WHERE name = :name');
                        $stmt->bindValue(':name', $tagName, PDO::PARAM_STR);
                        $stmt->execute();
                        $tagId = $stmt->fetchColumn();

                        // ربط التاج بالكورس
                        $stmt = $db->prepare('INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)');
                        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
                        $stmt->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }

                // إضافة الدروس إلى قاعدة البيانات
                foreach ($playlistItemsResponse['items'] as $item) {
                    $videoId = $item['snippet']['resourceId']['videoId'];
                    $title = $item['snippet']['title'];
                    $url = "https://www.youtube.com/watch?v=" . $videoId;
                    
                    $videoResponse = getVideoDetails($videoId, $apiKey);
                    $duration = isset($videoResponse['items'][0]['contentDetails']['duration']) ? ISO8601ToSeconds($videoResponse['items'][0]['contentDetails']['duration']) : 0;

                    $stmt = $db->prepare('INSERT INTO lessons (title, url, course_id, duration, status) VALUES (:title, :url, :course_id, :duration, :status)');
                    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
                    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
                    $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
                    $stmt->bindValue(':duration', $duration, PDO::PARAM_INT);
                    $stmt->bindValue(':status', 'active', PDO::PARAM_STR);
                    $stmt->execute();

                    $lessonId = $db->lastInsertId();

                    // إضافة التاجات للدرس
                    foreach ($courseTags as $tag) {
                        $tagName = trim($tag['value']);
                        if (!empty($tagName)) {
                            $stmt = $db->prepare('SELECT id FROM tags WHERE name = :name');
                            $stmt->bindValue(':name', $tagName, PDO::PARAM_STR);
                            $stmt->execute();
                            $tagId = $stmt->fetchColumn();

                            if ($tagId) {
                                $stmt = $db->prepare('INSERT INTO lesson_tags (lesson_id, tag_id) VALUES (:lesson_id, :tag_id)');
                                $stmt->bindValue(':lesson_id', $lessonId, PDO::PARAM_INT);
                                $stmt->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
                                $stmt->execute();
                            }
                        }
                    }
                }

                echo json_encode(['success' => true, 'message' => 'تم إضافة الكورس بنجاح!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة الكورس.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'رابط قائمة التشغيل غير صالح.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج إضافة كورسات</title>
    
    <!-- روابط البوتستراب -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- رابط ماتريال ديزاين -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    
    <!-- رابط تيلويند -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- رابط الفونت أوسم -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- الخط المطلوب -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Changa:wght@200..800&display=swap" rel="stylesheet">
    
    <!-- مكتبة توست -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- مكتبة SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- مكتبة Tagify -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-image: url('https://r4.wallpaperflare.com/wallpaper/504/416/967/youtube-geek-science-wallpaper-7816bd8810304c38c0dce17e6862d4ca.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">نموذج إضافة كورسات</h3>
                    </div>
                    <div class="card-body">
                        <form id="courseForm">
                            <div class="mb-3">
                                <label for="courseLink" class="form-label">رابط الكورس (قائمة تشغيل يوتيوب)</label>
                                <input type="url" class="form-control" id="courseLink" name="courseLink" required>
                            </div>
                            <div class="mb-3">
                                <label for="courseTags" class="form-label">تاجات الكورس</label>
                                <input type="text" class="form-control" id="courseTags" name="courseTags">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">إضافة الكورس</button>
                        </form>
                        <div id="loadingContainer" class="mt-3 text-center" style="display: none;">
                            <svg width="50" height="50" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" stroke="#007bff">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="2">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"/>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform
                                                attributeName="transform"
                                                type="rotate"
                                                from="0 18 18"
                                                to="360 18 18"
                                                dur="1s"
                                                repeatCount="indefinite"/>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                            <p class="mt-2">جاري التحميل...</p>
                        </div>
                        <button id="deleteAllData" class="btn btn-danger mt-3 w-100">
                            <i class="fas fa-trash-alt me-2"></i>حذف جميع البيانات
                        </button>
                        <div class="mt-3 d-flex justify-content-between">
                            <a href="courses.php" class="btn btn-outline-primary w-48">
                                <i class="fas fa-list me-2"></i>قائمة الكورسات
                            </a>
                            <a href="search.php" class="btn btn-outline-secondary w-48">
                                <i class="fas fa-search me-2"></i>البحث
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // تهيئة Tagify
    var input = document.querySelector('input[name=courseTags]');
    new Tagify(input);

    document.getElementById('courseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const courseLink = document.getElementById('courseLink').value;
        const courseTags = document.querySelector('input[name=courseTags]').value;
        
        if (courseLink && courseTags) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: 'هل تريد إضافة هذا الكورس؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'نعم، أضف الكورس',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    // إظهار SVG المتحرك
                    document.getElementById('loadingContainer').style.display = 'block';
                    
                    // إرسال البيانات إلى الخادم
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'courseLink': courseLink,
                            'courseTags': courseTags
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // إخفاء SVG المتحرك
                        document.getElementById('loadingContainer').style.display = 'none';
                        
                        if (data.success) {
                            Swal.fire('تم!', data.message, 'success');
                            this.reset();
                            document.querySelector('input[name=courseTags]').value = '';
                        } else {
                            Swal.fire('خطأ!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('خطأ!', 'حدث خطأ أثناء إرسال البيانات.', 'error');
                        // إخفاء SVG المتحرك في حالة الخطأ
                        document.getElementById('loadingContainer').style.display = 'none';
                    });
                }
            });
        } else {
            Swal.fire('خطأ!', 'يرجى ملء جميع الحقول المطلوبة.', 'error');
        }
    });

    document.getElementById('deleteAllData').addEventListener('click', function() {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'سيتم حذف جميع البيانات بشكل نهائي!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف الكل',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'delete_all'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم!', data.message, 'success');
                    } else {
                        Swal.fire('خطأ!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('خطأ!', 'حدث خطأ أثناء حذف البيانات.', 'error');
                });
            }
        });
    });
    </script>
</body>
</html>