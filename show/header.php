<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الدرس</title>
    <?php include_once("style.php"); ?>
    <style>
        .header {
            background: linear-gradient(45deg, #4a90e2, #63b3ed);
            color: white;
            padding: 1rem;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="header">
        <h1 class="text-3xl font-bold">منصة التعلم الإلكتروني</h1>
    </header>
    <!-- أضف هذا الكود بعد عنوان الهيدر -->
    <nav class="bg-white shadow-sm mt-4">
        <div class="container mx-auto">
            <ul class="flex justify-center space-x-4 p-4">
                <li><a href="http://localhost/home/" class="text-blue-600 hover:text-blue-800"><i class="fas fa-home"></i> الرئيسية</a></li>
                <li><a href="http://localhost/blackboard/" class="text-green-600 hover:text-green-800"><i class="fas fa-chalkboard"></i> السبورة</a></li>
                <li><a href="http://localhost/task-ai/" class="text-red-600 hover:text-red-800"><i class="fas fa-tasks"></i> المهام</a></li>
                <li><a href="http://localhost/info-code/bt.php" class="text-purple-600 hover:text-purple-800"><i class="fas fa-code"></i> بنك الأكواد</a></li>
                <li><a href="http://localhost/administration/public/" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-folder"></i> الملفات</a></li>
                <li><a href="http://localhost/Columns/" class="text-indigo-600 hover:text-indigo-800"><i class="fas fa-columns"></i> الأعمدة</a></li>
                <li><a href="http://localhost/ask/" class="text-pink-600 hover:text-pink-800"><i class="fas fa-question-circle"></i> الأسئلة</a></li>
                <li><a href="http://localhost/phpmyadminx/" class="text-gray-600 hover:text-gray-800"><i class="fas fa-database"></i> إدارة قواعد البيانات</a></li>
                <li><a href="http://localhost/pr.php" class="text-orange-600 hover:text-orange-800"><i class="fas fa-bug"></i> اصطياد الأخطاء</a></li>
                <li><a href="http://localhost/Timmy/" class="text-teal-600 hover:text-teal-800"><i class="fas fa-clock"></i> تيمي</a></li>
                <li><a href="http://localhost/copy/" class="text-blue-400 hover:text-blue-600"><i class="fas fa-clipboard"></i> حافظة الملاحظات</a></li>
                <li><a href="http://localhost/Taskme/" class="text-green-400 hover:text-green-600"><i class="fas fa-calendar-check"></i> المهام اليومية</a></li>
                <li><a href="http://subdomain.localhost/tasks" class="text-red-400 hover:text-red-600"><i class="fas fa-project-diagram"></i> CRM</a></li>
            </ul>
        </div>
    </nav>