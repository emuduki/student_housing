<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['landlord', 'admin'])) {
    header("Location: ../login.html");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Allow optional ?status=pending|approved|rejected|cancelled|all
$allowed = ['pending','approved','rejected','cancelled','all'];
$status = isset($_GET['status']) && in_array($_GET['status'], $allowed) ? $_GET['status'] : 'all';

if ($role == 'landlord') {
    $sql = "SELECT r.id, u.username AS student_name, h.title, h.location, h.rent, r.status, r.reserved_at
            FROM reservations r
            JOIN houses h ON r.house_id = h.id
            JOIN users u ON r.student_id = u.id
            WHERE h.landlord_id = ?";

    if ($status !== 'all') {
        $sql .= " AND r.status = ?";
    }
    $sql .= " ORDER BY r.reserved_at DESC";

    $stmt = $conn->prepare($sql);
    if ($status !== 'all') {
        $stmt->bind_param('is', $user_id, $status);
    } else {
        $stmt->bind_param('i', $user_id);
    }

} else {
    $sql = "SELECT r.id, u.username AS student_name, h.title, h.location, h.rent, r.status, r.reserved_at
            FROM reservations r
            JOIN houses h ON r.house_id = h.id
            JOIN users u ON r.student_id = u.id";

    if ($status !== 'all') {
        $sql .= " WHERE r.status = ?";
    }
    $sql .= " ORDER BY r.reserved_at DESC";

    $stmt = $conn->prepare($sql);
    if ($status !== 'all') {
        $stmt->bind_param('s', $status);
    }
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #0d6efd;
            color: white;
        }

        .badge {
            font-size: 0.9rem;
        }

        .btn-action {
            margin-right: 5px;
        }
    </style>

</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
    <div class="container-fluid d-flex justify-content-center">
        <a class="navbar-brand fw-bold text-center m-0" href="#" style="font-size: 1.25rem;">
             Manage Reservations
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-4">
    

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered align-middle text-center shadow-sm">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>House</th>
                    <th>Location</th>
                    <th>Rent</th>
                    <th>Status</th>
                    <th>Reserved At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td>$<?= htmlspecialchars($row['rent']) ?></td>
                    <td>
                        <?php 
                            if ($row['status'] == 'pending') echo "<span class='badge bg-warning text-dark'>Pending</span>";
                            if ($row['status'] == 'approved') echo "<span class='badge bg-success'>Approved</span>";
                            if ($row['status'] == 'rejected') echo "<span class='badge bg-danger'>Rejected</span>";
                        ?>
                    </td>
                    <td><?= $row['reserved_at'] ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending') { 
                            $returnParam = urlencode($status);
                        ?>
                            <a href="update_reservation.php?id=<?= $row['id'] ?>&status=approved&return_status=<?= $returnParam ?>" 
                               class="btn btn-success btn-sm btn-action"> Approve</a>
                            <a href="update_reservation.php?id=<?= $row['id'] ?>&status=rejected&return_status=<?= $returnParam ?>" 
                               class="btn btn-danger btn-sm btn-action">Reject</a>
                        <?php } else { ?>
                            <span class="text-muted">No actions</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info text-center">
            No reservations found.
        </div>
    <?php endif; ?>
</div>

</body>
</html>
