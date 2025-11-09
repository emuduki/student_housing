<?php
session_start();
include("../../config/db.php");

//  Role normalization
$role = strtolower(trim($_SESSION["role"] ?? ''));
if ($role !== 'student') {
    header("Location: ../login.html");
    exit();
}
// Get the current student's ID
$student_id = $_SESSION['user_id'];
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
            padding-top: 56px;
        }
        .search-hero {
            background: linear-gradient(135deg, #0a1f44, #0f4c81);
            border-radius: 15px;
            padding: 2rem 1rem;
            margin-bottom: 1.5rem;
        }
        .search-hero h2 {
            font-size: 1.75rem; /* Smaller heading */
            margin-bottom: 0.5rem;
        }

        .search-hero p {
            font-size: 0.95rem; /* Slightly smaller paragraph */
            margin-bottom: 1.5rem;
        }
        .search-bar {
            background: white;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: nowrap;
            align-items: center;
            border-radius: 30px;
            
        }

        .search-bar input::placeholder {
            color: #aaa;
            font-size: 0.9rem;
        }

        .property-card {
            transition: transform 0.2s;
            border-radius: 15px;
            overflow: hidden;
        }

        .property-card:hover {
            transform: translateY(-5px);
        }

        .property-card img {
            object-fit: cover;
            height: 220px;
            width: 100%;
        }

        .property-card .badge {
            font-size: 0.75rem;
            border-radius: 10px;
        }

        .property-card .btn-light:hover i {
            color: #e63946;
        }
    </style>
    </head>
    <body>

    <div class="p-3">
        <div class="search-section">
            <!-- Compact Hero / Search Bar Section -->
            <div class="search-hero text-center text-white rounded-4">
                <h2 class="fw-semibold">Find Your Perfect Student Home</h2>
                <p>Explore hundreds of student-friendly properties near your university</p>

                <div class="search-bar d-flex justify-content-between align-items-center gap-3 mx-auto bg-white rounded-pill px-3 py-2 shadow-sm" style="max-width: 900px;">
                    <!-- Search Input -->
                    <div class="input-group" style="flex: 1;">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                        </span>
                        <input type="text" class="form-control border-0 shadow-none" placeholder="Search by property name or location...">
                    </div>

                    <!-- University Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light rounded-pill dropdown-toggle px-3 py-2" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-location-dot me-1"></i> All Universities
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">USIU</a></li>
                            <li><a class="dropdown-item" href="#">Strathmore</a></li>
                            <li><a class="dropdown-item" href="#">UoN</a></li>
                            <li><a class="dropdown-item" href="#">KU</a></li>
                        </ul>
                    </div>

                    <!-- Filter Button -->
                    <button class="btn btn-outline-light text-dark bg-white border rounded-pill px-3 py-2">
                        <i class="fa-solid fa-sliders me-1"></i> Filters
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Load properties when the section loads
        function loadProperties() {
            const container = document.getElementById('propertiesContainer');
            container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            fetch('../../houses/houses.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    container.innerHTML = ''; // Clear existing content
                    
                    if (data.length === 0) {
                        container.innerHTML = '<div class="col-12"><div class="alert alert-info">No properties available at the moment.</div></div>';
                        return;
                    }
                    
                    data.forEach(property => {
                        const card = `
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card property-card shadow-sm border-0">
                                    <div class="position-relative">
                                        <img src="${property.image_url || '../../uploads/img.avif'}" 
                                            class="card-img-top" alt="${property.title}"
                                            onerror="this.src='../../uploads/img.avif'">
                                        <span class="badge bg-light text-dark position-absolute top-0 start-0 m-2">
                                            ${property.type || 'Apartment'}
                                        </span>
                                        <button class="btn btn-light position-absolute top-0 end-0 m-2 rounded-circle shadow-sm favorite-btn">
                                            <i class="fa-regular fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="fw-bold">${property.title}</h6>
                                        <p class="text-muted mb-2">
                                            <i class="fa-solid fa-location-dot me-1"></i> 
                                            ${property.location}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">KES ${property.price}/mo</h6>
                                                <small class="text-muted">Available Now</small>
                                            </div>
                                            <a href="../../houses/view_houses.php?id=${property.id}" 
                                            class="btn btn-dark btn-sm rounded-pill">
                                                <i class="fa-regular fa-eye me-1"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.innerHTML += card;
                    });

                    // Initialize favorite buttons
                    document.querySelectorAll('.favorite-btn').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const icon = this.querySelector('i');
                            icon.classList.toggle('fa-regular');
                            icon.classList.toggle('fa-solid');
                            icon.classList.toggle('text-danger');
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading properties:', error);
                    container.innerHTML = 
                        '<div class="col-12"><div class="alert alert-danger">Error loading properties. Please try again later.</div></div>';
                });
        }

        // Load properties on section load
        loadProperties();
                                            <div>
                                                <span class="text-primary fw-bold fs-6">KES 35,000</span><br>
                                                <small class="text-muted">per month</small>
                                            </div>
                                            <button class="btn btn-dark btn-sm rounded-pill">
                                                <i class="fa-regular fa-eye me-1"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    </script>
    
<body>