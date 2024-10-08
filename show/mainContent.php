<?php
include_once("show/database.php");

$lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;

$lesson = getLessonDetails($lessonId);

if ($lesson) {
    $courseId = $lesson['course_id'];
    $course = getCourseDetails($courseId);
    $playlistItems = getPlaylistItems($courseId);
    $video_id = getYoutubeVideoId($lesson['url']);
}

// دالة لتنسيق مدة الفيديو
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return ($hours > 0 ? $hours . ":" : "") . 
           (($minutes < 10 && $hours > 0) ? "0" : "") . $minutes . ":" . 
           ($secs < 10 ? "0" : "") . $secs;
}
?>

<h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($lesson['title']); ?></h1>

<!-- مشغل الفيديو -->
<div class="embed-responsive embed-responsive-16by9 mb-4">
    <iframe class="embed-responsive-item w-full h-96" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>" allowfullscreen></iframe>
</div>

<!-- معلومات الدرس -->
<div class="bg-white shadow-sm rounded p-4 mb-4">
    <h3 class="text-xl font-bold mb-3">معلومات الدرس</h3>
    <p><strong>اللغة:</strong> <span id="lessonLanguage"><?php echo htmlspecialchars($lesson['language_id']); ?></span></p>
    <p><strong>القسم:</strong> <span id="lessonSection"><?php echo htmlspecialchars(getSectionName($lesson['section_id'])); ?></span></p>
    <p><strong>الحالة:</strong> <span id="lessonTags"><?php echo getStatusLabel($lesson['status']); ?></span></p>
    <p><strong>المدة:</strong> <span id="lessonDuration"><?php echo formatDuration($lesson['duration']); ?></span></p>
    <p><strong>رابط اليوتيوب:</strong> <a href="<?php echo htmlspecialchars($lesson['url']); ?>" target="_blank">شاهد على يوتيوب</a></p>
    <div class="mt-3">
        <button class="btn btn-secondary me-2" id="changeStatus" data-lesson-id="<?php echo $lessonId; ?>">تغيير الحالة</button>
        <button class="btn btn-info me-2" id="changeSection" data-lesson-id="<?php echo $lessonId; ?>">تغيير القسم</button>
        <button class="btn <?php echo $lesson['views'] == 0 ? 'btn-info' : 'btn-success'; ?> me-2" id="watchLesson" data-lesson-id="<?php echo $lessonId; ?>">
            <i class="fas <?php echo $lesson['views'] == 0 ? 'fa-eye' : 'fa-check'; ?>"></i> 
            <span id="watchText"><?php echo $lesson['views'] == 0 ? 'مشاهدة' : 'تمت المشاهدة'; ?></span>
        </button>
    </div>
</div>

<!-- Modal for changing status -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="statusOptions">
            <!-- Status options will be dynamically added here -->
        </div>
    </div>
</div>

<!-- Modal for changing section -->
<div id="sectionModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="sectionOptions">
            <!-- Section options will be dynamically added here -->
        </div>
    </div>
</div>

<?php
// ... (keep all existing code) ...
?>