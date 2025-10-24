<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $student_id = $_SESSION['user_id'];

    // Cancel only if it's the student's own reservation and still pending
    $stmt = $conn->prepare("UPDATE reservations SET status='cancelled' 
                            WHERE id=? AND student_id=? AND status='pending'");
    $stmt->bind_param("ii", $reservation_id, $student_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Reservation cancelled successfully.";
    } else {
        $_SESSION['message'] = "Error cancelling reservation.";
    }
}

header("Location: my_reservations.php");
exit;
?>
