<?php

ini_set('log_errors', 1);
ini_set('error_log', 'D:\server\htdocs\you\search\custom_error.log');
// error_log(message: "بدء تنفيذ السكريبت");

// courses_handler.php

// Establish database connection
try {
    $db = new PDO('sqlite:../courses.db'); 
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful");
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Error connecting to the database. Please try again later.");
}

// بعد إنشاء الاتصال، قم بإضافة هذا الكود للتحقق من وجود البيانات
$tables = ['tags', 'courses', 'sections'];
foreach ($tables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        error_log("Number of rows in $table: $count");
    } catch(PDOException $e) {
        error_log("Failed to count rows in $table: " . $e->getMessage());
    }
}

function getLanguages() {
    global $db;
    try {
        $stmt = $db->query('SELECT * FROM tags');
        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched " . count($languages) . " languages.");
        return $languages;
    } catch(PDOException $e) {
        error_log("Error fetching languages: " . $e->getMessage());
        return [];
    }
}

function getCourses() {
    global $db;
    try {
        $stmt = $db->query('SELECT id, title FROM courses');
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched " . count($courses) . " courses.");
        return $courses;
    } catch(PDOException $e) {
        error_log("Error fetching courses: " . $e->getMessage());
        return [];
    }
}

function getSections() {
    global $db;
    try {
        $stmt = $db->query('SELECT id, name FROM sections');
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched " . count($sections) . " sections.");
        return $sections;
    } catch(PDOException $e) {
        error_log("Error fetching sections: " . $e->getMessage());
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

function searchLessons($search, $page = 1, $perPage = 48) {
    global $db;
    try {
        $query = 'SELECT l.*, c.title as course_title, t.name as language_name, s.name as section_name 
                  FROM lessons l
                  LEFT JOIN courses c ON l.course_id = c.id
                  LEFT JOIN tags t ON l.language_id = t.id
                  LEFT JOIN sections s ON l.section_id = s.id
                  WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $query .= ' AND (l.title LIKE ? OR c.title LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        // Count total results
        $countQuery = 'SELECT COUNT(*) FROM lessons l
                       LEFT JOIN courses c ON l.course_id = c.id
                       LEFT JOIN tags t ON l.language_id = t.id
                       LEFT JOIN sections s ON l.section_id = s.id
                       WHERE 1=1';

        if (!empty($search)) {
            $countQuery .= ' AND (l.title LIKE ? OR c.title LIKE ?)';
        }

        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($params);
        $totalResults = $countStmt->fetchColumn();

        // Pagination
        $totalPages = ceil($totalResults / $perPage);
        $offset = ($page - 1) * $perPage;
        $query .= ' LIMIT ? OFFSET ?';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $db->prepare($query);
        foreach ($params as $index => $param) {
            // Bind parameters starting from 1
            $stmt->bindValue($index + 1, $param);
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
            error_log("Fetched " . count($courses) . " courses for selected languages.");

            // Fetch sections
            $sectionQuery = "SELECT DISTINCT s.id, s.name FROM sections s WHERE s.language_id IN ($placeholders)";
            $sectionStmt = $db->prepare($sectionQuery);
            $sectionStmt->execute($languages);
            $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched " . count($sections) . " sections for selected languages.");
        }

        return [
            'success' => true,
            'courses' => $courses,
            'sections' => $sections
        ];
    } catch(PDOException $e) {
        error_log("Error fetching courses and sections: " . $e->getMessage());
        return [
            'success' => false,
            'courses' => [],
            'sections' => [],
            'error' => 'An error occurred while fetching courses and sections.'
        ];
    }
}

// Example usage of the functions
// Uncomment the lines below for testing purposes
/*
$languages = getLanguages();
$courses = getCourses();
$sections = getSections();

print_r($languages);
print_r($courses);
print_r($sections);
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            // الحالات الأخرى...

            case 'get_courses_and_sections':
                // تأكد من أن اللغات تأتي كمصفوفة
                $languages = isset($_POST['languages']) && is_array($_POST['languages']) 
                             ? array_map('intval', $_POST['languages']) 
                             : [];
                echo json_encode(get_courses_and_sections($languages));
                exit;

            // يمكنك إضافة حالات أخرى هنا...

            default:
                echo json_encode(['error' => 'Invalid action.']);
                exit;
        }
    }

    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $perPage = isset($_POST['perPage']) ? intval($_POST['perPage']) : 48;

    $searchResults = searchLessons($search, $page, $perPage);

    echo json_encode($searchResults);
    exit;
}
?>