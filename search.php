<?php
require_once 'search/search_operations.php';

$languages = getLanguages($db);
$courses = getCourses($db);
$sections = getSections($db);
$statuses = getStatuses();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wiki - صفحة البحث</title>
    
    <!-- روابط Bootstrap و Material Design و Tailwind CSS -->
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
        /* أنماط جديدة للقائمة في الهيدر */
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
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
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
    </style>
</head>
<body class="bg-gray-100">
    <!-- إضافة الهيدر مع القائمة الجديدة -->
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
                
                <button type="button" id="toggleFilters" class="mb-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                    <i class="fas fa-filter"></i>
                </button>

                <div id="filtersSection" class="hidden">
                    <!-- فلاتر البحث الجديدة بتصميم ماتريل ديزاين -->
                    <div class="flex flex-wrap -mx-3 mb-4">
                        <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                            <h3 class="text-lg font-semibold mb-2">اللغات</h3>
                            <select name="language" id="language" class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                <option value="">اختر اللغة</option>
                                <?php foreach ($languages as $language): ?>
                                    <option value="<?php echo $language['id']; ?>"><?php echo $language['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                            <h3 class="text-lg font-semibold mb-2">الكورسات</h3>
                            <select name="course" id="course" class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                <option value="">اختر الكورس</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
                            <h3 class="text-lg font-semibold mb-2">الأقسام</h3>
                            <select name="section" id="section" class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                <option value="">اختر القسم</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?php echo $section['id']; ?>"><?php echo $section['name']; ?></option>
                                <?php endforeach; ?>
                              
            </select>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6 md:mb-0">
            <h3 class="text-lg font-semibold mb-2">الحالات</h3>
            <select name="status" id="status" class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                <option value="">اختر الحالة</option>
                <?php foreach ($statuses as $key => $value): ?>
                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="flex justify-center mt-6">
    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        بحث
    </button>
</div>
</form>

<div id="searchResults" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
<!-- نتائج البحث ستظهر هنا -->
</div>

<div id="pagination" class="mt-8 flex justify-center">
<!-- أزرار التنقل بين الصفحات ستظهر هنا -->
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
    $('#toggleFilters').click(function() {
        $('#filtersSection').toggleClass('hidden');
    });

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

        results.forEach(lesson => {
            const card = $('<div>').addClass('lesson-card');
            card.html(`
                <h3>${lesson.title}</h3>
                <p>الكورس: ${lesson.course_title}</p>
                <p>اللغة: ${lesson.language_name}</p>
                <p>القسم: ${lesson.section_name}</p>
                <span class="status-badge ${getStatusBadgeClass(lesson.status)}">${getStatusLabel(lesson.status)}</span>
            `);
            resultsContainer.append(card);
        });
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

    function performSearch(page = 1) {
        const formData = $('#searchForm').serialize() + '&page=' + page;

        $.ajax({
            url: 'search/search_operations.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                displayResults(response.results);
                displayPagination(response.currentPage, response.totalPages);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }

    $('#searchForm').submit(function(e) {
        e.preventDefault();
        performSearch();
    });

    // Load initial results
    performSearch();
});
</script>

</body>
</html>