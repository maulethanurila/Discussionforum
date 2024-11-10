<?php
$conn = new mysqli("localhost", "username", "password", "discussion_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
