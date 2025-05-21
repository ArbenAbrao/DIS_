<?php
require_once __DIR__ . '/config.php';

$grade_level = $_GET['grade'] ?? '';

if (empty($grade_level)) {
    echo "<p>Please select a grade level.</p>";
    exit;
}

// Handle POST actions: time in, time out, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $action = $_POST['action'] ?? '';

    if (empty($id) || empty($action)) {
        echo json_encode(["error" => "Invalid request"]);
        exit;
    }

    $current_time = date("H:i:s");

    switch ($action) {
        case 'time_in':
            $query = "UPDATE attendance SET time_in_am = ? WHERE student_id = ? AND date = CURDATE()";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $current_time, $id);
            $stmt->execute();
            echo json_encode(["time_in" => $current_time]);
            break;

        case 'time_out':
            $query = "UPDATE attendance SET time_out_pm = ? WHERE student_id = ? AND date = CURDATE()";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $current_time, $id);
            $stmt->execute();
            echo json_encode(["time_out" => $current_time]);
            break;

        case 'delete':
            $query = "DELETE FROM attendance WHERE student_id = ? AND date = CURDATE()";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode(["status" => "deleted"]);
            break;

        default:
            echo json_encode(["error" => "Unknown action"]);
    }

    exit;
}

echo "<h2>" . htmlspecialchars($grade_level) . " Attendance</h2>";
?>

<style>
.table-wrapper {
  max-height: 400px; /* Height for ~10 rows */
  overflow-y: auto;
  border: 1px solid #ddd;
}

</style>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="studentTableBody">
            <?php
            $query = "
                SELECT 
                    students.name, 
                    students.grade_level, 
                    COALESCE(attendance.time_in_am, '') AS time_in, 
                    COALESCE(attendance.time_out_pm, '') AS time_out,
                    students.id AS id
                FROM students
                LEFT JOIN attendance 
                    ON students.id = attendance.student_id 
                    AND attendance.date = CURDATE()
                    AND attendance.exported = 0
                WHERE students.grade_level = ?
                ORDER BY students.name ASC";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $grade_level);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr data-id='{$row['id']}'>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td class='time-in'>{$row['time_in']}</td>
                            <td class='time-out'>{$row['time_out']}</td>
                            <td>
                                <button class='in-btn'>In</button>
                                <button class='out-btn'>Out</button>
                                <button class='delete-btn'>Delete</button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No students found for this grade level.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('#studentTableBody').addEventListener('click', e => {
        const btn = e.target;
        if (!btn.matches('button')) return;

        const tr = btn.closest('tr');
        const id = tr.dataset.id;
        let action = '';

        if (btn.classList.contains('in-btn')) action = 'time_in';
        else if (btn.classList.contains('out-btn')) action = 'time_out';
        else if (btn.classList.contains('delete-btn')) action = 'delete';
        else return;

        fetch(window.location.href, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ id, action })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            if (action === 'time_in' && data.time_in) {
                tr.querySelector('.time-in').textContent = data.time_in;
            } else if (action === 'time_out' && data.time_out) {
                tr.querySelector('.time-out').textContent = data.time_out;
            } else if (action === 'delete' && data.status === 'deleted') {
                tr.remove();
            }
        })
        .catch(() => alert('Request failed'));
    });
});
</script>
