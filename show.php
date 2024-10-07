<?php include_once("show/header.php"); ?>

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

<?php include_once("show/footer.php"); ?>