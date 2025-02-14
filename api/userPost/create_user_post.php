<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

include('../lib/db.php');

// Check request method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve session token from headers
    $headers = apache_request_headers();
    $sessionToken = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$sessionToken) {
        echo json_encode([
            'status' => 400,
            'success' => false,
            'message' => 'Session token is required.',
        ]);
        exit;
    }

    // Decode incoming JSON data
    $data = json_decode(file_get_contents("php://input"), true);

    // Sanitize input data
    $title = mysqli_real_escape_string($con, $data['title']);
    $description = mysqli_real_escape_string($con, $data['description']);
    $picture = mysqli_real_escape_string($con, $data['picture']);

    // Check if any required field is empty
    if (empty($title) || empty($description) || empty($picture)) {
        echo json_encode([
            'status' => 400,
            'success' => false,
            'message' => 'All fields are required.',
        ]);
        exit;
    }

    // Get the user_id from UserSessions table using session_token
    $getCurrentUser = "SELECT user_id FROM `UserSessions` WHERE session_token = '$sessionToken'";
    $result = mysqli_query($con, $getCurrentUser);

    if (mysqli_num_rows($result) > 0) {
        // Fetch the user_id
        $row = mysqli_fetch_assoc($result);
        $userId = $row['user_id'];

        // Insert post into the Posts table
        $query = "INSERT INTO `Posts` (user_id, title, content, picture) 
                    VALUES ('$userId', '$title', '$description', '$picture')";

        if (mysqli_query($con, $query)) {
            // Get new post count
            $countQuery = "SELECT COUNT(*) AS newPosts FROM `Posts` WHERE created_at >= NOW() - INTERVAL 10 MINUTE";
            $countResult = mysqli_query($con, $countQuery);
            $newPostCount = mysqli_fetch_assoc($countResult)['newPosts'];

            // Emit WebSocket event to notify users
            $postData = [
                'title' => $title,
                'content' => $description,
                'picture' => $picture,
                'newPostCount' => $newPostCount
            ];

            // Send WebSocket request to notify users
            $socketUrl = "http://localhost:5000/notify";
            $socketData = json_encode(['event' => 'newPost', 'data' => $newPostCount]);

            $ch = curl_init($socketUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $socketData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_exec($ch);
            curl_close($ch);

            // Return success response
            echo json_encode([
                'status' => 201,
                'success' => true,
                'message' => 'Post created successfully.',
                'newPostCount' => $newPostCount
            ]);
        } else {
            echo json_encode([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to insert post.',
                'error' => mysqli_error($con),
                'query' => $query
            ]);
        }
    } else {
        echo json_encode([
            'status' => 400,
            'success' => false,
            'message' => 'Invalid session token.',
        ]);
    }
}
?>
