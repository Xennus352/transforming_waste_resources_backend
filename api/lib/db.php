<?php
header('Content-Type: application/json');

// Database credentials
$hostname = "localhost";
$username = "smk";
$password = "smk";
$dbname = "transforming_waste_into_resources";

// Establish database connection
$con = mysqli_connect($hostname, $username, $password, $dbname);

// Check connection
if ($con) {
    $response = [
        'success' => true,
        'message' => 'Database is connected successfully!'
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Database connection failed!',
        'error' => mysqli_connect_error() // Provide detailed connection error
    ];
}

// Return JSON response
// echo json_encode($response);
