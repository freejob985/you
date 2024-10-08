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

    // تهيئة محرر النصوص المتقدم TinyMCE
    tinymce.init({
        selector: '#comment',
        height: 300,
        menubar: false,
        directionality: 'rtl',
        language: 'en',
        plugins: [
            'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
            'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media',
            'table', 'emoticons', 'template', 'help', 'codesample'
        ],
        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
            'forecolor backcolor emoticons | help | codesample',
        menu: {
            favs: { title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons' }
        },
        menubar: 'favs file edit view insert format tools table help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:25px; direction: rtl; text-align: right; }',
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Ruby', value: 'ruby' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'C', value: 'c' },
            { text: 'C#', value: 'csharp' },
            { text: 'C++', value: 'cpp' }
        ],
        setup: function (editor) {
            editor.on('init', function () {
                editor.getBody().style.direction = 'rtl';
                editor.getBody().style.textAlign = 'right';
                editor.getBody().style.fontSize = '25px';
            });
        }
    });

    // دالة لإضافة عناصر لقائمة التشغيل
function addPlaylistItem(title, lessonId, isActive, isCompleted) {
    const activeClass = isActive ? 'active' : '';
    const completedStyle = isCompleted ? 'text-decoration: line-through; font-weight: bold;' : '';
    const checkedAttribute = isCompleted ? 'checked' : '';
    const listItemStyle = isCompleted ? 'background: #aaccff;' : '';
    
    $('#playlist').append(`
        <li class="list-group-item cursor-pointer ${activeClass}" data-lesson-id="${lessonId}" style="${listItemStyle}">
            <div class="form-check">
                <input class="form-check-input mark-complete" type="checkbox" id="lesson-${lessonId}" ${checkedAttribute}>
                <label class="form-check-label" for="lesson-${lessonId}" style="${completedStyle}">
                    ${title}
                </label>
            </div>
        </li>
    `);
}
    // دالة لإضافة تعليق
    function addComment(commentId, comment, date) {
        const profileImage = 'https://scontent.fqtt2-1.fna.fbcdn.net/v/t39.30808-1/329724069_541779894594590_1088093019109401317_n.jpg?stp=dst-jpg_s200x200&_nc_cat=101&ccb=1-7&_nc_sid=0ecb9b&_nc_ohc=fIm0Nwlrgv0Q7kNvgGFLynt&_nc_ht=scontent.fqtt2-1.fna&_nc_gid=ASCqegk3pc-tq9ltJpTyXnH&oh=00_AYCitUEnER18EVOGKoI2ZgBnYZA45dA7iKjvzif06hReHA&oe=67094A7A';
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
            console.log('Raw response:', response);
            if (response.success && Array.isArray(response.playlistItems) && response.playlistItems.length > 0) {
                response.playlistItems.forEach(item => {
                    addPlaylistItem(item.title, item.id, item.id == lessonId, item.status === 'completed');
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
                const label = listItem.find('.form-check-label');
                if (isCompleted) {
                    label.css({
                        'text-decoration': 'line-through',
                        'font-weight': 'bold'
                    });
                    listItem.css('background', '#aaccff');
                } else {
                    label.css({
                        'text-decoration': 'none',
                        'font-weight': 'normal'
                    });
                    listItem.css('background', '');
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

    // تفعيل زر تبديل الشريط الجانبي
    $('#sidebarToggle').click(function(e) {
        e.stopPropagation(); // منع انتشار الحدث
        $('.sidebar').toggleClass('open');
        $('#sidebarToggle').toggleClass('open');
        $('body').toggleClass('sidebar-open');
    });

    // إغلاق الشريط الجانبي عند النقر خارجه
    $(document).click(function(event) {
        if (!$(event.target).closest('.sidebar, #sidebarToggle').length) {
            $('.sidebar').removeClass('open');
            $('#sidebarToggle').removeClass('open');
            $('body').removeClass('sidebar-open');
        }
    });

    // منع إغلاق الشريط الجانبي عند النقر داخله
    $('.sidebar').click(function(event) {
        event.stopPropagation();
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

    // Add these new functions and event listeners

    // Function to get status badge class
    function getStatusBadgeClass(status) {
        switch (status) {
            case 'completed': return 'bg-success';
            case 'watch':
            case 'review': return 'bg-primary';
            case 'problem':
            case 'retry':
            case 'retry_again': return 'bg-warning';
            case 'discussion':
            case 'search': return 'bg-info';
            case 'excluded': return 'bg-danger';
            case 'project': return 'bg-secondary';
            default: return 'bg-secondary';
        }
    }

    // Function to get status label
    function getStatusLabel(status) {
        switch (status) {
            case 'completed': return 'مكتمل';
            case 'watch': return 'مشاهدة';
            case 'problem': return 'مشكلة';
            case 'discussion': return 'نقاش';
            case 'search': return 'بحث';
            case 'retry': return 'إعادة';
            case 'retry_again': return 'إعادة ثانية';
            case 'review': return 'مراجعة';
            case 'excluded': return 'مستبعد';
            case 'project': return 'مشروع تطبيقي';
            default: return 'غير محدد';
        }
    }

    // Function to get status color
    function getStatusColor(status) {
        switch (status) {
            case 'completed': return '#28a745';
            case 'watch': return '#007bff';
            case 'problem': return '#dc3545';
            case 'discussion': return '#17a2b8';
            case 'search': return '#ffc107';
            case 'retry': return '#6c757d';
            case 'retry_again': return '#343a40';
            case 'review': return '#20c997';
            case 'excluded': return '#6610f2';
            case 'project': return '#e83e8c';
            default: return '#6c757d';
        }
    }

    // Function to populate status modal
    function populateStatusModal() {
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'GET',
            data: { 
                action: 'get_statuses',
                language_id: $('#lessonLanguage').text()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let html = `
                        <h2 class="text-center mb-4">تغيير حالة الدرس</h2>
                        <div class="status-options-container">
                    `;
                    response.statuses.forEach(status => {
                        html += `
                            <div class="status-option" data-status="${status.name}">
                                <div class="status-color" style="background-color: ${getStatusColor(status.name)};"></div>
                                <span class="status-label">${status.label}</span>
                            </div>
                        `;
                    });
                    html += `</div>`;
                    $('#statusOptions').html(html);
                    $('#statusModal').show();
                } else {
                    toastr.error('حدث خطأ أثناء جلب الحالات');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ في الاتصال بالخادم');
            }
        });
    }

    // Event listener for change status button
    $('#changeStatus').click(function() {
        populateStatusModal();
    });

    // Event listener for closing the modal
    $('.close').click(function() {
        $('#statusModal').hide();
    });

    // Event listener for selecting a status
    $('#statusOptions').on('click', '.status-option', function() {
        const newStatus = $(this).data('status');
        const lessonId = $('#changeStatus').data('lesson-id');
        const courseId = <?php echo isset($courseId) ? $courseId : 0; ?>;

        // Send AJAX request to update lesson status
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { 
                action: 'change_lesson_status', 
                lesson_id: lessonId, 
                status: newStatus,
                course_id: courseId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the status display in the UI
                    $('#lessonTags').text(getStatusLabel(newStatus));
                    $('#lessonTags').removeClass().addClass(`badge ${getStatusBadgeClass(newStatus)}`);
                    
                    // Update statistics
                    updateStatistics(response.statistics);
                    
                    toastr.success('تم تحديث حالة الدرس بنجاح');
                } else {
                    toastr.error('حدث خطأ أثناء تحديث حالة الدرس: ' + (response.error || 'خطأ غير معروف'));
                }
                $('#statusModal').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء تحديث حالة الدرس');
                $('#statusModal').hide();
            }
        });
    });

    // Close the modal when clicking outside of it
    $(window).click(function(event) {
        if (event.target == $('#statusModal')[0]) {
            $('#statusModal').hide();
        }
    });

    // إضافة مستمع الحدث لزر المشاهدة
    $('#watchLesson').click(function() {
        const lessonId = $(this).data('lesson-id');
        const $watchButton = $(this);
        const $watchText = $('#watchText');
        const $watchIcon = $watchButton.find('i');
        const currentState = $watchText.text() === 'مشاهدة' ? 0 : 1;
        const newState = 1 - currentState;

        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { 
                action: 'toggle_lesson_view', 
                lesson_id: lessonId,
                new_state: newState
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (newState === 1) {
                        $watchText.text('تمت المشاهدة');
                        $watchButton.removeClass('btn-info').addClass('btn-success');
                        $watchIcon.removeClass('fa-eye').addClass('fa-check');
                    } else {
                        $watchText.text('مشاهدة');
                        $watchButton.removeClass('btn-success').addClass('btn-info');
                        $watchIcon.removeClass('fa-check').addClass('fa-eye');
                    }
                    toastr.success('تم تحديث حالة المشاهدة بنجاح');
                } else {
                    toastr.error('حدث خطأ أثناء تحديث حالة المشاهدة');
                }
            },
            error: function() {
                toastr.error('حدث خطأ في الاتصال بالخادم');
            }
        });
    });

    // إضافة مستمع الحدث لزر تغيير القسم
    $('#changeSection').click(function() {
        const lessonId = $(this).data('lesson-id');
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'GET',
            data: { 
                action: 'get_sections',
                language_id: $('#lessonLanguage').text()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let html = '<h2 class="text-center mb-4">تغيير قسم الدرس</h2>';
                    response.sections.forEach(section => {
                        html += `
                            <div class="section-option" data-section-id="${section.id}">
                                <span class="section-label">${section.name}</span>
                            </div>
                        `;
                    });
                    html += '<button id="updateSection" class="btn btn-primary mt-3">تحديث القسم</button>';
                    $('#sectionOptions').html(html);
                    $('#sectionModal').show();

                    $('.section-option').click(function() {
                        $('.section-option').removeClass('selected');
                        $(this).addClass('selected');
                    });

                    $('#updateSection').click(function() {
                        const newSectionId = $('.section-option.selected').data('section-id');
                        if (!newSectionId) {
                            toastr.warning('الرجاء اختيار قسم');
                            return;
                        }
                        $.ajax({
                            url: 'show/ajax_handler.php',
                            method: 'POST',
                            data: { 
                                action: 'update_lesson_section',
                                lesson_id: lessonId,
                                section_id: newSectionId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    $('#lessonSection').text(response.section_name);
                                    toastr.success('تم تحديث قسم الدرس بنجاح');
                                    $('#sectionModal').hide();
                                } else {
                                    toastr.error('حدث خطأ أثناء تحديث قسم الدرس');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error('AJAX Error:', textStatus, errorThrown);
                                console.log('Response Text:', jqXHR.responseText);
                                toastr.error('حدث خطأ في الاتصال بالخادم');
                            }
                        });
                    });
                } else {
                    toastr.error('حدث خطأ أثناء جلب الأقسام');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ في الاتصال بالخادم');
            }
        });
    });

    // إضافة مستمعي أحداث لإغلاق الموديولات
    $('.close').click(function() {
        $('.modal').hide();
    });

    $(window).click(function(event) {
        if ($(event.target).hasClass('modal')) {
            $('.modal').hide();
        }
    });

    // ... (باقي الكود يبقى كما هو)
});
</script>