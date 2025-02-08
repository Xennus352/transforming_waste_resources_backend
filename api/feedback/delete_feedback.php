<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Decode the incoming JSON data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Sanitize input data
    $feedbackId = mysqli_real_escape_string($con, $data['feedback_id']);

    // delete the feedback
    $query = "DELETE FROM `Feedback` WHERE id='$feedbackId'";

    if (mysqli_query($con, $query)) {
        // Return success response
        echo json_encode([
            'status' => 200,
            'success' => true,
            'message' => 'Feedback deleted successfully.',
            'id' => $feedbackId
        ]);
    } else {
        // Return error response with detailed MySQL error
        echo json_encode([
            'status' => 500,
            'success' => false,
            'message' => 'Failed to delete feedback.',
            'error' => 'Query failed: ' . mysqli_error($con),
            'query' => $query
        ]);
    }
}
