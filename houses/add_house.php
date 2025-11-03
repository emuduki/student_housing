<?php
session_start();
include("../config/db.php");

// Enforce login for adding a house
if (!isset($_SESSION['user_id']) || !in_array(strtolower(trim($_SESSION['role'] ?? '')), ['landlord', 'admin'])) {
    header("Location: ../login.html");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $landlord_id = $_SESSION['user_id']; // Now we are sure this is a valid ID

    $title       = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? ($_POST['detailed_description'] ?? '');
    $address     = $_POST['address'] ?? '';
    $city        = $_POST['city'] ?? '';
    $state       = $_POST['state'] ?? '';
    $zip         = $_POST['zip'] ?? '';
    $area        = $_POST['area'] ?? '';
    $status      = $_POST['status'] ?? 'available';
    $type        = $_POST['type'] ?? '';
    $location    = trim(implode(', ', array_filter([$address, $city, $state, $zip])));

    $rentRaw = $_POST['rent'] ?? '';
    $rent    = is_numeric($rentRaw) ? (float)$rentRaw : 0;

    // Placeholder cover image (will be set after gallery upload)
    $imagePath = '';

  // Prepare the SQL insert safely
    $stmt = $conn->prepare("
        INSERT INTO houses 
        (title, description, location, rent, images, landlord_id, status, type, area, address, city, state, zip, detailed_description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("SQL Prepare Failed: " . $conn->error);
    }

    $stmt->bind_param(
    "sssdississssss",
    $title,
    $description,
    $location,
    $rent,
    $imagePath,
    $landlord_id,
    $status,
    $type,
    $area,
    $address,
    $city,
    $state,
    $zip,
    $description
);


    if (!$stmt->execute()) {
        echo "<div class='alert alert-danger text-center mt-3'>Error: " . htmlspecialchars($stmt->error) . "</div>";
    } else {
        $houseId = $stmt->insert_id;

        // Handle image uploads if any
        if (!empty($_FILES['gallery']['name'][0])) {
            $uploadDir = "../uploads/houses/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadedImages = [];

            foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {
                $fileName = time() . "_" . basename($_FILES['gallery']['name'][$key]);
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($tmp_name, $targetFile)) {
                    $uploadedImages[] = "uploads/houses/" . $fileName;
                }
            }

            if (!empty($uploadedImages)) {
                // Update main image in the house table
                $mainImage = $uploadedImages[0];
                $updateStmt = $conn->prepare("UPDATE houses SET images = ? WHERE id = ?");
                $updateStmt->bind_param("si", $mainImage, $houseId);
                $updateStmt->execute();

                // Ensure house_images table exists, then persist all image paths
                $conn->query(
                    "CREATE TABLE IF NOT EXISTS house_images (\n" .
                    "  id INT AUTO_INCREMENT PRIMARY KEY,\n" .
                    "  house_id INT NOT NULL,\n" .
                    "  image_path VARCHAR(255) NOT NULL,\n" .
                    "  sort_order INT DEFAULT 0,\n" .
                    "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n" .
                    "  INDEX (house_id),\n" .
                    "  CONSTRAINT fk_house_images_house FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE\n" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
                );

                $imgStmt = $conn->prepare("INSERT INTO house_images (house_id, image_path, sort_order) VALUES (?, ?, ?)");
                $order = 0;
                foreach ($uploadedImages as $imgPath) {
                    $imgStmt->bind_param("isi", $houseId, $imgPath, $order);
                    $imgStmt->execute();
                    $order++;
                }
                $imgStmt->close();
            }
        }

        echo "<div class='alert alert-success text-center mt-3'>✅ House added successfully!</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
          :root { --nav-h: 70px; }

          body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: var(--nav-h);
            overflow-x: hidden;
          }

          .navbar {
            transition: all 0.3s ease;
            z-index: 2000;
          }

          .nav-link { color: #333 !important; margin: 0 10px; }
          .nav-link:hover { color: #007bff !important; }

          .add-property-btn {
            background-color: #dc3545;
            color: #fff;
            padding: 8px 14px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
          }
          .add-property-btn:hover { background-color: #c82333; color: #fff; }

          /* Hero Section */
          .hero-section {
            position: relative;
            height: 300px;
            width: 100%;
            margin: 0;
            margin-top: calc(var(--nav-h) * -1);
            padding-top: var(--nav-h);
            background: url('../uploads/pexels-einfoto-2179603.jpg') center/cover no-repeat;
            overflow: hidden;
          }
          .hero-section .overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.35);
          }
          .hero-section .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
            color: #fff;
            text-align: left;
            padding-left: 3rem;
          }
          .hero-section .hero-content h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
          }
          .hero-section .hero-content p {
            font-size: 1.1rem;
            margin: 0;
          }

          /* --- Basic Information --- */
          .basic-head {
            font-weight: 700;
            font-size: 1.3rem;
            color: #333;
            margin-top: 1rem;
            border-left: 4px;
            padding-left: 10px;
          }

          .basic-info .form-label {
            font-weight: 600;
            color: #333;
          }

          .basic-info .form-control,
          .basic-info .form-select {
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #ccc;
            transition: border-color 0.3s ease;
          }

          .basic-info .form-control:focus,
          .basic-info .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.25);
          }
          .location-info .form-label {
        font-weight: 600;
        color: #333;
      }

          .location-info .form-control {
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #ccc;
            transition: border-color 0.3s ease;
          }

          .location-info .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.25);
          }

          .property-details .form-label {
            font-weight: 600;
            color: #333;
          }

          .property-details .form-select {
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #ccc;
            transition: border-color 0.3s ease;
          }

          .property-details .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.25);
          }

          .features-columns {
            column-count: 3; /* Creates 3 neat vertical columns */
            column-gap: 40px; /* space between columns */
          }

          .features-columns .form-check {
            break-inside: avoid; /* keeps checkboxes from splitting between columns */
            margin-bottom: 8px;
          }
          /* Bigger gallery drop box */
          .gallery-box {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            height: 350px; /* Increased height */
            background-color: #f8f9fa;
            position: relative;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
          }

          .cloud-icon {
            width: 48px;
            height: 48px;
            color: #6c757d;
            margin-bottom: 8px;
          }

          .hint-text { color: #6c757d; font-size: 0.95rem; }

          /* Centered inner content */
          .gallery-content {
            width: 100%;
            text-align: center;
          }

          /* Logo box inside gallery */
          .logo-box {
            border: 1px dashed #aaa;
            border-radius: 6px;
            padding: 15px;
            width: 220px;
            background-color: #fff;
            transition: background 0.3s ease;
          }

          .logo-box:hover {
            background: #f0f0f0;
          }

          /* Logo image preview */
          .logo-preview {
            max-height: 100px;
            display: none;
            margin-top: 10px;
            border-radius: 5px;
          }

          /* Gallery preview images */
          #galleryPreview img {
            width: 120px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
          }


          /* Specific Modal Styling for the Sign In Pop-up */
            .login-modal-image {
                /* Make sure the image on the left covers the space */
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: var(--bs-modal-border-radius);
            }
            .custom-login-modal .modal-dialog {
                max-width: 750px;   /* Increased from 700px */
                margin-top: 90px;   /* Keeps below navbar */
                }
            .custom-login-modal .modal-content {
                max-height: 75vh;   /* Slightly shorter so it doesn’t climb too high */
                }
                            /* Styling the login box from the image */
            .login-box-content {
                padding: 1.5rem !important;
            }
            /* Custom styling for the specific red login button */
            .login-box-content .btn-custom-red {
                border-radius: 0.375rem !important; /* Match standard form control rounded corners */
                padding: 0.75rem 1rem;
                font-size: 1.1rem;
                font-weight: 600;
                width: 100%; /* Make it full width like in the image */
            }
            /* Hiding the default close button for the custom layout */
            .login-box-content .btn-close {
                display: none;
            }
            /* Styling for the Register/Sign In text link area */
            .register-link-area {
                margin-top: 1rem;
                text-align: right;
            }
            .custom-login-modal .modal-dialog {
            max-width: 700px;
            margin-top: 80px;
            }
            .custom-login-modal .modal-content {
            max-height: 80vh; /* prevent it from covering the whole screen */
            overflow-y: auto;
            border-radius: 12px; /* smoother edges */
            }

            .d-flex.align-items-center.justify-content-between.mt-3 > * {
                flex: 1;
                text-align: center;
                }
            /* Grey box for the Register section under Login button */
            .register-box {
            background-color: #f1f1f1;
            padding: 8px 16px;
            margin-top: 15px;
            margin-right: 10px;
            border-radius: 6px;
            text-align: center;
            flex: 1;  
            }
            .register-box a {
                color: #333;
                font-weight: 600;
                text-decoration: none;
                }
            .register-box a:hover {
            color: #007bff;
            }
            .lost-password {
            font-weight: 700;       /* ← makes it bold */
            text-decoration: none;
            color: #6c757d; 
            flex: 1; 
            text-align: center;       
            }

            .lost-password:hover {
            text-decoration: underline;
            color: #101010;         /* blue on hover */
            }
            .d-flex.align-items-center.justify-content-between.mt-3 {
            gap: 10px;            
            }
            .gallery-box {
              margin-top: 16px;
              border: 2px dashed #ccc;
              border-radius: 8px;
              background-color: #f9f9f9;
              padding: 16px;
              position: relative;
              height: 160px;
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: center;
              text-align: center;
              cursor: pointer;
              transition: border-color 0.2s ease-in-out;
            }

            .gallery-box.dragover {
              border-color: #007bff;
              background-color: #eef7ff;
            }

            #galleryPreview {
              display: flex;
              flex-wrap: wrap;
              justify-content: center;
              gap: 10px;
              margin-top: 10px;
            }
            .cloud-icon { width: 32px; height: 32px; margin-bottom: 4px; }
            .gallery-content .fw-semibold { font-size: 0.95rem; }
            .hint-text { font-size: 0.85rem; 
            }
            #galleryPreview img {
              width: 100px;
              height: 80px;
              object-fit: cover;
              border-radius: 5px;
              border: 1px solid #ddd;
              box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            }


  </style>
</head>

<body class="bg-light">

  <div class="modal fade custom-login-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-12 col-md-5 d-none d-md-block">
                            <img src="../uploads/pexels-huy-phan-316220-2826787.jpg" alt="Welcome" class="login-modal-image">
                        </div>

                        <div class="col-12 col-md-7 login-box-content">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

                            <form action="../auth/login.php" method="POST">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person me-2" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.284 10 8 10c-2.28 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                                    <label class="form-label mb-0">Email</label>
                                    </div>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock me-2" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm-1 0a1 1 0 0 0-1 1v4h4V2a1 1 0 0 0-1-1H7zM.5 9a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1H.5zm0 2a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1H.5z"/></svg>
                                    <label class="form-label mb-0">Password</label>
                                    </div>
                                    <input type="password" name="password" class="form-control" required>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">
                                            Remember Me
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-custom-red">Login</button>
                                
                            <div class="d-flex align-items-center justify-content-between mt-3">
                            <div class="register-box mb-0">
                                <a href="register.html">Register</a>
                            </div>
                            <a href="#" class="text-muted small lost-password" onclick="event.preventDefault();">Lost your Password?</a>
                            </div>


                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="../index.html">
      <span>HousingPortal</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active fw-semibold" href="../index.html">Home</a></li>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
            <a class="nav-link fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                 Login
                     </a>
                </li>
        <li class="nav-item ms-2"><a class="btn add-property-btn" href="add_house.php">Add Property</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO SECTION -->
<section class="hero-section">
  <div class="overlay"></div>
  <div class="hero-content">
    <div>
      <p>Submit Property</p>
      <h1>Submit Your Property</h1>
    </div>
  </div>
</section>

<!-- FORM -->
<div class="container mt-4 mb-5">
  <form method="POST" enctype="multipart/form-data">

    <h5 class="basic-head">Basic Information</h5>
    <div class="row g-3 basic-info">
      <div class="col-12">
        <label class="form-label">Property Title</label>
        <input type="text" name="title" class="form-control full-width" placeholder="Enter property title" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="">Select Status</option>
          <option value="For Rent">For Rent</option>
          <option value="For Sale">For Sale</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Property Type</label>
        <select name="type" class="form-select" required>
          <option value="">Select Type</option>
          <option>Apartment</option>
          <option>House</option>
          <option>Villa</option>
          <option>Studio</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Price (Ksh)</label>
        <input type="number" step="0.01" name="rent" class="form-control" placeholder="0.00" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Area (sq ft)</label>
        <input name="area" class="form-control" placeholder="e.g. 1200">
      </div>
    </div>

   <!-- 🏠 Image Upload Box -->
  <div id="galleryDrop" class="gallery-box">
  <div class="gallery-content">
    <svg class="cloud-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M6 19a4 4 0 0 1-.2-8A5.5 5.5 0 0 1 17 9.5c0 .17 0 .34-.02.5H17a4.5 4.5 0 1 1 0 9H6zm6-9.5a.75.75 0 0 1 .75.75V15l1.72-1.72a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3A.75.75 0 0 1 9.53 13.3L11.25 15V10.25A.75.75 0 0 1 12 9.5z"/>
    </svg>
    <div class="fw-semibold">Drop images here or click to browse</div>
    <div class="hint-text">You can also paste images (Ctrl+V) or drag & drop</div>
    <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple class="d-none">
    <input type="hidden" name="primary_index" id="primaryIndex" value="0">
  </div>
  </div>
  <div id="galleryPreview"></div>


<!-- Location -->
<h5 class="mt-4 basic-head">Location</h5>
<div class="row g-3 location-info">
  <div class="col-md-8">
    <label class="form-label">Address</label>
    <input type="text" name="address" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">City</label>
    <input name="city" class="form-control">
  </div>
  <div class="col-md-6">
    <label class="form-label">State</label>
    <input name="state" class="form-control">
  </div>
  <div class="col-md-6">
    <label class="form-label">Zip Code</label>
    <input name="zip" class="form-control">
  </div>
</div>
<!-- Detailed Information -->
<h5 class="mt-4">Detailed Information</h5>
<div class="mb-3">
  <label class="form-label">Description</label>
  <textarea name="description" class="form-control" rows="4"></textarea>
</div>

<div class="row g-3 mb-3 property-details">
  <div class="col-md-4">
    <label class="form-label">Rooms</label>
    <select name="rooms" class="form-select">
      <option value="">Choose Rooms</option>
      <option value="bedsitter">Bed Sitter</option>
      <option value="1">1</option>
      <option value="2">2</option>
      <option value="3">3</option>
      <option value="4">4</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">Building Age (optional)</label>
    <select name="building_age" class="form-select">
      <option value="">Select</option>
      <option value="0-5">0–5 years</option>
      <option value="5-10">5–10 years</option>
      <option value="10-20">10–20 years</option>
      <option value="20+">20+ years</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">Garage (optional)</label>
    <select name="garage" class="form-select">
      <option value="">Choose</option>
      <option value="0">None</option>
      <option value="1">1</option>
      <option value="2">2</option>
    </select>
  </div>
</div>

<!-- Feature checkboxes -->
<div class="mb-3">
  <label class="form-label">Other Features (optional)</label>
  <div class="features-columns">
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="air_condition" id="f1"><label class="form-check-label" for="f1">Air Conditioning</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="wifi" id="f2"><label class="form-check-label" for="f2">Wi‑Fi</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="bedding" id="f3"><label class="form-check-label" for="f3">Bedding</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="heating" id="f4"><label class="form-check-label" for="f4">Heating</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="internet" id="f5"><label class="form-check-label" for="f5">Internet</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="parking" id="f6"><label class="form-check-label" for="f6">Parking</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="balcony" id="f7"><label class="form-check-label" for="f7">Balcony</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="pets" id="f8"><label class="form-check-label" for="f8">Pets Allowed</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="features[]" value="pool" id="f9"><label class="form-check-label" for="f9">Swimming Pool</label></div>
  </div>
</div>

<!-- Contact Information -->
<h5 class="mt-4">Contact Information</h5>
<div class="row g-3 mb-3">
  <div class="col-md-4"><label class="form-label">Name</label><input name="contact_name" class="form-control"></div>
  <div class="col-md-4"><label class="form-label">Email</label><input name="contact_email" type="email" class="form-control"></div>
  <div class="col-md-4"><label class="form-label">Phone (optional)</label><input name="contact_phone" type="tel" class="form-control"></div>
</div>

    <button type="submit" class="btn btn-danger mt-4">Submit & Preview</button>

  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const galleryDrop = document.getElementById('galleryDrop');
  const galleryInput = document.getElementById('galleryInput');
  const galleryPreview = document.getElementById('galleryPreview');
  const primaryIndexInput = document.getElementById('primaryIndex');
  let filesArr = [];

  function rebuildInputFromArray() {
    const dt = new DataTransfer();
    filesArr.forEach(f => dt.items.add(f));
    galleryInput.files = dt.files;
  }

  function renderPreview(){
    galleryPreview.innerHTML = '';
    filesArr.forEach((f, idx) => {
      if (!f.type.startsWith('image/')) return;
      const wrap = document.createElement('div');
      wrap.style.display = 'inline-flex';
      wrap.style.flexDirection = 'column';
      wrap.style.alignItems = 'center';
      wrap.style.margin = '6px';

      const img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.style.width = '120px';
      img.style.height = '100px';
      img.style.objectFit = 'cover';
      img.className = 'rounded border';
      wrap.appendChild(img);

      const controls = document.createElement('div');
      controls.className = 'mt-1';

      const primaryBtn = document.createElement('button');
      primaryBtn.type = 'button';
      const isPrimary = idx === Number(primaryIndexInput.value);
      primaryBtn.className = 'btn btn-sm ' + (isPrimary ? 'btn-success' : 'btn-outline-success');
      primaryBtn.textContent = isPrimary ? 'Primary' : 'Make Primary';
      primaryBtn.onclick = () => { primaryIndexInput.value = String(idx); renderPreview(); };
      controls.appendChild(primaryBtn);

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn btn-sm btn-outline-danger ms-2';
      removeBtn.textContent = 'Remove';
      removeBtn.onclick = () => {
        filesArr.splice(idx, 1);
        let p = Number(primaryIndexInput.value);
        if (idx === p) p = 0; else if (idx < p) p = Math.max(0, p - 1);
        primaryIndexInput.value = String(p);
        rebuildInputFromArray();
        renderPreview();
      };
      controls.appendChild(removeBtn);

      wrap.appendChild(controls);
      galleryPreview.appendChild(wrap);
    });
  }

  // Click to upload
  galleryDrop.addEventListener('click', ()=> galleryInput.click());

  // Drag & drop behavior
  ['dragenter','dragover'].forEach(e=> 
    galleryDrop.addEventListener(e, ev=>{
      ev.preventDefault();
      galleryDrop.classList.add('dragover');
    })
  );
  ['dragleave','drop'].forEach(e=> 
    galleryDrop.addEventListener(e, ev=>{
      ev.preventDefault();
      galleryDrop.classList.remove('dragover');
    })
  );

  // Drop event
  galleryDrop.addEventListener('drop', ev=>{
    const files = ev.dataTransfer.files;
    filesArr = Array.from(files);
    primaryIndexInput.value = '0';
    rebuildInputFromArray();
    renderPreview();
  });

  // When user selects via dialog
  galleryInput.addEventListener('change', e=>{
    filesArr = Array.from(e.target.files);
    primaryIndexInput.value = '0';
    renderPreview();
  });
})();
</script>
</body>
</html>
