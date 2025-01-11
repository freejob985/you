<?php
// تضمين الملفات المطلوبة
require_once 'index/php.php';
require_once 'index/helper_functions.php';

// التحقق من وجود معرف الكورس
if (!isset($_GET['course_id'])) {
    die("يجب تحديد معرف الكورس");
}

$courseId = (int)$_GET['course_id'];

// جلب معلومات الكورس
try {
    $stmt = $db->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        die("الكورس غير موجود");
    }
} catch(PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// معالجة الفلترة والبحث
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;

// بناء استعلام SQL
$query = "SELECT l.*, s.name as section_name 
          FROM lessons l 
          LEFT JOIN sections s ON l.section_id = s.id 
          WHERE l.course_id = :course_id";
$params = [':course_id' => $courseId];

if ($search) {
    $query .= " AND l.title LIKE :search";
    $params[':search'] = "%$search%";
}

if ($status) {
    $query .= " AND l.status = :status";
    $params[':status'] = $status;
}

// إجمالي عدد الدروس
$countStmt = $db->prepare(str_replace('l.*, s.name as section_name', 'COUNT(*)', $query));
$countStmt->execute($params);
$totalLessons = $countStmt->fetchColumn();

// إضافة الترتيب والصفحات
$query .= " ORDER BY l.id ASC LIMIT :offset, :limit";
$params[':offset'] = ($page - 1) * $perPage;
$params[':limit'] = $perPage;

// جلب الدروس
try {
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// حساب الإحصائيات
$statsQuery = "SELECT 
    COUNT(*) as total_lessons,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_lessons,
    SUM(duration) as total_duration
FROM lessons 
WHERE course_id = ?";

$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute([$courseId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// حساب عدد الصفحات
$totalPages = ceil($totalLessons / $perPage);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دروس <?php echo htmlspecialchars($course['title']); ?></title>
    
    <!-- تضمين ملفات CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="course_lessons.css" rel="stylesheet">
    <link href="assets/contextMenu.css" rel="stylesheet">
    
    <!-- الخطوط -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <!-- الإحصائيات -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_lessons']; ?></div>
                <div class="stat-label">إجمالي الدروس</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['completed_lessons']; ?></div>
                <div class="stat-label">الدروس المكتملة</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo formatDuration($stats['total_duration']); ?></div>
                <div class="stat-label">المدة الإجمالية</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php echo round(($stats['completed_lessons'] / $stats['total_lessons']) * 100); ?>%
                </div>
                <div class="stat-label">نسبة الإكمال</div>
            </div>
        </div>
        
        <!-- الفلترة والبحث -->
        <div class="filters-container">
            <form method="GET" action="" class="mb-3">
                <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                <div class="filter-group">
                    <input type="text" name="search" class="search-input" 
                           placeholder="ابحث عن درس..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status" class="form-select" style="width: auto;">
                        <option value="">كل الحالات</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                        <option value="watching" <?php echo $status === 'watching' ? 'selected' : ''; ?>>قيد المشاهدة</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                    </select>
                    <button type="submit" class="btn btn-primary">تطبيق</button>
                </div>
            </form>
        </div>
        
        <!-- جدول الدروس -->
        <div class="table-responsive">
            <table class="lessons-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان الدرس</th>
                        <th>القسم</th>
                        <th>المدة</th>
                        <th>الحالة</th>
                        <th>المشاهدات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lessons as $index => $lesson): ?>
                        <tr>
                            <td><?php echo ($page - 1) * $perPage + $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                            <td><?php echo htmlspecialchars($lesson['section_name'] ?? 'بدون قسم'); ?></td>
                            <td><?php echo formatDuration($lesson['duration']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $lesson['status']; ?>">
                                    <?php echo getStatusLabel($lesson['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $lesson['views']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="show.php?lesson_id=<?php echo $lesson['id']; ?>" 
                                       class="action-button btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="action-button btn-success toggle-status" 
                                            data-lesson-id="<?php echo $lesson['id']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-button btn-danger delete-lesson" 
                                            data-lesson-id="<?php echo $lesson['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- التنقل بين الصفحات -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <?php if ($page > 1): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="pagination-button">
                        السابق
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="pagination-button <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="pagination-button">
                        التالي
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- تضمين JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/contextMenu.js"></script>
    
    <script>
        $(document).ready(function() {
            // تبديل حالة الدرس
            $('.toggle-status').click(function() {
                const lessonId = $(this).data('lesson-id');
                const row = $(this).closest('tr');
                
                $.ajax({
                    url: 'lessons_actions.php',
                    method: 'POST',
                    data: {
                        action: 'toggle_status',
                        lesson_id: lessonId
                    },
                    success: function(response) {
                        if (response.success) {
                            // تحديث حالة الدرس في الجدول
                            row.find('.status-badge')
                               .removeClass()
                               .addClass('status-badge status-' + response.new_status)
                               .text(response.status_label);
                               
                            // تحديث الإحصائيات
                            location.reload();
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث حالة الدرس', 'error');
                    }
                });
            });

            // حذف درس
            $('.delete-lesson').click(function() {
                const lessonId = $(this).data('lesson-id');
                const row = $(this).closest('tr');
                
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "لن تتمكن من استرجاع هذا الدرس!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذفه!',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'lessons_actions.php',
                            method: 'POST',
                            data: {
                                action: 'delete_lesson',
                                lesson_id: lessonId
                            },
                            success: function(response) {
                                if (response.success) {
                                    row.fadeOut(400, function() {
                                        $(this).remove();
                                    });
                                    Swal.fire('تم!', 'تم حذف الدرس بنجاح.', 'success');
                                    // تحديث الإحصائيات
                                    location.reload();
                                } else {
                                    Swal.fire('خطأ!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('خطأ!', 'حدث خطأ أثناء حذف الدرس', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html> 