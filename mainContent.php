<!-- أضف هذا الكود بعد عرض معلومات الدرس -->
<div class="mt-3">
    <select id="lessonStatus" class="form-select mb-2">
        <option value="">اختر الحالة</option>
    </select>
    <select id="lessonSection" class="form-select">
        <option value="">اختر القسم</option>
    </select>
</div>

<!-- أضف هذا الكود بعد الأزرار الموجودة -->
<button class="btn btn-success" id="watchLesson" data-lesson-id="<?php echo $lessonId; ?>">مشاهدة</button>