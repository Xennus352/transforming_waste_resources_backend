<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');


if ($_SERVER['REQUEST_METHOD'] == "DELETE") {


    // Decode the incoming JSON data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Sanitize input data
    $blogId = mysqli_real_escape_string($con, $data['id']);


    $query =  "DELETE FROM  `WasteInfo` WHERE id = $blogId";


    if (mysqli_query($con, $query)) {
        // Return success response
        echo json_encode([
            'status' => 200,
            'success' => true,
            'message' => 'Information deleted successfully.',
        ]);
    } else {
        // Return error response with detailed MySQL error
        echo json_encode([
            'status' => 500,
            'success' => false,
            'message' => 'Failed to delete information.',
            'error' => 'Query failed: ' . mysqli_error($con),
            'query' => $query
        ]);
    }
}
