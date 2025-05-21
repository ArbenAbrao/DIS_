<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentNames = $_POST['studentNames'] ?? [];
    $grade = $_POST['grade'] ?? '';

    if (!empty($studentNames) && !empty($grade)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO students (name, grade_level) VALUES (?, ?)");

            foreach ($studentNames as $name) {
                $stmt->bind_param("ss", $name, $grade);
                $stmt->execute();
            }

            $conn->commit();
            echo json_encode([
                "success" => true,
                "message" => "Students added successfully!"
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Please enter at least one student name and select a grade."
        ]);
    }
}
?>
