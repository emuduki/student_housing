//still no change......<?php
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
<style>
    body { min-height: 100vh; display: flex; flex-direction: column; }

    .navbar{
        z-index: 1100;
    }

    /* Sidebar */
    .sidebar {
        min-width: 280px;
        background-color: #ffffff; /* darker shade than navbar */
        color:  #212529;
        padding-top: 70px;
        position: fixed;
        top: 0; /* same height as navbar */
        bottom: 0;
        overflow-y: auto;
        overflow-y: auto;
        border-right: 1px solid #dee2e6;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
    }
    .sidebar a {
        color: #212529;
        text-decoration: none;
        display: block;
        padding: 10px 18px;
        margin: 4px 0;
        font-weight: 500;
        border-radius: 4px;
        transition: all 0.2s ease-in-out;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background-color: #f1f3f5;
        color: #12be82ff;
    }
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


    .social-links a {
        display: block;
        color: #495057;
        text-decoration: none;
    }
    form label {
        color: #333;
     }

    form .form-control {
        border-radius: 10px;
        box-shadow: none;
        border-color: #ced4da;
    }

    form .form-control:focus {
        border-color: #12be82ff;
        box-shadow: 0 0 0 0.2rem rgba(18, 190, 130, 0.25);
    }
    .password-box {
        position: fixed;
        background-color: #ffffff;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        padding: 35px 80px; 
        width: 100%;
        max-width: 1000px;
}
.custom-input {
    height: 55px;              /* taller input boxes */
    font-size: 1.1rem;        /* slightly larger text */
    padding-left: 18px;
    width: 100%;
    border-radius: 10px;
}
    .btn-success {
  font-size: 1.05rem;
  padding: 10px 25px;
  border-radius: 10px;
}



</style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container-fluid px-4">
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
    <a href="#" class="active" onclick="loadSection('profile')">My Profile</a>
    <a href="#" onclick="loadSection('saved_properties')">Saved Property</a>
    <a href="../houses/houses.php">Manage Houses</a>
    <a href="#" onclick="loadSection('messages')">Messages</a>
    <a href="../houses/add_house.php">Add House</a>
    <a href="#" onclick="loadSection('change_password')">Change Password</a>
    <a href="../houses/manage_reservations.php">Manage Reservations</a>
</div>

<!-- Main Content -->
 <div class="main-content" id="main-content">

    <!-- Default: Profile Section -->
     <div class="container">
        <h2 class="fw-bold mb-4">My Account</h2>

        <form class="p-4 bg-white shadow-sm rounded">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" class="form-control" id="name" value="<?= htmlspecialchars($landlord['name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($landlord['email'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">Your Title</label>
                    <input type="text" class="form-control" id="title" value="<?= htmlspecialchars($landlord['title'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone number</label>
                    <input type="text" class="form-control" id="phone" value="<?= htmlspecialchars($landlord['phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" value="<?= htmlspecialchars($landlord['address'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" value="<?= htmlspecialchars($landlord['city'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="zip" class="form-label">State</label>
                    <input type="text" class="form-control" id="state" value="<?= htmlspecialchars($landlord['state'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label for="zip" class="form-label">Zip Code</label>
                    <input type="text" class="form-control" id="zip" value="<?= htmlspecialchars($landlord['zip'] ?? ''); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">About</label>
                <textarea class="form-control" id="about" rows="3"><?= htmlspecialchars($landlord['about'] ?? ''); ?></textarea>
            </div>

            <!-- Social Accounts -->
            <h4 class="fw-bold mt-4 mb-3">Social Accounts</h4>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Facebook</label>
                    <input type="text" class="form-control" id="facebook" value="<?= htmlspecialchars($landlord['facebook'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Twitter</label>
                    <input type="text" class="form-control" id="twitter" value="<?= htmlspecialchars($landlord['twitter'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Google Plus</label>
                    <input type="text" class="form-control" id="google_plus" value="<?= htmlspecialchars($landlord['google_plus'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">LinkedIn</label>
                    <input type="text" class="form-control" id="linkedin" value="<?= htmlspecialchars($landlord['linkedin'] ?? ''); ?>">
                </div>
            </div>

            <div class="text-center">
                <button class="btn btn-primary">Save Changes</button>
            </div>
        </form>
     </div>
     <!-- Change Password -->
     
 </div>


<script>
function loadSection(section) {
    const content = document.getElementById('main-content');
    const links = document.querySelectorAll('.sidebar a');
    links.forEach(link => link.classList.remove('active'));
    event.target.classList.add('active');

    if (section === 'profile') {
        content.innerHTML = `
            <h2 class="fw-bold mb-4">My Account</h2>
            <p>Reloading profile...</p>
        `;
        location.reload(); // reload to show the profile form again
    } 
    else if (section === 'change_password') {
    content.innerHTML = `
        <div class="password-box">
            <h4 class=" mb-4 fw-bold">Change Your Password</h4>
            <form method="POST" action="change_password.php">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="oldPassword" class="form-label">Old Password</label>
                        <input type="password" class="form-control" id="oldPassword" name="old_password" placeholder="Enter your old password" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Re-enter new password" required>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-success px-5">Update Password</button>
                </div>
            </form>
        </div>
    `;
}
    else if (section === 'messages') {
        content.innerHTML = '<h2>Messages</h2><p>No messages yet.</p>';
    } 
    else if (section === 'saved_properties') {
        content.innerHTML = '<h2>Saved Properties</h2><p>Coming soon...</p>';
    }
}
</script>


</body>
</html>
