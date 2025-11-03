<?php
session_start();
include("../../config/db.php");

// ✅ Role normalization
$role = strtolower(trim($_SESSION["role"] ?? ''));
if ($role !== 'landlord') {
    header("Location: ../auth/login.html");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Total properties
$properties_query = $conn->query("SELECT COUNT(*) AS total FROM houses WHERE landlord_id = $landlord_id");
$total_properties = $properties_query->fetch_assoc()['total'];

// Total reservations
$reservations_query = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE landlord_id = $landlord_id");
$total_reservations = $reservations_query->fetch_assoc()['total'];

// Total inquiries
$inquiries_query = $conn->query("SELECT COUNT(*) AS total FROM inquiries WHERE landlord_id = $landlord_id");
$total_inquiries = $inquiries_query->fetch_assoc()['total'];

// Estimated monthly income (example: sum of rent from current month)
$income_query = $conn->query("
    SELECT SUM(amount) AS total FROM payments 
    WHERE landlord_id = $landlord_id 
    AND MONTH(payment_date) = MONTH(CURRENT_DATE())
");
$total_income = $income_query->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
    </head>

    <body class="p-4">

        <h4 class="fw-bold mb-4">Dashboard Overview</h4>

        <!--Top Stat Cards-->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <span>Total Houses</span>
                        <i class="bi bi-building icon-box"></i>
                    </div>
                    <h4><?php echo $total_houses; ?></h4>
                    <small class="trend-up"><?= $available_houses; ?> available</small>
                </div>
            </div>
        
            <div class="col-md-6 col-lg-3">
                <div class="start-card">
                    <div class="d-flex justify-content-between">
                        <span>Reserved</span>
                        <i class="bi bi-house-door-open icon-box"></i>
                    </div>
                    <h4 class="fw-bold mt-2"><?=$reserved ?></h4>
                    <small class="trend-down"><?= $pending_reservations; ?> pending</small>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                <span>Total Views</span>
                <i class="bi bi-bar-chart icon-box"></i>
                </div>
                <h4 class="fw-bold mt-2"><?= $total_views ?></h4>
                <small class="trend-up">+<?= $view_growth ?>% this month</small>
            </div>
            </div>

            <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                <span>Revenue</span>
                <i class="bi bi-cash-coin icon-box"></i>
                </div>
                <h4 class="fw-bold mt-2">$<?= number_format($revenue) ?></h4>
                <small class="trend-up">+<?= $revenue_growth ?>%</small>
            </div>
            </div>
        </div>

        <!--Quick Actions-->
        <h6 class="fw-bold mb-3">Quick Actions</h6>
        <div class="quick-actions">
            <div class="action-card" onclick="window.location='../houses/add_house.php'">
                <i class="bi bi-plus-square action-icon text-primary"></i>
                <p class="fw-semibold mb-0">Add New House</p>
                <small>List a new property</small>
            </div>
            
            <div class="action-card" onclick="window.location='../houses/analytics.php'">
            <i class="bi bi-file-earmark-bar-graph action-icon text-purple"></i>
            <p class="fw-semibold mb-0">View Reports</p>
            <small>Generate analytics</small>
            </div>

            <div class="action-card" onclick="window.location='../houses/inquiries.php'">
            <i class="bi bi-chat-dots-fill action-icon text-success"></i>
            <p class="fw-semibold mb-0">Messages</p>
            <small>Check inquiries</small>
            </div>

            <div class="action-card" onclick="window.location='../houses/manage_reservations.php'">
            <i class="bi bi-calendar-check action-icon text-warning"></i>
            <p class="fw-semibold mb-0">Schedule</p>
            <small>Manage bookings</small>
            </div>
        </div>
        
    </body>
</html>