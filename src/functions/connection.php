<?php
require_once "../../config/database.php";
require_once "../../config/app.php";

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    exit("connection failed: " . $conn->connect_error);
}

// Function to check if a table exists
function table_exists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Create tables if they don't exist
$tables = ['users', 'posts', 'follows', 'hashtags', 'likes'];
$schema_dir = BASE_PATH . '/config/schema/';

foreach ($tables as $table) {
    if (!table_exists($conn, $table)) {
        $schema_file = $schema_dir . $table . '.sql';
        if (file_exists($schema_file)) {
            $sql = file_get_contents($schema_file);
            if ($conn->multi_query($sql)) {
                // Clear results to allow next query
                while ($conn->more_results() && $conn->next_result()) {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                }
            }
        }
    }
}
?>