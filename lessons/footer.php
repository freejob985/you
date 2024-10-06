    <footer class="fixed-bottom bg-dark text-white py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="footer-links d-flex flex-wrap justify-content-center">
                        <a href="http://localhost/home/" class="btn btn-outline-primary" title="الرئيسية">
                            <i class="fas fa-home"></i>
                            <span>الرئيسية</span>
                        </a>
                        <a href="http://localhost/blackboard/" class="btn btn-outline-success" title="السبورة">
                            <i class="fas fa-chalkboard"></i>
                            <span>السبورة</span>
                        </a>
                        <a href="http://localhost/task-ai/" class="btn btn-outline-info" title="المهام">
                            <i class="fas fa-tasks"></i>
                            <span>المهام</span>
                        </a>
                        <a href="http://localhost/info-code/bt.php" class="btn btn-outline-warning" title="بنك الأكواد">
                            <i class="fas fa-code"></i>
                            <span>بنك الأكواد</span>
                        </a>
                        <a href="http://localhost/administration/public/" class="btn btn-outline-danger" title="الملفات">
                            <i class="fas fa-folder"></i>
                            <span>الملفات</span>
                        </a>
                        <a href="http://localhost/Columns/" class="btn btn-outline-light" title="الاعمدة">
                            <i class="fas fa-columns"></i>
                            <span>الاعمدة</span>
                        </a>
                        <a href="http://localhost/ask/" class="btn btn-outline-secondary" title="الاسئلة">
                            <i class="fas fa-question-circle"></i>
                            <span>الاسئلة</span>
                        </a>
                        <a href="http://localhost/phpmyadminx/" class="btn btn-outline-primary" title="ادارة قواعد البيانات">
                            <i class="fas fa-database"></i>
                            <span>قواعد البيانات</span>
                        </a>
                        <a href="http://localhost/pr.php" class="btn btn-outline-success" title="اصطياد الاخطاء">
                            <i class="fas fa-bug"></i>
                            <span>اصطياد الاخطاء</span>
                        </a>
                        <a href="http://localhost/Timmy/" class="btn btn-outline-info" title="تيمي">
                            <i class="fas fa-clock"></i>
                            <span>تيمي</span>
                        </a>
                        <a href="http://localhost/copy/" class="btn btn-outline-warning" title="حافظة الملاحظات">
                            <i class="fas fa-clipboard"></i>
                            <span>الملاحظات</span>
                        </a>
                        <a href="http://localhost/Taskme/" class="btn btn-outline-danger" title="المهام اليومية">
                            <i class="fas fa-calendar-check"></i>
                            <span>المهام اليومية</span>
                        </a>
                        <a href="http://subdomain.localhost/tasks" class="btn btn-outline-light" title="CRM">
                            <i class="fas fa-users"></i>
                            <span>CRM</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- روابط جافا سكريبت -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        // تهيئة توست
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // دالة لتحديث حالة الدرس في الواجهة
        function updateLessonStatus(lessonId, status) {
            const lessonCard = $(`.card:has([data-lesson-id="${lessonId}"])`);
            const lessonTitle = lessonCard.find('.lesson-title');
            const statusBadge = lessonCard.find('.lesson-status');
            const thumbnail = lessonCard.find('img');
            const cardHeader = lessonCard.find('.card-header');

            lessonTitle.toggleClass('completed', status === 'completed');
            statusBadge.removeClass().addClass(`lesson-status badge ${getStatusBadgeClass(status)}`);
            statusBadge.text(getStatusLabel(status));
            
            if (status === 'completed') {
                lessonCard.addClass('completed-card');
                thumbnail.addClass('grayscale');
                cardHeader.css('background-color', '#000000');
            } else {
                lessonCard.removeClass('completed-card');
                thumbnail.removeClass('grayscale');
                cardHeader.css('background-color', getStatusColor(status));
            }

            // تحديث حالة الـ checkbox
            lessonCard.find('.mark-complete-checkbox').prop('checked', status === 'completed');

            // تحديث نسبة الإكمال
            updateCompletionPercentage();
        }

        // دالة لتحديث نسبة إكمال الكورس
        function updateCompletionPercentage() {
            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'get_completion_percentage',
                    course_id: <?php echo $courseId; ?>
                },
                success: function(response) {
                    if (response.success) {
                        const completedPercentage = response.percentage;
                        const remainingPercentage = 100 - completedPercentage;
                        
                        $('.progress-bar.bg-success').css('width', completedPercentage + '%')
                            .attr('aria-valuenow', completedPercentage)
                            .text(completedPercentage + '% مكتمل');
                        $('.progress-bar.bg-primary').css('width', remainingPercentage + '%')
                            .attr('aria-valuenow', remainingPercentage)
                            .text(remainingPercentage + '% غير مكتمل');
                        
                        $('.completed-lessons').text(response.completed_lessons);
                        $('.remaining-lessons').text(response.total_lessons - response.completed_lessons);
                    }
                }
            });
        }

        // تحديث حدث النقر على زر المشاهدة
        $('.watch-button').click(function() {
            const lessonId = $(this).data('lesson-id');
            const views = $(this).data('views');
            const newViews = views === 0 ? 1 : 0;
            const button = $(this);
            const checkbox = button.closest('.card').find('.mark-complete-checkbox');

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'toggle_watch',
                    lesson_id: lessonId,
                    views: newViews
                },
                success: function(response) {
                    if (response.success) {
                        button.data('views', newViews);
                        button.removeClass('btn-primary btn-success')
                              .addClass(newViews > 0 ? 'btn-success' : 'btn-primary');
                        button.html(`<i class="fas ${newViews > 0 ? 'fa-check' : 'fa-eye'}"></i> ${newViews > 0 ? 'تم المشاهدة' : 'مشاهدة'}`);
                        
                        checkbox.prop('checked', newViews > 0);
                        
                        updateLessonStatus(lessonId, newViews > 0 ? 'completed' : 'active');
                        toastr.success('تم تحديث حالة الدرس');
                        
                        // تحديث نسبة الإكمال
                        updateCompletionPercentage();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // تحديث حدث تغيير صندوق الاختيار
        $('.mark-complete-checkbox').change(function() {
            const lessonId = $(this).data('lesson-id');
            const completed = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'mark_complete',
                    lesson_id: lessonId,
                    completed: completed
                },
                success: function(response) {
                    if (response.success) {
                        updateLessonStatus(lessonId, completed ? 'completed' : 'active');
                        toastr.success('تم تحديث حالة الإكمال');
                        
                        // تحديث زر المشاهدة
                        const watchButton = $(`.watch-button[data-lesson-id="${lessonId}"]`);
                        watchButton.data('views', completed ? 1 : 0);
                        watchButton.removeClass('btn-primary btn-success')
                                   .addClass(completed ? 'btn-success' : 'btn-primary');
                        watchButton.html(`<i class="fas ${completed ? 'fa-check' : 'fa-eye'}"></i> ${completed ? 'تم المشاهدة' : 'مشاهدة'}`);
                        
                        // تحديث نسبة الإكمال
                        updateCompletionPercentage();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        $('.assign-section-button').click(function() {
            const lessonId = $(this).data('lesson-id');
            $('#assignSectionModal').data('lesson-id', lessonId).modal('show');
        });

        // تأكيد تعيين القسم
        $('#confirmAssignSection').click(function() {
            const lessonId = $('#assignSectionModal').data('lesson-id');
            const sectionId = $('#sectionSelect').val();

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'assign_section',
                    lesson_id: lessonId,
                    section_id: sectionId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('تم تعيين القسم بنجاح');
                        $('#assignSectionModal').modal('hide');
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // زر تحديد الحالة
        $('.set-status-button').click(function() {
            const lessonId = $(this).data('lesson-id');
            $('#setStatusModal').data('lesson-id', lessonId).modal('show');
        });

        // تأكيد تحديد الحالة
        $('#confirmSetStatus').click(function() {
            const lessonId = $('#setStatusModal').data('lesson-id');
            const status = $('#statusSelect').val().join(',');

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'set_status',
                    lesson_id: lessonId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        updateLessonStatus(lessonId, status);
                        toastr.success('تم تحديد الحالة بنجاح');
                        $('#setStatusModal').modal('hide');
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // تحديد الإكمال باستخدام الـ checkbox
        $('.mark-complete-checkbox').change(function() {
            const lessonId = $(this).data('lesson-id');
            const completed = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'mark_complete',
                    lesson_id: lessonId,
                    completed: completed
                },
                success: function(response) {
                    if (response.success) {
                        updateLessonStatus(lessonId, completed ? 'completed' : 'active');
                        toastr.success('تم تحديث حالة الإكمال');
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // زر حذف الدرس
        $('.delete-lesson-button').click(function() {
            const lessonId = $(this).data('lesson-id');
            const lessonCard = $(this).closest('.card');

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن هذا الإجراء!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف الدرس',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'lessons_actions.php',
                        method: 'POST',
                        data: {
                            action: 'delete_lesson',
                            lesson_id: lessonId
                        },
                        success: function(response) {
                            if (response.success) {
                                lessonCard.remove();
                                updateCompletionPercentage();
                                toastr.success('تم حذف الدرس بنجاح');
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            });
        });

        // دوال مساعدة لتحديد لون وتسمية الحالة
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

        function getStatusColor(status) {
            switch (status) {
                case 'completed': return '#000000';
                case 'watch':
                case 'review': return '#007bff';
                case 'problem':
                case 'retry':
                case 'retry_again': return '#ffc107';
                case 'discussion':
                case 'search': return '#17a2b8';
                case 'excluded': return '#dc3545';
                case 'project': return '#6c757d';
                default: return '#7E0C0CFF';
            }
        }

        // تهيئة Tagify
        var input = document.querySelector('#lessonTags');
        var tagify = new Tagify(input, {
            delimiters: ",",
            dropdown: {
                enabled: 0
            }
        });

        // فتح مودال تحرير الأقسام
        $('.edit-tags-button').click(function() {
            var lessonId = $(this).data('lesson-id');
            var currentTags = $(this).closest('.card').find('.section-tags').text().split(',').map(tag => tag.trim());
            
            tagify.removeAllTags();
            tagify.addTags(currentTags);
            
            $('#editTagsModal').data('lesson-id', lessonId);
        });

        // إرسال الأقسام الجديدة عند الضغط على زر التأكيد
        $('#confirmEditTags').click(function() {
            var lessonId = $('#editTagsModal').data('lesson-id');
            var tags = tagify.value.map(tag => tag.value);
            
            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'update_section_tags',
                    lesson_id: lessonId,
                    section_tags: JSON.stringify(tags)
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('تم تحديث أقسام الدرس بنجاح');
                        $('#editTagsModal').modal('hide');
                        // تحديث الأقسام المعروضة في البطاقة
                        $(`.card:has([data-lesson-id="${lessonId}"])`).find('.section-tags').text(tags.join(', '));
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // Add the updateProgressBar function
        function updateProgressBar() {
            $.ajax({
                url: 'lessons_actions.php',
                method: 'POST',
                data: {
                    action: 'get_completion_percentage',
                    course_id: <?php echo $courseId; ?>
                },
                success: function(response) {
                    if (response.success) {
                        const completedPercentage = response.percentage;
                        const remainingPercentage = 100 - completedPercentage;
                        
                        // تحديث شريط التقدم في الهيدر
                        $('.header-progress .progress-bar.bg-success').css('width', completedPercentage + '%')
                            .attr('aria-valuenow', completedPercentage)
                            .text(completedPercentage + '% مكتمل');
                        
                        $('.header-progress .progress-bar-remaining').css('width', remainingPercentage + '%')
                            .attr('aria-valuenow', remainingPercentage)
                            .text(remainingPercentage + '% متبقي');
                        
                        // تحديث إحصائيات الهيدر
                        $('.course-stats .stat-item:nth-child(2) .value').text(response.completed_lessons);
                        $('.course-stats .stat-item:nth-child(3) .value').text(response.total_lessons - response.completed_lessons);
                        $('.course-stats .stat-item:nth-child(4) .value').text(completedPercentage + '%');
                    }
                }
            });
        }

        // تحديث البروجرس بار عند تحميل الصفحة
        updateProgressBar();

        // تحديث البروجرس بار عند تغيير حالة الدرس
        $('.mark-complete-checkbox, .watch-button').on('change click', function() {
            updateProgressBar();
        });

        // New function to update footer button colors
        function updateFooterButtonColors() {
            $('.footer-links .btn').each(function() {
                var btn = $(this);
                var color = btn.css('border-color');
                btn.css('color', color);
                btn.hover(
                    function() {
                        $(this).css('background-color', color);
                        $(this).css('color', '#fff');
                    },
                    function() {
                        $(this).css('background-color', 'transparent');
                        $(this).css('color', color);
                    }
                );
            });
        }

        // Call the function when the page loads
        updateFooterButtonColors();
    });
    </script>
</body>
</html>