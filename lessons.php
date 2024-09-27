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

// دالة مساعدة لتنسيق الوقت
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return ($hours > 0 ? $hours . ":" : "") . 
           (($minutes < 10 && $hours > 0) ? "0" : "") . $minutes . ":" . 
           ($secs < 10 ? "0" : "") . $secs;
}

// حساب نسبة إكمال الدروس (افتراضيًا 0%)
$completionPercentage = 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دروس الكورس: <?php echo htmlspecialchars($course['title']); ?></title>
    
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
    
    <!-- مكتبة DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        .progress {
            height: 25px;
        }
        .progress-bar {
            line-height: 25px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <table id="lessonsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>عنوان الدرس</th>
                    <th>المدة</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lessons as $lesson): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                        <td><?php echo formatDuration($lesson['duration']); ?></td>
                        <td><?php echo $lesson['status']; ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($lesson['url']); ?>" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fas fa-play"></i> مشاهدة
                            </a>
                            <button class="btn btn-success btn-sm mark-complete" data-lesson-id="<?php echo $lesson['id']; ?>">
                                <i class="fas fa-check"></i> تم الإكمال
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="mt-4">
            <h4>نسبة إكمال الكورس</h4>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?php echo $completionPercentage; ?>%;" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
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

    <script>
    $(document).ready(function() {
        // تهيئة جدول DataTables
        $('#lessonsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
            }
        });

        // تهيئة توست
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // وظيفة تحديث نسبة الإكمال
        function updateCompletionPercentage() {
            $.ajax({
                url: 'update_completion.php',
                type: 'POST',
                data: { course_id: <?php echo $courseId; ?> },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('.progress-bar').css('width', response.percentage + '%').attr('aria-valuenow', response.percentage).text(response.percentage + '%');
                    }
                }
            });
        }

        // تعليم الدرس كمكتمل
        $('.mark-complete').on('click', function() {
            var lessonId = $(this).data('lesson-id');
            var button = $(this);

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "هل تريد تعليم هذا الدرس كمكتمل؟",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، تم الإكمال',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'mark_lesson_complete.php',
                        type: 'POST',
                        data: { lesson_id: lessonId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                button.prop('disabled', true).text('تم الإكمال');
                                toastr.success('تم تعليم الدرس كمكتمل بنجاح');
                                updateCompletionPercentage();
                            } else {
                                toastr.error('حدث خطأ أثناء تعليم الدرس كمكتمل');
                            }
                        },
                        error: function() {
                            toastr.error('حدث خطأ في الاتصال بالخادم');
                        }
                    });
                }
            });
        });

        // تحديث نسبة الإكمال عند تحميل الصفحة
        updateCompletionPercentage();
    });
    </script>
</body>
</html>