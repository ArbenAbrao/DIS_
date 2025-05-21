<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$conn = new mysqli("localhost", "root", "", "attendance_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Manila');
$currentDate = date("F d, Y");

// Fetch attendance using correct TIME fields
$query = "SELECT students.name, students.grade_level,
                 attendance.time_in_id,
                 attendance.time_out_id,
                 students.id AS student_id
          FROM students
          LEFT JOIN attendance 
          ON students.id = attendance.student_id 
          AND attendance.date = CURDATE()
          AND attendance.exported = 0
          ORDER BY students.grade_level ASC, students.name ASC";

$result = $conn->query($query);

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

$spreadsheet = new Spreadsheet();
$sheetIndex = 0;

foreach ($gradeLevels as $grade => $students) {
    $sheet = ($sheetIndex === 0) ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
    $sheet->setTitle(substr($grade, 0, 31));

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

    $row = 2;
    foreach ($students as $student) {
        // Format time properly (hh:mm AM/PM) if not empty
        $timeIn = !empty($student['time_in_id']) ? date("h:i A", strtotime($student['time_in_id'])) : '';
        $timeOut = !empty($student['time_out_id']) ? date("h:i A", strtotime($student['time_out_id'])) : '';

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

    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $sheetIndex++;
}

$baseFolder = 'C:/Attendance_Backups/';
$currentYear = date("Y");
$currentMonth = strtoupper(date("F"));

$yearFolder = $baseFolder . $currentYear . "_ATTENDANCE/";
$monthlyFolder = $yearFolder . $currentMonth . "_ATTENDANCE/";

if (!is_dir($yearFolder)) {
    if (!mkdir($yearFolder, 0777, true)) {
        die("❌ Failed to create year folder: $yearFolder");
    }
}
if (!is_dir($monthlyFolder)) {
    if (!mkdir($monthlyFolder, 0777, true)) {
        die("❌ Failed to create monthly folder: $monthlyFolder");
    }
}

$fileName = "Attendance_" . date("Y-m-d") . "_" . date("l") . ".xlsx";
$filePath = $monthlyFolder . $fileName;

try {
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);
} catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
    die("❌ Failed to save file: " . $e->getMessage());
}

// Mark as exported and clear time fields
if (!empty($studentIdsToMark)) {
    $idList = implode(',', array_map('intval', $studentIdsToMark));

    $conn->query("UPDATE attendance 
                  SET exported = 1, exported_at = NOW() 
                  WHERE student_id IN ($idList) AND date = CURDATE()");

    $conn->query("UPDATE attendance 
                  SET time_in_id = NULL, time_out_id = NULL 
                  WHERE student_id IN ($idList) AND date = CURDATE()");
}

$conn->close();
echo "✅ Exported successfully to <strong>$filePath</strong>";
?>
