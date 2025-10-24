<?php
session_start();
include("config/db.php"); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>alert('New passwords do not match'); window.history.back();</script>";
        exit;
    }

    // Fetch current password hash from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify old password
        if (password_verify($old_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update in DB
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $user_id);
            if ($update->execute()) {
                echo "<script>alert('Password changed successfully!'); window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Error updating password.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Old password is incorrect.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User not found.'); window.history.back();</script>";
    }
}
?>
