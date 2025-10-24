<?php
session_start();
include("../config/db.php");

$role = strtolower(trim($_SESSION['role'] ?? ''));
if ($role !== 'student') {
    header("Location: ../login.html");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch houses with landlord name
$result = $conn->query("
    SELECT h.*, u.username AS landlord_name 
    FROM houses h 
    JOIN users u ON h.landlord_id = u.id
    ORDER BY h.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Houses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            background-attachment: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
        }
        
        .navbar {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            font-weight: 600;
            color: #0d6efd;
        }

        .card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .card-title {
            font-weight: 600;
            color: #333;
        }

        .card-text {
            color: #555;
            font-size: 0.95rem;
        }

        .btn-primary {
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        .empty-state {
            background: #e9f3ff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

    </style>
</head>
<body class="bg-light">

<div class="container mt-4">

    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
    <div class="container-fluid d-flex justify-content-center">
        <a class="navbar-brand fw-bold" href="#">Available Houses</a>
        <div class="d-flex align-items-center">
        </div>
    </div>
</nav>

<div style="margin-top: 70px;"></div>


<div class="container py-5">
    <div class="text-center mb-5">
        <p class="text-muted">Browse through our list of available houses and reserve your preferred one.</p>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while($house = $result->fetch_assoc()): ?>
                <?php
                $imgVal = $house['images'] ?? '';
                $imgSrc = (!empty($imgVal) && file_exists('../' . $imgVal)) 
                    ? '../' . $imgVal 
                    : 'https://via.placeholder.com/400x250?text=No+Image+Available';
                ?>
                <div class="col-md-4 col-sm-6">
                <div class="property-card shadow-sm h-100">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="property-img" alt="House Image">
                    <div class="property-info d-flex flex-column h-100">
                        <h5><?= htmlspecialchars($house['title']) ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($house['location']) ?></p>
                        <span class="price mb-2">Ksh<?= htmlspecialchars($house['rent']) ?> / month</span>
                        <p class="mb-3"><?= htmlspecialchars($house['description']) ?></p>
                        <p class="text-muted mb-3"><em>Listed by: <?= htmlspecialchars($house['landlord_name']) ?></em></p>
                        <a href="reserve.php?house_id=<?= (int)$house['id'] ?>" class="btn btn-primary mt-auto w-100">📌 Reserve</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state text-center">
            <h4 class="text-primary fw-bold">No Houses Available</h4>
            <p class="text-muted mb-0">Please check back later for new listings from landlords.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
