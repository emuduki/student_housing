<?php
session_start();
include("../config/db.php");

// ✅ Role normalization
$role = strtolower(trim($_SESSION["role"] ?? ''));
if ($role !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch counts
$totalHouses = $conn->query("SELECT COUNT(*) AS total FROM houses")->fetch_assoc()['total'];
$totalReservations = $conn->query("SELECT COUNT(*) AS total FROM reservations")->fetch_assoc()['total'];
$totalLandlords = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='landlord'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
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
        top: 56px; /* height of navbar */
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

    /* Main content */
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

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">🏠 Student Housing Admin</a>
        <div class="d-flex align-items-center">
            <span class="me-3 text-light">Role: <strong><?= ucfirst($role) ?></strong></span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<!-- ✅ Sidebar -->
<div class="sidebar">
    <a href="../houses/add_house.php"> Add House</a>
    <a href="../houses/houses.php"> Manage Houses</a>
    <a href="../houses/manage_reservations.php">Manage Reservations</a>
    <a href="../users/manage_landlords.php"> Manage Landlords</a>
    <a href="../users/manage_students.php"> Manage Students</a>
</div>

<!-- ✅ Main Content -->
<div class="main-content">


    <!-- Welcome Card -->
    <div class="alert alert-primary text-center shadow-sm">
        <h2>Welcome, Admin!</h2>
        <p>You are logged in to your dashboard.</p>
    </div>

    <!-- Dashboard Cards -->
    <div class="row text-center g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-hover shadow-sm bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Houses</h5>
                    <p class="card-text fs-4"><?= $totalHouses ?></p>
                    <a href="../houses/houses.php" class="btn btn-light btn-sm">Manage Houses</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-hover shadow-sm bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Reservations</h5>
                    <p class="card-text fs-4"><?= $totalReservations ?></p>
                    <a href="../houses/manage_reservations.php" class="btn btn-light btn-sm">Manage Reservations</a>
                    <!--<a href="../houses/manage_reservations.php?status=pending" class="btn btn-light btn-sm ms-2">View Pending</a>-->
                    <!--<a href="../houses/manage_reservations.php?status=approved" class="btn btn-light btn-sm ms-2">View Approved</a>-->
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-hover shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Landlords</h5>
                    <p class="card-text fs-4"><?= $totalLandlords ?></p>
                    <a href="../users/manage_landlords.php" class="btn btn-dark btn-sm">Manage Landlords</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Quick Actions -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Quick Actions
        </div>
        <div class="card-body">
            <p>Use the buttons above to quickly manage houses, reservations, and landlords. Everything you need at a glance!</p>
        </div>
    </div>

</div>

</body>
</html>
