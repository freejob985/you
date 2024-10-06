<?php
// تضمين ملف api_functions.php
require_once 'lessons/php.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<?php
// تضمين ملف header.php
require_once 'lessons/header.php';
?>
<body>
    <!-- الهيدر الثابت -->
    <header class="fixed-header">
        <div class="container">
            <h1>نظام إدارة الدروس</h1>
            <div class="course-info">
                <div class="info-item">
                    <i class="fas fa-book"></i>
                    <span>عدد الكورسات: <?php echo $totalCourses; ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-tasks"></i>
                    <span>نسبة الإكمال: <?php echo $overallCompletionPercentage; ?>%</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span>الدروس المتبقية: <?php echo $remainingLessons; ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-list-ol"></i>
                    <span>إجمالي الدروس: <?php echo $totalLessons; ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-5">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">إحصائيات الكورس</h5>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completionPercentage; ?>%;" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $completionPercentage; ?>% مكتمل (<?php echo $completedLessons; ?> دروس)
                            </div>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo 100 - $completionPercentage; ?>%;" aria-valuenow="<?php echo 100 - $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo 100 - $completionPercentage; ?>% غير مكتمل (<?php echo $totalLessons - $completedLessons; ?> دروس)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($currentLessons as $lesson): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 <?php echo ($lesson['status'] === 'completed') ? 'completed-card' : ''; ?>">
                        <div class="card-header" style="background-color: <?php echo getStatusColor($lesson['status']); ?>;">
                            <h5 class="card-title mb-0">
                                <span class="lesson-title <?php echo ($lesson['status'] === 'completed') ? 'completed' : ''; ?>" data-lesson-id="<?php echo $lesson['id']; ?>">
                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($lesson['thumbnail'])): ?>
                                <img src="<?php echo htmlspecialchars($lesson['thumbnail']); ?>" alt="Thumbnail" class="img-fluid mb-3 <?php echo ($lesson['status'] === 'completed') ? 'grayscale' : ''; ?>" style="width: 100%; height: auto;">
                            <?php endif; ?>
                            <p class="card-text">المدة: <?php echo formatDuration($lesson['duration']); ?></p>
                            <p class="card-text">
                                <span class="lesson-status badge <?php echo getStatusBadgeClass($lesson['status']); ?>">
                                    <?php echo getStatusLabel($lesson['status']); ?>
                                </span>
                            </p>
                            <div class="btn-group d-flex flex-wrap" role="group">
<a href="show.php?lesson_id=<?php echo $lesson['id']; ?>" class="btn btn-primary btn-sm mb-2">
    <i class="fas fa-eye"></i> عرض الدرس
</a>
                          <button class="btn <?php echo ($lesson['views'] > 0) ? 'btn-success' : 'btn-primary'; ?> btn-sm watch-button mb-2" data-lesson-id="<?php echo $lesson['id']; ?>" data-views="<?php echo $lesson['views']; ?>">
    <i class="fas <?php echo ($lesson['views'] > 0) ? 'fa-check' : 'fa-eye'; ?>"></i> <?php echo ($lesson['views'] > 0) ? 'تم المشاهدة' : 'مشاهدة'; ?>
</button>
                             <button class="btn btn-secondary btn-sm assign-section-button mb-2" data-bs-toggle="modal" data-bs-target="#assignSectionModal" data-lesson-id="<?php echo $lesson['id']; ?>">
    <i class="fas fa-layer-group"></i> تعيين القسم
</button>
                           <button class="btn btn-info btn-sm set-status-button mb-2" data-bs-toggle="modal" data-bs-target="#setStatusModal" data-lesson-id="<?php echo $lesson['id']; ?>">
    <i class="fas fa-flag"></i> تحديد الحالة
</button>
                                <button class="btn btn-danger btn-sm delete-lesson-button mb-2" data-lesson-id="<?php echo $lesson['id']; ?>">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                                <button class="btn btn-info btn-sm edit-tags-button mb-2" data-bs-toggle="modal" data-bs-target="#editTagsModal" data-lesson-id="<?php echo $lesson['id']; ?>">
    <i class="fas fa-tags"></i> تحرير الأقسام
</button>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input mark-complete-checkbox" type="checkbox" data-lesson-id="<?php echo $lesson['id']; ?>" <?php echo ($lesson['status'] === 'completed') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="flexSwitchCheckDefault">تم الإكمال</label>
                            </div>
                            <a href="show.php?lesson_id=<?php echo $lesson['id']; ?>" class="btn btn-primary btn-sm mb-2">
                                <i class="fas fa-eye"></i> عرض الدرس
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link rounded-circle" href="?course_id=<?php echo $courseId; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        
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
<!-- مودال لتحديد القسم -->
<div class="modal fade" id="assignSectionModal" tabindex="-1" aria-labelledby="assignSectionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="assignSectionModalLabel">تعيين القسم للدرس</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <select id="sectionSelect" class="form-select">
          <?php foreach ($sections as $section): ?>
            <option value="<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="button" class="btn btn-primary" id="confirmAssignSection">تأكيد</button>
      </div>
    </div>
  </div>
</div>

    <!-- مودال لتحديد الحالة -->
<!-- مودال لتحديد الحالة -->
<div class="modal fade" id="setStatusModal" tabindex="-1" aria-labelledby="setStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="setStatusModalLabel">تحديد حالة الدرس</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <select id="statusSelect" class="form-select" multiple>
          <option value="watch">مشاهدة</option>
          <option value="problem">مشكلة</option>
          <option value="discussion">نقاش</option>
          <option value="search">بحث</option>
          <option value="retry">إعادة</option>
          <option value="retry_again">إعادة ثانية</option>
          <option value="review">مراجعة</option>
          <option value="completed">مكتمل</option>
          <option value="excluded">مستبعد</option>
          <option value="project">مشروع تطبيقي</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="button" class="btn btn-primary" id="confirmSetStatus">تأكيد</button>
      </div>
    </div>
  </div>
</div>

<!-- مودال تحرير الأقسام -->
<div class="modal fade" id="editTagsModal" tabindex="-1" aria-labelledby="editTagsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTagsModalLabel">تحرير أقسام الدرس</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input id="lessonTags" type="text" placeholder="أدخل الأقسام هنا">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        <button type="button" class="btn btn-primary" id="confirmEditTags">تأكيد</button>
      </div>
    </div>
  </div>
</div>

    <!-- الفوتر الثابت -->
    <footer class="fixed-footer">
        <div class="container">
            <div class="footer-links">
                <a href="http://localhost/home/" class="footer-link"><i class="fas fa-home"></i> الرئيسية</a>
                <a href="http://localhost/blackboard/" class="footer-link"><i class="fas fa-chalkboard"></i> السبورة</a>
                <a href="http://localhost/task-ai/" class="footer-link"><i class="fas fa-tasks"></i> المهام</a>
                <a href="http://localhost/info-code/bt.php" class="footer-link"><i class="fas fa-code"></i> بنك الأكواد</a>
                <a href="http://localhost/administration/public/" class="footer-link"><i class="fas fa-folder"></i> الملفات</a>
                <a href="http://localhost/Columns/" class="footer-link"><i class="fas fa-columns"></i> الأعمدة</a>
                <a href="http://localhost/ask/" class="footer-link"><i class="fas fa-question-circle"></i> الأسئلة</a>
                <a href="http://localhost/phpmyadminx/" class="footer-link"><i class="fas fa-database"></i> إدارة قواعد البيانات</a>
                <a href="http://localhost/pr.php" class="footer-link"><i class="fas fa-bug"></i> اصطياد الأخطاء</a>
                <a href="http://localhost/Timmy/" class="footer-link"><i class="fas fa-clock"></i> تيمي</a>
                <a href="http://localhost/copy/" class="footer-link"><i class="fas fa-clipboard"></i> حافظة الملاحظات</a>
                <a href="http://localhost/Taskme/" class="footer-link"><i class="fas fa-calendar-check"></i> المهام اليومية</a>
                <a href="http://subdomain.localhost/tasks" class="footer-link"><i class="fas fa-project-diagram"></i> CRM</a>
            </div>
        </div>
    </footer>

<?php
// تضمين ملف footer.php
require_once 'lessons/footer.php';
?>

</body>
</html>