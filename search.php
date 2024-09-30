<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'search/search_operations.php';
$languages = getLanguages();
$statuses = getStatuses();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wiki - صفحة البحث</title>

    <!-- روابط CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- رابط Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- روابط الخطوط العربية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Changa:wght@200..800&display=swap" rel="stylesheet">

    <!-- الأنماط المخصصة -->
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: 60px; /* إضافة مساحة للفوتر الثابت */
        }
        .changa-font {
            font-family: 'Changa', sans-serif;
        }
        .search-icon {
            width: 20px;
            height: 20px;
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }
        /* أنماط للقائمة في الهيدر */
        .header-nav {
            display: flex;
            align-items: center;
        }
        .header-nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
        }
        /* أنماط للشيك بوكس بتصميم ماتريل ديزاين */
        .md-checkbox {
            position: relative;
            margin: 16px 0;
            text-align: right;
        }
        .md-checkbox label {
            cursor: pointer;
            padding-right: 28px;
            display: inline-block;
        }
        .md-checkbox label:before, .md-checkbox label:after {
            content: "";
            position: absolute;
            right: 0;
            top: 0;
        }
        .md-checkbox label:before {
            width: 20px;
            height: 20px;
            background: #fff;
            border: 2px solid rgba(0, 0, 0, 0.54);
            border-radius: 2px;
            cursor: pointer;
            transition: background .3s;
        }
        .md-checkbox input[type="checkbox"] {
            outline: 0;
            visibility: hidden;
            width: 20px;
            margin: 0;
            display: block;
            float: right;
            font-size: inherit;
        }
        .md-checkbox input[type="checkbox"]:checked + label:before {
            background: #2196F3;
            border: none;
        }
        .md-checkbox input[type="checkbox"]:checked + label:after {
            transform: rotate(45deg);
            content: "";
            position: absolute;
            top: 1px;
            right: 6px;
            width: 6px;
            height: 12px;
            border: 2px solid #fff;
            border-top: none;
            border-left: none;
        }
        .lesson-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #fff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        .lesson-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .lesson-card p {
            font-size: 0.875rem;
            color: #4a5568;
            margin-bottom: 0.25rem;
        }
        .lesson-card .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #fff;
        }
        /* أنماط للفلاتر */
        .filters-section {
            display: none; /* إخفاء الفلاتر بشكل افتراضي */
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background-color: #f9fafb;
        }
        .toggle-filters-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px; /* حجم الأيقونة */
            color: #000; /* لون الأيقونة */
            padding: 10px;
            transition: transform 0.3s ease;
        }
        .toggle-filters-btn:hover {
            transform: scale(1.1);
        }
        .toggle-filters-btn.active {
            transform: rotate(180deg);
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 أعمدة */
            gap: 1rem;
        }
        /* أنماط للسكروول */
        * {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }
        *::-webkit-scrollbar {
            width: 8px;
        }
        *::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        *::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        *::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- الهيدر -->
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center">
                <img src="pngegg.png" alt="My Wiki Logo" class="h-8 mr-2">
                <h1 class="text-2xl font-bold">My Wiki</h1>
            </div>
            <nav class="header-nav">
                <a href="#">الرئيسية</a>
                <a href="#">المقالات</a>
                <a href="#">عن الموقع</a>
                <a href="#">اتصل بنا</a>
            </nav>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold mb-8 text-center changa-font">My Wiki</h1>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <form id="searchForm" class="mb-6">
                <div class="flex flex-wrap -mx-3 mb-4">
                    <div class="w-full px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="search">
                            البحث
                        </label>
                        <div class="relative">
                            <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 pr-10 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="search" name="search" type="text" placeholder="ادخل كلمات البحث هنا...">
                            <img src="https://cdn-icons-png.flaticon.com/128/3850/3850203.png" alt="Search Icon" class="search-icon">
                        </div>
                    </div>
                </div>

                <!-- زر إظهار/إخفاء الفلاتر -->
                <div class="flex justify-end">
                    <button type="button" class="toggle-filters-btn" aria-label="إظهار/إخفاء الفلاتر">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>

                <!-- قسم الفلاتر -->
                <div class="filters-section">
                    <div class="filters-grid">
                        <!-- فلاتر اللغة -->
                        <div>
                            <h5 class="mb-2 font-semibold">اللغة</h5>
                            <?php foreach ($languages as $language): ?>
                                <div class="md-checkbox">
                                    <input type="checkbox" id="language_<?php echo $language['id']; ?>" name="languages[]" value="<?php echo $language['id']; ?>">
                                    <label for="language_<?php echo $language['id']; ?>"><?php echo htmlspecialchars($language['name']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- فلاتر الكورس -->
                        <div id="coursesFilter">
                            <h5 class="mb-2 font-semibold">الكورس</h5>
                            <!-- سيتم تحديث الكورسات ديناميكياً بناءً على اختيار اللغة -->
                        </div>

                        <!-- فلاتر القسم -->
                        <div id="sectionsFilter">
                            <h5 class="mb-2 font-semibold">القسم</h5>
                            <!-- سيتم تحديث الأقسام ديناميكياً بناءً على اختيار اللغة -->
                        </div>

                        <!-- فلاتر الحالة -->
                        <div>
                            <h5 class="mb-2 font-semibold">الحالة</h5>
                            <?php foreach ($statuses as $key => $status): ?>
                                <div class="md-checkbox">
                                    <input type="checkbox" id="status_<?php echo $key; ?>" name="statuses[]" value="<?php echo htmlspecialchars($key); ?>">
                                    <label for="status_<?php echo $key; ?>"><?php echo htmlspecialchars($status); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- زر مسح الفلاتر -->
                    <div class="flex justify-center mt-4">
                        <button id="clearFilters" type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                            <i class="fas fa-eraser mr-2"></i>
                            <span>مسح الفلاتر</span>
                        </button>
                    </div>
                </div>

                <div class="flex justify-center mt-6">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        بحث
                    </button>
                </div>
            </form>

            <div id="searchResults" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- سيتم عرض نتائج البحث هنا -->
            </div>

            <div id="searchInfo" class="mt-4 text-center text-gray-600">
                <!-- سيتم عرض معلومات البحث هنا -->
            </div>

            <div id="pagination" class="mt-8 flex justify-center">
                <!-- سيتم عرض أزرار التنقل بين الصفحات هنا -->
            </div>
        </div>
    </div>

    <footer class="footer bg-gray-800 text-white py-4">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 My Wiki. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <!-- روابط JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>

    <script>
    $(document).ready(function() {
        function getStatusBadgeClass(status) {
            switch (status) {
                case 'completed': return 'bg-success';
                case 'watch':
                case 'review': return 'bg-primary';
                case 'problem':
                case 'retry':
                case 'retry_again': return 'bg-warning';
                case 'discussion':
                case 'search': return 'bg-info';
                case 'excluded': return 'bg-danger';
                case 'project': return 'bg-secondary';
                default: return 'bg-secondary';
            }
        }

        function getStatusLabel(status) {
            const statusLabels = {
                'completed': 'مكتمل',
                'watch': 'مشاهدة',
                'problem': 'مشكلة',
                'discussion': 'نقاش',
                'search': 'بحث',
                'retry': 'إعادة',
                'retry_again': 'إعادة ثانية',
                'review': 'مراجعة',
                'excluded': 'مستبعد',
                'project': 'مشروع تطبيقي'
            };
            return statusLabels[status] || 'غير محدد';
        }

function displayResults(results) {
    const resultsContainer = $('#searchResults');
    resultsContainer.empty();

    if (results.length === 0) {
        resultsContainer.html('<p class="text-center">لا توجد نتائج</p>');
        return;
    }

    results.forEach(lesson => {
        const card = $('<div>').addClass('lesson-card');
        card.html(`
            <img src="${lesson.thumbnail}" alt="${lesson.title}" class="thumbnail w-full h-48 object-cover mb-4">
            <h3>${lesson.title}</h3>
            <p>الكورس: ${lesson.course_title}</p>
            <p>اللغة: ${lesson.language_name}</p>
            <p>القسم: ${lesson.section_name}</p>
            <div class="lesson-info flex items-center justify-between mt-2">
                <p>المدة: ${formatDuration(lesson.duration)}</p>
                <span class="status-badge ${getStatusBadgeClass(lesson.status)}">${getStatusLabel(lesson.status)}</span>
            </div>
            <div class="mt-2 flex justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded lesson-details-btn" data-lesson='${JSON.stringify(lesson)}'>
                    تفاصيل الدرس
                </button>
                <a href="${lesson.url}" target="_blank" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fab fa-youtube mr-2"></i>يوتيوب
                </a>
                <a href="show.php?lesson_id=${lesson.id}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">مشاهدة الدرس</a>
            </div>
        `);
        resultsContainer.append(card);
    });

    // إضافة مستمع الحدث لأزرار تفاصيل الدرس
    $('.lesson-details-btn').on('click', function() {
        const lessonData = JSON.parse($(this).attr('data-lesson'));
        showLessonDetails(lessonData);
    });
}

        function formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours > 0 ? hours + ":" : ""}${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        function displayPagination(currentPage, totalPages) {
            const paginationContainer = $('#pagination');
            paginationContainer.empty();

            if (totalPages > 1) {
                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = $('<button>')
                        .addClass('mx-1 px-3 py-1 rounded')
                        .text(i)
                        .click(function() {
                            performSearch(i);
                        });

                    if (i === currentPage) {
                        pageButton.addClass('bg-blue-500 text-white');
                    } else {
                        pageButton.addClass('bg-gray-200 text-gray-700 hover:bg-gray-300');
                    }

                    paginationContainer.append(pageButton);
                }
            }
        }

        function displaySearchInfo(totalResults, currentPage, totalPages) {
            const searchInfo = $('#searchInfo');
            searchInfo.html(`عدد النتائج: ${totalResults} | الصفحة ${currentPage} من ${totalPages}`);
        }

        function performSearch(page = 1) {
            const searchQuery = $('#search').val();
            const filters = getSelectedFilters();

            $.ajax({
                url: 'search/search_operations.php',
                method: 'POST',
                data: {
                    search: searchQuery,
                    page: page,
                    filters: filters
                },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        alert(response.error);
                        return;
                    }
                    displayResults(response.results);
                    displayPagination(response.currentPage, response.totalPages);
                    displaySearchInfo(response.totalResults, response.currentPage, response.totalPages);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.log(xhr.responseText);
                    alert('حدث خطأ أثناء البحث. يرجى المحاولة مرة أخرى.');
                }
            });
        }

        function getSelectedFilters() {
            return {
                languages: $('input[name="languages[]"]:checked').map(function() {
                    return this.value;
                }).get(),
                courses: $('input[name="courses[]"]:checked').map(function() {
                    return this.value;
                }).get(),
                sections: $('input[name="sections[]"]:checked').map(function() {
                    return this.value;
                }).get(),
                statuses: $('input[name="statuses[]"]:checked').map(function() {
                    return this.value;
                }).get()
            };
        }

        $('#searchForm').submit(function(e) {
            e.preventDefault();
            performSearch();
        });

        // Load initial results
        performSearch();

        let searchTimeout;
        $('#search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch();
            }, 300);
        });

        // إظهار/إخفاء الفلاتر
        $('.toggle-filters-btn').click(function() {
            $('.filters-section').slideToggle();
        });

        // تحديث البحث عند تغيير أي فلتر
        $('.filters-section').on('change', 'input[type="checkbox"]', function() {
            performSearch();
        });

        // زر مسح الفلاتر
        $('#clearFilters').click(function() {
            $('.filters-section input[type="checkbox"]').prop('checked', false);
            performSearch();
        });

        function updateCoursesAndSections() {
            const selectedLanguages = $('input[name="languages[]"]:checked').map(function() {
                return this.value;
            }).get();

            $.ajax({
                url: 'search/search_operations.php',                method: 'POST',
                data: {
                    action: 'get_courses_and_sections',
                    languages: selectedLanguages
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateFilterOptions('#coursesFilter', 'courses[]', response.courses, 'title', 'الكورس');
                        updateFilterOptions('#sectionsFilter', 'sections[]', response.sections, 'name', 'القسم');

                        // إعادة تعيين التحديدات السابقة
                        $('input[name="courses[]"]:checked').prop('checked', false);
                        $('input[name="sections[]"]:checked').prop('checked', false);

                        performSearch();
                    } else {
                        console.error('Error:', response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function updateFilterOptions(containerSelector, inputName, options, labelField, heading) {
            const container = $(containerSelector);
            container.empty();

            container.append(`<h5 class="mb-2 font-semibold">${heading}</h5>`);

            if (options.length === 0) {
                container.append('<p>لا توجد خيارات متاحة</p>');
            } else {
                options.forEach(function(option) {
                    const checkboxHtml = `
                        <div class="md-checkbox">
                            <input type="checkbox" id="${inputName}_${option.id}" name="${inputName}" value="${option.id}">
                            <label for="${inputName}_${option.id}">${option[labelField]}</label>
                        </div>
                    `;
                    container.append(checkboxHtml);
                });
            }
        }

        // تحديث الكورسات والأقسام عند تغيير اختيار اللغة
        $('input[name="languages[]"]').change(function() {
            updateCoursesAndSections();
        });

        // تنعيم الاسكرول
        $('a[href*="#"]:not([href="#"])').click(function() {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top
                    }, 1000);
                    return false;
                }
            }
        });

        function getYoutubeIdFromUrl(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        function showLessonDetails(lesson) {
            const modalBody = $('#lessonModal .modal-body');
            const tags = lesson.section_tags ? lesson.section_tags.split(',').map(tag => `<span class="tag">${tag.trim()}</span>`).join(' ') : '';
            const youtubeId = getYoutubeIdFromUrl(lesson.url);
            
            modalBody.html(`
                <div class="embed-responsive embed-responsive-16by9 mb-4">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/${youtubeId}" allowfullscreen></iframe>
                </div>
                <table class="table table-bordered">
                    <tr><th>العنوان</th><td>${lesson.title}</td></tr>
                    <tr><th>الكورس</th><td>${lesson.course_title}</td></tr>
                    <tr><th>اللغة</th><td>${lesson.language_name}</td></tr>
                    <tr><th>القسم</th><td>${lesson.section_name}</td></tr>
                    <tr><th>المدة</th><td>${formatDuration(lesson.duration)}</td></tr>
                    <tr><th>الحالة</th><td>${getStatusLabel(lesson.status)}</td></tr>
                    <tr><th>المشاهدات</th><td>${lesson.views}</td></tr>
                    <tr><th>التاجات</th><td>${tags}</td></tr>
                </table>
            `);
            
            $('#lessonModal').modal('show');
        }
    });
    </script>

</body>
</html>