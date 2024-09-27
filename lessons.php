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

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'completed':
            return 'bg-success';
        case 'watch':
        case 'review':
            return 'bg-primary';
        case 'problem':
        case 'retry':
        case 'retry_again':
            return 'bg-warning';
        case 'discussion':
        case 'search':
            return 'bg-info';
        case 'excluded':
            return 'bg-danger';
        case 'project':
            return 'bg-secondary';
        default:
            return 'bg-secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'completed':
            return 'مكتمل';
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
        case 'excluded':
            return 'مستبعد';
        case 'project':
            return 'مشروع تطبيقي';
        default:
            return 'غير محدد';
    }
}
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
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <!-- DataTables Responsive CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.7/css/responsive.bootstrap5.min.css">
    
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
    font-weight: 700 !important;
}
        .thumbnail-img {
            width: 60px;
            height: auto;
        }
        .btn-group .btn {
            margin-right: 5px;
        }
        .watch-button {
            min-width: 100px;
        }
        .form-check-input {
            cursor: pointer;
        }
        #lessonsTable tbody tr {
            transition: background-color 0.3s ease;
        }
        #lessonsTable tbody tr:hover {
            background-color: #f5f5f5;
        }
        .badge.bg-success { background-color: #28a745 !important; }
        .badge.bg-warning { background-color: #ffc107 !important; }
        .badge.bg-danger { background-color: #dc3545 !important; }
        .badge.bg-info { background-color: #17a2b8 !important; }
        .badge.bg-primary { background-color: #007bff !important; }
        .badge.bg-secondary { background-color: #6c757d !important; }
    #lessonsTable {
        width: 100% !important;
    }
    
    .dataTables_wrapper {
        overflow-x: hidden;
overflow-y: hidden;
    }
    
    .table-responsive {
        overflow-x: hidden;
    }
    #lessonsTable td, #lessonsTable th {
        text-align: center;
        vertical-align: middle;
    }

    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <div class="table-responsive">
            <table id="lessonsTable" class="table table-striped table-bordered table-hover shadow-sm">
                <thead class="bg-primary text-white">
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
                            <td style="padding: 0px;">
                                <?php if (!empty($lesson['thumbnail'])): ?>
                                    <img src="<?php echo htmlspecialchars($lesson['thumbnail']); ?>" alt="Thumbnail" class="img-thumbnail" style="width:50%;height: 50%;">
                                <?php else: ?>
                                    <span class="text-muted">لا توجد صورة</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="lesson-title" data-lesson-id="<?php echo $lesson['id']; ?>" style="cursor: pointer;">
                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDuration($lesson['duration']); ?></td>
                            <td>
                                <span class="lesson-status badge <?php echo getStatusBadgeClass($lesson['status']); ?>">
                                    <?php echo getStatusLabel($lesson['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-primary btn-sm watch-button" data-lesson-id="<?php echo $lesson['id']; ?>" data-views="<?php echo $lesson['views']; ?>">
                                        <i class="fas fa-eye"></i> <?php echo ($lesson['views'] > 0) ? 'تم المشاهدة' : 'مشاهدة'; ?>
                                    </button>
                                    <button class="btn btn-secondary btn-sm assign-section-button" data-lesson-id="<?php echo $lesson['id']; ?>">
                                        <i class="fas fa-layer-group"></i>
                                    </button>
                                    <button class="btn btn-info btn-sm set-status-button" data-lesson-id="<?php echo $lesson['id']; ?>">
                                        <i class="fas fa-flag"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-lesson-button" data-lesson-id="<?php echo $lesson['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <div class="form-check form-switch d-inline-block ms-2">
                                        <input class="form-check-input mark-complete-checkbox" type="checkbox" data-lesson-id="<?php echo $lesson['id']; ?>" <?php echo ($lesson['status'] === 'completed') ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <h4>نسبة إكمال الكورس</h4>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: <?php echo $completionPercentage; ?>%;" 
                     aria-valuenow="<?php echo $completionPercentage; ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
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

    <!-- مكتبة jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Responsive JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.7/js/responsive.bootstrap5.min.js"></script>
    
    <!-- مكتبة Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- مكتبة Tagify -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    
    <!-- مكتبة SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- مكتبة Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // تهيئة جدول DataTables
        var table = $('#lessonsTable').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
            },
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -1 }
            ],
            order: [[1, 'asc']],
            scrollY: false,
            scrollCollapse: false,
            paging: true,
            searching: false
        });
        
        // تهيئة Tagify للأقسام الجديدة
        var input = document.querySelector('#newSectionTags');
        new Tagify(input);
        
        // تهيئة Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
        
  $('.watch-button').on('click', function() {
    var lessonId = $(this).data('lesson-id');
    var views = parseInt($(this).data('views'));
    var newViews = views === 0 ? 1 : 0;
    var button = $(this);
    var row = button.closest('tr');
    var lessonTitle = row.find('.lesson-title');
    
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
                button.data('views', newViews);
                button.html('<i class="fas fa-eye"></i> ' + (newViews === 1 ? 'تم المشاهدة' : 'مشاهدة'));
                if (newViews === 1) {
                    lessonTitle.css({'text-decoration': 'line-through', 'font-weight': 'bold'});
                } else {
                    lessonTitle.css({'text-decoration': 'none', 'font-weight': 'normal'});
                }
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('حدث خطأ في الاتصال بالخادم.');
        }
    });
});
        // مربع اختيار إكمال الدرس
        $('.mark-complete-checkbox').on('change', function() {
            var lessonId = $(this).data('lesson-id');
            var isCompleted = $(this).is(':checked');
            var row = $(this).closest('tr');
            var lessonTitle = row.find('.lesson-title');
            
            $.ajax({
                url: 'lessons_actions.php',
                type: 'POST',
                data: {
                    action: 'mark_complete',
                    lesson_id: lessonId,
                    completed: isCompleted ? 1 : 0
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (isCompleted) {
                            row.addClass('completed');
                            lessonTitle.css({'text-decoration': 'line-through', 'font-weight': 'bold'});
                            row.find('.lesson-status').removeClass().addClass('lesson-status badge bg-success').text('مكتمل');
                        } else {
                            row.removeClass('completed');
                            lessonTitle.css({'text-decoration': 'none', 'font-weight': 'normal'});
                            row.find('.lesson-status').removeClass().addClass('lesson-status badge bg-warning').text('غير مكتمل');
                        }
                        updateCompletionPercentage();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('حدث خطأ في الاتصال بالخادم.');
                }
            });
        });
        
        // زر تعيين القسم
        $('.assign-section-button').on('click', function() {
            var lessonId = $(this).data('lesson-id');
            $('#assignSectionModal').data('lesson-id', lessonId).modal('show');
        });
        
        // أزرار اختيار نوع القسم
        $('#newSectionButton').on('click', function() {
            $('#newSectionInput').show();
            $('#existingSectionSelect').hide();
        });
        
        $('#existingSectionButton').on('click', function() {
            $('#newSectionInput').hide();
            $('#existingSectionSelect').show();
        });
        
        // زر تأكيد تعيين القسم
        $('#assignSectionConfirmButton').on('click', function() {
            var lessonId = $('#assignSectionModal').data('lesson-id');
            var sectionId = $('#existingSections').val();
            var newSections = $('#newSectionTags').val();
            
            if (sectionId) {
                assignExistingSection(lessonId, sectionId);
            } else if (newSections) {
                addNewSections(lessonId, newSections);
            } else {
                toastr.error('الرجاء اختيار قسم موجود أو إضافة قسم جديد.');
            }
        });
        
        // تحديث حالة الدرس
        $('.set-status-button').on('click', function() {
            var lessonId = $(this).data('lesson-id');
            showSetStatusModal(lessonId);
        });
        
        // حذف الدرس
        $('.delete-lesson-button').on('click', function() {
            var lessonId = $(this).data('lesson-id');
            confirmDeleteLesson(lessonId);
        });
    });
    
    function assignExistingSection(lessonId, sectionId) {
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
                    $('#assignSectionModal').modal('hide');
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('حدث خطأ في الاتصال بالخادم.');
            }
        });
    }
    
    function addNewSections(lessonId, newSections) {
        $.ajax({
            url: 'lessons_actions.php',
            type: 'POST',
            data: {
                action: 'add_new_sections',
                lesson_id: lessonId,
                sections: newSections
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#assignSectionModal').modal('hide');
                    toastr.success(response.message);
                    // تحديث قائمة الأقسام الموجودة
                    var select = $('#existingSections');
                    response.section_ids.forEach(function(sectionId, index) {
                        var sectionName = JSON.parse(newSections)[index].value;
                        select.append($('<option>', {
                            value: sectionId,
                            text: sectionName
                        }));
                    });
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('حدث خطأ في الاتصال بالخادم.');
            }
        });
    }
    
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
                    var percentage = response.percentage;
                    $('.progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage).text(percentage + '%');
                }
            },
            error: function() {
                console.error('فشل في تحديث نسبة الإكمال');
            }
        });
    }
    
    function showSetStatusModal(lessonId) {
        Swal.fire({
            title: 'تحديد حالة الدرس',
            input: 'select',
            inputOptions: {
                'watch': 'مشاهدة',
                'problem': 'مشكلة',
                'discussion': 'نقاش',
                'search': 'بحث',
                'retry': 'إعادة',
                'retry_again': 'إعادة ثانية',
                'review': 'مراجعة',
                'completed': 'مكتمل',
                'excluded': 'مستبعد',
                'project': 'مشروع تطبيقي'
            },
            showCancelButton: true,
            confirmButtonText: 'تحديث',
            cancelButtonText: 'إلغاء',
            showLoaderOnConfirm: true,
            preConfirm: (status) => {
                return $.ajax({
                    url: 'lessons_actions.php',
                    type: 'POST',
                    data: {
                        action: 'set_status',
                        lesson_id: lessonId,
                        status: status
                    },
                    dataType: 'json'
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message)
                    }
                    return response
                }).catch(error => {
                    Swal.showValidationMessage(`فشل الطلب: ${error}`)
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                updateLessonStatusUI(lessonId, result.value.status);
                Swal.fire('تم!', 'تم تحديث حالة الدرس بنجاح.', 'success');
            }
        });
    }
    
    function confirmDeleteLesson(lessonId) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "لن تتمكن من استرجاع هذا الدرس!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذفه!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'lessons_actions.php',
                    type: 'POST',
                    data: {
                        action: 'delete_lesson',
                        lesson_id: lessonId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#lesson-row-' + lessonId).remove();
                            updateCompletionPercentage();
                            Swal.fire('تم الحذف!', 'تم حذف الدرس بنجاح.', 'success');
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('خطأ!', 'حدث خطأ أثناء الاتصال بالخادم.', 'error');
                    }
                });
            }
        });
    }
    
 function updateLessonStatusUI(lessonId, status) {
    var row = $('#lesson-row-' + lessonId);
    var statusBadge = row.find('.lesson-status');
    var lessonTitle = row.find('.lesson-title');
    
    // تحديث الصف
    row.removeClass('completed');
    lessonTitle.css({'text-decoration': 'none', 'font-weight': 'normal'});
    if (status === 'completed') {
        row.addClass('completed');
        lessonTitle.css({'text-decoration': 'line-through', 'font-weight': 'bold'});
    }
    
    // تحديث شارة الحالة
    statusBadge.removeClass().addClass('lesson-status badge ' + getStatusBadgeClass(status));
    statusBadge.text(getStatusLabel(status));
    
    // تحديث مربع الاختيار
    row.find('.mark-complete-checkbox').prop('checked', status === 'completed');
}
    
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
    </script>
</body>
</html>
