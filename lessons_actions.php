<?php
header('Content-Type: application/json');

// إنشاء اتصال بقاعدة البيانات SQLite باستخدام PDO
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'toggle_watch':
            toggleWatch($db);
            break;
        case 'mark_complete':
            markComplete($db);
            break;
        case 'assign_section':
            assignSection($db);
            break;
        case 'add_new_sections':
            addNewSections($db);
            break;
        case 'set_status':
            setStatus($db);
            break;
        case 'get_completion_percentage':
            getCompletionPercentage($db);
            break;
        case 'delete_lesson':
            deleteLesson($db);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'الإجراء غير معروف.']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مسموحة.']);
}

function toggleWatch($db) {
    $lesson_id = $_POST['lesson_id'] ?? null;
    $views = $_POST['views'] ?? null;

    if (!$lesson_id || !is_numeric($lesson_id) || ($views !== '0' && $views !== '1')) {
        echo json_encode(['success' => false, 'message' => 'معرف الدرس أو قيمة المشاهدة غير صالحة.']);
        exit;
    }

    try {
        $stmt = $db->prepare('UPDATE lessons SET views = :views WHERE id = :lesson_id');
        $stmt->bindValue(':views', $views, PDO::PARAM_INT);
        $stmt->bindValue(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'تم تحديث حالة المشاهدة.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث حالة المشاهدة: ' . $e->getMessage()]);
    }
}

function markComplete($db) {
    $lesson_id = $_POST['lesson_id'] ?? null;
    $completed = $_POST['completed'] ?? null;

    if (!$lesson_id || !is_numeric($lesson_id) || ($completed !== '1' && $completed !== '0')) {
        echo json_encode(['success' => false, 'message' => 'معرف الدرس أو قيمة الإكمال غير صالحة.']);
        exit;
    }

    $status = ($completed === '1') ? 'completed' : 'active';

    try {
        $stmt = $db->prepare('UPDATE lessons SET status = :status WHERE id = :lesson_id');
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'تم تحديث حالة الدرس بنجاح.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث حالة الدرس: ' . $e->getMessage()]);
    }
}

function assignSection($db) {
    $lesson_id = $_POST['lesson_id'] ?? null;
    $section_id = $_POST['section_id'] ?? null;

    if (!$lesson_id || !is_numeric($lesson_id) || !$section_id || !is_numeric($section_id)) {
        echo json_encode(['success' => false, 'message' => 'معرف الدرس أو معرف القسم غير صالحين.']);
        exit;
    }

    try {
        // التحقق من وجود القسم
        $stmt = $db->prepare('SELECT id FROM sections WHERE id = :section_id');
        $stmt->bindValue(':section_id', $section_id, PDO::PARAM_INT);
        $stmt->execute();
        $section = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$section) {
            echo json_encode(['success' => false, 'message' => 'القسم غير موجود.']);
            exit;
        }

        // تحديث الدرس بقسم جديد
        $stmt = $db->prepare('UPDATE lessons SET section_id = :section_id WHERE id = :lesson_id');
        $stmt->bindValue(':section_id', $section_id, PDO::PARAM_INT);
        $stmt->bindValue(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'تم تعيين القسم للدرس بنجاح.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تعيين القسم: ' . $e->getMessage()]);
    }
}

function addNewSections($db) {
    $sections = $_POST['sections'] ?? null;

    if (!$sections) {
        echo json_encode(['success' => false, 'message' => 'الأقسام غير موجودة.']);
        exit;
    }

    $sectionsArray = json_decode($sections, true);
    if (!is_array($sectionsArray)) {
        echo json_encode(['success' => false, 'message' => 'تنسيق الأقسام غير صالح.']);
        exit;
    }

    $section_ids = [];

    try {
        $db->beginTransaction();

        $stmtInsert = $db->prepare('INSERT OR IGNORE INTO sections (name) VALUES (:name)');
        $stmtSelect = $db->prepare('SELECT id FROM sections WHERE name = :name');

        foreach ($sectionsArray as $sectionName) {
            $trimmedName = trim($sectionName);
            if ($trimmedName === '') continue;

            // إدراج القسم الجديد
            $stmtInsert->bindValue(':name', $trimmedName, PDO::PARAM_STR);
            $stmtInsert->execute();

            // الحصول على معرف القسم
            $stmtSelect->bindValue(':name', $trimmedName, PDO::PARAM_STR);
            $stmtSelect->execute();
            $section = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            if ($section) {
                $section_ids[] = $section['id'];
            }
        }

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'تم إضافة الأقسام الجديدة بنجاح.', 'section_ids' => $section_ids]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة الأقسام: ' . $e->getMessage()]);
    }
}

function setStatus($db) {
    $lesson_id = $_POST['lesson_id'] ?? null;
    $status = $_POST['status'] ?? null;

    $allowed_statuses = [
        'watch', 'problem', 'discussion', 'search', 'retry', 'retry_again', 'review', 'completed',
        'excluded', 'project', 'active', 'paused', 'archived', 'pending', 'in_progress',
        'needs_review', 'approved', 'rejected', 'on_hold', 'cancelled', 'deferred'
    ];

    if (!$lesson_id || !is_numeric($lesson_id) || !$status) {
        echo json_encode(['success' => false, 'message' => 'معرف الدرس أو الحالة غير صالحة.']);
        exit;
    }

    $statuses = explode(',', $status);
    $invalid_statuses = array_diff($statuses, $allowed_statuses);

    if (!empty($invalid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'بعض الحالات غير صالحة: ' . implode(', ', $invalid_statuses)]);
        exit;
    }

    try {
        $stmt = $db->prepare('UPDATE lessons SET status = :status WHERE id = :lesson_id');
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'تم تحديد حالة الدرس بنجاح.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديد حالة الدرس: ' . $e->getMessage()]);
    }
}

function getCompletionPercentage($db) {
    $course_id = $_POST['course_id'] ?? null;

    if (!$course_id || !is_numeric($course_id)) {
        echo json_encode(['success' => false, 'message' => 'معرف الكورس غير صالح.']);
        exit;
    }

    try {
        // عدد الدروس الكلي
        $stmtTotal = $db->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = :course_id');
        $stmtTotal->bindValue(':course_id', $course_id, PDO::PARAM_INT);
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        // عدد الدروس المكتملة
        $stmtCompleted = $db->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = :course_id AND status = "completed"');
        $stmtCompleted->bindValue(':course_id', $course_id, PDO::PARAM_INT);
        $stmtCompleted->execute();
        $completed = $stmtCompleted->fetchColumn();

        $percentage = ($total > 0) ? round(($completed / $total) * 100) : 0;

        echo json_encode(['success' => true, 'percentage' => $percentage]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حساب نسبة الإكمال: ' . $e->getMessage()]);
    }
}

function deleteLesson($db) {
    $lesson_id = $_POST['lesson_id'] ?? null;

    if (!$lesson_id || !is_numeric($lesson_id)) {
        echo json_encode(['success' => false, 'message' => 'معرف الدرس غير صالح.']);
        exit;
    }

    try {
        $stmt = $db->prepare('DELETE FROM lessons WHERE id = :lesson_id');
        $stmt->bindValue(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'تم حذف الدرس بنجاح.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف الدرس: ' . $e->getMessage()]);
    }
}
?>