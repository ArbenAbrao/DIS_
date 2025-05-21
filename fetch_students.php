<?php
include 'config.php';

// Prepare SQL query and execute
$query = "SELECT * FROM attendance ORDER BY id DESC";

if ($result = $conn->query($query)) {
    $students = [];
    
    // Fetch all the data from the result set
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    // Return data as JSON
    echo json_encode($students);
    
    // Free result memory
    $result->free();
} else {
    // Handle query error
    echo json_encode(["error" => "Failed to retrieve data from database"]);
}

// Close connection
$conn->close();
?>
