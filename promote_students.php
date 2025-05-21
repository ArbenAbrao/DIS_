<?php
require_once 'config.php';

$promotion_map = [
    "Nursery" => "Kinder",
    "Kinder" => "Grade 1",
    "Grade 1" => "Grade 2",
    "Grade 2" => "Grade 3",
    "Grade 3" => "Grade 4",
    "Grade 4" => "Grade 5",
    "Grade 5" => "Grade 6",
    "Grade 6" => "Grade 7",
    "Grade 7" => "Grade 8",
    "Grade 8" => "Grade 9",
    "Grade 9" => "Grade 10",
    "Grade 10" => "Grade 11",
    "Grade 11" => "Grade 12"
];

$conn->begin_transaction();

try {
    // 1. Remove old Grade 12 students
    $deleteStmt = $conn->prepare("DELETE FROM students WHERE grade_level = 'Grade 12'");
    if (!$deleteStmt->execute()) {
        throw new Exception("Error deleting Grade 12 students: " . $deleteStmt->error);
    }
    $deleteStmt->close();

    // 2. Promote students from other grades
    foreach (array_reverse($promotion_map) as $current => $next) {
        $stmt = $conn->prepare("UPDATE students SET grade_level = ? WHERE grade_level = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $next, $current);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close(); // Close statement after execution
    }

    // 3. Commit changes
    $conn->commit();

    // 4. Fetch updated student list
    $result = $conn->query("SELECT id, name, grade_level FROM students ORDER BY grade_level ASC");
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode(["success" => true, "students" => $students]);
} catch (Exception $e) {
    $conn->rollback(); // Undo changes if an error occurs
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
