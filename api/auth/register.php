<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // get from ui 
    $data = json_decode(file_get_contents("php://input"), true);

    // extract value from $data 
    $username = mysqli_real_escape_string($con, $data['username']);
    $email = mysqli_real_escape_string($con, $data['email']);

    $unHashpassword = mysqli_real_escape_string($con, $data['password']);
    $password = hash('sha256', $unHashpassword);
    $query = "INSERT INTO `Users` (username,email,picture,password,role) VALUES ('$username','$email','','$password','user')";
    if (mysqli_query($con, $query)) {
        // Return success response
        echo json_encode([
            'status' => 201,
            'success' => true,
            'message' => 'User created successfully.',
        ]);
    } else {
        // Return error response with detailed MySQL error
        echo json_encode([
            'success' => false,
            'error' => 'Query failed: ' . mysqli_error($con),
            'query' => $query
        ]);
    }
}
