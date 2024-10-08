<?php
include_once("show/database.php");

$lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;

$lesson = getLessonDetails($lessonId);



// print_r($lessonId);
if ($lesson) {
    $courseId = $lesson['course_id'];
    $course = getCourseDetails($courseId);
    $playlistItems = getPlaylistItems($courseId);
// dd($playlistItems);
    // استخراج معرف الفيديو من الرابط
    $video_id = getYoutubeVideoId($lesson['url']);
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
    <p><strong>الحالة:</strong> <span id="lessonTags"><?php echo htmlspecialchars($lesson['status']); ?></span></p>
    <p><strong>معلومات إضافية:</strong> <span id="lessonInfo"><?php echo htmlspecialchars($lesson['duration']); ?></span></p>
    <p><strong>رابط اليوتيوب:</strong> <a href="<?php echo htmlspecialchars($lesson['url']); ?>" target="_blank">شاهد على يوتيوب</a></p>
    <div class="mt-3">
        <button class="btn btn-primary me-2" id="editLesson" data-lesson-id="<?php echo $lessonId; ?>">تعديل</button>
        <button class="btn btn-secondary me-2" id="changeStatus" data-lesson-id="<?php echo $lessonId; ?>">تغيير الحالة</button>
        <button class="btn btn-info" id="watchLesson" data-lesson-id="<?php echo $lessonId; ?>">مشاهدة</button>
        <button class="btn btn-info" id="changeStatus" data-lesson-id="<?php echo $lessonId; ?>">تغيير الحالة</button>
    </div>
</div>

<!-- Add this modal at the end of the file -->
<div id="statusModal" class="status-modal">
    <div class="status-modal-content">
        <span class="close">&times;</span>
        <h2>تغيير حالة الدرس</h2>
        <div id="statusOptions">
            <!-- Status options will be dynamically added here -->
        </div>
    </div>
</div>

<?php
// ... (keep all existing code) ...
?>