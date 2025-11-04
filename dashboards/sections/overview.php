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

// Total properties (houses)
$properties_query = $conn->query("SELECT COUNT(*) AS total FROM houses WHERE landlord_id = $landlord_id");
$total_properties = $properties_query ? $properties_query->fetch_assoc()['total'] : 0;
$total_houses = $total_properties; // Alias for display

// Available houses (not reserved/approved)
$available_query = $conn->query("
    SELECT COUNT(*) AS total FROM houses h 
    WHERE h.landlord_id = $landlord_id 
    AND h.id NOT IN (
        SELECT house_id FROM reservations WHERE status = 'approved'
    )
");
$available_houses = $available_query ? $available_query->fetch_assoc()['total'] : 0;

// Total reservations (join through houses)
$reservations_query = $conn->query("
    SELECT COUNT(*) AS total 
    FROM reservations r 
    JOIN houses h ON r.house_id = h.id 
    WHERE h.landlord_id = $landlord_id
");
$total_reservations = $reservations_query ? $reservations_query->fetch_assoc()['total'] : 0;
$reserved = $total_reservations; // Alias for display

// Pending reservations
$pending_query = $conn->query("
    SELECT COUNT(*) AS total 
    FROM reservations r 
    JOIN houses h ON r.house_id = h.id 
    WHERE h.landlord_id = $landlord_id AND r.status = 'pending'
");
$pending_reservations = $pending_query ? $pending_query->fetch_assoc()['total'] : 0;

// Total inquiries (if table exists)
$inquiries_query = @$conn->query("SELECT COUNT(*) AS total FROM inquiries WHERE landlord_id = $landlord_id");
$total_inquiries = ($inquiries_query && $inquiries_query !== false) ? $inquiries_query->fetch_assoc()['total'] : 0;

// Estimated monthly income (if payments table exists)
$income_query = @$conn->query("
    SELECT SUM(amount) AS total FROM payments 
    WHERE landlord_id = $landlord_id 
    AND MONTH(payment_date) = MONTH(CURRENT_DATE())
");
$total_income = ($income_query && $income_query !== false) ? ($income_query->fetch_assoc()['total'] ?? 0) : 0;
$revenue = $total_income; // Alias for display

// Placeholder values for features not yet implemented
$total_views = 0;
$view_growth = 0;
$revenue_growth = 0;
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
            background-color: #f5f5f5;
        }
        .stat-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 140px;
            width: 100%;
            transition: all 0.3s ease;
        }
        .start-card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 140px;
            width: 100%;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .stat-card > div:first-child {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .stat-card > div:first-child > span {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }
        .stat-card h4 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin: 0 0 8px 0;
        }
        .start-card h4 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin: 0 0 8px 0;
        }
        .icon-box {
            font-size: 2.5rem;
            color: #0d6efd;
            opacity: 0.9;
        }
        .trend-up { 
            color: #28a745; 
            font-size: 0.85rem; 
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .trend-up::before {
            content: "↑";
            font-size: 0.9rem;
        }
        .trend-down { 
            color: #dc3545; 
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .trend-down::before {
            content: "~";
            font-size: 0.9rem;
        }
        .quick-actions-wrapper {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            padding: 20px 24px;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            padding: 0;
        }
        .action-card {
            border-radius: 10px;
            text-align: center;
            padding: 18px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .action-card:nth-child(1) {
            background: #1e3a8a;
        }
        .action-card:nth-child(2) {
            background: #6f42c1;
        }
        .action-card:nth-child(3) {
            background: #28a745;
        }
        .action-card:nth-child(4) {
            background: #fd7e14;
        }
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        .action-icon {
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: #fff !important;
        }
        .action-card p {
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .action-card small {
            color: rgba(255,255,255,0.9);
            font-size: 0.75rem;
        }
       
        .start-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .start-card > div:first-child {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .start-card > div:first-child > span {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }
    </style>
    </head>

    <body class="p-4">

        <h4 class="fw-bold mb-4">Dashboard Overview</h4>

        <!--Top Stat Cards-->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stat-card mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Total Houses</span>
                        <i class="bi bi-building icon-box"></i>
                    </div>
                    <h4><?php echo $total_houses; ?></h4>
                    <small class="trend-up"><?= $available_houses; ?> available</small>
                </div>
                <div class="start-card">
                    <div class="d-flex justify-content-between">
                        <span>Reserved</span>
                        <i class="bi bi-people icon-box"></i>
                    </div>
                    <h4><?=$reserved ?></h4>
                    <small class="trend-down"><?= $pending_reservations; ?> pending</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="stat-card mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Total Views</span>
                        <i class="bi bi-bar-chart icon-box"></i>
                    </div>
                    <h4><?= $total_views ?></h4>
                    <small class="trend-up">+<?= $view_growth ?>% this month</small>
                </div>
                <div class="stat-card">
                    <div class="d-flex justify-content-between">
                        <span>Revenue</span>
                        <i class="bi bi-cash-coin icon-box"></i>
                    </div>
                    <h4>$<?= number_format($revenue) ?></h4>
                    <small class="trend-up">+<?= $revenue_growth ?>%</small>
                </div>
            </div>
        </div>

        <!--Quick Actions-->
        <h6 class="fw-bold mb-3">Quick Actions</h6>
        <div class="quick-actions-wrapper">
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
        </div>
        
    </body>
</html>