<?php
// تضمين الملفات المطلوبة
require_once 'index/php.php';
require_once 'index/helper_functions.php';

// التحقق من وجود معرف الكورس
if (!isset($_GET['course_id'])) {
    die("يجب تحديد معرف الكورس");
}

$courseId = (int)$_GET['course_id'];

// جلب معلومات الكورس والحصول على معرف اللغة
try {
    $stmt = $db->prepare('SELECT c.*, t.id as language_id FROM courses c LEFT JOIN tags t ON c.language_id = t.id WHERE c.id = ?');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        die("الكورس غير موجود");
    }
} catch(PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// جلب الأقسام المرتبطة باللغة
$sections = [];
if ($course['language_id']) {
    $stmt = $db->prepare('SELECT * FROM sections WHERE language_id = ? ORDER BY name');
    $stmt->execute([$course['language_id']]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// حساب الإحصائيات المحدثة
$statsQuery = "SELECT 
    COUNT(*) as total_lessons,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_lessons,
    SUM(CASE WHEN status = 'completed' THEN duration ELSE 0 END) as completed_duration,
    SUM(CASE WHEN status != 'completed' THEN duration ELSE 0 END) as remaining_duration,
    SUM(duration) as total_duration
FROM lessons 
WHERE course_id = ?";

$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute([$courseId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// معالجة الفلترة والبحث
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 20;

// بناء استعلام SQL
$query = "SELECT l.*, s.name as section_name 
          FROM lessons l 
          LEFT JOIN sections s ON l.section_id = s.id 
          WHERE l.course_id = :course_id";
$params = [':course_id' => $courseId];

if ($search) {
    $query .= " AND l.title LIKE :search";
    $params[':search'] = "%$search%";
}

if ($status) {
    $query .= " AND l.status = :status";
    $params[':status'] = $status;
}

// إجمالي عدد الدروس
$countStmt = $db->prepare(str_replace('l.*, s.name as section_name', 'COUNT(*)', $query));
$countStmt->execute($params);
$totalLessons = $countStmt->fetchColumn();

// إضافة الترتيب والصفحات
$query .= " ORDER BY l.id ASC LIMIT :offset, :limit";
$params[':offset'] = ($page - 1) * $perPage;
$params[':limit'] = $perPage;

// جلب الدروس
try {
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// حساب عدد الصفحات
$totalPages = ceil($totalLessons / $perPage);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دروس <?php echo htmlspecialchars($course['title']); ?></title>
    
    <!-- الخطوط -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="course_lessons.css" rel="stylesheet">
    <link href="assets/contextMenu.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <!-- الإحصائيات المحدثة -->
        <div class="stats-container">
            <!-- الإحصائيات الحالية -->
            <div class="stat-card">
                <div class="stat-value"><?php echo formatDuration($stats['total_duration']); ?></div>
                <div class="stat-label">المدة الإجمالية</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_lessons'] - $stats['completed_lessons']; ?></div>
                <div class="stat-label">الدروس المتبقية</div>
            </div>
            <!-- إضافة شريط التقدم -->
            <div class="progress-card">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" 
                         style="width: <?php echo round(($stats['completed_lessons'] / $stats['total_lessons']) * 100); ?>%">
                        <?php echo round(($stats['completed_lessons'] / $stats['total_lessons']) * 100); ?>%
                    </div>
                </div>
                <div class="progress-label">تقدم الكورس</div>
            </div>
        </div>
        
        <!-- تحديث قسم الأقسام -->
        <div class="sections-header">
            <h3>الأقسام</h3>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                <i class="fas fa-plus"></i> إضافة قسم
            </button>
        </div>
        <div class="sections-container">
            <button class="section-button" data-section-id="all">الكل</button>
            <?php foreach ($sections as $section): ?>
                <button class="section-button draggable" 
                        data-section-id="<?php echo $section['id']; ?>"
                        data-section-name="<?php echo htmlspecialchars($section['name']); ?>"
                        draggable="true">
                    <?php echo htmlspecialchars($section['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- الفلترة والبحث -->
        <div class="filters-container">
            <form method="GET" action="" class="mb-3">
                <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
                <div class="filter-group">
                    <input type="text" name="search" class="search-input" 
                           placeholder="ابحث عن درس..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status" class="form-select" style="width: auto;">
                        <option value="">كل الحالات</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>مكتمل</option>
                        <option value="watching" <?php echo $status === 'watching' ? 'selected' : ''; ?>>قيد المشاهدة</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>قيد الانتظار</option>
                    </select>
                    <button type="submit" class="btn btn-primary">تطبيق</button>
                </div>
            </form>
        </div>
        
        <!-- جدول الدروس -->
        <div class="table-responsive">
            <table class="lessons-table">
                <thead>
                    <tr>
                        <th>عنوان الدرس</th>
                        <th>القسم</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="lessonsTableBody">
                    <?php foreach ($lessons as $lesson): ?>
                        <tr data-lesson-id="<?php echo $lesson['id']; ?>" class="lesson-row">
                            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                            <td class="section-cell" 
                                data-section-id="<?php echo $lesson['section_id']; ?>"
                                data-lesson-id="<?php echo $lesson['id']; ?>"
                                ondblclick="handleSectionDoubleClick(this)">
                                <?php echo htmlspecialchars($lesson['section_name'] ?? 'بدون قسم'); ?>
                            </td>
                            <td><?php echo getStatusLabel($lesson['status']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="show.php?lesson_id=<?php echo $lesson['id']; ?>" 
                                       class="action-button btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="action-button btn-success toggle-status" 
                                            data-lesson-id="<?php echo $lesson['id']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-button btn-danger delete-lesson" 
                                            data-lesson-id="<?php echo $lesson['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- إضافة قائمة الأقسام القابلة للسحب -->
        <div id="sectionsList" class="sections-draggable">
            <?php foreach ($sections as $section): ?>
                <div class="section-item" 
                     data-section-id="<?php echo $section['id']; ?>"
                     data-section-name="<?php echo htmlspecialchars($section['name']); ?>"
                     ondblclick="handleSectionItemDoubleClick(this)"
                     draggable="true">
                    <?php echo htmlspecialchars($section['name']); ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- التنقل بين الصفحات -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <?php if ($page > 1): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="pagination-button">
                        السابق
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="pagination-button <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?course_id=<?php echo $courseId; ?>&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="pagination-button">
                        التالي
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- إضافة هذا القسم في الـ HTML بعد عنوان الصفحة -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="stat-item">
                                    <h6>إجمالي الدروس</h6>
                                    <span class="stat-value"><?php echo $stats['total_lessons']; ?></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-item">
                                    <h6>الدروس المكتملة</h6>
                                    <span class="stat-value"><?php echo $stats['completed_lessons']; ?></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h6>مدة الدروس المكتملة</h6>
                                    <span class="stat-value"><?php echo formatDuration($stats['completed_duration']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h6>مدة الدروس المتبقية</h6>
                                    <span class="stat-value"><?php echo formatDuration($stats['remaining_duration']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-item">
                                    <h6>نسبة الإكمال</h6>
                                    <span class="stat-value"><?php echo round(($stats['completed_lessons'] / $stats['total_lessons']) * 100, 1); ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Context Menu -->
    <script src="assets/contextMenu.js"></script>

    <!-- مودال إضافة قسم جديد -->
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة قسم جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم القسم</label>
                        <input type="text" class="form-control" id="newSectionName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="saveSectionBtn">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // تفعيل فلترة الأقسام
            $('.section-button').click(function() {
                const sectionId = $(this).data('section-id');
                $('.section-button').removeClass('active');
                $(this).addClass('active');
                
                if (sectionId === 'all') {
                    $('.lessons-table tbody tr').show();
                } else {
                    $('.lessons-table tbody tr').hide();
                    $(`.lessons-table tbody tr[data-section-id="${sectionId}"]`).show();
                }
            });

            // تبديل حالة الدرس
            $('.toggle-status').click(function() {
                const lessonId = $(this).data('lesson-id');
                const row = $(this).closest('tr');
                
                $.ajax({
                    url: 'lessons_actions.php',
                    method: 'POST',
                    data: {
                        action: 'toggle_status',
                        lesson_id: lessonId
                    },
                    success: function(response) {
                        if (response.success) {
                            // تحديث حالة الدرس في الجدول
                            row.find('.status-badge')
                               .removeClass()
                               .addClass('status-badge status-' + response.new_status)
                               .text(response.status_label);
                               
                            // تحديث الإحصائيات
                            location.reload();
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث حالة الدرس', 'error');
                    }
                });
            });

            // حذف درس
            $('.delete-lesson').click(function() {
                const lessonId = $(this).data('lesson-id');
                const row = $(this).closest('tr');
                
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
                            method: 'POST',
                            data: {
                                action: 'delete_lesson',
                                lesson_id: lessonId
                            },
                            success: function(response) {
                                if (response.success) {
                                    row.fadeOut(400, function() {
                                        $(this).remove();
                                    });
                                    Swal.fire('تم!', 'تم حذف الدرس بنجاح.', 'success');
                                    // تحديث الإحصائيات
                                    location.reload();
                                } else {
                                    Swal.fire('خطأ!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('خطأ!', 'حدث خطأ أثناء حذف الدرس', 'error');
                            }
                        });
                    }
                });
            });

            // تهيئة مودال إضافة قسم جديد
            const addSectionModal = new bootstrap.Modal(document.getElementById('addSectionModal'));
            
            // معالج حدث النقر على زر حفظ القسم الجديد
            $('#saveSectionBtn').click(function() {
                const sectionName = $('#newSectionName').val().trim();
                const languageId = <?php echo $course['language_id']; ?>;
                
                if (!sectionName) {
                    Swal.fire('تنبيه', 'يرجى إدخال اسم القسم', 'warning');
                    return;
                }
                
                // إرسال طلب إضافة القسم
                $.ajax({
                    url: 'lessons_actions.php',
                    method: 'POST',
                    data: {
                        action: 'add_section',
                        language_id: languageId,
                        section_name: sectionName
                    },
                    success: function(response) {
                        if (response.success) {
                            // إضافة القسم الجديد إلى القائمة المنسدلة
                            const newOption = new Option(response.section.name, response.section.id);
                            $('#sectionFilter').append(newOption);
                            
                            // إغلاق المودال وإظهار رسالة نجاح
                            addSectionModal.hide();
                            $('#newSectionName').val('');
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'تم!',
                                text: 'تم إضافة القسم بنجاح'
                            });
                            
                            // تحديث قائمة الأقسام
                            loadSections();
                        } else {
                            Swal.fire('خطأ', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('خطأ', 'حدث خطأ أثناء إضافة القسم', 'error');
                    }
                });
            });
            
            // دالة تحديث قائمة الأقسام
            function loadSections() {
                $.ajax({
                    url: 'lessons_actions.php',
                    method: 'POST',
                    data: {
                        action: 'get_sections',
                        language_id: <?php echo $course['language_id']; ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            const sectionFilter = $('#sectionFilter');
                            sectionFilter.empty();
                            sectionFilter.append(new Option('جميع الأقسام', ''));
                            
                            response.sections.forEach(function(section) {
                                sectionFilter.append(new Option(section.name, section.id));
                            });
                        }
                    }
                });
            }
        });
    </script>

    <script>
    /**
     * تهيئة وظائف السحب والإفلات
     */
    document.addEventListener('DOMContentLoaded', function() {
        // تهيئة أزرار الأقسام للسحب
        const sectionButtons = document.querySelectorAll('.section-button.draggable');
        sectionButtons.forEach(button => {
            button.addEventListener('dragstart', handleButtonDragStart);
            button.addEventListener('dragend', handleDragEnd);
        });

        // تهيئة خلايا الجدول كمناطق إفلات
        const sectionCells = document.querySelectorAll('.section-cell');
        sectionCells.forEach(cell => {
            cell.addEventListener('dragover', handleDragOver);
            cell.addEventListener('dragenter', handleDragEnter);
            cell.addEventListener('dragleave', handleDragLeave);
            cell.addEventListener('drop', handleDrop);
        });
    });

    /**
     * معالجة بدء سحب زر القسم
     * @param {DragEvent} e - حدث السحب
     */
    function handleButtonDragStart(e) {
        const button = e.target;
        const sectionId = button.dataset.sectionId;
        const sectionName = button.dataset.sectionName;

        e.dataTransfer.setData('application/json', JSON.stringify({
            sectionId: sectionId,
            sectionName: sectionName,
            sourceType: 'button'
        }));

        button.classList.add('dragging');
        
        // إضافة تأثير بصري للسحب
        const dragImage = button.cloneNode(true);
        dragImage.style.opacity = '0.6';
        document.body.appendChild(dragImage);
        e.dataTransfer.setDragImage(dragImage, 0, 0);
        setTimeout(() => document.body.removeChild(dragImage), 0);
    }

    /**
     * معالجة نهاية السحب
     * @param {DragEvent} e - حدث السحب
     */
    function handleDragEnd(e) {
        e.target.classList.remove('dragging');
        document.querySelectorAll('.drag-over').forEach(el => {
            el.classList.remove('drag-over');
        });
    }

    /**
     * معالجة السحب فوق منطقة الإفلات
     * @param {DragEvent} e - حدث السحب
     */
    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    }

    /**
     * معالجة الدخول إلى منطقة الإفلات
     * @param {DragEvent} e - حدث السحب
     */
    function handleDragEnter(e) {
        const cell = e.target.closest('.section-cell');
        if (cell) {
            cell.classList.add('drag-over');
        }
    }

    /**
     * معالجة الخروج من منطقة الإفلات
     * @param {DragEvent} e - حدث السحب
     */
    function handleDragLeave(e) {
        const cell = e.target.closest('.section-cell');
        const relatedTarget = e.relatedTarget?.closest('.section-cell');
        if (cell && cell !== relatedTarget) {
            cell.classList.remove('drag-over');
        }
    }

    /**
     * معالجة إفلات العنصر
     * @param {DragEvent} e - حدث السحب
     */
    function handleDrop(e) {
        e.preventDefault();
        const cell = e.target.closest('.section-cell');
        if (!cell) return;

        cell.classList.remove('drag-over');

        try {
            const data = JSON.parse(e.dataTransfer.getData('application/json'));
            const lessonId = cell.dataset.lessonId;

            if (data.sourceType === 'button' && lessonId) {
                // إظهار مؤشر التحميل
                const originalContent = cell.innerHTML;
                cell.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                // تحديث القسم
                updateLessonSection(lessonId, data.sectionId, cell, originalContent);
            }
        } catch (error) {
            console.error('خطأ في معالجة البيانات المنقولة:', error);
        }
    }

    /**
     * تحديث قسم الدرس
     * @param {string} lessonId - معرف الدرس
     * @param {string} sectionId - معرف القسم
     * @param {HTMLElement} cell - خلية الجدول
     * @param {string} originalContent - المحتوى الأصلي للخلية
     */
    function updateLessonSection(lessonId, sectionId, cell, originalContent) {
        $.ajax({
            url: 'lessons_actions.php',
            method: 'POST',
            data: {
                action: 'update_lesson_section',
                lesson_id: lessonId,
                section_id: sectionId
            },
            success: function(response) {
                if (response.success) {
                    cell.textContent = response.section_name;
                    cell.dataset.sectionId = sectionId;
                    
                    // تحديث التنسيقات
                    updateSectionCellStyles(cell);
                    
                    // إظهار رسالة نجاح
                    toastr.success('تم تحديث القسم بنجاح');
                } else {
                    cell.innerHTML = originalContent;
                    toastr.error(response.message);
                }
            },
            error: function() {
                cell.innerHTML = originalContent;
                toastr.error('حدث خطأ في الاتصال بالخادم');
            }
        });
    }

    /**
     * تحديث تنسيقات خلية القسم
     * @param {HTMLElement} cell - خلية القسم
     */
    function updateSectionCellStyles(cell) {
        // إزالة الألوان السابقة
        cell.classList.forEach(className => {
            if (className.startsWith('section-color-')) {
                cell.classList.remove(className);
            }
        });
        
        // إضافة اللون الجديد
        const sectionId = cell.dataset.sectionId;
        if (sectionId) {
            const colorIndex = (parseInt(sectionId) % 8) + 1;
            cell.classList.add(`section-color-${colorIndex}`);
        }
    }
    </script>

    <script>
    /**
     * معالجة النقر المزدوج على خلية القسم في الجدول
     * @param {HTMLElement} cell - خلية القسم
     */
    function handleSectionDoubleClick(cell) {
        const lessonId = cell.dataset.lessonId;
        
        // تأكيد إزالة القسم
        Swal.fire({
            title: 'إزالة القسم',
            text: 'هل تريد إزالة هذا القسم من الدرس؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                updateLessonSection(lessonId, null, cell);
            }
        });
    }

    /**
     * معالجة النقر المزدوج على عنصر القسم في القائمة
     * @param {HTMLElement} item - عنصر القسم
     */
    function handleSectionItemDoubleClick(item) {
        const sectionId = item.dataset.sectionId;
        const sectionName = item.dataset.sectionName;
        
        // تأكيد حذف القسم
        Swal.fire({
            title: 'حذف القسم',
            text: `هل تريد حذف القسم "${sectionName}"؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteSection(sectionId, item);
            }
        });
    }

    /**
     * حذف قسم من قاعدة البيانات
     * @param {number} sectionId - معرف القسم
     * @param {HTMLElement} element - عنصر القسم في DOM
     */
    function deleteSection(sectionId, element) {
        $.ajax({
            url: 'lessons_actions.php',
            method: 'POST',
            data: {
                action: 'delete_section',
                section_id: sectionId
            },
            success: function(response) {
                if (response.success) {
                    // إزالة العنصر من DOM
                    element.remove();
                    
                    // تحديث خلايا الجدول المرتبطة
                    $(`.section-cell[data-section-id="${sectionId}"]`).each(function() {
                        $(this).text('بدون قسم').removeAttr('data-section-id');
                    });
                    
                    Swal.fire('تم!', 'تم حذف القسم بنجاح', 'success');
                } else {
                    Swal.fire('خطأ', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('خطأ', 'حدث خطأ أثناء حذف القسم', 'error');
            }
        });
    }

    // تحديث دالة updateLessonSection لتدعم إزالة القسم
    function updateLessonSection(lessonId, sectionId, cell) {
        $.ajax({
            url: 'lessons_actions.php',
            method: 'POST',
            data: {
                action: 'update_lesson_section',
                lesson_id: lessonId,
                section_id: sectionId
            },
            success: function(response) {
                if (response.success) {
                    // تحديث نص الخلية
                    cell.textContent = sectionId ? response.section_name : 'بدون قسم';
                    
                    if (sectionId) {
                        cell.dataset.sectionId = sectionId;
                    } else {
                        cell.removeAttribute('data-section-id');
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'تم!',
                        text: 'تم تحديث القسم بنجاح',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('خطأ', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('خطأ', 'حدث خطأ أثناء تحديث القسم', 'error');
            }
        });
    }
    </script>

    <script>
    // تهيئة السحب والإفلات للأقسام
    document.addEventListener('DOMContentLoaded', function() {
        // تهيئة السحب والإفلات بين الدروس
        const lessonsTableBody = document.getElementById('lessonsTableBody');
        new Sortable(lessonsTableBody, {
            animation: 150,
            handle: '.section-cell', // السماح بالسحب من خلية القسم فقط
            draggable: 'tr', // السماح بسحب صفوف الجدول
            onEnd: function(evt) {
                const lessonId = evt.item.getAttribute('data-lesson-id');
                const targetRow = evt.item;
                const prevRow = targetRow.previousElementSibling;
                const nextRow = targetRow.nextElementSibling;
                
                // تحديث ترتيب الدروس في قاعدة البيانات
                updateLessonOrder(lessonId, prevRow, nextRow);
            }
        });

        // تهيئة السحب والإفلات من أزرار الأقسام إلى الجدول
        const sectionButtons = document.querySelectorAll('.section-button');
        sectionButtons.forEach(button => {
            button.setAttribute('draggable', 'true');
            button.addEventListener('dragstart', handleSectionDragStart);
        });

        const sectionCells = document.querySelectorAll('.section-cell');
        sectionCells.forEach(cell => {
            cell.addEventListener('dragover', handleDragOver);
            cell.addEventListener('drop', handleSectionDrop);
        });
    });

    // معالجة بدء سحب زر القسم
    function handleSectionDragStart(e) {
        const sectionId = e.target.getAttribute('data-section-id');
        e.dataTransfer.setData('text/plain', sectionId);
        e.target.classList.add('dragging');
    }

    // السماح بالإفلات
    function handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('drag-over');
    }

    // معالجة إفلات القسم
    function handleSectionDrop(e) {
        e.preventDefault();
        const cell = e.currentTarget;
        cell.classList.remove('drag-over');
        
        const sectionId = e.dataTransfer.getData('text/plain');
        const lessonId = cell.getAttribute('data-lesson-id');
        
        // تحديث قسم الدرس
        updateLessonSection(lessonId, sectionId, cell);
    }

    // تحديث ترتيب الدروس
    function updateLessonOrder(lessonId, prevRow, nextRow) {
        const data = {
            lesson_id: lessonId,
            prev_lesson_id: prevRow ? prevRow.getAttribute('data-lesson-id') : null,
            next_lesson_id: nextRow ? nextRow.getAttribute('data-lesson-id') : null
        };

        $.ajax({
            url: 'lessons_actions.php',
            method: 'POST',
            data: {
                action: 'update_lesson_order',
                ...data
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('تم تحديث ترتيب الدروس بنجاح');
                } else {
                    toastr.error('حدث خطأ أثناء تحديث الترتيب');
                    // إعادة تحميل الصفحة لاستعادة الترتيب الصحيح
                    location.reload();
                }
            },
            error: function() {
                toastr.error('حدث خطأ في الاتصال بالخادم');
                location.reload();
            }
        });
    }
    </script>
</body>
</html> 