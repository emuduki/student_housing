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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body { min-height: 100vh; display: flex; flex-direction: column; }

    .navbar{
        z-index: 1100;
    }

    /* Sidebar */
    .sidebar {
        min-width: 260px;
        background-color: #ffffff; /* darker shade than navbar */
        padding-top: 20px 0;
        height: calc(100vh - 56px); /* full height minus navbar */
        position: fixed;
        top: 56px; /* same height as navbar */
        left: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        border-right: 1px solid #dee2e6;
        box-shadow: 2px 0 6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
        /* When collapsed */
    .sidebar.collapsed {
        margin-left: -280px;
    }
        .sidebar .brand {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        color: #12be82;
        margin-bottom: 10px;
        }
        .sidebar ul {
        list-style: none;
        padding: 0;
        margin-top: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
        }
        .sidebar ul li {
        margin: 2px 10px;
        }
        .sidebar ul li a {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        text-decoration: none;
        color: #212529;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.25s ease;
        position: relative;
        }

        .sidebar ul li a:hover {
        background-color: #f2f2f2;
        color: #12be82;
        }
    
        .sidebar ul li a.active {
        background-color: #000;
        color: #fff !important;
        font-weight: 600;
        }
        .sidebar .badge {
        font-size: 0.75rem;
        border-radius: 10px;
        padding: 3px 7px;
        position: absolute;
        right: 12px;
        }
        .add-btn-container {
        margin-top: auto;
        padding: 20px;
        }
        .add-btn {
        background-color: #000;
        color: #fff;
        padding: 12px 20px;
        border-radius: 10px;
        text-align: center;
        display: block;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s;
        }
        .add-btn:hover {
        background-color: #12be82;
        color: #fff;
        }

        /*main content */
        .main-content {
            margin-left: 200px;
            padding: 90px 25px 25px;
        }

        .profile-section h4 {
            color: #495057;
        }

        .profile-section p {
            margin-bottom: 10px;
        }

        /* Main Content */
        .main-content {
        margin-left: 320px;
        padding: 90px 40px 40px;
        background-color: #ffffff; /* solid white background */
        border-radius: 12px;
        border: 1px solid #dee2e6;
        box-shadow: 0 4 20px rgba(0,0,0,0.15);
        min-height: 100vh; /* ensure it covers the full height */
    }




</style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container-fluid px-4">
            <!--Sidebar Toogle Button-->
            <button class="btn btn-outline-secondary me-3" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <!-- Brand Logo -->
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="index.html">
                <img src="#" alt="Logo" height="35" class="me-2" onerror="this.style.display='none'">
                <span class="d-none d-sm-inline">HousingPortal</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" aria-current="page" href="../index.html">Home</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn add-property-btn" href="../houses/add_house.php">Add Property</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>



<!-- Sidebar -->
<div class="sidebar">
    
    <ul class="nav flex-column mt-4">
        <li><a href="javascript:void(0);" class="active" onclick="loadSection('overview')"><i class="bi bi-house-door me-2"></i>Overview</a></li>
        <li><a href="../houses/houses.php"><i class="bi bi-building me-2"></i>My Properties</a></li>
        <li><a href="../houses/manage_reservations.php"><i class="bi bi-calendar me-2"></i>Reservations</a></li>

        <li><a href="../houses/inquiries.php"><i class="bi bi-envelope me-2"></i>Inquiries</a></li>
        <li><a href="../houses/analytics.php"><i class="bi bi-bar-chart me-2"></i>Analytics</a></li>
        <li><a href="../houses/income_reports.php"><i class="bi bi-cash-coin me-2"></i>Income Reports</a></li>
        <li><a href="../houses/availability.php"><i class="bi bi-calendar2-week me-2"></i>Availability</a></li>

        <!-- Divider line-->
        <hr class="my-3 mx-3">

        <li><a href="../users/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
    </ul>

    <div class="add-btn-container mt-auto">
        <a href="../houses/add_house.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add New House
        </a>
    </div>

</div>

<!-- Main Content -->
 <div class="content" id="content">
    <!--Section content will be loaded here-->
 </div>


<script>
  document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector(".sidebar");
    const toggleButton = document.getElementById("sidebarToggle");

    // Sidebar toggle
    toggleButton.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
    });

    // Load section dynamically
    function loadSection(section, el = null) {
        fetch(`sections/${section}.php`)
            .then(res => res.text())
            .then(html => {
                document.getElementById("content").innerHTML = html;

                // Update active link
                document.querySelectorAll(".sidebar a").forEach(a => a.classList.remove("active"));
                if (el) el.classList.add("active");
            })
            .catch(err => console.error("Error loading section:", err));
    }

    // Attach click events to sidebar links
    document.querySelectorAll(".sidebar a[onclick]").forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            const section = this.getAttribute("onclick").match(/'(.+)'/)[1];
            loadSection(section, this);
        });
    });

    // Load overview by default
    loadSection("overview");
});
</script>


</body>
</html>
