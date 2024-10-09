<?php
// Include the database functions
include_once("show/database.php");

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

<!-- Lesson Title -->
<h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars(string: $lesson['title']); ?></h1>

<!-- Video player -->
<div class="embed-responsive embed-responsive-16by9 mb-4">
    <iframe class="embed-responsive-item w-full h-96" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>" allowfullscreen></iframe>
</div>

<!-- Lesson Information Section -->
<div class="bg-white shadow-sm rounded p-4 mb-4 lesson-info-section">
    <h3 class="text-xl font-bold mb-3">معلومات الدرس</h3>
    <p><strong>اللغة:</strong> <span id="lessonLanguage"><?php echo htmlspecialchars(getLanguageName($lesson['language_id'])); ?></span></p>
    <p><strong>الحالة:</strong> <span id="lessonStatus"><?php echo htmlspecialchars(getStatusLabel($lesson['status'])); ?></span></p>
    <p><strong>المدة:</strong> <span id="lessonDuration"><?php echo htmlspecialchars($lesson['duration']); ?></span></p>
    <p><strong>القسم:</strong> <span id="lessonSection"><?php echo htmlspecialchars(getSectionName($lesson['section_id'])); ?></span></p>
    <p><strong>التصنيفات:</strong> <span id="lessonTags"><?php echo htmlspecialchars($lesson['section_tags']); ?></span></p>
    <p><strong>رابط YouTube:</strong> <a href="<?php echo htmlspecialchars($lesson['url']); ?>" target="_blank">مشاهدة على YouTube</a></p>
    <div class="mt-3">
        <!-- Button to change lesson status -->
        <button class="btn btn-primary me-2" id="changeStatus" data-lesson-id="<?php echo $lessonId; ?>">تغيير الحالة</button>
        <!-- Button to toggle watch status -->
        <button class="btn btn-info me-2" id="watchLesson" data-lesson-id="<?php echo $lessonId; ?>" data-views="<?php echo $lesson['views']; ?>">
            <?php echo $lesson['views'] == 0 ? '<i class="fas fa-eye"></i> مشاهدة' : '<i class="fas fa-check"></i> تمت المشاهدة'; ?>
        </button>
        <!-- Button to change section -->
        <button class="btn btn-secondary me-2" id="changeSection" data-lesson-id="<?php echo $lessonId; ?>">تغيير القسم</button>
        <!-- Button to change tags -->
        <button class="btn btn-secondary me-2" id="changeTags" data-lesson-id="<?php echo $lessonId; ?>">تغيير التصنيفات</button>
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
