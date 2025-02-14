<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Retrieve session token from the request headers
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

    // Get the user_id from UserSessions table using session_token
    $getCurrentUser  = "SELECT user_id FROM `UserSessions` WHERE session_token = '$sessionToken'";
    $result = mysqli_query($con, $getCurrentUser);

    if (mysqli_num_rows($result) > 0) {
        // Fetch the user_id
        $row = mysqli_fetch_assoc($result);
        $userId = $row['user_id'];
        // $userId = '6';

        // Query to get saved posts for the current user
        $sql = "SELECT 
                    C.id AS comment_id,
                    C.comment,
                    C.post_id,
                    P.title AS post_title, 
                    P.content AS post_content,  
                    C.user_id,
                    COALESCE(total_likes.total_like_count, 0) AS total_like_count,
                    GROUP_CONCAT(L.user_id) AS liked_by_users
                FROM 
                    Comments C
                LEFT JOIN 
                    Posts P ON C.post_id = P.id  
                LEFT JOIN 
                    (SELECT post_id, COUNT(user_id) AS total_like_count
                    FROM Likes
                    GROUP BY post_id) total_likes
                ON 
                    C.post_id = total_likes.post_id
                LEFT JOIN 
                    Likes L ON C.post_id = L.post_id
                GROUP BY 
                    C.id, C.post_id, P.title, P.content 
                ORDER BY 
                    C.post_id, C.id";

        $likedCount = mysqli_query($con, $sql);

        if (mysqli_num_rows($likedCount) > 0) {
            $data = array();

            while ($row = mysqli_fetch_assoc($likedCount)) {
                $data[] = $row;
            }

            echo json_encode(['success' => true, 'message' => 'Successfully retrieved likes', 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database query failed']);
        }
    } else {
        // Return error response if session token not found
        echo json_encode([
            'success' => false,
            'message' => 'Invalid session token.',
        ]);
    }
} else {
    // Return error response for unsupported request methods
    echo json_encode([
        'success' => false,
        'message' => 'Unsupported request method.',
    ]);
}
