<?php 
session_start();
include '../config/db.php';

$landlord_id = $_SESSION['user_id'] ?? null;
if (!$landlord_id) {
    die("Unauthorized access.");
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($new_password !== $confirm_password) {
    die("New password and confirmation do not match.");
}

//Fetch current password from database
$query = $conn->prepare("SELECT password FROM landlords WHERE id = ?");
$query->bind_param("i", $landlord_id);
$query->execute();
$result = $query->get_result()->fetch_assoc();

if (!password_verify($current_password, $result['password'])) {
    die("Current password is incorrect.");
}

//Update to new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$query = $conn->prepare("UPDATE landlords SET password = ? WHERE id = ?");
$query->bind_param("si", $hashed_password, $landlord_id);
$update->execute();

if ($query->affected_rows > 0) {
    echo "Password updated successfully.";
} else {
    echo "Failed to update password. Please try again.";
}
?>