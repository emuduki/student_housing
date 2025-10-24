<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "landlord" && $_SESSION["role"] !== "admin")) {
    header("Location: ../login.html");
    exit();
}

$landlord_id = $_SESSION["user_id"];
if ($_SESSION["role"] === "admin") {
    $result = $conn->query("SELECT * FROM houses");
} else {
    $stmt = $conn->prepare("SELECT * FROM houses WHERE landlord_id = ?");
    $stmt->bind_param("i", $landlord_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Houses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-3">Your Houses</h3>
    <a href="add_house.php" class="btn btn-success mb-3">+ Add New House</a>
    <table class="table table-bordered table-striped">
        <tr>
            <th>Title</th>
            <th>Location</th>
            <th>Rent</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row["title"] ?></td>
            <td><?= $row["location"] ?></td>
            <td>$<?= $row["rent"] ?></td>
            <td>
                <?php if ($row["images"]) { ?>
                    <img src="../<?= htmlspecialchars($row["images"]) ?>" width="100">
                <?php } ?>
            </td>
            <td>
                <a href="edit_house.php?id=<?= $row["id"] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete_house.php?id=<?= $row["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this house?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
