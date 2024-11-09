// تهيئة Tagify للأقسام
let sectionTagify;
let sectionsModal;

$(document).ready(function() {
    // تهيئة Modal
    sectionsModal = new bootstrap.Modal(document.getElementById('sectionsModal'));

    // تهيئة Tagify
    sectionTagify = new Tagify(document.querySelector('#sectionTags'), {
        maxTags: 10,
        dropdown: {
            maxItems: 20,
            classname: "tags-look",
            enabled: 0,
            closeOnSelect: false
        }
    });

    // زر فتح modal الأقسام
    $('#manageSections').click(function() {
        loadCurrentSections();
        sectionsModal.show();
    });

    // زر حفظ الأقسام
    $('#saveSections').click(function() {
        saveSections();
    });
});

// تحميل الأقسام الحالية
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
                sectionTagify.removeAllTags();
                sectionTagify.addTags(response.sections.map(s => s.name));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading sections:', error);
            Swal.fire('خطأ!', 'حدث خطأ أثناء تحميل الأقسام', 'error');
        }
    });
}

// عرض الأقسام في الواجهة
function displaySections(sections) {
    const sectionsList = $('.sections-list');
    sectionsList.empty();

    sections.forEach(section => {
        sectionsList.append(`
            <div class="section-item p-2 mb-2">
                <span class="section-name">${section.name}</span>
            </div>
        `);
    });
}

// حفظ الأقسام
function saveSections() {
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
                sectionsModal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'تم!',
                    text: 'تم تحديث الأقسام بنجاح',
                    confirmButtonText: 'حسناً'
                });
            } else {
                Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث الأقسام', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating sections:', error);
            Swal.fire('خطأ!', 'حدث خطأ أثناء الاتصال بالخادم', 'error');
        }
    });
}