<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الدرس</title>
    
    <?php include_once("show/style.php");?>

</head>
<body class="bg-gray-100">
    <div id="sidebarToggle">
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