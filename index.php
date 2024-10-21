<?php
// تضمين ملف api_functions.php
require_once 'index/php.php';
require_once 'index/helper_functions.php';

// تهيئة نظام التسجيل
initializeLogging();

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<?php
// تضمين ملف header.php
require_once 'index/header.php';
?>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">نموذج إضافة كورسات</h3>
                    </div>
                    <div class="card-body">
                        <form id="courseForm">
                            <div class="mb-3">
                                <label for="courseLink" class="form-label">رابط الكورس (قائمة تشغيل يوتيوب)</label>
                                <input type="url" class="form-control" id="courseLink" name="courseLink" required>
                            </div>
                            <div class="mb-3">
                                <label for="courseLanguage" class="form-label">لغة الكورس</label>
                                <select class="form-select" id="courseLanguage" name="courseLanguage" required>
                                    <option value="">اختر اللغة</option>
                                    <?php
                                    $languages = getLanguages($db);
                                    foreach ($languages as $language) {
                                        echo "<option value='{$language['id']}'>{$language['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">إضافة الكورس</button>
                        </form>
                        <div id="loadingContainer" class="mt-3 text-center" style="display: none;">
                            <svg width="50" height="50" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" stroke="#007bff">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="2">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"/>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform
                                                attributeName="transform"
                                                type="rotate"
                                                from="0 18 18"
                                                to="360 18 18"
                                                dur="1s"
                                                repeatCount="indefinite"/>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                            <p class="mt-2">جاري التحميل...</p>
                        </div>
                        <button id="deleteAllData" class="btn btn-danger mt-3 w-100">
                            <i class="fas fa-trash-alt me-2"></i>حذف جميع البيانات
                        </button>
                        <div class="mt-3 d-flex justify-content-between">
                            <a href="courses.php" class="btn btn-outline-primary w-48">
                                <i class="fas fa-list me-2"></i>قائمة الكورسات
                            </a>
                            <a href="search.php" class="btn btn-outline-secondary w-48">
                                <i class="fas fa-search me-2"></i>البحث
                            </a>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <button id="addSectionsBtn" class="btn btn-outline-success w-48">
                                <i class="fas fa-plus me-2"></i>إضافة أقسام للغة
                            </button>
                            <button id="addLanguageBtn" class="btn btn-outline-info w-48">
                                <i class="fas fa-language me-2"></i>إضافة لغة جديدة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding sections -->
    <div class="modal fade" id="addSectionsModal" tabindex="-1" aria-labelledby="addSectionsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSectionsModalLabel">إضافة أقسام للغة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSectionsForm">
                        <div class="mb-3">
                            <label for="languageSelect" class="form-label">اختر اللغة</label>
                            <select class="form-select" id="languageSelect" name="languageSelect" required>
                                <?php
                                foreach ($languages as $language) {
                                    echo "<option value='{$language['id']}'>{$language['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sectionsTags" class="form-label">أقسام اللغة</label>
                            <input type="text" class="form-control" id="sectionsTags" name="sectionsTags" required>
                        </div>
                        <button type="submit" class="btn btn-primary">إضافة الأقسام</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding language -->
    <div class="modal fade" id="addLanguageModal" tabindex="-1" aria-labelledby="addLanguageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLanguageModalLabel">إضافة لغة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addLanguageForm">
                        <div class="mb-3">
                            <label for="languageTags" class="form-label">اللغات الجديدة</label>
                            <input type="text" class="form-control" id="languageTags" name="languageTags" required>
                        </div>
                        <button type="submit" class="btn btn-primary">إضافة اللغات</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="progressBar" class="progress mt-3" style="display: none;">
        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div id="progressText" class="mt-2"></div>

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
                                
                                console.log('Server response:', response); // Log the raw response
                                
                                try {
                                    const result = JSON.parse(response);
                                    console.log('Parsed result:', result); // Log the parsed result
                                    if (result.success) {
                                        Swal.fire('تم!', result.message, 'success');
                                        // Reset the form
                                        document.getElementById('courseForm').reset();
                                    } else {
                                        Swal.fire('خطأ!', result.message || 'حدث خطأ غير معروف', 'error');
                                    }
                                } catch (error) {
                                    console.error('Error parsing JSON:', error);
                                    Swal.fire('خطأ!', 'حدث خطأ أثناء معالجة الاستجابة: ' + response, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                // Hide loading animation
                                document.getElementById('loadingContainer').style.display = 'none';
                                console.error('AJAX Error:', status, error);
                                console.log('Response Text:', xhr.responseText);
                                Swal.fire('خطأ!', 'حدث خطأ أثناء إضافة الكورس: ' + error, 'error');
                            }
                        });
                    }
                });
            } else {
                Swal.fire('خطأ!', 'يرجى ملء جميع الحقول المطلوبة.', 'error');
            }
        });
    </script>

<?php
// تضمين ملف footer.php
require_once 'index/footer.php';
?>

</body>

</html>
