<?php
require_once __DIR__ . '/config.php';
date_default_timezone_set('Asia/Manila'); // Set timezone

// Log POST request for debugging
file_put_contents("debug_log.txt", json_encode($_POST) . "\n", FILE_APPEND);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? '';
    $student_id = $_POST["student_id"] ?? '';

    if (empty($student_id)) {
        echo json_encode(["success" => false, "message" => "Invalid student ID"]);
        exit;
    }

    $timestamp = date("h:i:s A"); // 12-hour format with AM/PM

    switch ($action) {
        case "in":
            // Insert or update time_in_am and time_in_id
            $query = "INSERT INTO attendance (student_id, date, time_in_am, time_in_id) 
                      VALUES (?, CURDATE(), ?, ?) 
                      ON DUPLICATE KEY UPDATE time_in_am = VALUES(time_in_am), time_in_id = VALUES(time_in_id)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $student_id, $timestamp, $timestamp);
            break;

        case "out":
            // Update time_out_pm and time_out_id
            $query = "UPDATE attendance 
                      SET time_out_pm = ?, time_out_id = ? 
                      WHERE student_id = ? AND date = CURDATE() AND time_in_am IS NOT NULL";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $timestamp, $timestamp, $student_id);
            break;

        case "delete":
            // Delete attendance record and student
            $query1 = "DELETE FROM attendance WHERE student_id = ?";
            $query2 = "DELETE FROM students WHERE id = ?";

            $stmt1 = $conn->prepare($query1);
            $stmt2 = $conn->prepare($query2);
            $stmt1->bind_param("i", $student_id);
            $stmt2->bind_param("i", $student_id);

            if ($stmt1->execute() && $stmt2->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "message" => "Database error during deletion"]);
            }
            exit;

        default:
            echo json_encode(["success" => false, "message" => "Invalid action"]);
            exit;
    }

    // Execute and return response for "in" and "out" actions
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "time" => $timestamp]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    exit;
}
?>
