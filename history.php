<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$grade = isset($_GET['grade']) ? trim($_GET['grade']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : ''; // Get date parameter

$query = "
    SELECT s.name, s.grade_level, a.date, a.time_in_am, a.time_out_pm

    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE 1=1
";

$params = [];
$types = "";

// Filter by grade level
if (!empty($grade)) {
    $query .= " AND s.grade_level = ?";
    $params[] = $grade;
    $types .= "s";
}

// Filter by employee type (e.g., Admin, Faculty, Guard)
if (!empty($type)) {
    $query .= " AND s.grade_level = ?";
    $params[] = $type;
    $types .= "s";
}

// Search by name
if (!empty($search)) {
    $query .= " AND s.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

// Filter by date
if (!empty($date)) {
    $query .= " AND a.date = ?";
    $params[] = $date;
    $types .= "s";
}

// Order by latest date first
$query .= " ORDER BY a.date DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
