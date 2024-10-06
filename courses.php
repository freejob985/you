<?php
// إنشاء اتصال بقاعدة البيانات SQLite باستخدام PDO
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// استعلام لجلب جميع الكورسات
$stmt = $db->query('SELECT * FROM courses ORDER BY id DESC');
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إضافة استعلام لحساب إجمالي عدد الدروس وعدد الدروس المكتملة
$stmt = $db->query('SELECT COUNT(*) as total_lessons, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons FROM lessons');
$lessonStats = $stmt->fetch(PDO::FETCH_ASSOC);
$totalLessons = $lessonStats['total_lessons'];
$completedLessons = $lessonStats['completed_lessons'];

// دالة مساعدة لحساب النسبة المئوية
function calculatePercentage($completed, $total) {
    return $total > 0 ? round(($completed / $total) * 100) : 0;
}

// حساب النسبة المئوية الإجمالية
$overallPercentage = calculatePercentage($completedLessons, $totalLessons);

// دالة مساعدة لتنسيق الوقت
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return ($hours > 0 ? $hours . ":" : "") . 
           (($minutes < 10 && $hours > 0) ? "0" : "") . $minutes . ":" . 
           ($secs < 10 ? "0" : "") . $secs;
}

// معالجة طلب AJAX لحذف الكورس
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_course') {
    $courseId = $_POST['courseId'];
    try {
        $db->beginTransaction();
        
        // حذف الدروس المرتبطة بالكورس
        $stmt = $db->prepare('DELETE FROM lessons WHERE course_id = :course_id');
        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        
        // حذف العلاقات بين الكورس والتاجات
        $stmt = $db->prepare('DELETE FROM course_tags WHERE course_id = :course_id');
        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        
        // حذف الكورس نفسه
        $stmt = $db->prepare('DELETE FROM courses WHERE id = :course_id');
        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'تم حذف الكورس بنجاح']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف الكورس: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة الكورسات</title>
    
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
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        
        /* أنماط إضافية لشريط التقدم */
        .progress {
            height: 20px;
            margin-bottom: 10px;
        }
        .progress-bar {
            line-height: 20px;
        }
        
        /* أنماط محدثة للتذييل الثابت */
        .footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background-color: #f8f9fa;
            color: black;
            text-align: center;
            padding: 10px 0;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        }
        .footer .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .footer a {
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            margin: 5px;
            border-radius: 5px;
            transition: background-color 0.3s;
            flex-grow: 1;
            text-align: center;
        }
        .footer a:hover {
            opacity: 0.8;
        }
        
        /* ألوان مميزة للأزرار في الفوتر */
        .footer a:nth-child(1) { background-color: #007bff; }
        .footer a:nth-child(2) { background-color: #28a745; }
        .footer a:nth-child(3) { background-color: #dc3545; }
        .footer a:nth-child(4) { background-color: #ffc107; }
        .footer a:nth-child(5) { background-color: #17a2b8; }
        .footer a:nth-child(6) { background-color: #6610f2; }
        .footer a:nth-child(7) { background-color: #fd7e14; }
        .footer a:nth-child(8) { background-color: #20c997; }
        .footer a:nth-child(9) { background-color: #e83e8c; }
        .footer a:nth-child(10) { background-color: #6f42c1; }
        .footer a:nth-child(11) { background-color: #795548; }
        .footer a:nth-child(12) { background-color: #343a40; }
        .footer a:nth-child(13) { background-color: #f8f9fa; color: #000; }
        
        /* تعديل للمحتوى الرئيسي لإضافة هامش سفلي */
        .container {
            margin-bottom: 80px;
        }

        /* تنعيم الاسكرول */
        html {
            scroll-behavior: smooth;
        }

.footer {
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100%;
    background-color: #f8f9fa;
    color: black;
    text-align: center;
    padding: 27px 0;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
}
.footer a:nth-child(13) {
    background-color: #007bff;
    color: #ffffff;
}
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">قائمة الكورسات</h1>
        
        <!-- إضافة شريط التقدم الإجمالي -->
        <div class="progress mb-4">
            <div class="progress-bar" role="progressbar" style="width: <?php echo $overallPercentage; ?>%;" 
                 aria-valuenow="<?php echo $overallPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                <?php echo $overallPercentage; ?>% مكتمل
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <?php
                // حساب نسبة الإكمال لكل كورس
                $stmt = $db->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed FROM lessons WHERE course_id = :course_id');
                $stmt->bindValue(':course_id', $course['id'], PDO::PARAM_INT);
                $stmt->execute();
                $courseStats = $stmt->fetch(PDO::FETCH_ASSOC);
                $coursePercentage = calculatePercentage($courseStats['completed'], $courseStats['total']);
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text">
                                <i class="fas fa-book-open me-2"></i> عدد الدروس: <?php echo $course['lessons_count']; ?><br>
                                <i class="fas fa-clock me-2"></i> المدة الإجمالية: <?php echo formatDuration($course['duration']); ?>
                            </p>
                            <!-- إضافة شريط التقدم لكل كورس -->
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $coursePercentage; ?>%;" 
                                     aria-valuenow="<?php echo $coursePercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?php echo $coursePercentage; ?>% مكتمل
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <button class="btn btn-danger btn-sm delete-course" data-course-id="<?php echo $course['id']; ?>">
                                <i class="fas fa-trash-alt"></i> حذف الكورس
                            </button>
                            <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm ms-2">
                                <i class="fas fa-list-ul"></i> عرض الدروس
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>إضافة كورس جديد
            </a>
        </div>
    </div>

    <!-- التذييل المحدث -->
    <footer class="footer">
    <div class="container-fluid">
            <a href="http://localhost/home/"><i class="fas fa-home"></i> الرئيسية</a>
            <a href="http://localhost/blackboard/"><i class="fas fa-chalkboard"></i> السبورة</a>
            <a href="http://localhost/task-ai/"><i class="fas fa-tasks"></i> المهام</a>
            <a href="http://localhost/info-code/bt.php"><i class="fas fa-code"></i> بنك الأكواد</a>
            <a href="http://localhost/administration/public/"><i class="fas fa-folder"></i> الملفات</a>
            <a href="http://localhost/Columns/"><i class="fas fa-columns"></i> الأعمدة</a>
            <a href="http://localhost/ask/"><i class="fas fa-question-circle"></i> الأسئلة</a>
            <a href="http://localhost/phpmyadminx/"><i class="fas fa-database"></i> إدارة قواعد البيانات</a>
            <a href="http://localhost/pr.php"><i class="fas fa-bug"></i> اصطياد الأخطاء</a>
            <a href="http://localhost/Timmy/"><i class="fas fa-robot"></i> تيمي</a>
            <a href="http://localhost/copy/"><i class="fas fa-clipboard"></i> حافظة الملاحظات</a>
            <a href="http://localhost/Taskme/"><i class="fas fa-calendar-check"></i> المهام اليومية</a>
            <a href="http://subdomain.localhost/tasks"><i class="fas fa-project-diagram"></i> CRM</a>
        </div>
    </footer>

    <script>
    $(document).ready(function() {
        // تهيئة توست
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // حذف الكورس
        $('.delete-course').on('click', function() {
            var courseId = $(this).data('course-id');
            var card = $(this).closest('.col-md-4');

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من استرجاع هذا الكورس!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، احذفه!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'courses.php',
                        type: 'POST',
                        data: {
                            action: 'delete_course',
                            courseId: courseId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                card.fadeOut(300, function() { $(this).remove(); });
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('حدث خطأ أثناء حذف الكورس');
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>