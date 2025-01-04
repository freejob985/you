/**
 * navigation.js
 * يتعامل مع وظائف التنقل بين الدروس
 * 
 * التبعيات:
 * - jQuery
 * - SweetAlert2 للتنبيهات
 */

$(document).ready(function() {
    // تهيئة أزرار التنقل
    initNavigationButtons();
    
    // تحديث حالة الأزرار عند تحميل الصفحة
    updateNavigationButtons();
});

/**
 * تهيئة أحداث النقر على أزرار التنقل
 */
function initNavigationButtons() {
    $('#prevLesson').click(function() {
        navigateToLesson('prev');
    });
    
    $('#nextLesson').click(function() {
        navigateToLesson('next');
    });
}

/**
 * تحديث حالة أزرار التنقل (تفعيل/تعطيل)
 */
function updateNavigationButtons() {
    const lessonId = new URLSearchParams(window.location.search).get('lesson_id');
    if (!lessonId) return;

    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: {
            action: 'get_adjacent_lessons',
            lesson_id: lessonId
        },
        success: function(response) {
            if (response.prev === null) {
                $('#prevLesson').prop('disabled', true);
            }
            if (response.next === null) {
                $('#nextLesson').prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error getting adjacent lessons:', error);
            Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث أزرار التنقل', 'error');
        }
    });
}

/**
 * الانتقال إلى الدرس السابق أو التالي
 * @param {string} direction - اتجاه التنقل ('prev' أو 'next')
 */
function navigateToLesson(direction) {
    const lessonId = new URLSearchParams(window.location.search).get('lesson_id');
    if (!lessonId) return;

    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'GET',
        data: {
            action: 'get_adjacent_lessons',
            lesson_id: lessonId
        },
        success: function(response) {
            const targetId = response[direction];
            if (targetId) {
                window.location.href = `show.php?lesson_id=${targetId}`;
            }
        },
        error: function(xhr, status, error) {
            console.error('Error navigating to lesson:', error);
            Swal.fire('خطأ!', 'حدث خطأ أثناء الانتقال إلى الدرس', 'error');
        }
    });
} 