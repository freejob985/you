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
        
    case 'add_section':
        if (!isset($_POST['language_id']) || !isset($_POST['section_name'])) {
            echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
            return;
        }

        $languageId = (int)$_POST['language_id'];
        $sectionName = trim($_POST['section_name']);

        try {
            // التحقق من عدم وجود قسم بنفس الاسم
            $stmt = $db->prepare('SELECT id FROM sections WHERE language_id = ? AND name = ?');
            $stmt->execute([$languageId, $sectionName]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'هذا القسم موجود بالفعل']);
                return;
            }

            // إضافة القسم الجديد
            $stmt = $db->prepare('INSERT INTO sections (language_id, name) VALUES (?, ?)');
            $stmt->execute([$languageId, $sectionName]);

            // جلب معرف القسم الجديد
            $newSectionId = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'تم إضافة القسم بنجاح',
                'section' => [
                    'id' => $newSectionId,
                    'name' => $sectionName
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة القسم: ' . $e->getMessage()]);
        }
        break;
        
    case 'get_sections':
        if (!isset($_POST['language_id'])) {
            echo json_encode(['success' => false, 'message' => 'معرف اللغة مطلوب']);
            return;
        }

        $languageId = (int)$_POST['language_id'];
        
        try {
            $stmt = $db->prepare('SELECT id, name FROM sections WHERE language_id = ? ORDER BY name');
            $stmt->execute([$languageId]);
            $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'sections' => $sections
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلب الأقسام: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'update_lesson_section':
        if (!isset($_POST['lesson_id'])) {
            echo json_encode(['success' => false, 'message' => 'معرف الدرس مطلوب']);
            return;
        }

        $lessonId = (int)$_POST['lesson_id'];
        $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : null;

        try {
            if ($sectionId) {
                $stmt = $db->prepare('UPDATE lessons SET section_id = ? WHERE id = ?');
                $stmt->execute([$sectionId, $lessonId]);

                $stmt = $db->prepare('SELECT name FROM sections WHERE id = ?');
                $stmt->execute([$sectionId]);
                $sectionName = $stmt->fetchColumn();
            } else {
                $stmt = $db->prepare('UPDATE lessons SET section_id = NULL WHERE id = ?');
                $stmt->execute([$lessonId]);
                $sectionName = 'بدون قسم';
            }

            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث القسم بنجاح',
                'section_name' => $sectionName
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث القسم: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'delete_section':
        if (!isset($_POST['section_id'])) {
            echo json_encode(['success' => false, 'message' => 'معرف القسم مطلوب']);
            return;
        }

        $sectionId = (int)$_POST['section_id'];

        try {
            // تحديث الدروس المرتبطة بهذا القسم
            $stmt = $db->prepare('UPDATE lessons SET section_id = NULL WHERE section_id = ?');
            $stmt->execute([$sectionId]);

            // حذف القسم
            $stmt = $db->prepare('DELETE FROM sections WHERE id = ?');
            $stmt->execute([$sectionId]);

            echo json_encode([
                'success' => true,
                'message' => 'تم حذف القسم بنجاح'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف القسم: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'update_lesson_order':
        if (!isset($_POST['lesson_id'])) {
            echo json_encode(['success' => false, 'message' => 'معرف الدرس مطلوب']);
            return;
        }

        $lessonId = (int)$_POST['lesson_id'];
        $prevLessonId = isset($_POST['prev_lesson_id']) ? (int)$_POST['prev_lesson_id'] : null;
        $nextLessonId = isset($_POST['next_lesson_id']) ? (int)$_POST['next_lesson_id'] : null;

        try {
            // بدء المعاملة
            $db->beginTransaction();

            // الحصول على الترتيب الحالي للدروس المجاورة
            if ($prevLessonId) {
                $stmt = $db->prepare('SELECT sort_order FROM lessons WHERE id = ?');
                $stmt->execute([$prevLessonId]);
                $prevOrder = $stmt->fetchColumn();
            }

            if ($nextLessonId) {
                $stmt = $db->prepare('SELECT sort_order FROM lessons WHERE id = ?');
                $stmt->execute([$nextLessonId]);
                $nextOrder = $stmt->fetchColumn();
            }

            // حساب الترتيب الجديد
            if (!$prevLessonId) {
                // إذا كان أول درس
                $newOrder = isset($nextOrder) ? $nextOrder - 1000 : 0;
            } elseif (!$nextLessonId) {
                // إذا كان آخر درس
                $newOrder = isset($prevOrder) ? $prevOrder + 1000 : 0;
            } else {
                // إذا كان في الوسط
                $newOrder = ($prevOrder + $nextOrder) / 2;
            }

            // تحديث ترتيب الدرس
            $stmt = $db->prepare('UPDATE lessons SET sort_order = ? WHERE id = ?');
            $stmt->execute([$newOrder, $lessonId]);

            // تأكيد المعاملة
            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث ترتيب الدرس بنجاح'
            ]);
        } catch (Exception $e) {
            // التراجع عن المعاملة في حالة حدوث خطأ
            $db->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث ترتيب الدرس: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'update_lesson_status':
        try {
            $lessonId = $_POST['lesson_id'];
            $status = $_POST['status'];
            
            // التحقق من صحة البيانات
            if (!$lessonId || !$status || !isValidStatus($status)) {
                throw new Exception('البيانات غير صحيحة');
            }
            
            // تحديث حالة الدرس
            $stmt = $db->prepare('UPDATE lessons SET status = ? WHERE id = ?');
            $result = $stmt->execute([$status, $lessonId]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'تم تحديث الحالة بنجاح'
                ]);
            } else {
                throw new Exception('فشل تحديث الحالة');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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

/**
 * إضافة قسم جديد
 */
function handleAddSection($db) {
    if (!isset($_POST['language_id']) || !isset($_POST['section_name'])) {
        echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
        return;
    }

    $languageId = (int)$_POST['language_id'];
    $sectionName = trim($_POST['section_name']);

    try {
        $stmt = $db->prepare('INSERT INTO sections (language_id, name) VALUES (?, ?)');
        $stmt->execute([$languageId, $sectionName]);

        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة القسم بنجاح',
            'section' => [
                'id' => $db->lastInsertId(),
                'name' => $sectionName
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة القسم']);
    }
}

/**
 * التحقق من صحة الحالة
 * @param string $status الحالة المراد التحقق منها
 * @return bool
 */
function isValidStatus($status) {
    $validStatuses = [
        'completed', 'watch', 'review', 'problem', 
        'retry', 'retry_again', 'discussion', 'search',
        'excluded', 'project'
    ];
    return in_array($status, $validStatuses);
}
?>