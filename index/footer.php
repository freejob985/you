    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Tagify -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
 <script>
    // تهيئة Tagify
    var sectionsInput = document.querySelector('input[name=sectionsTags]');
    new Tagify(sectionsInput);

    var languageInput = document.querySelector('input[name=languageTags]');
    new Tagify(languageInput);
    var courseTagsInput = new Tagify(document.getElementById('courseTags'));


// حوالي السطر 20
document.getElementById('courseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const courseLink = document.getElementById('courseLink').value;
    const courseLanguage = document.getElementById('courseLanguage').value;
    
    if (courseLink && courseLanguage) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'هل تريد إضافة هذا الكورس؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، أضف الكورس',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // إظهار SVG المتحرك
                document.getElementById('loadingContainer').style.display = 'block';
                
                // إرسال البيانات إلى الخادم
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'courseLink': courseLink,
                        'courseLanguage': courseLanguage
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // إخفاء SVG المتحرك
                    document.getElementById('loadingContainer').style.display = 'none';
                    
                    if (data.success) {
                        Swal.fire('تم!', data.message, 'success');
                        // إعادة تعيين النموذج
                        document.getElementById('courseForm').reset();
                    } else {
                        Swal.fire('خطأ!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // إخفاء SVG المتحرك
                    document.getElementById('loadingContainer').style.display = 'none';
                    Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة الكورس.', 'error');
                });
            }
        });
    } else {
        Swal.fire('خطأ!', 'يرجى ملء جميع الحقول المطلوبة.', 'error');
    }
});





    document.getElementById('deleteAllData').addEventListener('click', function() {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'سيتم حذف جميع البيانات بشكل نهائي!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف الكل',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'delete_all'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم!', data.message, 'success');
                      } else {
        Swal.fire('خطأ!', data.message, 'error');
    }
})
.catch(error => {
    console.error('Error:', error);
    Swal.fire('خطأ!', 'حدث خطأ أثناء حذف البيانات.', 'error');
});
}
});
});

// إضافة أقسام للغة
document.getElementById('addSectionsBtn').addEventListener('click', function() {
$('#addSectionsModal').modal('show');
});

document.getElementById('addSectionsForm').addEventListener('submit', function(e) {
e.preventDefault();
const languageId = document.getElementById('languageSelect').value;
const sections = document.getElementById('sectionsTags').value;

fetch('', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        'action': 'add_sections',
        'languageId': languageId,
        'sections': sections
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        Swal.fire('تم!', data.message, 'success');
        $('#addSectionsModal').modal('hide');
        this.reset();
    } else {
        Swal.fire('خطأ!', data.message, 'error');
    }
})
.catch(error => {
    console.error('Error:', error);
    Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة الأقسام.', 'error');
});
});

// إضافة لغة جديدة
document.getElementById('addLanguageBtn').addEventListener('click', function() {
$('#addLanguageModal').modal('show');
});

document.getElementById('addLanguageForm').addEventListener('submit', function(e) {
e.preventDefault();
const languages = document.getElementById('languageTags').value;

fetch('', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        'action': 'add_language',
        'languages': languages
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        Swal.fire('تم!', data.message, 'success');
        $('#addLanguageModal').modal('hide');
        this.reset();
        // تحديث قائمة اللغات
        updateLanguageList();
    } else {
        Swal.fire('خطأ!', data.message, 'error');
    }
})
.catch(error => {
    console.error('Error:', error);
    Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة اللغة.', 'error');
});
});
function addLanguage() {
    const languageTags = document.getElementById('languageTags').value;
    
    if (languageTags) {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'add_language',
                'languageTags': languageTags
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('تم!', data.message, 'success');
                $('#addLanguageModal').modal('hide');
                document.getElementById('languageTags').value = '';
                
                // تحديث قائمة اللغات
                updateLanguageList();
            } else {
                Swal.fire('خطأ!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة اللغة.', 'error');
        });
    } else {
        Swal.fire('خطأ!', 'يرجى إدخال اسم اللغة.', 'error');
    }
}
// تحديث دالة تحديث قائمة اللغات
function updateLanguageList() {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'get_languages'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const languageSelect = document.getElementById('courseLanguage');
            const languageSelectModal = document.getElementById('languageSelect');
            languageSelect.innerHTML = '<option value="">اختر اللغة</option>';
            languageSelectModal.innerHTML = '';
            data.languages.forEach(language => {
                languageSelect.innerHTML += `<option value="${language.id}">${language.name}</option>`;
                languageSelectModal.innerHTML += `<option value="${language.id}">${language.name}</option>`;
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث قائمة اللغات.', 'error');
    });
}

// ربط الدالة بنموذج إضافة اللغة
document.getElementById('addLanguageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    addLanguage();
});

</script>
