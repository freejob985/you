<?php
header('Content-Type: application/json');

if (file_exists('course_progress.txt')) {
    $progress = json_decode(file_get_contents('course_progress.txt'), true);
    if ($progress['progress'] < 100) {
        echo json_encode([
            'status' => 'in_progress',
            'progress' => $progress['progress'],
            'current' => $progress['current'],
            'total' => $progress['total'],
            'latest_lesson' => $progress['latest_lesson']
        ]);
    } else {
        echo json_encode([
            'status' => 'completed',
            'message' => 'تم إضافة الكورس بنجاح!'
        ]);
        unlink('course_progress.txt');
    }
} else {
    echo json_encode([
        'status' => 'not_started',
        'message' => 'لم يبدأ إضافة الكورس بعد.'
    ]);
}
