<script>
$(document).ready(function() {
    const lessonId = <?php echo $lessonId; ?>;

    // تهيئة Toast
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // دالة لإضافة عناصر لقائمة التشغيل
    function addPlaylistItem(title, lessonId, isActive) {
        const activeClass = isActive ? 'active' : '';
        $('#playlist').append(`
            <li class="list-group-item cursor-pointer ${activeClass}" data-lesson-id="${lessonId}">
                ${title}
            </li>
        `);
    }

    // دالة لإضافة تعليق
    function addComment(comment, date) {
        $('#comments').prepend(`
            <div class="bg-gray-50 p-3 rounded mb-2">
                <p>${comment}</p>
                <small class="text-muted">${date}</small>
            </div>
        `);
    }

    // دالة لإضافة كود برمجي
    function addCodeExample(language, code) {
        const codeId = 'code-' + Math.random().toString(36).substr(2, 9);
        $('#codeExamples').append(`
            <div class="code-block mb-4">
                <h4 class="text-lg font-semibold mb-2 text-white">${language}</h4>
                <pre><code class="language-${language}" id="${codeId}">${code}</code></pre>
            </div>
        `);
        hljs.highlightElement(document.getElementById(codeId));
    }

    // جلب قائمة التشغيل
    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: { action: 'get_playlist', course_id: <?php echo $courseId; ?> },
        dataType: 'json',
        success: function(response) {
            console.log('Raw response:', response);
            if (Array.isArray(response) && response.length > 0) {
                response.forEach(item => {
                    addPlaylistItem(item.title, item.id, item.id == lessonId);
                });
            } else {
                console.log('No playlist items returned');
                toastr.warning('لا توجد عناصر في قائمة التشغيل');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
            console.log('Status:', jqXHR.status);
            console.log('Status Text:', jqXHR.statusText);
            toastr.error('حدث خطأ أثناء جلب البيانات');
        }
    });

    // التعامل مع النقر على عناصر قائمة التشغيل
    $('#playlist').on('click', 'li', function() {
        const clickedLessonId = $(this).data('lesson-id');
        window.location.href = `show.php?id=${clickedLessonId}`;
    });

    // التعامل مع إرسال نموذج التعليق
    $('#commentForm').submit(function(e) {
        e.preventDefault();
        const comment = $('#comment').val();

        // التحقق من صحة البيانات
        if (comment.trim() === '') {
            Swal.fire({
                title: 'خطأ!',
                text: 'يرجى كتابة تعليق قبل الإرسال.',
                icon: 'error',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // إرسال التعليق إلى الخادم
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { action: 'add_comment', lesson_id: lessonId, comment: comment },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    addComment(comment, 'الآن');
                    toastr.success('تم إضافة التعليق بنجاح');
                    $('#commentForm')[0].reset();
                } else {
                    toastr.error('حدث خطأ أثناء إضافة التعليق');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء إضافة التعليق');
            }
        });
    });

    // التعامل مع إرسال نموذج الكود
    $('#codeForm').submit(function(e) {
        e.preventDefault();
        const language = $('#language').val();
        const code = $('#code').val();

        // التحقق من صحة البيانات
        if (code.trim() === '') {
            Swal.fire({
                title: 'خطأ!',
                text: 'يرجى كتابة الكود قبل الإرسال.',
                icon: 'error',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // إرسال الكود إلى الخادم
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { action: 'add_code', lesson_id: lessonId, language: language, code: code },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    addCodeExample(language, code);
                    toastr.success('تم إضافة الكود بنجاح');
                    $('#codeForm')[0].reset();
                } else {
                    toastr.error('حدث خطأ أثناء إضافة الكود');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء إضافة الكود');
            }
        });
    });

    // جلب التعليقات الحالية
    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: { action: 'get_comments', lesson_id: lessonId },
        dataType: 'json',
        success: function(response) {
            console.log('Raw response:', response);
            if (Array.isArray(response)) {
                response.forEach(comment => {
                    addComment(comment.content, comment.created_at);
                });
            } else {
                console.log('No comments returned');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
            console.log('Status:', jqXHR.status);
            console.log('Status Text:', jqXHR.statusText);
            toastr.error('حدث خطأ أثناء جلب التعليقات');
        }
    });

    // جلب الأكواد الحالية
    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: { action: 'get_codes', lesson_id: lessonId },
        dataType: 'json',
        success: function(response) {
            console.log('Raw response:', response);
            if (Array.isArray(response)) {
                response.forEach(code => {
                    addCodeExample(code.language, code.code);
                });
            } else {
                console.log('No codes returned');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
            toastr.error('حدث خطأ أثناء جلب الأكواد');
        }
    });

    // تبديل عرض نموذج التعليق
    $('#toggleCommentForm').click(function() {
        $('#commentFormContainer').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
    });

    // تبديل عرض نموذج الكود
    $('#toggleCodeForm').click(function() {
        $('#codeForm').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
    });

    // تهيئة محرر النصوص المتقدم
    tinymce.init({
        selector: '#comment',
        height: 300,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });

    // تفعيل الشريط الجانبي
    $('#sidebarToggle').click(function() {
        $('.sidebar').toggleClass('open');
        $('.sidebar-toggle').toggleClass('open');
        $('body').toggleClass('sidebar-open');
    });

    // إغلاق الشريط الجانبي عند النقر خارجه
    $(document).click(function(event) {
        if (!$(event.target).closest('.sidebar, .sidebar-toggle').length) {
            $('.sidebar').removeClass('open');
            $('.sidebar-toggle').removeClass('open');
            $('body').removeClass('sidebar-open');
        }
    });
});

// Initialize TinyMCE
tinymce.init({
    selector: '#comment',
    height: 300,
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | ' +
    'bold italic backcolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
});
</script>