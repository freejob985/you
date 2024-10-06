<?php
// إنشاء اتصال بقاعدة البيانات SQLite باستخدام PDO
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// استعلام لجلب جميع الكورسات مع عدد الدروس المكتملة
$stmt = $db->query('
    SELECT 
        c.*, 
        COUNT(l.id) as completed_lessons,
        (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons
    FROM 
        courses c
    LEFT JOIN 
        lessons l ON c.id = l.course_id AND l.status = "completed"
    GROUP BY 
        c.id
    ORDER BY 
        c.id DESC
');
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دالة مساعدة لتنسيق الوقت
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return ($hours > 0 ? $hours . ":" : "") . 
           (($minutes < 10 && $hours > 0) ? "0" : "") . $minutes . ":" . 
           ($secs < 10 ? "0" : "") . $secs;
}

// استعلام لجلب إحصائيات الكورسات
$statsQuery = $db->query('
    SELECT 
        COUNT(DISTINCT c.id) as total_courses,
        COUNT(DISTINCT l.id) as total_lessons,
        SUM(c.lessons_count) as total_planned_lessons,
        SUM(c.duration) as total_duration,
        SUM(CASE WHEN l.status = "completed" THEN 1 ELSE 0 END) as completed_lessons
    FROM courses c
    LEFT JOIN lessons l ON c.id = l.course_id
');
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

// حساب نسبة الإكمال الإجمالية
$overallCompletionRate = ($stats['completed_lessons'] / $stats['total_lessons']) * 100;
$remainingLessons = $stats['total_lessons'] - $stats['completed_lessons'];

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
            padding-top: 60px; /* لإضافة مساحة للهيدر الثابت */
            padding-bottom: 60px; /* لإضافة مساحة للفوتر الثابت */
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .header {
            background: linear-gradient(45deg, #007bff, #6610f2);
            color: white;
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .footer a {
            color: #ffffff;
            text-decoration: none;
            margin: 0 10px;
        }
        .footer a:hover {
            color: #007bff;
        }
        .progress {
            height: 20px;
        }
        .progress-bar {
            line-height: 20px;
        }
    </style>
</head>
<body>
    <!-- الهيدر الثابت -->
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h4>إجمالي الكورسات: <?php echo $stats['total_courses']; ?></h4>
                </div>
                <div class="col-md-3">
                    <h4>إجمالي الدروس: <?php echo $stats['total_lessons']; ?></h4>
                </div>
                <div class="col-md-3">
                    <h4>نسبة الإكمال: <?php echo number_format($overallCompletionRate, 2); ?>%</h4>
                </div>
                <div class="col-md-3">
                    <h4>الدروس المتبقية: <?php echo $remainingLessons; ?></h4>
                </div>
            </div>
            <!-- إضافة شريط التقدم الإجمالي -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $overallCompletionRate; ?>%;" aria-valuenow="<?php echo $overallCompletionRate; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo number_format($overallCompletionRate, 2); ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-5">
        <h1 class="text-center mb-4">قائمة الكورسات</h1>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <?php 
                    $courseCompletionRate = ($course['completed_lessons'] / $course['total_lessons']) * 100;
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
                            <!-- إضافة شريط التقدم للكورس -->
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $courseCompletionRate; ?>%;" aria-valuenow="<?php echo $courseCompletionRate; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?php echo number_format($courseCompletionRate, 2); ?>%
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

    <!-- الفوتر الثابت -->
    <footer class="footer">
        <div class="container text-center">
            <a href="http://localhost/home/"><i class="fas fa-home"></i> الرئيسية</a>
            <a href="http://localhost/blackboard/"><i class="fas fa-chalkboard"></i> السبورة</a>
            <a href="http://localhost/task-ai/"><i class="fas fa-tasks"></i> المهام</a>
            <a href="http://localhost/info-code/bt.php"><i class="fas fa-code"></i> بنك الأكواد</a>
            <a href="http://localhost/administration/public/"><i class="fas fa-folder"></i> الملفات</a>
            <a href="http://localhost/Columns/"><i class="fas fa-columns"></i> الاعمدة</a>
            <a href="http://localhost/ask/"><i class="fas fa-question-circle"></i> الاسئلة</a>
            <a href="http://localhost/phpmyadminx/"><i class="fas fa-database"></i> ادارة قواعد البيانات</a>
            <a href="http://localhost/pr.php"><i class="fas fa-bug"></i> اصطياد الاخطاء</a>
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