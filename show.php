<?php include_once("show/header.php"); ?>

<!-- إضافة jQuery CDN في بداية الصفحة -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>

<!-- إضافة SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <div class="col-md-12 p-4">
            <?php
            if (isset($_GET['lesson_id'])) {
                $lessonId = $_GET['lesson_id'];
                include_once("show/mainContent.php");
                
                // إضافة نموذج الأقسام
                ?>
                <div class="bg-white shadow-sm rounded p-4 mt-4">
                    <h3 class="text-xl font-bold mb-3">
                        إدارة الأقسام
                        <button id="toggleSectionsForm" class="btn btn-sm btn-outline-primary float-left">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </h3>
                    <form id="sectionsForm">
                        <input type="hidden" id="lessonId" value="<?php echo $lessonId; ?>">
                        <div class="mb-3">
                            <label for="sectionTags" class="form-label">الأقسام</label>
                            <input type="text" class="form-control" id="sectionTags" placeholder="أضف الأقسام">
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ الأقسام</button>
                    </form>
                </div>
                <?php
            } else {
                echo "<p>لم يتم تحديد درس للعرض.</p>";
            }
            ?>
            
            <?php include_once("show/commentFormContainer.php");?>
            <?php include_once("show/codeForm.php");?>
        </div>
    </div>
</div>

<!-- إضافة سكربت التعامل مع الأقسام -->
<script>
$(document).ready(function() {
    // تهيئة Tagify للأقسام مع خيارات متقدمة
    var sectionTagsInput = document.querySelector('#sectionTags');
    var tagify = new Tagify(sectionTagsInput, {
        enforceWhitelist: false,
        dropdown: {
            enabled: 1,
            maxItems: 30,
            position: 'text',
            closeOnSelect: false,
            highlightFirst: true
        }
    });
    
    // تحميل الأقسام الحالية والمتاحة
    function loadCurrentSections() {
        var lessonId = $('#lessonId').val();
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'GET',
            data: {
                action: 'get_sections',
                lesson_id: lessonId
            },
            success: function(response) {
                if (response.success) {
                    // تحديث قائمة الاقتراحات
                    tagify.settings.whitelist = response.availableSections;
                    
                    // إضافة الأقسام الحالية
                    tagify.removeAllTags();
                    if (response.sections && response.sections.length) {
                        tagify.addTags(response.sections);
                    }
                }
            }
        });
    }
    
    // تحميل الأقسام عند تحميل الصفحة
    loadCurrentSections();
    
    // معالجة نموذج الأقسام
    $('#sectionsForm').submit(function(e) {
        e.preventDefault();
        var lessonId = $('#lessonId').val();
        var sectionTags = tagify.value.map(tag => tag.value);
        
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: {
                action: 'update_tags',
                lesson_id: lessonId,
                section_tags: JSON.stringify(sectionTags)
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'تم!',
                        text: 'تم حفظ الأقسام بنجاح',
                        icon: 'success',
                        confirmButtonText: 'حسناً'
                    });
                    loadCurrentSections(); // إعادة تحميل الأقسام
                } else {
                    Swal.fire({
                        title: 'خطأ!',
                        text: response.error || 'حدث خطأ أثناء حفظ الأقسام',
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'خطأ!',
                    text: 'حدث خطأ في الاتصال بالخادم',
                    icon: 'error',
                    confirmButtonText: 'حسناً'
                });
            }
        });
    });
    
    // التبديل بين عرض/إخفاء نموذج الأقسام
    $('#toggleSectionsForm').click(function() {
        $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
        $('#sectionsForm').slideToggle();
    });
});
</script>

<?php include_once("show/footer.php"); ?>