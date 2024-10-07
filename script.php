function loadStatusesAndSections() {
    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: { action: 'get_statuses' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let statusOptions = '<option value="">اختر الحالة</option>';
                response.statuses.forEach(status => {
                    statusOptions += `<option value="${status}">${status}</option>`;
                });
                $('#lessonStatus').html(statusOptions);
            }
        }
    });

    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: { action: 'get_sections' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let sectionOptions = '<option value="">اختر القسم</option>';
                response.sections.forEach(section => {
                    sectionOptions += `<option value="${section.id}">${section.name}</option>`;
                });
                $('#lessonSection').html(sectionOptions);
            }
        }
    });
}

function updateLessonStatusOrSection(lessonId, type, value) {
    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'POST',
        data: {
            action: 'update_lesson_status_or_section',
            lesson_id: lessonId,
            type: type,
            value: value,
            course_id: <?php echo isset($courseId) ? $courseId : 0; ?>
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                updateStatistics(response.statistics);
            } else {
                toastr.error(response.error);
            }
        }
    });
}

// أضف هذا الكود في نهاية $(document).ready(function() { ... });

loadStatusesAndSections();

$('#lessonStatus, #lessonSection').change(function() {
    const lessonId = <?php echo $lessonId; ?>;
    const type = $(this).attr('id') === 'lessonStatus' ? 'status' : 'section';
    const value = $(this).val();
    updateLessonStatusOrSection(lessonId, type, value);
});

$('#watchLesson').click(function() {
    const lessonId = $(this).data('lesson-id');
    updateLessonStatusOrSection(lessonId, 'status', 'watched');
    $(this).prop('disabled', true).text('تمت المشاهدة');
});