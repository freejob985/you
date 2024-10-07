function getStatuses() {
    $db = connectDB();
    $stmt = $db->query("SELECT DISTINCT status FROM lessons");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getSections() {
    $db = connectDB();
    $stmt = $db->query("SELECT * FROM sections");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateLessonStatusOrSection($lessonId, $type, $value) {
    $db = connectDB();
    $column = ($type === 'status') ? 'status' : 'section_id';
    $stmt = $db->prepare("UPDATE lessons SET $column = :value WHERE id = :lesson_id");
    $stmt->bindParam(':value', $value);
    $stmt->bindParam(':lesson_id', $lessonId, PDO::PARAM_INT);
    return $stmt->execute();
}