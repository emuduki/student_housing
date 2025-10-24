<?php
session_start();
include("../config/db.php");

// ✅ Role normalization
$role = strtolower(trim($_SESSION["role"] ?? ''));
if ($role !== 'landlord') {
    header("Location: ../login.html");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Fetch counts
$totalHouses = $conn->query("SELECT COUNT(*) AS total FROM houses WHERE landlord_id = $landlord_id")->fetch_assoc()['total'];
$totalReservations = $conn->query("SELECT COUNT(*) AS total FROM reservations r JOIN houses h ON r.house_id = h.id WHERE h.landlord_id = $landlord_id")->fetch_assoc()['total'];
$totalPending = $conn->query("SELECT COUNT(*) AS total FROM reservations r JOIN houses h ON r.house_id = h.id WHERE h.landlord_id = $landlord_id AND r.status='pending'")->fetch_assoc()['total'];
$totalApproved = $conn->query("SELECT COUNT(*) AS total FROM reservations r JOIN houses h ON r.house_id = h.id WHERE h.landlord_id = $landlord_id AND r.status='approved'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Landlord Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { min-height: 100vh; display: flex; flex-direction: column; }

    /* Sidebar */
    .sidebar {
        min-width: 200px;
        max-width: 200px;
        background-color: #343a40; /* darker shade than navbar */
        color: white;
        display: flex;
        flex-direction: column;
        padding: 20px;
        position: fixed;
        top: 56px; /* same height as navbar */
        left: 0;
        bottom: 0;
        overflow-y: auto;
    }
    .sidebar a {
        color: white;
        text-decoration: none;
        display: block;
        padding: 12px 10px;
        margin-bottom: 5px;
        border-radius: 4px;
        font-weight: 300;
    }
    .sidebar a:hover { background-color: #495057; }

    .main-content {
        margin-left: 200px;
        padding: 20px;
        margin-top: 10px;
        width: calc(100% - 200px);
    }

    .card-hover:hover {
        transform: translateY(-5px);
        transition: 0.3s;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: relative;
            width: 100%;
            height: auto;
            top: 0;
        }
        .main-content {
            margin-left: 0;
            width: 100%;
            margin-top: 20px;
        }
    }
</style>
</head>
<body class="bg-light">

<!-- Navbar (same style as student) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">🏠 Student Housing</a>
        <div class="d-flex align-items-center">
            <span class="me-3 text-light"><strong><?= ucfirst($role) ?></strong></span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <a href="#">My Profile</a>
    <a href="#">Saved Property</a>
    <a href="../houses/houses.php">Manage Houses</a>
    <a href="#">Messages</a>
    <a href="../houses/add_house.php">Add House</a>
    <a href="#">Change Password</a>
    <a href="../houses/manage_reservations.php">Manage Reservations</a>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Welcome Card -->
    <div class="alert alert-primary text-center shadow-sm">
        <h2>Welcome, Landlord!</h2>
        <p>Manage your houses and reservations easily.</p>
    </div>

    <!-- Dashboard Cards -->
    <div class="row text-center g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-hover shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Houses</h5>
                    <p class="card-text fs-4"><?= $totalHouses ?></p>
                    <a href="../houses/houses.php" class="btn btn-light btn-sm">View Houses</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-hover shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Total Reservations</h5>
                    <p class="card-text fs-4"><?= $totalReservations ?></p>
                    <a href="../houses/manage_reservations.php" class="btn btn-dark btn-sm">View All</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-hover shadow-sm bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <p class="card-text fs-4"><?= $totalPending ?></p>
                    <a href="../houses/manage_reservations.php?status=pending" class="btn btn-light btn-sm">View Pending</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-hover shadow-sm bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Approved</h5>
                    <p class="card-text fs-4"><?= $totalApproved ?></p>
                    <a href="../houses/manage_reservations.php?status=approved" class="btn btn-light btn-sm">View Approved</a>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
