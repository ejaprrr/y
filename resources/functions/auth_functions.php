<?php
/**
 * Authentication related functions
 */

/**
 * Fetch user from session or redirect to login
 *
 * @param mysqli $conn Database connection
 * @return array User data
 */
function get_user_from_session($conn) {
    start_session();
    if (!empty($_SESSION["user_name"])) {
        $user_name = $_SESSION["user_name"];
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    redirect("/y/public/auth/login.php");
}

/**
 * Validate password strength
 *
 * @param string $password Password to validate
 * @return bool True if valid
 */
function validate_password($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,64}$/', $password);
}

/**
 * Check if a user is logged in
 *
 * @return bool True if user is logged in
 */
function is_logged_in() {
    start_session();
    return !empty($_SESSION["user_name"]);
}

/**
 * Log a user out (destroy session)
 */
function logout() {
    start_session();
    session_unset();
    session_destroy();
}
?>