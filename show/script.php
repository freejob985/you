<script>
$(document).ready(function() {
    const lessonId = <?php echo $lessonId; ?>;

    // إعدادات Toast
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // تهيئة محرر النصوص المتقدم
    tinymce.init({
        selector: '#comment',
        height: 300,
        menubar: false,
        directionality: 'rtl',
        language: 'ar',
        plugins: [
            'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
            'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media',
            'table', 'emoticons', 'help'
        ],
        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
            'forecolor backcolor emoticons | help',
        menu: {
            favs: { title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons' }
        },
        menubar: 'favs file edit view insert format tools table help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
    });

    // دالة لإضافة عناصر لقائمة التشغيل
    function addPlaylistItem(title, lessonId, isActive, isCompleted, views) {
        const activeClass = isActive ? 'active' : '';
        const completedClass = isCompleted ? 'completed font-bold' : '';
        const checkedAttribute = isCompleted ? 'checked' : '';
        const viewsText = views > 0 ? `${views} مشاهدة` : 'غير مشاهد';
        $('#playlist').append(`
            <li class="list-group-item cursor-pointer ${activeClass} ${completedClass}" data-lesson-id="${lessonId}">
                <div class="form-check d-flex justify-content-between align-items-center">
                    <div>
                        <input class="form-check-input mark-complete" type="checkbox" id="lesson-${lessonId}" ${checkedAttribute}>
                        <label class="form-check-label" for="lesson-${lessonId}">
                            ${title}
                        </label>
                    </div>
                    <small class="text-muted">${viewsText}</small>
                </div>
            </li>
        `);
    }

    // دالة لإضافة تعليق
    function addComment(commentId, comment, date) {
        const profileImage = 'https://static.wikia.nocookie.net/harrypotter/images/c/ce/Harry_Potter_DHF1.jpg/revision/latest/thumbnail/width/360/height/360?cb=20140603201724';
        $('#comments').prepend(`
            <div class="comment-card" data-comment-id="${commentId}">
                <img src="${profileImage}" alt="Profile" class="comment-image">
                <div class="comment-content">
                    <p class="comment-author">اسم المستخدم</p>
                    <p class="comment-text">${comment}</p>
                    <small class="comment-date">${date}</small>
                </div>
                <button class="btn btn-danger btn-sm delete-comment"><i class="fas fa-trash-alt"></i></button>
            </div>
        `);
    }

    // دالة لإضافة كود برمجي
    function addCodeExample(codeId, language, code) {
        const codeElementId = 'code-' + codeId;
        $('#codeExamples').append(`
            <div class="code-block mb-4" data-code-id="${codeId}">
                <h4 class="text-lg font-semibold mb-2 text-white">${language}</h4>
                <pre><code class="language-${language}" id="${codeElementId}">${code}</code></pre>
                <button class="btn btn-primary btn-sm copy-code mt-2"><i class="fas fa-copy"></i> نسخ الكود</button>
                <button class="btn btn-danger btn-sm delete-code mt-2"><i class="fas fa-trash-alt"></i></button>
            </div>
        `);
        hljs.highlightElement(document.getElementById(codeElementId));
    }

    // تحديث الإحصائيات
    function updateStatistics(statistics) {
        $('#playlistStatistics').html(`
            <p><strong>الدروس المكتملة:</strong> ${statistics.completed_lessons}</p>
            <p><strong>الدروس غير المكتملة:</strong> ${statistics.incomplete_lessons}</p>
            <p><strong>الحالات:</strong> ${statistics.statuses.join(', ')}</p>
            <p><strong>الأقسام:</strong> ${statistics.sections.join(', ')}</p>
        `);
    }

    // جلب قائمة التشغيل
    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: { action: 'get_playlist', course_id: <?php echo isset($courseId) ? $courseId : 0; ?> },
        dataType: 'json',
        success: function(response) {
            console.log('Raw response:', response); // إضافة سجل للاستجابة الكاملة
            if (response.success && Array.isArray(response.playlistItems) && response.playlistItems.length > 0) {
                response.playlistItems.forEach(item => {
                    addPlaylistItem(item.title, item.id, item.id == lessonId, item.status === 'completed', item.views);
                });
                // تحديث الإحصائيات
                updateStatistics(response.statistics);
            } else {
                console.log('No playlist items returned or error occurred');
                toastr.warning('لا توجد عناصر في قائمة التشغيل أو حدث خطأ');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
            toastr.error('حدث خطأ أثناء جلب البيانات');
        }
    });

    // التعامل مع النقر على عناصر قائمة التشغيل
    $('#playlist').on('click', 'li', function(e) {
        if ($(e.target).is('.mark-complete')) return;
        const clickedLessonId = $(this).data('lesson-id');
        window.location.href = `show.php?lesson_id=${clickedLessonId}`;
    });

    // تحديث حالة الدرس عند تغيير الشيك بوكس
    $('#playlist').on('change', '.mark-complete', function(e) {
        e.stopPropagation();
        const lessonId = $(this).closest('li').data('lesson-id');
        const isCompleted = $(this).is(':checked');
        const courseId = <?php echo isset($courseId) ? $courseId : 0; ?>;

        // إرسال طلب AJAX لتحديث حالة الدرس
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { 
                action: 'change_lesson_status', 
                lesson_id: lessonId, 
                status: isCompleted ? 'completed' : 'active',
                course_id: courseId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    const listItem = $(`#playlist li[data-lesson-id="${lessonId}"]`);
                    if (isCompleted) {
                        listItem.addClass('completed font-bold');
                    } else {
                        listItem.removeClass('completed font-bold');
                    }
                    toastr.success('تم تحديث حالة الدرس بنجاح');
                    updateStatistics(response.statistics);
                } else {
                    toastr.error('حدث خطأ أثناء تحديث حالة الدرس: ' + (response.error || 'خطأ غير معروف'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء تحديث حالة الدرس');
            }
        });
    });

    // التعامل مع إرسال نموذج التعليق
    $('#commentForm').submit(function(e) {
        e.preventDefault();
        const comment = tinymce.get('comment').getContent();

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
                console.log('Add comment response:', response);
                if (response.success) {
                    addComment(response.comment_id, comment, 'الآن');
                    toastr.success('تم إضافة التعليق بنجاح');
                    tinymce.get('comment').setContent('');
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
                console.log('Add code response:', response);
                if (response.success) {
                    addCodeExample(response.code_id, language, code);
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
                    addComment(comment.id, comment.content, comment.created_at);
                });
            } else {
                console.log('No comments returned');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            console.log('Response Text:', jqXHR.responseText);
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
                    addCodeExample(code.id, code.language, code.code);
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

    // حذف التعليق
    $('#comments').on('click', '.delete-comment', function() {
        const commentCard = $(this).closest('.comment-card');
        const commentId = commentCard.data('comment-id');

        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم حذف التعليق نهائياً!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // إرسال طلب الحذف إلى الخادم
                $.ajax({
                    url: 'show/ajax_handler.php',
                    method: 'POST',
                    data: { action: 'delete_comment', comment_id: commentId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            commentCard.remove();
                            toastr.success('تم حذف التعليق بنجاح');
                        } else {
                            toastr.error('حدث خطأ أثناء حذف التعليق');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error:', textStatus, errorThrown);
                        console.log('Response Text:', jqXHR.responseText);
                        toastr.error('حدث خطأ أثناء حذف التعليق');
                    }
                });
            }
        });
    });

    // حذف الكود
    $('#codeExamples').on('click', '.delete-code', function() {
        const codeBlock = $(this).closest('.code-block');
        const codeId = codeBlock.data('code-id');

        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم حذف الكود نهائياً!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // إرسال طلب الحذف إلى الخادم
                $.ajax({
                    url: 'show/ajax_handler.php',
                    method: 'POST',
                    data: { action: 'delete_code', code_id: codeId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            codeBlock.remove();
                            toastr.success('تم حذف الكود بنجاح');
                        } else {
                            toastr.error('حدث خطأ أثناء حذف الكود');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error:', textStatus, errorThrown);
                        console.log('Response Text:', jqXHR.responseText);
                        toastr.error('حدث خطأ أثناء حذف الكود');
                    }
                });
            }
        });
    });

    // نسخ الكود
    $('#codeExamples').on('click', '.copy-code', function() {
        const codeBlock = $(this).closest('.code-block');
        const codeId = codeBlock.data('code-id');
        const codeElement = codeBlock.find('code')[0];
        const codeText = codeElement.innerText;

        navigator.clipboard.writeText(codeText).then(() => {
            toastr.success('تم نسخ الكود إلى الحافظة');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            toastr.error('حدث خطأ أثناء نسخ الكود');
        });
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

    // تفعيل الشريط الجانبي
    $('#sidebarToggle').click(function() {
        $('.sidebar').toggleClass('open');
        $('.sidebar-toggle').toggleClass('open');
        $('body').toggleClass('sidebar-open');
    });

    // إغلاق الشريط الجانبي عند النقر خارجه
    $(document).click(function(event) {
        if (!$(event.target).closest('.sidebar, .sidebar-toggle').length) {
            if ($('.sidebar').hasClass('open')) {
                $('.sidebar').removeClass('open');
                $('.sidebar-toggle').removeClass('open');
                $('body').removeClass('sidebar-open');
            }
        }
    });

    // تحديث الإحصائيات
    function updateStatistics(statistics) {
        $('#playlistStatistics').html(`
            <p><strong>الدروس المكتملة:</strong> ${statistics.completed_lessons}</p>
            <p><strong>الدروس غير المكتملة:</strong> ${statistics.incomplete_lessons}</p>
            <p><strong>الحالات:</strong> ${statistics.statuses.join(', ')}</p>
            <p><strong>الأقسام:</strong> ${statistics.sections.join(', ')}</p>
        `);
    }

});
</script>