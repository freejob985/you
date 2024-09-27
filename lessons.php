<?php
// إنشاء اتصال بقاعدة البيانات SQLite باستخدام PDO
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// التحقق من وجود معرف الكورس في الـ URL
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    die("معرف الكورس غير صالح");
}

$courseId = $_GET['course_id'];

// استعلام لجلب معلومات الكورس
$stmtCourse = $db->prepare('SELECT * FROM courses WHERE id = :course_id');
$stmtCourse->bindValue(':course_id', $courseId, PDO::PARAM_INT);
$stmtCourse->execute();
$course = $stmtCourse->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("الكورس غير موجود");
}

// استعلام لجلب الدروس الخاصة بالكورس
$stmtLessons = $db->prepare('SELECT * FROM lessons WHERE course_id = :course_id ORDER BY id ASC');
$stmtLessons->bindValue(':course_id', $courseId, PDO::PARAM_INT);
$stmtLessons->execute();
$lessons = $stmtLessons->fetchAll(PDO::FETCH_ASSOC);

// استعلام لجلب الأقسام
$stmtSections = $db->prepare('SELECT * FROM sections ORDER BY name ASC');
$stmtSections->execute();
$sections = $stmtSections->fetchAll(PDO::FETCH_ASSOC);

// دالة مساعدة لتنسيق الوقت
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return ($hours > 0 ? $hours . ":" : "") . 
           (($minutes < 10 && $hours > 0) ? "0" : "") . $minutes . ":" . 
           ($secs < 10 ? "0" : "") . $secs;
}

// حساب نسبة إكمال الدروس
$totalLessons = count($lessons);
$completedLessons = 0;
foreach ($lessons as $lesson) {
    if ($lesson['status'] === 'completed') {
        $completedLessons++;
    }
}
$completionPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دروس الكورس: <?php echo htmlspecialchars($course['title']); ?></title>
    
    <!-- روابط البوتستراب -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- رابط ماتريال ديزاين -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    
    <!-- رابط الفونت أوسم -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
    <!-- الخط المطلوب -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Changa:wght@200..800&display=swap" rel="stylesheet">
    
    <!-- مكتبة توست -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- مكتبة DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    
    <!-- مكتبة Tagify -->
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        .progress {
            height: 25px;
        }
        .progress-bar {
            line-height: 25px;
        }
        .completed {
            text-decoration: line-through;
        }
        .thumbnail-img {
            width: 60px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <table id="lessonsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>عنوان الدرس</th>
                    <th>المدة</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lessons as $lesson): ?>
                    <tr id="lesson-row-<?php echo $lesson['id']; ?>" class="<?php echo ($lesson['status'] === 'completed') ? 'completed' : ''; ?>">
                        <td>
                            <?php if (!empty($lesson['thumbnail'])): ?>
                                <img src="<?php echo htmlspecialchars($lesson['thumbnail']); ?>" alt="Thumbnail" class="thumbnail-img">
                            <?php else: ?>
                                <span>لا توجد صورة</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="lesson-title" data-lesson-id="<?php echo $lesson['id']; ?>" style="cursor: pointer;">
                                <?php echo htmlspecialchars($lesson['title']); ?>
                            </span>
                        </td>
                        <td><?php echo formatDuration($lesson['duration']); ?></td>
                        <td>
                            <span class="lesson-status">
                                <?php echo ($lesson['status'] === 'completed') ? 'مكتمل' : 'غير مكتمل'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm watch-button" data-lesson-id="<?php echo $lesson['id']; ?>" data-views="<?php echo $lesson['views']; ?>">
                                <?php echo ($lesson['views'] > 0) ? 'تم المشاهدة' : 'مشاهدة'; ?>
                            </button>
                            <button class="btn btn-secondary btn-sm assign-section-button" data-lesson-id="<?php echo $lesson['id']; ?>">
                                <i class="fas fa-layer-group"></i>
                            </button>
                            <button class="btn btn-info btn-sm set-status-button" data-lesson-id="<?php echo $lesson['id']; ?>">
                                <i class="fas fa-flag"></i>
                            </button>
                            <input type="checkbox" class="mark-complete-checkbox" data-lesson-id="<?php echo $lesson['id']; ?>" <?php echo ($lesson['status'] === 'completed') ? 'checked' : ''; ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="mt-4">
            <h4>نسبة إكمال الكورس</h4>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?php echo $completionPercentage; ?>%;" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                    <?php echo $completionPercentage; ?>%
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="courses.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>العودة إلى قائمة الكورسات
            </a>
        </div>
    </div>

    <!-- مودال لتحديد القسم -->
    <div class="modal fade" id="assignSectionModal" tabindex="-1" aria-labelledby="assignSectionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="assignSectionModalLabel">تحديد القسم</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <button id="newSectionButton" class="btn btn-success w-100 mb-2">قسم جديد</button>
                <button id="existingSectionButton" class="btn btn-primary w-100">قسم موجود</button>
            </div>
            <div id="newSectionInput" class="mb-3" style="display: none;">
                <label for="newSectionTags" class="form-label">أضف أقسام جديدة</label>
                <input type="text" class="form-control" id="newSectionTags">
            </div>
            <div id="existingSectionSelect" class="mb-3" style="display: none;">
                <label for="existingSections" class="form-label">اختر قسم موجود</label>
                <select class="form-control" id="existingSections">
                    <option value="">اختر قسم</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            <button type="button" class="btn btn-primary" id="assignSectionConfirmButton">تعيين القسم</button>
          </div>
        </div>
      </div>
    </div>

    <!-- مودال لتحديد حالة الدرس -->
    <div class="modal fade" id="setStatusModal" tabindex="-1" aria-labelledby="setStatusModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="setStatusModalLabel">تحديد حالة الدرس</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <select class="form-control" id="lessonStatusSelect">
                <option value="">اختر حالة</option>
                <option value="watch">مشاهدة</option>
                <option value="problem">مشكلة</option>
                <option value="discussion">نقاش</option>
                <option value="search">بحث</option>
                <option value="retry">إعادة</option>
                <option value="retry_again">إعادة ثانية</option>
                <option value="review">مراجعة</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            <button type="button" class="btn btn-primary" id="setStatusConfirmButton">تحديد الحالة</button>
          </div>
        </div>
      </div>
    </div>

    <!-- مكتبة jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- مكتبة DataTables -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
    
    <!-- مكتبة Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- مكتبة Tagify -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    
    <!-- مكتبة SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- مكتبة توست -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // تهيئة جدول DataTables
        $('#lessonsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
            }
        });

        // تهيئة توست
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // تهيئة Tagify للأقسام الجديدة
        var newSectionInput = document.querySelector('#newSectionTags');
        var tagifySections = new Tagify(newSectionInput, {
            delimiters: ",",
            maxTags: 10,
            dropdown: {
                enabled: 0
            }
        });

        // أزرار المودال لتحديد القسم
        $('#newSectionButton').on('click', function() {
            $('#newSectionInput').show();
            $('#existingSectionSelect').hide();
        });

        $('#existingSectionButton').on('click', function() {
            $('#existingSectionSelect').show();
            $('#newSectionInput').hide();
        });

        var currentLessonId = null;

        // فتح مودال تعيين القسم
        $('.assign-section-button').on('click', function() {
            currentLessonId = $(this).data('lesson-id');
            $('#assignSectionModal').modal('show');
        });

        // تأكيد تعيين القسم
        $('#assignSectionConfirmButton').on('click', function() {
            if (currentLessonId === null) {
                toastr.error('معرف الدرس غير موجود.');
                return;
            }

            var selectedSectionId = $('#existingSections').val();
            var newSections = tagifySections.value.map(tag => tag.value).filter(name => name.trim() !== '');

            if ($('#newSectionInput').is(':visible')) {
                if (newSections.length === 0) {
                    toastr.error('يرجى إدخال أسماء الأقسام الجديدة.');
                    return;
                }

                // إرسال أقسام جديدة إلى الخادم
                $.ajax({
                    url: 'lessons_actions.php',
                    type: 'POST',
                    data: {
                        action: 'add_new_sections',
                        sections: JSON.stringify(newSections)
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var newSectionIds = response.section_ids;
                            // تعيين القسم الأول من الأقسام الجديدة للدرس
                            if (newSectionIds.length > 0) {
                                assignSectionToLesson(currentLessonId, newSectionIds[0]);
                            }
                            Swal.fire('تم!', 'تم إضافة الأقسام الجديدة وتعيين القسم للدرس بنجاح.', 'success');
                            $('#assignSectionModal').modal('hide');
                            tagifySections.removeAllTags();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('حدث خطأ في الاتصال بالخادم.');
                    }
                });
            } else if ($('#existingSectionSelect').is(':visible')) {
                if (selectedSectionId === "") {
                    toastr.error('يرجى اختيار قسم موجود.');
                    return;
                }

                assignSectionToLesson(currentLessonId, selectedSectionId);
                Swal.fire('تم!', 'تم تعيين القسم للدرس بنجاح.', 'success');
                $('#assignSectionModal').modal('hide');
            }
        });

        function assignSectionToLesson(lessonId, sectionId) {
            $.ajax({
                url: 'lessons_actions.php',
                type: 'POST',
                data: {
                    action: 'assign_section',
                    lesson_id: lessonId,
                    section_id: sectionId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('تم تعيين القسم بنجاح.');
                        // يمكنك تحديث الواجهة إذا لزم الأمر
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ في الاتصال بالخادم.');
                }
            });
        }

        // فتح مودال تحديد حالة الدرس
        $('.set-status-button').on('click', function() {
            currentLessonId = $(this).data('lesson-id');
            $('#setStatusModal').modal('show');
        });

        // تأكيد تعيين حالة الدرس
        $('#setStatusConfirmButton').on('click', function() {
            var selectedStatus = $('#lessonStatusSelect').val();
            if (selectedStatus === "") {
                toastr.error('يرجى اختيار حالة للدرس.');
                return;
            }

            if (currentLessonId === null) {
                toastr.error('معرف الدرس غير موجود.');
                return;
            }

            $.ajax({
                url: 'lessons_actions.php',
                type: 'POST',
                data: {
                    action: 'set_status',
                    lesson_id: currentLessonId,
                    status: selectedStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('تم تحديد حالة الدرس بنجاح.');
                        // تحديث حالة الدرس في الجدول
                        $('#lesson-row-' + currentLessonId + ' .lesson-status').text(getStatusLabel(selectedStatus));
                        $('#setStatusModal').modal('hide');
                        $('#lessonStatusSelect').val('');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ في الاتصال بالخادم.');
                }
            });
        });

        function getStatusLabel(status) {
            switch(status) {
                case 'watch':
                    return 'مشاهدة';
                case 'problem':
                    return 'مشكلة';
                case 'discussion':
                    return 'نقاش';
                case 'search':
                    return 'بحث';
                case 'retry':
                    return 'إعادة';
                case 'retry_again':
                    return 'إعادة ثانية';
                case 'review':
                    return 'مراجعة';
                default:
                    return 'غير محدد';
            }
        }

        // وظيفة تحديث نسبة الإكمال
        function updateCompletionPercentage() {
            $.ajax({
                url: 'lessons_actions.php',
                type: 'POST',
                data: { 
                    action: 'get_completion_percentage',
                    course_id: <?php echo $courseId; ?>
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('.progress-bar').css('width', response.percentage + '%').attr('aria-valuenow', response.percentage).text(response.percentage + '%');
                    }
                }
            });
        }

        // وظيفة تحديث حالة الدرس عند الضغط على زر المشاهدة
        $('.watch-button').on('click', function() {
            var lessonId = $(this).data('lesson-id');
            var currentViews = $(this).data('views');

            var newViews = (currentViews > 0) ? 0 : 1;
            var newButtonText = (newViews > 0) ? 'تم المشاهدة' : 'مشاهدة';

            $.ajax({
                url: 'lessons_actions.php',
                type: 'POST',
                data: {
                    action: 'toggle_watch',
                    lesson_id: lessonId,
                    views: newViews
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // تحديث النص والبيانات الخاصة بزر المشاهدة
                        $('#lesson-row-' + lessonId + ' .watch-button').text(newButtonText).data('views', newViews);
                        toastr.success('تم تحديث حالة المشاهدة بنجاح.');
                        updateCompletionPercentage();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ في الاتصال بالخادم.');
                }
            });
        });

        // وظيفة تعليم الدرس كمكتمل عند الضغط على الشيك بوكس
        $('.mark-complete-checkbox').on('change', function() {
            var lessonId = $(this).data('lesson-id');
            var isChecked = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: 'lessons_actions.php',
                type: 'POST',
                data: {
                    action: 'mark_complete',
                    lesson_id: lessonId,
                    completed: isChecked
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (isChecked) {
                            $('#lesson-row-' + lessonId).addClass('completed');
                            $('#lesson-row-' + lessonId + ' .lesson-status').text('مكتمل');
                            toastr.success('تم تعليم الدرس كمكتمل.');
                        } else {
                            $('#lesson-row-' + lessonId).removeClass('completed');
                            $('#lesson-row-' + lessonId + ' .lesson-status').text('غير مكتمل');
                            toastr.success('تم إلغاء تعليم الدرس كمكتمل.');
                        }
                        updateCompletionPercentage();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ في الاتصال بالخادم.');
                }
            });
        });

        // وظيفة تعليم الدرس كمكتمل عند الضغط على اسم الدرس
        $('.lesson-title').on('click', function() {
            var lessonId = $(this).data('lesson-id');

            $.ajax({
                url: 'lessons_actions.php',
                type: 'POST',
                data: {
                    action: 'mark_complete',
                    lesson_id: lessonId,
                    completed: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#lesson-row-' + lessonId).addClass('completed');
                        $('#lesson-row-' + lessonId + ' .lesson-status').text('مكتمل');
                        $('#lesson-row-' + lessonId + ' .mark-complete-checkbox').prop('checked', true);
                        toastr.success('تم تعليم الدرس كمكتمل.');
                        updateCompletionPercentage();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ في الاتصال بالخادم.');
                }
            });
        });
    });
    </script>
</body>
</html>
