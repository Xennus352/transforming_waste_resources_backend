<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

date_default_timezone_set('Asia/Yangon');
// to generate token 
function generateSessionToken()
{
    return bin2hex(random_bytes(32)); // Generate a 64-character random token
}


// handle for login and create session
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // get from ui 
    $data = json_decode(file_get_contents("php://input"), true);

    // extract value from $data 
    $username = mysqli_real_escape_string($con, $data['username']);
    $unHashpassword = mysqli_real_escape_string($con, $data['password']);
    $password = hash('sha256', $unHashpassword);

    // for searching user info in database
    $query = "SELECT * FROM `Users` WHERE username = '$username'";
    $result = mysqli_query($con, $query);


    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Use password_verify for checking the password
        if ($password == $row['password']) {

            // User authenticated, create session token
            $sessionToken = generateSessionToken();
            // Get the current Unix timestamp
            $createdAtUnix = time(); // Current time in seconds since the Unix Epoch

            // Calculate the expiration timestamp (1 hour later)
            $expiresAtUnix = $createdAtUnix + 3600; // 3600 seconds = 1 hour

            // Get the created time in seconds since midnight
            $createdAtHour = date('H', $createdAtUnix);
            $createdAtMinute = date('i', $createdAtUnix);
            $createdAt = ($createdAtHour * 3600) + ($createdAtMinute * 60);

            // Get the expiration time in seconds since midnight
            $expiresAtHour = date('H', $expiresAtUnix);
            $expiresAtMinute = date('i', $expiresAtUnix);
            $expiresAt = ($expiresAtHour * 3600) + ($expiresAtMinute * 60);

            // Insert session into the database
            $insertSessionQuery = "
                 INSERT INTO UserSessions (user_id, session_token, created_at, expires_at) 
                 VALUES ('{$row['id']}', '$sessionToken', '$createdAt', '$expiresAt')
             ";
            if (mysqli_query($con, $insertSessionQuery)) {
                // Return the session token
                echo json_encode([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Login successful',
                    'session_token' => $sessionToken,
                    'tokenExpiration' => $expiresAt
                ]);
            } else {
                echo json_encode([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Failed to create session',
                ]);
            }
        } else {
            echo json_encode([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid username or password',
            ]);
        }
    } else {
        // Return fail response
        echo json_encode([
            'status' => 500,
            'success' => false,
            'message' => 'User login fail.',
        ]);
    }
}
