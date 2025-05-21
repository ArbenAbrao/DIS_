<?php
// Enable PHP error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Database connection
$conn = new mysqli("localhost", "root", "", "attendance_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Get current time and date
$currentTime = date("h:i A");
$currentDate = date("F d, Y"); // e.g., May 14, 2025

// Fetch only unexported attendance data for today
$query = "SELECT students.name, students.grade_level, 
                 COALESCE(attendance.time_in_am, '') AS time_in, 
                 COALESCE(attendance.time_out_pm, '') AS time_out,
                 students.id AS student_id
          FROM students
          LEFT JOIN attendance 
          ON students.id = attendance.student_id 
          AND attendance.date = CURDATE()
          AND attendance.exported = 0
          ORDER BY students.grade_level ASC, students.name ASC";

$result = $conn->query($query);

// Group data by grade level
$gradeLevels = [];
$studentIdsToMark = [];

while ($data = $result->fetch_assoc()) {
    $grade = $data['grade_level'];
    if (!isset($gradeLevels[$grade])) {
        $gradeLevels[$grade] = [];
    }
    $gradeLevels[$grade][] = $data;
    $studentIdsToMark[] = $data['student_id'];
}

// Prepare spreadsheet
$spreadsheet = new Spreadsheet();
$sheetIndex = 0;

foreach ($gradeLevels as $grade => $students) {
    $sheet = ($sheetIndex === 0) ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
    $sheet->setTitle(substr($grade, 0, 31)); // Excel sheet name limit is 31 chars

    // Headers
    $sheet->setCellValue('A1', 'Student Name');
    $sheet->setCellValue('B1', 'Time In');
    $sheet->setCellValue('C1', 'Time Out');
    $sheet->setCellValue('D1', 'Remarks');
    $sheet->setCellValue('E1', 'Date');

    $sheet->getStyle('A1:E1')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DDDDDD'],
        ],
    ]);

    // Data rows
    $row = 2;
    foreach ($students as $student) {
        $timeInRaw = $student['time_in'];
        $timeOutRaw = $student['time_out'];

        $timeIn = (!empty($timeInRaw) && strtotime($timeInRaw) !== false)
                    ? date("h:i A", strtotime($timeInRaw)) : '';

        $timeOut = (!empty($timeOutRaw) && strtotime($timeOutRaw) !== false)
                    ? date("h:i A", strtotime($timeOutRaw)) : '';

        // Optional debug log
        // error_log("Student: {$student['name']} | TimeIn: $timeInRaw | Parsed: $timeIn");

        $sheet->setCellValue("A$row", $student['name']);
        $sheet->setCellValue("B$row", $timeIn);
        $sheet->setCellValue("C$row", $timeOut);

        $remarkCell = "D$row";
        if (!empty($timeIn) && !empty($timeOut)) {
            $sheet->setCellValue($remarkCell, 'Present');
            $sheet->getStyle($remarkCell)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C6EFCE'],
                ],
                'font' => ['color' => ['rgb' => '006100']],
            ]);
        } else {
            $sheet->setCellValue($remarkCell, 'Absent');
            $sheet->getStyle($remarkCell)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFC7CE'],
                ],
                'font' => ['color' => ['rgb' => '9C0006']],
            ]);
        }

        $sheet->setCellValue("E$row", $currentDate);
        $row++;
    }

    // Auto size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $sheetIndex++;
}

// Create nested folder structure: Year > Month
$baseFolder = 'C:/Attendance_Backups/';
$currentYear = date("Y");
$currentMonth = strtoupper(date("F"));

$yearFolder = $baseFolder . $currentYear . "_ATTENDANCE/";
$monthlyFolder = $yearFolder . $currentMonth . "_ATTENDANCE/";

// Create year folder if not exists
if (!is_dir($yearFolder)) {
    if (!mkdir($yearFolder, 0777, true)) {
        die("❌ Failed to create year folder: $yearFolder");
    }
}

// Create month folder if not exists
if (!is_dir($monthlyFolder)) {
    if (!mkdir($monthlyFolder, 0777, true)) {
        die("❌ Failed to create monthly folder: $monthlyFolder");
    }
}

// Create Excel file name and path
$fileName = "Attendance_" . date("Y-m-d") . "_" . date("l") . ".xlsx";
$filePath = $monthlyFolder . $fileName;

// Save the Excel file
try {
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);
} catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
    die("❌ Failed to save file: " . $e->getMessage());
}

// Mark records as exported in the database
if (!empty($studentIdsToMark)) {
    $idList = implode(',', array_map('intval', $studentIdsToMark));

    // Update exported status
    $conn->query("UPDATE attendance 
                  SET exported = 1, exported_at = NOW() 
                  WHERE student_id IN ($idList) AND date = CURDATE()");

    // Clear time in/out fields
    $conn->query("UPDATE attendance 
                  SET time_in_am = NULL, time_out_pm = NULL 
                  WHERE student_id IN ($idList) AND date = CURDATE()");
}

$conn->close();
echo "✅ Exported successfully to <strong>$filePath</strong>";
?>
