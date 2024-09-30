<?php
ini_set('log_errors', 1);
ini_set('error_log', 'D:\server\htdocs\you\search\custom_error.log');
// error_log("بدء تنفيذ السكريبت");

// إنشاء اتصال بقاعدة البيانات
try {
    $db = new PDO('sqlite:D:\server\htdocs\you\courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("تم الاتصال بقاعدة البيانات بنجاح");
} catch(PDOException $e) {
    error_log("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
    die("حدث خطأ أثناء الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.");
}

// التحقق من وجود البيانات في الجداول
$tables = ['tags', 'courses', 'sections', 'lessons'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        error_log("عدد الصفوف في الجدول $table: $count");
    } catch(PDOException $e) {
        error_log("فشل في عد الصفوف في الجدول $table: " . $e->getMessage());
    }
}

function getLanguages() {
    global $db;
    try {
        $stmt = $db->query('SELECT * FROM tags');
        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("تم جلب " . count($languages) . " لغة.");
        return $languages;
    } catch(PDOException $e) {
        error_log("خطأ أثناء جلب اللغات: " . $e->getMessage());
        return [];
    }
}

function getStatuses() {
    return [
        'watch' => 'مشاهدة',
        'problem' => 'مشكلة',
        'discussion' => 'نقاش',
        'search' => 'بحث',
        'retry' => 'إعادة',
        'retry_again' => 'إعادة ثانية',
        'review' => 'مراجعة',
        'completed' => 'مكتمل',
        'excluded' => 'مستبعد',
        'project' => 'مشروع تطبيقي'
    ];
}

function searchLessons($search, $page = 1, $perPage = 48, $filters = []) {
    global $db;
    try {
        $query = 'SELECT l.*, c.title as course_title, t.name as language_name, s.name as section_name 
                  FROM lessons l
                  LEFT JOIN courses c ON l.course_id = c.id
                  LEFT JOIN tags t ON l.language_id = t.id
                  LEFT JOIN sections s ON l.section_id = s.id
                  WHERE 1=1';
        $params = [];

        // البحث النصي
        if (!empty($search)) {
            $query .= ' AND (l.title LIKE :search OR c.title LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        // تطبيق الفلاتر
        if (!empty($filters)) {
            // فلترة اللغات
            if (!empty($filters['languages'])) {
                $placeholders = [];
                foreach ($filters['languages'] as $index => $language_id) {
                    $placeholder = ':language' . $index;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $language_id;
                }
                $query .= ' AND l.language_id IN (' . implode(',', $placeholders) . ')';
            }

            // فلترة الكورسات
            if (!empty($filters['courses'])) {
                $placeholders = [];
                foreach ($filters['courses'] as $index => $course_id) {
                    $placeholder = ':course' . $index;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $course_id;
                }
                $query .= ' AND l.course_id IN (' . implode(',', $placeholders) . ')';
            }

            // فلترة الأقسام
            if (!empty($filters['sections'])) {
                $placeholders = [];
                foreach ($filters['sections'] as $index => $section_id) {
                    $placeholder = ':section' . $index;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $section_id;
                }
                $query .= ' AND l.section_id IN (' . implode(',', $placeholders) . ')';
            }

            // فلترة الحالات
            if (!empty($filters['statuses'])) {
                $placeholders = [];
                foreach ($filters['statuses'] as $index => $status) {
                    $placeholder = ':status' . $index;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $status;
                }
                $query .= ' AND l.status IN (' . implode(',', $placeholders) . ')';
            }
        }

        // حساب إجمالي النتائج
        $countQuery = 'SELECT COUNT(*) FROM lessons l
                       LEFT JOIN courses c ON l.course_id = c.id
                       LEFT JOIN tags t ON l.language_id = t.id
                       LEFT JOIN sections s ON l.section_id = s.id
                       WHERE 1=1';
        $countParams = [];

        if (!empty($search)) {
            $countQuery .= ' AND (l.title LIKE :search OR c.title LIKE :search)';
            $countParams[':search'] = '%' . $search . '%';
        }

        // تطبيق الفلاتر على استعلام العد
        if (!empty($filters)) {
            // فلترة اللغات
            if (!empty($filters['languages'])) {
                $placeholders = [];
                foreach ($filters['languages'] as $index => $language_id) {
                    $placeholder = ':count_language' . $index;
                    $placeholders[] = $placeholder;
                    $countParams[$placeholder] = $language_id;
                }
                $countQuery .= ' AND l.language_id IN (' . implode(',', $placeholders) . ')';
            }

            // فلترة الكورسات
            if (!empty($filters['courses'])) {
                $placeholders = [];
                foreach ($filters['courses'] as $index => $course_id) {
                    $placeholder = ':count_course' . $index;
                    $placeholders[] = $placeholder;
                    $countParams[$placeholder] = $course_id;
                }
                $countQuery .= ' AND l.course_id IN (' . implode(',', $placeholders) . ')';
            }

            // فلترة الأقسام
            if (!empty($filters['sections'])) {
                $placeholders = [];
                foreach ($filters['sections'] as $index => $section_id) {
                    $placeholder = ':count_section' . $index;
                    $placeholders[] = $placeholder;
                    $countParams[$placeholder] = $section_id;
                }
                $countQuery .= ' AND l.section_id IN (' . implode(',', $placeholders) . ')';
            }

            // فلترة الحالات
            if (!empty($filters['statuses'])) {
                $placeholders = [];
                foreach ($filters['statuses'] as $index => $status) {
                    $placeholder = ':count_status' . $index;
                    $placeholders[] = $placeholder;
                    $countParams[$placeholder] = $status;
                }
                $countQuery .= ' AND l.status IN (' . implode(',', $placeholders) . ')';
            }
        }

        $countStmt = $db->prepare($countQuery);
        foreach ($countParams as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $totalResults = $countStmt->fetchColumn();

        // Pagination
        $totalPages = ceil($totalResults / $perPage);
        $offset = ($page - 1) * $perPage;
        $query .= ' LIMIT :perPage OFFSET :offset';
        $params[':perPage'] = $perPage;
        $params[':offset'] = $offset;

        $stmt = $db->prepare($query);

        // Bind parameters
        foreach ($params as $key => $value) {
            if (strpos($key, 'perPage') !== false || strpos($key, 'offset') !== false) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'results' => $results,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    } catch(PDOException $e) {
        error_log("Error searching lessons: " . $e->getMessage());
        return [
            'results' => [],
            'totalPages' => 0,
            'currentPage' => $page,
            'error' => 'An error occurred while searching for lessons.'
        ];
    }
}

function get_courses_and_sections($languages) {
    global $db;
    try {
        $courses = [];
        $sections = [];

        if (!empty($languages)) {
            $placeholders = implode(',', array_fill(0, count($languages), '?'));

            // Fetch courses
            $courseQuery = "SELECT DISTINCT c.id, c.title FROM courses c WHERE c.language_id IN ($placeholders)";
            $courseStmt = $db->prepare($courseQuery);
            $courseStmt->execute($languages);
            $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch sections
            $sectionQuery = "SELECT DISTINCT s.id, s.name FROM sections s WHERE s.language_id IN ($placeholders)";
            $sectionStmt = $db->prepare($sectionQuery);
            $sectionStmt->execute($languages);
            $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [
            'success' => true,
            'courses' => $courses,
            'sections' => $sections
        ];
    } catch(PDOException $e) {
        return [
            'success' => false,
            'courses' => [],
            'sections' => [],
            'error' => 'An error occurred while fetching courses and sections.'
        ];
    }
}

// التعامل مع طلبات POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'get_courses_and_sections':
                $languages = isset($_POST['languages']) && is_array($_POST['languages']) ? $_POST['languages'] : [];
                echo json_encode(get_courses_and_sections($languages));
                exit;

            default:
                echo json_encode(['error' => 'Invalid action.']);
                exit;
        }
    }

    // جمع معايير البحث والفلاتر
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $perPage = isset($_POST['perPage']) ? intval($_POST['perPage']) : 48;

    // جمع الفلاتر من الطلب
    $filters = [];
    if (isset($_POST['filters']) && is_array($_POST['filters'])) {
        // فلترة اللغات
        if (isset($_POST['filters']['languages']) && is_array($_POST['filters']['languages'])) {
            $filters['languages'] = array_map('intval', $_POST['filters']['languages']);
        }

        // فلترة الكورسات
        if (isset($_POST['filters']['courses']) && is_array($_POST['filters']['courses'])) {
            $filters['courses'] = array_map('intval', $_POST['filters']['courses']);
        }

        // فلترة الأقسام
        if (isset($_POST['filters']['sections']) && is_array($_POST['filters']['sections'])) {
            $filters['sections'] = array_map('intval', $_POST['filters']['sections']);
        }

        // فلترة الحالات
        if (isset($_POST['filters']['statuses']) && is_array($_POST['filters']['statuses'])) {
            // تأكد من أن القيم موجودة في قائمة الحالات المسموح بها
            $allowedStatuses = array_keys(getStatuses());
            $filters['statuses'] = array_intersect($_POST['filters']['statuses'], $allowedStatuses);
        }
    }

    $searchResults = searchLessons($search, $page, $perPage, $filters);

    echo json_encode($searchResults);
    exit;
}
?>
