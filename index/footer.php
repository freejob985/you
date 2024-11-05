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
    $('#courseForm').submit(function(e) {
        e.preventDefault();
        const courseLink = document.getElementById('courseLink').value;
        const courseLanguage = document.getElementById('courseLanguage').value;
        
        if (courseLink && courseLanguage) {
            $('#courseForm').hide();
            $('#progressContainer').show();
            startCourseAddition(courseLink, courseLanguage);
        } else {
            alert('يرجى ملء جميع الحقول المطلوبة.');
        }
    });

    function startCourseAddition(courseLink, courseLanguage) {
        $('#courseForm').hide();
        $('#progressContainer').show();
        $.ajax({
            url: 'add_course_background.php',
            method: 'POST',
            data: {
                courseLink: courseLink,
                courseLanguage: courseLanguage
            },
            success: function(response) {
                console.log('Response:', response);
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    if (response.success) {
                        statusCheckInterval = setInterval(checkCourseAdditionStatus, 2000);
                    } else {
                        Swal.fire('خطأ!', response.message, 'error');
                        $('#courseForm').show();
                        $('#progressContainer').hide();
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                    Swal.fire('خطأ!', 'حدث خطأ أثناء معالجة الاستجابة', 'error');
                    $('#courseForm').show();
                    $('#progressContainer').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة الكورس', 'error');
                $('#courseForm').show();
                $('#progressContainer').hide();
            }
        });
    }

    function checkCourseAdditionStatus() {
        $.ajax({
            url: 'check_course_status.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Status response:', response);
                if (response.status === 'completed') {
                    clearInterval(statusCheckInterval);
                    updateProgressUI(response);
                    Swal.fire('تم!', response.message, 'success');
                    $('#courseForm').show();
                    $('#progressContainer').hide();
                } else if (response.status === 'in_progress') {
                    updateProgressUI(response);
                } else if (response.status === 'not_started') {
                    console.log('Course addition not started yet');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    function updateProgressUI(data) {
        $('#progressBar').css('width', data.progress + '%').attr('aria-valuenow', data.progress).text(data.progress + '%');
        $('#courseTitleText').text(`الكورس: ${data.course_title}`);
        $('#progressText').text(`جاري إضافة الدرس ${data.current} من ${data.total}`);
        $('#latestLessonText').text(`آخر درس تمت إضافته: ${data.latest_lesson}`);
    }

    // Delete all data
    document.getElementById('deleteAllData').addEventListener('click', function() {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'سيتم حذف جميع البيانات بشكل نهائي!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف الكل',
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
                    Swal.fire('تنبه!', response.message, 'warning');
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

    // بدء التحقق كل 2 ثانية
    var statusCheckInterval;

</script>

