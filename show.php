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
                
                // إضافة أزرار التنقل مع تصميم محسن وترتيب معكوس
                echo '<div class="navigation-buttons mt-4 d-flex justify-content-between align-items-center">';
                echo '<button id="nextLesson" class="btn btn-navigation btn-next px-4 py-3">
                        <i class="fas fa-arrow-right ml-2"></i> 
                        <span>الدرس التالي</span>
                      </button>';
                echo '<button id="prevLesson" class="btn btn-navigation btn-prev px-4 py-3">
                        <span>الدرس السابق</span>
                        <i class="fas fa-arrow-left mr-2"></i>
                      </button>';
                echo '</div>';
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

<!-- إضافة CSS مخصص لأزرار التنقل -->
<style>
.btn-navigation {
    font-size: 1.2rem;
    font-weight: bold;
    transition: all 0.3s ease;
    border-radius: 15px;
    min-width: 200px;
    background: linear-gradient(45deg, #2c3e50, #3498db);
    color: white;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn-navigation:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    background: linear-gradient(45deg, #3498db, #2c3e50);
    color: white;
}

.btn-navigation:disabled {
    background: #6c757d;
    transform: none;
    cursor: not-allowed;
}

.btn-navigation i {
    transition: transform 0.3s ease;
}

.btn-next:hover i {
    transform: translateX(-5px);
}

.btn-prev:hover i {
    transform: translateX(5px);
}
</style>

<?php include_once("show/footer.php"); ?>