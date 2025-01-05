<?php
// Include the database functions
include_once("show/database.php");

// دالة لتنسيق مدة الدرس
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return ($hours > 0 ? $hours . ":" : "") . 
           (($minutes < 10 && $hours > 0) ? "0" : "") . $minutes . ":" . 
           ($secs < 10 ? "0" : "") . $secs;
}

// دالة لتحديد صنف شارة الحالة
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'completed':
            return 'bg-success';
        case 'watch':
        case 'review':
            return 'bg-primary';
        case 'problem':
        case 'retry':
        case 'retry_again':
            return 'bg-warning';
        case 'discussion':
        case 'search':
            return 'bg-info';
        case 'excluded':
            return 'bg-danger';
        case 'project':
            return 'bg-secondary';
        default:
            return 'bg-secondary';
    }
}

// دالة للحصول على تسمية الحالة بالعربية
function getStatusLabel($status) {
    switch ($status) {
        case 'completed':
            return 'مكتمل';
        case 'watch':
            return 'مشاهدة';
        case 'problem':
            return 'مشكلة';
        case 'discussion':
            return 'نقاش';
        case 'search':
            return 'بحث';
        case 'retry':
            return 'إعادة';
        case 'retry_again':
            return 'إعادة ثانية';
        case 'review':
            return 'مراجعة';
        case 'excluded':
            return 'مستبعد';
        case 'project':
            return 'مشروع تطبيقي';
        default:
            return 'غير محدد';
    }
}

// دالة للحصول على لون الحالة
function getStatusColor($status) {
    switch ($status) {
        case 'completed': return '#FFFFFFFF';
        case 'watch':
        case 'review': return '#FCFCFCFF';
        case 'problem':return '#232020FF';
        case 'retry':
        case 'retry_again': return '#FFFFFFFF';
        case 'discussion':
        case 'search': return '#FFFFFFFF';
        case 'excluded': return '#FFFFFFFF';
        case 'project': return '#FFFFFFFF';
        default: return '#FFFFFFFF';
    }
}

// Retrieve the lesson ID from the GET parameters
$lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;

// Fetch lesson details using the lesson ID
$lesson = getLessonDetails($lessonId);

// Check if the lesson exists
if ($lesson) {
    $courseId = $lesson['course_id'];
    $course = getCourseDetails($courseId);
    $playlistItems = getPlaylistItems($courseId);
    $video_id = getYoutubeVideoId($lesson['url']);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?></title>
    <style>
        /* تنعيم الأسكرول */
        html {
            scroll-behavior: smooth;
        }
        
        /* تخصيص شريط التمرير */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* تنسيق صورة البروفايل */
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #3498db;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            border-color: #2c3e50;
        }

        .lesson-header {
            background: linear-gradient(to right, #f8f9fa, white);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .lesson-info {
            flex-grow: 1;
        }

        .lesson-meta {
            font-size: 0.9rem;
        }

        .lesson-meta i {
            color: #3498db;
            margin-right: 5px;
        }

        /* تحسين تنسيق الفيديو */
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* نسبة 16:9 */
            height: 0;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>

<!-- أزرار العودة -->
<div class="mb-4 mt-4">
    <a href="courses.php" class="btn btn-primary me-2">
        <i class="fas fa-arrow-left"></i> العودة إلى الكورسات
    </a>
    <a href="lessons.php?course_id=<?php echo $courseId; ?>" class="btn btn-secondary">
        <i class="fas fa-list"></i> العودة إلى الدروس
    </a>
</div>

<!-- إضافة صورة البروفايل في الهيدر -->
<div class="lesson-header d-flex align-items-center mb-4">
    <div class="profile-image me-3">
        <img src="show/assets/images/HarryPotter_WB_F4_HarryPotterMidshot_Promo_080615_Port.jpg" 
             alt="Profile Image" 
             class="profile-avatar">
    </div>
    <div class="lesson-info">
        <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($lesson['title']); ?></h1>
        <div class="lesson-meta text-muted">
            <span class="me-3"><i class="fas fa-clock"></i> <?php echo $lesson['duration']; ?></span>
            <span><i class="fas fa-eye"></i> <?php echo $lesson['views']; ?> مشاهدة</span>
        </div>
    </div>
</div>

<!-- باقي محتوى الدرس -->
<div class="lesson-content bg-white shadow-sm rounded p-4">
    <!-- محتوى الفيديو -->
    <div class="video-container mb-4">
        <?php if (isset($lesson['url'])): ?>
            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" 
                        src="https://www.youtube.com/embed/<?php echo getYoutubeVideoId($lesson['url']); ?>" 
                        allowfullscreen></iframe>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lesson Information Section -->
<div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white shadow-sm rounded p-4 mb-4 lesson-info-section">
    <h3 class="text-xl font-bold mb-3">معلومات الدرس</h3>
    <p><strong>اللغة:</strong> <span id="lessonLanguage"><?php echo htmlspecialchars(getLanguageName($lesson['language_id'])); ?></span></p>
    <p><strong>الحالة:</strong> 
        <span id="lessonStatus" class="badge <?php echo getStatusBadgeClass($lesson['status']); ?>" 
              style="color: <?php echo getStatusColor($lesson['status']); ?>;">
            <?php echo htmlspecialchars(getStatusLabel($lesson['status'])); ?>
        </span>
    </p>
    <p><strong>المدة:</strong> <span id="lessonDuration"><?php echo formatDuration($lesson['duration']); ?></span></p>
    <p><strong>القسم:</strong> <span id="lessonSection"><?php echo htmlspecialchars(getSectionName($lesson['section_id'])); ?></span></p>
    <!-- <p><strong>التصنيفات:</strong> <span id="lessonTags"><?php echo htmlspecialchars($lesson['section_tags']); ?></span></p> -->
    <p><strong>رابط YouTube:</strong> <a href="<?php echo htmlspecialchars($lesson['url']); ?>" target="_blank" class="text-yellow-300 hover:text-yellow-100">مشاهدة على YouTube</a></p>
    <div class="mt-3">
        <!-- Button to change lesson status -->
        <button class="btn btn-light me-2" id="changeStatus" data-lesson-id="<?php echo $lessonId; ?>">تغيير الحالة</button>
        <!-- Button to toggle watch status -->
        <button class="btn btn-light me-2" id="watchLesson" data-lesson-id="<?php echo $lessonId; ?>" data-views="<?php echo $lesson['views']; ?>">
            <?php echo $lesson['views'] == 0 ? '<i class="fas fa-eye"></i> مشاهدة' : '<i class="fas fa-check"></i> تمت المشاهدة'; ?>
        </button>
        <!-- Button to change section -->
        <button class="btn btn-light me-2" id="changeSection" data-lesson-id="<?php echo $lessonId; ?>">تغيير القسم</button>
        <!-- زر جديد لفتح modal الأقسام -->
        <button class="btn btn-light me-2" id="manageSections" data-lesson-id="<?php echo $lessonId; ?>">
            <i class="fas fa-tags"></i> إدارة الأقسام
        </button>
    </div>
</div>

<!-- نحذف القسم القديم ونضيف Modal الأقسام -->
<div class="modal fade" id="sectionsModal" tabindex="-1" aria-labelledby="sectionsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionsModalLabel">إدارة الأقسام</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sectionsForm">
                    <input type="hidden" id="lessonLanguageId" value="<?php echo htmlspecialchars($lesson['language_id']); ?>">
                    <div class="mb-3">
                        <label for="sectionTags" class="form-label">الأقسام</label>
                        <input type="text" class="form-control" id="sectionTags" placeholder="أضف الأقسام">
                    </div>
                </form>
                
                <div id="currentSections" class="mt-4">
                    <h6 class="font-bold mb-2">الأقسام الحالية</h6>
                    <div class="sections-list">
                        <!-- سيتم إضافة الأقسام هنا ديناميكياً -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" id="saveSections">حفظ التغييرات</button>
            </div>
        </div>
    </div>
</div>

<!-- Modals for changing section and tags -->
<?php include_once("show/sectionModal.php"); ?>
<?php include_once("show/tagModal.php"); ?>

<!-- Add this modal at the end of the file for changing status -->
<div id="statusModal" class="status-modal">
    <div class="status-modal-content">
        <span class="close">&times;</span>
        <div id="statusOptions">
            <!-- Status options will be dynamically added here -->
        </div>
    </div>
</div>

<!-- Rest of your code remains the same -->
<!-- Including the comment form and code form -->
<?php
// Include the comment form container
include_once("show/commentFormContainer.php");

// Include the code form
include_once("show/codeForm.php");
?>

</body>
</html>