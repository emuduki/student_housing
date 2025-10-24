<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['landlord','admin'])) {
    header("Location: ../login.html");
    exit;
}

$reservation_id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;
$return_status = $_GET['return_status'] ?? null;

if ($reservation_id && in_array($status, ['approved','rejected'])) {
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $reservation_id);

    if ($stmt->execute()) {
        $redirect = 'manage_reservations.php';
        if ($return_status) $redirect .= '?status=' . urlencode($return_status);
        echo "<script>alert('Reservation updated successfully!'); window.location='$redirect';</script>";
    } else {
        $redirect = 'manage_reservations.php';
        if ($return_status) $redirect .= '?status=' . urlencode($return_status);
        echo "<script>alert('Error updating reservation.'); window.location='$redirect';</script>";
    }
} else {
    header("Location: manage_reservations.php");
}
