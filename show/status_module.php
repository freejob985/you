<?php
// هيدر الموديول بلون مميز متدرج
echo '<div class="status-module-header p-3 mb-3" style="background: linear-gradient(45deg, #4a90e2, #63b3ed);">';
echo '<h2 class="text-center text-white">حالة الدروس</h2>';
echo '</div>';
echo '<br>';
// جدول الحالات
echo '<table class="table table-bordered status-table">';
echo '<thead class="thead-light">';
echo '<tr><th>الحالة</th><th>اللون</th></tr>';
echo '</thead>';
echo '<tbody>';
$statuses = ['watch', 'problem', 'discussion', 'search', 'retry', 'retry_again', 'review', 'completed', 'excluded', 'project'];
foreach ($statuses as $status) {
    echo '<tr>';
    echo '<td class="d-flex align-items-center">';
    echo '<span class="status-icon me-2">' . getStatusIcon($status) . '</span>';
    echo getStatusLabel($status);
    echo '</td>';
    echo '<td><div class="status-color ' . getStatusColorClass($status) . '"></div></td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';

// فوتر الموديول بلون مميز متدرج
echo '<div class="status-module-footer p-3 mt-3" style="background: linear-gradient(45deg, #63b3ed, #4a90e2);">';
echo '<p class="text-center text-white">يمكنك تغيير حالة الدرس من هنا</p>';
echo '</div>';

// دالة للحصول على أيقونة الحالة
function getStatusIcon($status) {
    switch ($status) {
        case 'completed': return '<i class="fas fa-check"></i>';
        case 'watch': return '<i class="fas fa-eye"></i>';
        case 'problem': return '<i class="fas fa-exclamation-triangle"></i>';
        case 'discussion': return '<i class="fas fa-comments"></i>';
        case 'search': return '<i class="fas fa-search"></i>';
        case 'retry': return '<i class="fas fa-redo"></i>';
        case 'retry_again': return '<i class="fas fa-redo-alt"></i>';
        case 'review': return '<i class="fas fa-clipboard-check"></i>';
        case 'excluded': return '<i class="fas fa-ban"></i>';
        case 'project': return '<i class="fas fa-project-diagram"></i>';
        default: return '<i class="fas fa-question"></i>';
    }
}

// دالة للحصول على اسم الصف اللوني للحالة
function getStatusColorClass($status) {
    switch ($status) {
        case 'completed': return 'bg-success';
        case 'watch': return 'bg-primary';
        case 'problem': return 'bg-danger';
        case 'discussion': return 'bg-info';
        case 'search': return 'bg-warning';
        case 'retry': return 'bg-secondary';
        case 'retry_again': return 'bg-dark';
        case 'review': return 'bg-light';
        case 'excluded': return 'bg-danger';
        case 'project': return 'bg-info';
        default: return 'bg-secondary';
    }
}
?>