<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.html");
    exit;
}

$student_id = $_SESSION['user_id'];

// Optional status filter: ?status=pending or ?status=approved
$allowedStatuses = ['pending', 'approved', 'rejected', 'cancelled'];
$status = isset($_GET['status']) && in_array($_GET['status'], $allowedStatuses) ? $_GET['status'] : null;

if ($status) {
    $stmt = $conn->prepare("SELECT r.id, h.title, h.location, h.rent, r.status, r.reserved_at 
                        FROM reservations r 
                        JOIN houses h ON r.house_id = h.id 
                        WHERE r.student_id = ? AND r.status = ? ORDER BY r.reserved_at DESC");
    $stmt->bind_param("is", $student_id, $status);
} else {
    $stmt = $conn->prepare("SELECT r.id, h.title, h.location, h.rent, r.status, r.reserved_at 
                        FROM reservations r 
                        JOIN houses h ON r.house_id = h.id 
                        WHERE r.student_id = ? ORDER BY r.reserved_at DESC");
    $stmt->bind_param("i", $student_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 40px;
            background-color: #f8f9fa;
        }
        .table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            outline: 2px solid #00000022;
            box-shadow: 0 0 8px rgba(0,0,0,0.2);
        }
        .table thead th {
            background-color: #0d6efd;
            color: white;
        }
        .btn.active {
            outline: 2px solid #00000022;
            box-shadow: 0 0 8px rgba(0,0,0,0.2);
            font-weight: bold;
        }
    </style>
</head>
<body class="container mt-5">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
    <div class="container-fluid d-flex justify-content-center">
        <a class="navbar-brand fw-bold text-center m-0" href="#" style="font-size: 1.25rem;">
             My Reservations
        </a>
    </div>
</nav>

<!-- Table -->
<div class="container mt-4">
    <table class="table table-bordered align-middle text-center shadow-sm">
        <thead>
            <tr>
                <th>House</th>
                <th>Location</th>
                <th>Rent</th>
                <th>Status</th>
                <th>Reserved At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td>$<?= htmlspecialchars($row['rent']) ?></td>
                        <td>
                            <?php 
                                if ($row['status'] == 'pending') echo "<span class='badge bg-warning'>Pending</span>";
                                elseif ($row['status'] == 'approved') echo "<span class='badge bg-success'>Approved</span>";
                                elseif ($row['status'] == 'rejected') echo "<span class='badge bg-danger'>Rejected</span>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['reserved_at']) ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <form method="POST" action="cancel_reservation.php" style="display:inline;">
                                    <input type="hidden" name="reservation_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No reservations found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
