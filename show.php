<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الدرس</title>
    
    <?php include_once("show/style.php");?>

</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-500 to-purple-600 text-white py-4 shadow-lg">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold text-center">منصة التعلم الإلكتروني</h1>
        </div>
    </header>

    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>
    <div class="sidebar bg-white shadow-sm p-3" id="sidebar">
        <h3 class="text-xl font-bold mb-3">قائمة التشغيل</h3>
        <ul class="list-group" id="playlist">
            <!-- سيتم إضافة عناصر القائمة هنا ديناميكياً -->
        </ul>
    </div>
    
    <div class="container-fluid" id="mainContent">
        <div class="row">
            <!-- المحتوى الرئيسي -->
            <div class="col-md-12 p-4">
                <?php
                if (isset($_GET['lesson_id'])) {
                    $lessonId = $_GET['lesson_id'];
                    include_once("show/mainContent.php");
                } else {
                    echo "<p>لم يتم تحديد درس للعرض.</p>";
                }
                ?>
                
                <!-- التعليقات -->
                <?php include_once("show/commentFormContainer.php");?>
                <?php include_once("show/codeForm.php");?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-purple-600 to-blue-500 text-white py-6 mt-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-center items-center">
                <a href="http://localhost/home/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-home mr-2"></i>
                    <span>الرئيسية</span>
                </a>
                <a href="http://localhost/blackboard/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-chalkboard mr-2"></i>
                    <span>السبورة</span>
                </a>
                <a href="http://localhost/task-ai/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-tasks mr-2"></i>
                    <span>المهام</span>
                </a>
                <a href="http://localhost/info-code/bt.php" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-code mr-2"></i>
                    <span>بنك الأكواد</span>
                </a>
                <a href="http://localhost/administration/public/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-folder mr-2"></i>
                    <span>الملفات</span>
                </a>
                <a href="http://localhost/Columns/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-columns mr-2"></i>
                    <span>الأعمدة</span>
                </a>
                <a href="http://localhost/ask/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-question-circle mr-2"></i>
                    <span>الأسئلة</span>
                </a>
                <a href="http://localhost/phpmyadminx/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-database mr-2"></i>
                    <span>إدارة قواعد البيانات</span>
                </a>
                <a href="http://localhost/pr.php" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-bug mr-2"></i>
                    <span>اصطياد الأخطاء</span>
                </a>
                <a href="http://localhost/Timmy/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-robot mr-2"></i>
                    <span>تيمي</span>
                </a>
                <a href="http://localhost/copy/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-clipboard mr-2"></i>
                    <span>حافظة الملاحظات</span>
                </a>
                <a href="http://localhost/Taskme/" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-calendar-check mr-2"></i>
                    <span>المهام اليومية</span>
                </a>
                <a href="http://subdomain.localhost/tasks" class="flex items-center mx-3 my-2 hover:text-yellow-300 transition duration-300">
                    <i class="fas fa-project-diagram mr-2"></i>
                    <span>CRM</span>
                </a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (مطلوب لـ Toast JS) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Toast JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/7e1mldkbut3yp4tyeob9lt5s57pb8wrb5fqbh11d6n782gm7/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    
    <?php include_once("show/script.php");?>

</body>
</html>