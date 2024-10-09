<!-- Include this script at the end of your HTML body -->
<script>
// Wait for the document to be ready
$(document).ready(function() {
    // Get the lesson ID from PHP
    const lessonId = <?php echo $lessonId; ?>;

    // Toast settings for notifications
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // Initialize TinyMCE editor for the comment textarea
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

    /**
     * Function to add a playlist item to the playlist
     * @param {string} title - The title of the lesson
     * @param {number} lessonId - The ID of the lesson
     * @param {boolean} isActive - Whether the lesson is currently active
     * @param {boolean} isCompleted - Whether the lesson is completed
     */
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

    /**
     * Function to add a comment to the comments section
     * @param {number} commentId - The ID of the comment
     * @param {string} comment - The content of the comment
     * @param {string} date - The date of the comment
     */
    function addComment(commentId, comment, date) {
        const profileImage = 'https://scontent.fqtt2-1.fna.fbcdn.net/v/t39.30808-1/329724069_541779894594590_1088093019109401317_n.jpg?stp=dst-jpg_s200x200&_nc_cat=101&ccb=1-7&_nc_sid=0ecb9b&_nc_ohc=FNTIXa2wDe0Q7kNvgFfRYW5&_nc_ht=scontent.fqtt2-1.fna&_nc_gid=AkV-4oPU4iZUpIerixkP1G6&oh=00_AYCawrcRwb1qzgcZNtHJu66cDM5T4byg62Vf8KyUGH186A&oe=670BB53A';
        $('#comments').prepend(`
            <div class="comment-card" data-comment-id="${commentId}">
                <img src="${profileImage}" alt="Profile" class="comment-image">
                <div class="comment-content">
                    <p class="comment-author">User Name</p>
                    <p class="comment-text">${comment}</p>
                    <small class="comment-date">${date}</small>
                </div>
                <button class="btn btn-danger btn-sm delete-comment"><i class="fas fa-trash-alt"></i></button>
            </div>
        `);
    }

    /**
     * Function to add a code example to the code examples section
     * @param {number} codeId - The ID of the code snippet
     * @param {string} language - The programming language of the code
     * @param {string} code - The code content
     */
    function addCodeExample(codeId, language, code) {
        const codeElementId = 'code-' + codeId;
        $('#codeExamples').append(`
            <div class="code-block mb-4" data-code-id="${codeId}">
                <h4 class="text-lg font-semibold mb-2 text-white">${language}</h4>
                <pre><code class="language-${language}" id="${codeElementId}">${code}</code></pre>
                <button class="btn btn-danger btn-sm delete-code mt-2"><i class="fas fa-trash-alt"></i> حذف الكود</button>
                <button class="btn btn-primary btn-sm copy-code mt-2"><i class="fas fa-copy"></i> نسخ الكود</button>
            </div>
        `);
        hljs.highlightElement(document.getElementById(codeElementId));
    }

    /**
     * Function to update the playlist statistics
     * @param {object} statistics - The statistics object
     */
    function updateStatistics(statistics) {
        $('#playlistStatistics').html(`
            <p><strong>الدروس المكتملة:</strong> ${statistics.completed_lessons}</p>
            <p><strong>الدروس غير المكتملة:</strong> ${statistics.incomplete_lessons}</p>
            <p><strong>الحالات:</strong> ${statistics.statuses.join(', ')}</p>
            <p><strong>الأقسام:</strong> ${statistics.sections.join(', ')}</p>
        `);
    }

    // Fetch playlist items via AJAX
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
                // Update statistics
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

    // Handle click on playlist items
    $('#playlist').on('click', 'li', function(e) {
        if ($(e.target).is('.mark-complete')) return;
        const clickedLessonId = $(this).data('lesson-id');
        window.location.href = `show.php?lesson_id=${clickedLessonId}`;
    });

    // Update lesson status when checkbox is changed
    $('#playlist').on('change', '.mark-complete', function(e) {
        e.stopPropagation();
        const lessonId = $(this).closest('li').data('lesson-id');
        const isCompleted = $(this).is(':checked');
        const courseId = <?php echo isset($courseId) ? $courseId : 0; ?>;
        // Send AJAX request to update lesson status
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
                    toastr.error('خطأ في تحديث حالة الدرس: ' + (response.error || 'خطأ غير معروف'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء تحديث حالة الدرس');
            }
        });
    });

    // Handle comment form submission
    $('#commentForm').submit(function(e) {
        e.preventDefault();
        const comment = tinymce.get('comment').getContent();

        // Validate input
        if (comment.trim() === '') {
            Swal.fire({
                title: 'خطأ!',
                text: 'يرجى كتابة تعليق قبل الإرسال.',
                icon: 'error',
                confirmButtonText: 'موافق'
            });
            return;
        }

        // Send comment to server
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
                    toastr.error('خطأ في إضافة التعليق');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء إضافة التعليق');
            }
        });
    });

    // Handle code form submission
    $('#codeForm').submit(function(e) {
        e.preventDefault();
        const language = $('#language').val();
        const code = $('#code').val();

        // Validate input
        if (code.trim() === '') {
            Swal.fire({
                title: 'خطأ!',
                text: 'يرجى إدخال الكود قبل الإرسال.',
                icon: 'error',
                confirmButtonText: 'موافق'
            });
            return;
        }

        // Send code to server
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
                    toastr.error('خطأ في إضافة الكود');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء إضافة الكود');
            }
        });
    });

    // Fetch existing comments via AJAX
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

    // Fetch existing codes via AJAX
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

    // Delete comment event handler
    $('#comments').on('click', '.delete-comment', function() {
        const commentCard = $(this).closest('.comment-card');
        const commentId = commentCard.data('comment-id');

        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم حذف هذا التعليق نهائياً!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send delete request to server
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
                            toastr.error('خطأ في حذف التعليق');
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

    // Delete code event handler
    $('#codeExamples').on('click', '.delete-code', function() {
        const codeBlock = $(this).closest('.code-block');
        const codeId = codeBlock.data('code-id');

        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم حذف هذا الكود نهائياً!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send delete request to server
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
                            toastr.error('خطأ في حذف الكود');
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

    // Copy code event handler
    $('#codeExamples').on('click', '.copy-code', function() {
        const codeBlock = $(this).closest('.code-block');
        const codeId = codeBlock.data('code-id');
        const codeElement = codeBlock.find('code')[0];
        const codeText = codeElement.innerText;

        navigator.clipboard.writeText(codeText).then(() => {
            toastr.success('تم نسخ الكود إلى الحافظة');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            toastr.error('خطأ في نسخ الكود');
        });
    });

    // Toggle comment form display
    $('#toggleCommentForm').click(function() {
        $('#commentFormContainer').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
    });

    // Toggle code form display
    $('#toggleCodeForm').click(function() {
        $('#codeForm').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
    });

    // Activate sidebar toggle
    $('#sidebarToggle').click(function(e) {
        e.stopPropagation(); // Prevent event propagation
        $('.sidebar').toggleClass('open');
        $('#sidebarToggle').toggleClass('open');
        $('body').toggleClass('sidebar-open');
    });

    // Close sidebar when clicking outside
    $(document).click(function(event) {
        if (!$(event.target).closest('.sidebar, #sidebarToggle').length) {
            $('.sidebar').removeClass('open');
            $('#sidebarToggle').removeClass('open');
            $('body').removeClass('sidebar-open');
        }
    });

    // Prevent closing sidebar when clicking inside it
    $('.sidebar').click(function(event) {
        event.stopPropagation();
    });

    /**
     * Function to get the badge class for a status
     * @param {string} status - The status of the lesson
     * @returns {string} - The badge class
     */
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

    /**
     * Function to get the status label
     * @param {string} status - The status of the lesson
     * @returns {string} - The label for the status
     */
    function getStatusLabel(status) {
        switch (status) {
            case 'completed': return 'مكتمل';
            case 'watch': return 'مشاهدة';
            case 'problem': return 'مشكلة';
            case 'discussion': return 'نقاش';
            case 'search': return 'بحث';
            case 'retry': return 'إعادة';
            case 'retry_again': return 'إعادة مرة أخرى';
            case 'review': return 'مراجعة';
            case 'excluded': return 'مستبعد';
            case 'project': return 'مشروع';
            default: return 'غير محدد';
        }
    }

    /**
     * Function to get the color for a status
     * @param {string} status - The status of the lesson
     * @returns {string} - The color code for the status
     */
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

    /**
     * Function to populate the status modal with options
     */
    function populateStatusModal() {
        const statuses = ['watch', 'problem', 'discussion', 'search', 'retry', 'retry_again', 'review', 'completed', 'excluded', 'project'];
        let html = `
            <div class="status-module-header p-3 mb-3" style="background: linear-gradient(45deg, #4a90e2, #63b3ed);">
                <h2 class="text-center text-white">تغيير حالة الدرس</h2>
            </div>
            <div class="status-options-container p-3">
        `;
        statuses.forEach(status => {
            html += `
                <div class="status-option d-flex align-items-center p-2 mb-2" data-status="${status}">
                    <div class="status-color me-3" style="background-color: ${getStatusColor(status)}; width: 30px; height: 30px; border-radius: 50%;"></div>
                    <span class="status-label">${getStatusLabel(status)}</span>
                </div>
            `;
        });
        $('#statusOptions').html(html);
    }

    // Event listener for change status button
    $('#changeStatus').click(function() {
        populateStatusModal();
        $('#statusModal').show();
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
                    $('#lessonStatus').text(getStatusLabel(newStatus));
                    $('#lessonStatus').removeClass().addClass(`badge ${getStatusBadgeClass(newStatus)}`);
                    
                    // Update statistics
                    updateStatistics(response.statistics);
                    
                    toastr.success('تم تحديث حالة الدرس بنجاح');
                } else {
                    toastr.error('فشل في تحديث حالة الدرس: ' + (response.error || 'خطأ غير معروف'));
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

    // Event listener for changing section
    $('#changeSection').click(function() {
        $('#sectionModal').modal('show');
    });

    // Handle section form submission
    $('#sectionForm').submit(function(e) {
        e.preventDefault();
        const lessonId = $('#changeSection').data('lesson-id');
        const sectionId = $('#sectionSelect').val();

        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { action: 'update_section', lesson_id: lessonId, section_id: sectionId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#lessonSection').text(response.section_name);
                    toastr.success('تم تحديث القسم بنجاح');
                    $('#sectionModal').modal('hide');
                } else {
                    toastr.error('خطأ في تحديث القسم: ' + (response.error || 'خطأ غير معروف'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error('حدث خطأ أثناء تحديث القسم');
            }
        });
    });

    // Event listener for changing tags
    $('#changeTags').click(function() {
        $('#tagModal').modal('show');
    });

    // Handle tag form submission
    $('#tagForm').submit(function(e) {
        e.preventDefault();
        const lessonId = $('#changeTags').data('lesson-id');
        const tags = $('#tagInput').val();

        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { action: 'update_tags', lesson_id: lessonId, section_tags: tags },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#lessonTags').text(tags);
                    toastr.success('تم تحديث التصنيفات بنجاح');
                    $('#tagModal').modal('hide');
                } else {
                    toastr.error('خطأ في تحديث التصنيفات: ' + (response.error || 'خطأ غير معروف'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error('حدث خطأ أثناء تحديث التصنيفات');
            }
        });
    });

    // Watch lesson button event handler
    $('#watchLesson').click(function() {
        const lessonId = $(this).data('lesson-id');
        const currentViews = parseInt($(this).data('views'));
        
        $.ajax({
            url: 'show/ajax_handler.php',
            method: 'POST',
            data: { action: 'toggle_view_status', lesson_id: lessonId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const newViews = response.new_views;
                    $('#watchLesson').data('views', newViews);
                    if (newViews > currentViews) {
                        $('#watchLesson').html('<i class="fas fa-check"></i> تمت المشاهدة');
                        toastr.success('تم تحديث حالة المشاهدة');
                    } else {
                        $('#watchLesson').html('<i class="fas fa-eye"></i> مشاهدة');
                        toastr.info('تم إلغاء حالة المشاهدة');
                    }
                } else {
                    toastr.error('حدث خطأ أثناء تحديث حالة المشاهدة');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                toastr.error('حدث خطأ أثناء تحديث حالة المشاهدة');
            }
        });
    });

});
</script>