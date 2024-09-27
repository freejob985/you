<?php
// إنشاء اتصال بقاعدة البيانات SQLite باستخدام PDO
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// التحقق من وجود معرف الكورس في الـ URL
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    die("معرف الكورس غير صالح");
}

$courseId = $_GET['course_id'];

// استعلام لجلب معلومات الكورس
$stmtCourse = $db->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmtCourse->bindValue(':course_id', $courseId, PDO::PARAM_INT);
$stmtCourse->execute();
$course = $stmtCourse->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("الكورس غير موجود");
}

// استعلام لجلب الدروس الخاصة بالكورس
$stmtLessons = $db->prepare('SELECT * FROM lessons WHERE course_id = :course_id ORDER BY id ASC');
$stmtLessons->bindValue(':course_id', $courseId, PDO::PARAM_INT);
$stmtLessons->execute();
$lessons = $stmtLessons->fetchAll(PDO::FETCH_ASSOC);

// استعلام لجلب الأقسام
$stmtSections = $db->prepare('SELECT * FROM sections ORDER BY name ASC');
$stmtSections->execute();
$sections = $stmtSections->fetchAll(PDO::FETCH_ASSOC);

// دالة مساعدة لتنسيق الوقت
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return ($hours > 0 ? $hours . ":" : "") . 
           (($minutes < 10 && $hours > 0) ? "0" : "") . $minutes . ":" . 
           ($secs < 10 ? "0" : "") . $secs;
}

// حساب نسبة إكمال الدروس
$totalLessons = count($lessons);
$completedLessons = 0;
foreach ($lessons as $lesson) {
    if ($lesson['status'] === 'completed') {
        $completedLessons++;
    }
}
$completionPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

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

function getStatusColor($status) {
    switch ($status) {
        case 'completed': return '#000000';
        case 'watch':
        case 'review': return '#007bff';
        case 'problem':
        case 'retry':
        case 'retry_again': return '#ffc107';
        case 'discussion':
        case 'search': return '#17a2b8';
        case 'excluded': return '#dc3545';
        case 'project': return '#6c757d';
        default: return '#7E0C0CFF';
    }
}

function getCompletionPercentage($db) {
    $course_id = $_POST['course_id'] ?? null;

    if (!$course_id || !is_numeric($course_id)) {
        echo json_encode(['success' => false, 'message' => 'معرف الكورس غير صالح.']);
        exit;
    }

    try {
        // عدد الدروس الكلي
        $stmtTotal = $db->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = :course_id');
        $stmtTotal->bindValue(':course_id', $course_id, PDO::PARAM_INT);
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        // عدد الدروس المكتملة
        $stmtCompleted = $db->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = :course_id AND status = "completed"');
        $stmtCompleted->bindValue(':course_id', $course_id, PDO::PARAM_INT);
        $stmtCompleted->execute();
        $completed = $stmtCompleted->fetchColumn();

        $percentage = ($total > 0) ? round(($completed / $total) * 100) : 0;

        echo json_encode([
            'success' => true, 
            'percentage' => $percentage, 
            'total_lessons' => $total, 
            'completed_lessons' => $completed
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حساب نسبة الإكمال: ' . $e->getMessage()]);
    }
}

// تحديد عدد الدروس لكل صفحة
$lessonsPerPage = 12;

// حساب عدد الصفحات
$totalPages = ceil(count($lessons) / $lessonsPerPage);

// الحصول على رقم الصفحة الحالية
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));

// حساب الدروس التي سيتم عرضها في الصفحة الحالية
$startIndex = ($currentPage - 1) * $lessonsPerPage;
$currentLessons = array_slice($lessons, $startIndex, $lessonsPerPage);

?>