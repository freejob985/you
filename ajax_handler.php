<?php

// أضف هذه الحالات في switch statement الموجود في ملف ajax_handler.php

case 'get_statuses':
    $statuses = getStatuses();
    echo json_encode(['success' => true, 'statuses' => $statuses]);
    break;

case 'get_sections':
    $sections = getSections();
    echo json_encode(['success' => true, 'sections' => $sections]);
    break;

case 'update_lesson_status_or_section':
    $lessonId = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $value = isset($_POST['value']) ? $_POST['value'] : '';
    $result = updateLessonStatusOrSection($lessonId, $type, $value);
    if ($result) {
        $courseId = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $statistics = getCourseStatistics($courseId);
        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث الدرس بنجاح',
            'statistics' => $statistics
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'فشل تحديث الدرس']);
    }
    break;

?>