<?php
// Establish database connection
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

// التحقق من وجود جدول courses وإنشائه إذا لم يكن موجودًا
try {
    $db->exec('CREATE TABLE IF NOT EXISTS courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        lessons_count INTEGER,
        duration INTEGER,
        thumbnail TEXT,
        language_id INTEGER,
        FOREIGN KEY (language_id) REFERENCES tags(id)
    )');
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'خطأ في إنشاء جدول الكورسات: ' . $e->getMessage()]));
}

// التحقق من وجود جدول sections وإنشائه إذا لم يكن موجودًا
try {
    $db->exec('CREATE TABLE IF NOT EXISTS sections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        language_id INTEGER,
        FOREIGN KEY (language_id) REFERENCES tags(id)
    )');
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'خطأ في إنشاء جدول الأقسام: ' . $e->getMessage()]));
}

// التحقق من وجود جدول tags وإنشائه إذا لم يكن موجودًا
try {
    $db->exec('CREATE TABLE IF NOT EXISTS tags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE
    )');
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'خطأ في إنشاء جدول التاجات: ' . $e->getMessage()]));
}

// التحقق من وجود جدول lessons وإنشائه إذا لم يكن موجودًا (كما فعلنا سابقًا)
try {
    $db->exec('CREATE TABLE IF NOT EXISTS lessons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        url TEXT NOT NULL,
        course_id INTEGER,
        duration INTEGER,
        section_id INTEGER,
        status TEXT,
        thumbnail TEXT,
        views INTEGER DEFAULT 0,
        language_id INTEGER,
        section_tags TEXT,
        FOREIGN KEY (course_id) REFERENCES courses(id),
        FOREIGN KEY (section_id) REFERENCES sections(id),
        FOREIGN KEY (language_id) REFERENCES tags(id)
    )');
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'خطأ في إنشاء جدول الدروس: ' . $e->getMessage()]));
}

function getLanguages($db) {
    // Assuming language_id is stored directly in the courses table
    $stmt = $db->query('SELECT DISTINCT language_id as id, language_id as name FROM courses ORDER BY language_id');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCourses($db) {
    $stmt = $db->query('SELECT id, title FROM courses ORDER BY title');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSections($db) {
    $stmt = $db->query('SELECT id, name FROM sections ORDER BY name');
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

// تعديل دالة searchLessons (حوالي السطر 70)
function searchLessons($db, $filters, $page = 1, $perPage = 48) {
    try {
        $query = 'SELECT l.*, c.title as course_title, c.language_id as language_name, s.name as section_name 
                  FROM lessons l
                  LEFT JOIN courses c ON l.course_id = c.id
                  LEFT JOIN sections s ON l.section_id = s.id
                  WHERE 1=1';
        $params = [];

        if (!empty($filters['language'])) {
            $query .= ' AND c.language_id = :language_id';
            $params[':language_id'] = $filters['language'];
        }

        if (!empty($filters['course'])) {
            $query .= ' AND l.course_id = :course_id';
            $params[':course_id'] = $filters['course'];
        }

        if (!empty($filters['section'])) {
            $query .= ' AND l.section_id = :section_id';
            $params[':section_id'] = $filters['section'];
        }

        if (!empty($filters['status'])) {
            $query .= ' AND l.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND (l.title LIKE :search OR c.title LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Count total results
        $countStmt = $db->prepare(str_replace('SELECT l.*, c.title as course_title, c.language_id as language_name, s.name as section_name', 'SELECT COUNT(*)', $query));
        $countStmt->execute($params);
        $totalResults = $countStmt->fetchColumn();

        // Pagination
        $totalPages = ceil($totalResults / $perPage);
        $offset = ($page - 1) * $perPage;
        $query .= ' ORDER BY l.id LIMIT :limit OFFSET :offset';
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode([
            'success' => true,
            'results' => $results,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    } catch(PDOException $e) {
        return json_encode(['success' => false, 'message' => 'خطأ في البحث: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $filters = [
        'language' => $_POST['language'] ?? '',
        'course' => $_POST['course'] ?? '',
        'section' => $_POST['section'] ?? '',
        'status' => $_POST['status'] ?? '',
        'search' => $_POST['search'] ?? ''
    ];

    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    
    echo searchLessons($db, $filters, $page);
    exit;
}