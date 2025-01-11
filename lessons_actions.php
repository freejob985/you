<?php
header('Content-Type: application/json');

// إنشاء اتصال بقاعدة البيانات
try {
    $db = new PDO('sqlite:courses.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في الاتصال بقاعدة البيانات']);
    exit;
}

// التحقق من وجود إجراء
if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'لم يتم تحديد الإجراء']);
    exit;
}

// معالجة الإجراءات المختلفة
switch ($_POST['action']) {
    case 'toggle_status':
        handleToggleStatus($db);
        break;
        
    case 'delete_lesson':
        handleDeleteLesson($db);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'الإجراء غير معروف']);
        break;
}

/**
 * تبديل حالة الدرس بين مكتمل وقيد الانتظار
 */
function handleToggleStatus($db) {
    if (!isset($_POST['lesson_id'])) {
        echo json_encode(['success' => false, 'message' => 'معرف الدرس مطلوب']);
        return;
    }

    $lessonId = (int)$_POST['lesson_id'];

    try {
        // جلب الحالة الحالية للدرس
        $stmt = $db->prepare('SELECT status FROM lessons WHERE id = ?');
        $stmt->execute([$lessonId]);
        $currentStatus = $stmt->fetchColumn();

        // تحديد الحالة الجديدة
        $newStatus = $currentStatus === 'completed' ? 'pending' : 'completed';

        // تحديث حالة الدرس
        $stmt = $db->prepare('UPDATE lessons SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $lessonId]);

        echo json_encode([
            'success' => true,
            'new_status' => $newStatus,
            'status_label' => getStatusLabel($newStatus)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث حالة الدرس']);
    }
}

/**
 * حذف درس
 */
function handleDeleteLesson($db) {
    if (!isset($_POST['lesson_id'])) {
        echo json_encode(['success' => false, 'message' => 'معرف الدرس مطلوب']);
        return;
    }

    $lessonId = (int)$_POST['lesson_id'];

    try {
        $stmt = $db->prepare('DELETE FROM lessons WHERE id = ?');
        $stmt->execute([$lessonId]);

        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الدرس بنجاح'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف الدرس']);
    }
}

/**
 * تحويل حالة الدرس إلى نص قابل للقراءة
 */
function getStatusLabel($status) {
    $statusLabels = [
        'completed' => 'مكتمل',
        'watching' => 'قيد المشاهدة',
        'pending' => 'قيد الانتظار',
        'problem' => 'مشكلة',
        'discussion' => 'نقاش',
        'search' => 'بحث',
        'retry' => 'إعادة',
        'retry_again' => 'إعادة ثانية',
        'review' => 'مراجعة',
        'excluded' => 'مستبعد',
        'project' => 'مشروع تطبيقي',
        'watch' => 'مشاهدة'
    ];

    return $statusLabels[$status] ?? 'غير محدد';
}
?>