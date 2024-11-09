// تهيئة Tagify للأقسام
let sectionTagify = new Tagify(document.querySelector('#sectionTags'), {
    maxTags: 10,
    dropdown: {
        maxItems: 20,
        classname: "tags-look",
        enabled: 0,
        closeOnSelect: false
    }
});

// تحميل الأقسام الحالية عند تحميل الصفحة
function loadCurrentSections() {
    const lessonId = new URLSearchParams(window.location.search).get('lesson_id');
    if (!lessonId) return;

    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'POST',
        data: {
            action: 'get_lesson_sections',
            lesson_id: lessonId
        },
        success: function(response) {
            if (response.success) {
                displaySections(response.sections);
                // تحديث Tagify بالأقسام الحالية
                sectionTagify.addTags(response.sections.map(s => s.name));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading sections:', error);
        }
    });
}

// عرض الأقسام في الواجهة
function displaySections(sections) {
    const sectionsList = $('.sections-list');
    sectionsList.empty();

    sections.forEach(section => {
        sectionsList.append(`
            <div class="section-item p-2 mb-2 bg-light rounded">
                <span class="section-name">${section.name}</span>
            </div>
        `);
    });
}

// معالجة تقديم نموذج الأقسام
$('#sectionsForm').submit(function(e) {
    e.preventDefault();
    const lessonId = new URLSearchParams(window.location.search).get('lesson_id');
    const languageId = $('#lessonLanguageId').val();
    const sections = sectionTagify.value;

    $.ajax({
        url: 'show/ajax_handler.php',
        method: 'POST',
        data: {
            action: 'update_lesson_sections',
            lesson_id: lessonId,
            language_id: languageId,
            sections: sections
        },
        success: function(response) {
            if (response.success) {
                displaySections(response.sections);
                Swal.fire('تم!', 'تم تحديث الأقسام بنجاح', 'success');
            } else {
                Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث الأقسام', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating sections:', error);
            Swal.fire('خطأ!', 'حدث خطأ أثناء الاتصال بالخادم', 'error');
        }
    });
});

// تحميل الأقسام عند تحميل الصفحة
$(document).ready(function() {
    loadCurrentSections();
});