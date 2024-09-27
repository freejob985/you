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

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دروس الكورس: <?php echo htmlspecialchars($course['title']); ?></title>
    
    <!-- روابط البوتستراب -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- رابط ماتريال ديزاين -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    
    <!-- رابط الفونت أوسم -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- الخط المطلوب -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Changa:wght@200..800&display=swap" rel="stylesheet">
    
    <!-- مكتبة توست -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- مكتبة Tagify -->
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px;
            transition: background-color 0.3s ease;
        }
        .completed-card .card-header {
            background-color: #000000 !important;
        }
        .lesson-title {
            font-weight: bold;
            color: #FFFFFF;
        }
        .completed {
            text-decoration: line-through;
            color: #FFFFFF;
        }
        .card-body {
            padding: 20px;
        }
        .progress {
            height: 25px;
            border-radius: 15px;
        }
        .progress-bar {
            line-height: 25px;
        }
        .badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }
        .btn-group .btn {
            margin-right: 5px;
        }
        .form-check-input {
            cursor: pointer;
        }
        .completed-row {
            background-color: #e8f5e9 !important;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .pagination .page-link {
            color: #007bff;
        }
        .grayscale {
            filter: grayscale(100%);
        }
        .pagination .page-link {
            width: 40px;
            height: 40px;
            text-align: center;
            line-height: 28px;
            font-weight: bold;
            border: 2px solid #007bff;
            color: #007bff;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link,
        .pagination .page-link:hover {
            background-color: #007bff;
            color: #fff;
        }

        .pagination .page-item {
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">إحصائيات الكورس</h5>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completionPercentage; ?>%;" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $completionPercentage; ?>% مكتمل (<?php echo $completedLessons; ?> دروس)
                            </div>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo 100 - $completionPercentage; ?>%;" aria-valuenow="<?php echo 100 - $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo 100 - $completionPercentage; ?>% غير مكتمل (<?php echo $totalLessons - $completedLessons; ?> دروس)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($currentLessons as $lesson): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 <?php echo ($lesson['status'] === 'completed') ? 'completed-card' : ''; ?>">
                        <div class="card-header" style="background-color: <?php echo getStatusColor($lesson['status']); ?>;">
                            <h5 class="card-title mb-0">
                                <span class="lesson-title <?php echo ($lesson['status'] === 'completed') ? 'completed' : ''; ?>" data-lesson-id="<?php echo $lesson['id']; ?>">
                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($lesson['thumbnail'])): ?>
                                <img src="<?php echo htmlspecialchars($lesson['thumbnail']); ?>" alt="Thumbnail" class="img-fluid mb-3 <?php echo ($lesson['status'] === 'completed') ? 'grayscale' : ''; ?>" style="width: 100%; height: auto;">
                            <?php endif; ?>
                            <p class="card-text">المدة: <?php echo formatDuration($lesson['duration']); ?></p>
                            <p class="card-text">
                                <span class="lesson-status badge <?php echo getStatusBadgeClass($lesson['status']); ?>">
                                    <?php echo getStatusLabel($lesson['status']); ?>
                                </span>
                            </p>
                            <div class="btn-group d-flex flex-wrap" role="group">
                          <button class="btn <?php echo ($lesson['views'] > 0) ? 'btn-success' : 'btn-primary'; ?> btn-sm watch-button mb-2" data-lesson-id="<?php echo $lesson['id']; ?>" data-views="<?php echo $lesson['views']; ?>">
    <i class="fas <?php echo ($lesson['views'] > 0) ? 'fa-check' : 'fa-eye'; ?>"></i> <?php echo ($lesson['views'] > 0) ? 'تم المشاهدة' : 'مشاهدة'; ?>
</button>
                             <button class="btn btn-secondary btn-sm assign-section-button mb-2" data-bs-toggle="modal" data-bs-target="#assignSectionModal" data-lesson-id="<?php echo $lesson['id']; ?>">
    <i class="fas fa-layer-group"></i> تعيين القسم
</button>
                           <button class="btn btn-info btn-sm set-status-button mb-2" data-bs-toggle="modal" data-bs-target="#setStatusModal" data-lesson-id="<?php echo $lesson['id']; ?>">
    <i class="fas fa-flag"></i> تحديد الحالة
</button>
                                <button class="btn btn-danger btn-sm delete-lesson-button mb-2" data-lesson-id="<?php echo $lesson['id']; ?>">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input mark-complete-checkbox" type="checkbox" data-lesson-id="<?php echo $lesson['id']; ?>" <?php echo ($lesson['status'] === 'completed') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="flexSwitchCheckDefault">تم الإكمال</label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link rounded-circle" href="?course_id=<?php echo $courseId; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        
        <div class="mt-4">
            <h4>نسبة إكمال الكورس</h4>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: <?php echo $completionPercentage; ?>%;" 
                     aria-valuenow="<?php echo $completionPercentage; ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <?php echo $completionPercentage; ?>%
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="courses.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>العودة إلى قائمة الكورسات
            </a>
        </div>
    </div>

    <!-- مودال لتحديد القسم -->
<!-- مودال لتحديد القسم -->
<div class="modal fade" id="assignSectionModal" tabindex="-1" aria-labelledby="assignSectionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="assignSectionModalLabel">تعيين القسم للدرس</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <select id="sectionSelect" class="form-select">
          <?php foreach ($sections as $section): ?>
            <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="button" class="btn btn-primary" id="confirmAssignSection">تأكيد</button>
      </div>
    </div>
  </div>
</div>

    <!-- مودال لتحديد الحالة -->
<!-- مودال لتحديد الحالة -->
<div class="modal fade" id="setStatusModal" tabindex="-1" aria-labelledby="setStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="setStatusModalLabel">تحديد حالة الدرس</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <select id="statusSelect" class="form-select" multiple>
          <option value="watch">مشاهدة</option>
          <option value="problem">مشكلة</option>
          <option value="discussion">نقاش</option>
          <option value="search">بحث</option>
          <option value="retry">إعادة</option>
          <option value="retry_again">إعادة ثانية</option>
          <option value="review">مراجعة</option>
          <option value="completed">مكتمل</option>
          <option value="excluded">مستبعد</option>
          <option value="project">مشروع تطبيقي</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="button" class="btn btn-primary" id="confirmSetStatus">تأكيد</button>
      </div>
    </div>
  </div>
</div>
    <!-- روابط جافا سكريبت -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        // تهيئة توست
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // دالة لتحديث حالة الدرس في الواجهة
        function updateLessonStatus(lessonId, status) {
            const lessonCard = $(`.card:has([data-lesson-id="${lessonId}"])`);
            const lessonTitle = lessonCard.find('.lesson-title');
            const statusBadge = lessonCard.find('.lesson-status');
            const thumbnail = lessonCard.find('img');
            const cardHeader = lessonCard.find('.card-header');

            lessonTitle.toggleClass('completed', status === 'completed');
            statusBadge.removeClass().addClass(`lesson-status badge ${getStatusBadgeClass(status)}`);
            statusBadge.text(getStatusLabel(status));
            
            if (status === 'completed') {
                lessonCard.addClass('completed-card');
                thumbnail.addClass('grayscale');
                cardHeader.css('background-color', '#000000');
            } else {
                lessonCard.removeClass('completed-card');
                thumbnail.removeClass('grayscale');
                cardHeader.css('background-color', getStatusColor(status));
            }

            // تحديث حالة الـ checkbox
            lessonCard.find('.mark-complete-checkbox').prop('checked', status === 'completed');

            // تحديث نسبة الإكمال
            updateCompletionPercentage();
        }

        // دالة لتحديث نسبة إكمال الكورس
        function updateCompletionPercentage() {
            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'get_completion_percentage',
                    course_id: <?php echo $courseId; ?>
                },
                success: function(response) {
                    if (response.success) {
                        const completedPercentage = response.percentage;
                        const remainingPercentage = 100 - completedPercentage;
                        
                        $('.progress-bar.bg-success').css('width', completedPercentage + '%')
                            .attr('aria-valuenow', completedPercentage)
                            .text(completedPercentage + '% مكتمل');
                        $('.progress-bar.bg-primary').css('width', remainingPercentage + '%')
                            .attr('aria-valuenow', remainingPercentage)
                            .text(remainingPercentage + '% غير مكتمل');
                        
                        $('.completed-lessons').text(response.completed_lessons);
                        $('.remaining-lessons').text(response.total_lessons - response.completed_lessons);
                    }
                }
            });
        }

        // تحديث حدث النقر على زر المشاهدة




// تحديث حدث النقر على زر المشاهدة
// تحديث حدث النقر على زر المشاهدة
$('.watch-button').click(function() {
    const lessonId = $(this).data('lesson-id');
    const views = $(this).data('views');
    const newViews = views === 0 ? 1 : 0;
    const button = $(this);
    const checkbox = button.closest('.card').find('.mark-complete-checkbox');

    $.ajax({
        url: 'lessons_actions.php',
        method: 'POST',
        data: {
            action: 'toggle_watch',
            lesson_id: lessonId,
            views: newViews
        },
        success: function(response) {
            if (response.success) {
                button.data('views', newViews);
                button.removeClass('btn-primary btn-success')
                      .addClass(newViews > 0 ? 'btn-success' : 'btn-primary');
                button.html(`<i class="fas ${newViews > 0 ? 'fa-check' : 'fa-eye'}"></i> ${newViews > 0 ? 'تم المشاهدة' : 'مشاهدة'}`);
                
                checkbox.prop('checked', newViews > 0);
                
                updateLessonStatus(lessonId, newViews > 0 ? 'completed' : 'active');
                toastr.success('تم تحديث حالة الدرس');
                
                // تحديث نسبة الإكمال
                updateCompletionPercentage();
            } else {
                toastr.error(response.message);
            }
        }
    });
});

        // تحديث حدث تغيير صندوق الاختيار
        $('.mark-complete-checkbox').change(function() {
            const lessonId = $(this).data('lesson-id');
            const completed = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'mark_complete',
                    lesson_id: lessonId,
                    completed: completed
                },
                success: function(response) {
                    if (response.success) {
                        updateLessonStatus(lessonId, completed ? 'completed' : 'active');
                        toastr.success('تم تحديث حالة الإكمال');
                        
                        // تحديث زر المشاهدة
                        const watchButton = $(`.watch-button[data-lesson-id="${lessonId}"]`);
                        watchButton.data('views', completed ? 1 : 0);
                        watchButton.removeClass('btn-primary btn-success')
                                   .addClass(completed ? 'btn-success' : 'btn-primary');
                        watchButton.html(`<i class="fas ${completed ? 'fa-check' : 'fa-eye'}"></i> ${completed ? 'تم المشاهدة' : 'مشاهدة'}`);
                        
                        // تحديث نسبة الإكمال
                        updateCompletionPercentage();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        $('.assign-section-button').click(function() {
            const lessonId = $(this).data('lesson-id');
            $('#assignSectionModal').data('lesson-id', lessonId).modal('show');
        });

        // تأكيد تعيين القسم
        $('#confirmAssignSection').click(function() {
            const lessonId = $('#assignSectionModal').data('lesson-id');
            const sectionId = $('#sectionSelect').val();

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'assign_section',
                    lesson_id: lessonId,
                    section_id: sectionId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('تم تعيين القسم بنجاح');
                        $('#assignSectionModal').modal('hide');
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // زر تحديد الحالة
        $('.set-status-button').click(function() {
            const lessonId = $(this).data('lesson-id');
            $('#setStatusModal').data('lesson-id', lessonId).modal('show');
        });

        // تأكيد تحديد الحالة
        $('#confirmSetStatus').click(function() {
            const lessonId = $('#setStatusModal').data('lesson-id');
            const status = $('#statusSelect').val().join(',');

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'set_status',
                    lesson_id: lessonId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        updateLessonStatus(lessonId, status);
                        toastr.success('تم تحديد الحالة بنجاح');
                        $('#setStatusModal').modal('hide');
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // تحديد الإكمال باستخدام الـ checkbox
        $('.mark-complete-checkbox').change(function() {
            const lessonId = $(this).data('lesson-id');
            const completed = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'mark_complete',
                    lesson_id: lessonId,
                    completed: completed
                },
                success: function(response) {
                    if (response.success) {
                        updateLessonStatus(lessonId, completed ? 'completed' : 'active');
                        toastr.success('تم تحديث حالة الإكمال');
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // زر حذف الدرس
        $('.delete-lesson-button').click(function() {
            const lessonId = $(this).data('lesson-id');
            const lessonCard = $(this).closest('.card');

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن هذا الإجراء!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف الدرس',
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
                                lessonCard.remove();
                                updateCompletionPercentage();
                                toastr.success('تم حذف الدرس بنجاح');
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            });
        });

        // دوال مساعدة لتحديد لون وتسمية الحالة
        function getStatusBadgeClass(status) {
            switch (status) {
                case 'completed': return 'bg-success';
                case 'watch':
                case 'review': return 'bg-primary';
                case 'problem':
                case 'retry':
                case 'retry_again': return 'bg-warning';
                case 'discussion':
                case 'search': return 'bg-info';
                case 'excluded': return 'bg-danger';
                case 'project': return 'bg-secondary';
                default: return 'bg-secondary';
            }
        }

        function getStatusLabel(status) {
            switch (status) {
                case 'completed': return 'مكتمل';
                case 'watch': return 'مشاهدة';
                case 'problem': return 'مشكلة';
                case 'discussion': return 'نقاش';
                case 'search': return 'بحث';
                case 'retry': return 'إعادة';
                case 'retry_again': return 'إعادة ثانية';
                case 'review': return 'مراجعة';
                case 'excluded': return 'مستبعد';
                case 'project': return 'مشروع تطبيقي';
                default: return 'غير محدد';
            }
        }

        function getStatusColor(status) {
            switch (status) {
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

        // تحديث البروجرس بار عند تحميل الصفحة
        updateProgressBar();

        // تحديث البروجرس بار عند تغيير حالة الدرس
        $('.mark-complete-checkbox, .watch-button').on('change click', function() {
            updateProgressBar();
        });
    });
    </script>
</body>
</html>