<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.html");
    exit;
}

$student_id = $_SESSION['user_id'];
$house_id = $_GET['house_id'] ?? null;

if ($house_id) {
    // Check if this student already reserved this house
    $check = $conn->prepare("SELECT * FROM reservations WHERE student_id = ? AND house_id = ?");
    $check->bind_param("ii", $student_id, $house_id);
    $check->execute();
    $result = $check->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('You have already reserved this house.'); window.location='browse_houses.php';</script>";
        exit;
    }

    //2. Check if house is already approved for another student
    $check2 = $conn->prepare("SELECT * FROM reservations WHERE house_id = ? AND status = 'approved'");
    $check2->bind_param("i", $house_id);
    $check2->execute();
    $taken = $check2->get_result();

    if ($taken->num_rows > 0) {
        echo "<script>alert('Sorry, this house is already taken.'); window.location='browse_houses.php';</script>";
        exit;
    }

    // 3. Insert reservation as pending
    $stmt = $conn->prepare("INSERT INTO reservations (student_id, house_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $student_id, $house_id);

    if ($stmt->execute()) {
        echo "<script>alert('Reservation successful! Pending approval.'); window.location='my_reservations.php';</script>";
    } else {
            echo "<script>alert('Error while reserving. Please try again.'); window.location='browse_houses.php';</script>";
    }
} else {
        header("Location: browse_houses.php");
}
?>
   
