<?php
// Establish database connection
try {
    $db = new PDO('sqlite:../courses.db'); 
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

function getLanguages($db) {
    $stmt = $db->query('SELECT id, name FROM tags WHERE id IN (SELECT DISTINCT language_id FROM courses)');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCourses($db) {
    $stmt = $db->query('SELECT id, title FROM courses');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSections($db) {
    $stmt = $db->query('SELECT id, name FROM sections');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

function searchLessons($db, $filters, $page = 1, $perPage = 48) {
    $query = 'SELECT l.*, c.title as course_title, t.name as language_name, s.name as section_name 
              FROM lessons l
              LEFT JOIN courses c ON l.course_id = c.id
              LEFT JOIN tags t ON l.language_id = t.id
              LEFT JOIN sections s ON l.section_id = s.id
              WHERE 1=1';
    $params = [];

    if (!empty($filters['languages'])) {
        $placeholders = implode(',', array_fill(0, count($filters['languages']), '?'));
        $query .= " AND l.language_id IN ($placeholders)";
        $params = array_merge($params, $filters['languages']);
    }

    if (!empty($filters['courses'])) {
        $placeholders = implode(',', array_fill(0, count($filters['courses']), '?'));
        $query .= " AND l.course_id IN ($placeholders)";
        $params = array_merge($params, $filters['courses']);
    }

    if (!empty($filters['sections'])) {
        $placeholders = implode(',', array_fill(0, count($filters['sections']), '?'));
        $query .= " AND l.section_id IN ($placeholders)";
        $params = array_merge($params, $filters['sections']);
    }

    if (!empty($filters['statuses'])) {
        $placeholders = implode(',', array_fill(0, count($filters['statuses']), '?'));
        $query .= " AND l.status IN ($placeholders)";
        $params = array_merge($params, $filters['statuses']);
    }

    if (!empty($filters['search'])) {
        $query .= ' AND (l.title LIKE ? OR c.title LIKE ?)';
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }

    // Count total results
    $countStmt = $db->prepare(str_replace('SELECT l.*, c.title as course_title, t.name as language_name, s.name as section_name', 'SELECT COUNT(*)', $query));
    $countStmt->execute($params);
    $totalResults = $countStmt->fetchColumn();

    // Pagination
    $totalPages = ceil($totalResults / $perPage);
    $offset = ($page - 1) * $perPage;
    $query .= ' LIMIT ? OFFSET ?';
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'results' => $results,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = [
        'language' => $_POST['language'] ?? '',
        'course' => $_POST['course'] ?? '',
        'section' => $_POST['section'] ?? '',
        'status' => $_POST['status'] ?? '',
        'search' => $_POST['search'] ?? ''
    ];

    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $searchResults = searchLessons($db, $filters, $page);

    echo json_encode($searchResults);
    exit;
}