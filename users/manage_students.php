<?php
session_start();
include("../config/db.php");

// Only admin can access
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch all users with role = student
$result = $conn->query("SELECT * FROM users WHERE role='student' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-hover:hover { transform: translateY(-5px); transition: 0.3s; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">

      <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
    <div class="container-fluid d-flex justify-content-center">
        <a class="navbar-brand fw-bold text-center m-0" href="#" style="font-size: 1.25rem;">
             Manage Students
        </a>
    </div>
</nav>


    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while($student = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card card-hover shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($student['username']) ?></h5>
                            <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
                            <p class="card-text"><strong>Registered At:</strong> <?= htmlspecialchars($student['created_at'] ?? 'N/A') ?></p>
                            <div class="mt-auto">
                                <a href="edit_user.php?id=<?= (int)$student['id'] ?>" class="btn btn-warning btn-sm">✏ Edit</a>
                                <a href="delete_user.php?id=<?= (int)$student['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?')">🗑 Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <strong>No students found.</strong> Add some students to manage.
        </div>
    <?php endif; ?>

</div>

</body>
</html>
