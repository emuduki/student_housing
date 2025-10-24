<?php
session_start();
include("../config/db.php");

$role = strtolower(trim($_SESSION['role'] ?? ''));
if (!in_array($role, ['landlord', 'admin'])) {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($role === 'admin') {
    $result = $conn->query("SELECT h.*, u.username AS landlord_name FROM houses h JOIN users u ON h.landlord_id=u.id ORDER BY h.id DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM houses WHERE landlord_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Houses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 50px;
        }
        .card-img-top { height: 200px; object-fit: cover; }
        .card-hover:hover { transform: translateY(-5px); transition: 0.3s; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">

    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
  <div class="container-fluid justify-content-center">
    <a class="navbar-brand fw-bold text-center m-0" href="#" style="font-size: 1.25rem;">
      Manage Houses
    </a>
  </div>
</nav>



    <a href="add_house.php" class="btn btn-success mb-4">➕ Add House</a>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while($house = $result->fetch_assoc()): ?>
                <?php
                $imgVal = $house['images'] ?? '';
                $imgSrc = (!empty($imgVal) && file_exists('../' . $imgVal)) ? '../' . $imgVal : 'https://via.placeholder.com/300x200?text=No+Image';
                ?>
                <div class="col-md-4">
                <div class="property-card shadow-sm h-100">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="property-img" alt="House Image">
                    <div class="property-info d-flex flex-column h-100">
                        <h5><?= htmlspecialchars($house['title']) ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($house['location']) ?></p>
                        <span class="price mb-2">Ksh<?= htmlspecialchars($house['rent']) ?> / month</span>
                        <p class="mb-3"><?= htmlspecialchars($house['description']) ?></p>
                        <?php if($role === 'admin'): ?>
                            <p class="text-muted"><em>Listed by: <?= htmlspecialchars($house['landlord_name']) ?></em></p>
                        <?php endif; ?>
                        <div class="mt-auto">
                            <a href="edit_house.php?id=<?= (int)$house['id'] ?>" class="btn btn-warning btn-sm">✏ Edit</a>
                            <a href="delete_house.php?id=<?= (int)$house['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this house?')">🗑 Delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <strong>No houses found.</strong>
            <?php if($role === 'landlord') echo " You haven't added any houses yet."; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
