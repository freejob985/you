    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Tagify -->
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
 <script>
    // Initialize Tagify
    var sectionsInput = document.querySelector('input[name=sectionsTags]');
    new Tagify(sectionsInput);

    var languageInput = document.querySelector('input[name=languageTags]');
    new Tagify(languageInput);
    var courseTagsInput = new Tagify(document.getElementById('courseTags'));

    // Course form submission
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
                    // Show loading animation
                    document.getElementById('loadingContainer').style.display = 'block';
                    
                    // Send data to server
                    $.ajax({
                        url: '',
                        method: 'POST',
                        data: {
                            courseLink: courseLink,
                            courseLanguage: courseLanguage
                        },
                        success: function(response) {
                            // Hide loading animation
                            document.getElementById('loadingContainer').style.display = 'none';
                            
                            if (response.success) {
                                Swal.fire('تم!', response.message, 'success');
                                // Reset the form
                                document.getElementById('courseForm').reset();
                            } else {
                                Swal.fire('خطأ!', response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            // Hide loading animation
                            document.getElementById('loadingContainer').style.display = 'none';
                            Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة الكورس.', 'error');
                        }
                    });
                }
            });
        } else {
            Swal.fire('خطأ!', 'يرجى ملء جميع الحقول المطلوبة.', 'error');
        }
    });

    // Delete all data
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
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: {
                        action: 'delete_all'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('تم!', response.message, 'success');
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire('خطأ!', 'حدث خطأ أثناء حذف البيانات.', 'error');
                    }
                });
            }
        });
    });

    // Add sections to language
    document.getElementById('addSectionsBtn').addEventListener('click', function() {
        $('#addSectionsModal').modal('show');
    });

    document.getElementById('addSectionsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const languageId = document.getElementById('languageSelect').value;
        const sectionsTags = document.getElementById('sectionsTags').value;

        $.ajax({
            url: '',
            method: 'POST',
            data: {
                action: 'add_sections',
                languageId: languageId,
                sectionsTags: sectionsTags
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('تم!', response.message, 'success');
                    $('#addSectionsModal').modal('hide');
                    document.getElementById('addSectionsForm').reset();
                    // Update language select options
                    updateLanguageSelect();
                } else {
                    Swal.fire('خطأ!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة الأقسام.', 'error');
            }
        });
    });

    // Add new language
    document.getElementById('addLanguageBtn').addEventListener('click', function() {
        $('#addLanguageModal').modal('show');
    });

    document.getElementById('addLanguageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const languages = document.getElementById('languageTags').value;

        $.ajax({
            url: '',
            method: 'POST',
            data: {
                action: 'add_language',
                languageTags: languages
            },
            success: function(response) {
                // تحويل الاستجابة إلى كائن JSON إذا لم تكن كذلك بالفعل
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        Swal.fire('خطأ!', 'حدث خطأ أثناء معالجة الاستجابة.', 'error');
                        return;
                    }
                }

                if (response.success) {
                    Swal.fire({
                        title: 'تم!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'حسنًا'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#addLanguageModal').modal('hide');
                            document.getElementById('addLanguageForm').reset();
                            // Update language select options
                            updateLanguageSelect();
                        }
                    });
                } else {
                    Swal.fire('تنبيه!', response.message, 'warning');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة اللغة.', 'error');
            }
        });
    });

    // Update language select options
    function updateLanguageSelect() {
        $.ajax({
            url: '',
            method: 'POST',
            data: {
                action: 'get_languages'
            },
            success: function(response) {
                if (response.success) {
                    const courseLanguageSelect = document.getElementById('courseLanguage');
                    const languageSelect = document.getElementById('languageSelect');
                    
                    // Clear existing options
                    courseLanguageSelect.innerHTML = '<option value="">اختر اللغة</option>';
                    languageSelect.innerHTML = '';
                    
                    // Add new options
                    response.languages.forEach(function(language) {
                        courseLanguageSelect.innerHTML += `<option value="${language.id}">${language.name}</option>`;
                        languageSelect.innerHTML += `<option value="${language.id}">${language.name}</option>`;
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث قائمة اللغات.', 'error');
            }
        });
    }

    // Call updateLanguageSelect on page load
    updateLanguageSelect();
</script>