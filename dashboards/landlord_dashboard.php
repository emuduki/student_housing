<?php
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body { min-height: 100vh; display: flex; flex-direction: column; }

    .navbar{
        z-index: 1100;
    }

    /* Sidebar */
    .sidebar {
        min-width: 260px;
        background-color: #ffffff; /* darker shade than navbar */
        padding-top: 20px 0;
        height: calc(100vh - 56px); /* full height minus navbar */
        position: fixed;
        z-index: 1000;
        top: 56px; /* same height as navbar */
        left: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        border-right: 1px solid #dee2e6;
        box-shadow: 2px 0 6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
        /* When collapsed */
    .sidebar.collapsed {
        margin-left: -280px;
    }
        .sidebar .brand {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        color: #12be82;
        margin-bottom: 10px;
        }
        .sidebar ul {
        list-style: none;
        padding: 0;
        margin-top: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
        }
        .sidebar ul li {
        margin: 2px 10px;
        }
        .sidebar ul li a {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        text-decoration: none;
        color: #212529;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.25s ease;
        position: relative;
        }

        .sidebar ul li a:hover {
        background-color: #f2f2f2;
        color: #12be82;
        }
    
        .sidebar ul li a.active {
        background-color: #000;
        color: #fff !important;
        font-weight: 600;
        }
        .sidebar .badge {
        font-size: 0.75rem;
        border-radius: 10px;
        padding: 3px 7px;
        position: absolute;
        right: 12px;
        }
        .add-btn-container {
        margin-top: auto;
        padding: 20px;
        }
        .add-btn {
        background-color: #000;
        color: #fff;
        padding: 12px 20px;
        border-radius: 10px;
        text-align: center;
        display: block;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s;
        }
        .add-btn:hover {
        background-color: #12be82;
        color: #fff;
        }

        /*main content */
        .main-content {
            padding: 20px 40px; /* space on top/bottom and left/right */
            background-color: #f8f9fa; /* optional: light background for contrast */
            min-height: 100vh;
            box-sizing: border-box; /* ensures padding is included in width */
            transition: all 0.3s ease;
        }
        .upload-box {
            border: 2px dashed rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 32px;
            background: rgba(0,0,0,0.03);
            cursor: pointer;
            transition: .2s;
        }

        .upload-box:hover {
            border-color: rgba(0,0,0,0.4);
            background: rgba(0,0,0,0.05);
        }

        .drag-area input {
            cursor: pointer;
        }
        
        #addPropertyModal .modal-dialog {
            max-width: 50%; /* Adjust as needed (e.g. 50%, 700px, etc.) */
            margin: auto; /* Center the modal */
        }
       


</style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container-fluid px-4">
            <!--Sidebar Toogle Button-->
            <button class="btn btn-outline-secondary me-3" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <!-- Brand Logo -->
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
    
    <ul class="nav flex-column mt-4">
        <li><a href="javascript:void(0);" class="active" onclick="loadSection('overview')"><i class="bi bi-house-door me-2"></i>Overview</a></li>
        <li><a href="../houses/houses.php"><i class="bi bi-building me-2"></i>My Properties</a></li>
    <li><a href="javascript:void(0);" onclick="loadSection('manage_reservations')"><i class="bi bi-calendar me-2"></i>Reservations</a></li>

        <li><a href="javascript:void(0);" onclick="loadSection('inquiries')"><i class="bi bi-envelope me-2"></i>Inquiries</a></li>
        <li><a href="javascript:void(0);" onclick="loadSection('analytics')"><i class="bi bi-bar-chart me-2"></i>Analytics</a></li>
        <li><a href="javascript:void(0);" onclick="loadSection('income_reports')"><i class="bi bi-cash-coin me-2"></i>Income Reports</a></li>
        <li><a href="javascript:void(0);" onclick="loadSection('availability')"><i class="bi bi-calendar2-week me-2"></i>Availability</a></li>

        <!-- Divider line-->
        <hr class="my-3 mx-3">

        <li><a href="javascript:void(0);" onclick="loadSection('profile')"><i class="bi bi-person me-2"></i>Profile</a></li>
    </ul>

    <div class="add-btn-container mt-auto">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
            <i class="bi bi-plus-lg me-1"></i>Add New House
        </a>
    </div>

</div>

<!-- Main Content -->
<div class="main-content" id="content">
    <!--Section content will be loaded here-->
</div>

<div class="modal fade" id="addPropertyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px;">
            
            <!-- Modal Header -->
            <div class="modal-header border-0">
                <h4 class=" fw-bold mb-0">Post New Housing Listing</h4 >
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
             <div class="modal-body">

                <p class="text-muted">Add a new housing property.</p>

                <!--Image Upload-->
                <div class="upload-box mb-4">
                    <div class="drag-area text-center">
                        <i class="bi bi-cloud-arrow-up fs-1"></i>
                        <p>Drag & drop your images here, or click to select files</p>
                        <small class="text-muted">Up to 5 images (JPG, PNG, GIF)</small>
                        <input type="file" id="imageUploadInput" multiple accept="image/*" class="form-control mt-3">
                    </div>
                </div>

                <!--Form fields-->
                <form id="propertyForm">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label class="fw-semibold">Property Title*</label>
                            <input type="text" class="form-control" placeholder="e.g., Student Room Near University" required>
                        </div>

                        <div class="col-md-12">
                            <label class="fw-semibold">Address*</label>
                            <input type="text" class="form-control" placeholder="Street Address" required>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">City*</label>
                            <input type="text" class="form-control" placeholder="City" required>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Monthly Rent (KES)*</label>
                            <input type="number" class="form-control" placeholder="e.g., 5,000" required>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Property Type*</label>
                            <select class="form-select" required>
                                <option value="" disabled selected>Select type</option>
                                <option>Apartment</option>
                                <option>House</option>
                                <option>Studio</option>
                                <option>Shared Room</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Bedrooms</label>
                            <input type="number" class="form-control" placeholder="e.g., 2" required>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Area (sq ft)*</label>
                            <input type="number" class="form-control" placeholder="950">
                        </div>

                        <div class="col-md-6">
                            <label class="fw-semibold">Status *</label>
                            <select class="form-select" required>
                                <option value="Available">Available</option>
                                <option value="Reserved">Reserved</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="fw-semibold">Description</label>
                            <textarea class="form-control" rows="3" placeholder="Describe your property (ideal for students, distance to campus, etc.)"></textarea>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="fw-semibold d-block">Amenities</label>
                             <div class="row">
                                    <div class="col-6 col-md-4"><input type="checkbox"> WiFi</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Kitchen</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Study Desk</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Water Included</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Public Transport</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Parking</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Furnished</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Shared Kitchen</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Electricity Included</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Quiet Area</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> AC</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Washer/Dryer</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Security</div>
                                    <div class="col-6 col-md-4"><input type="checkbox"> Close to Campus</div>
                                </div>   
                            </div>
                    </div>

                </form>

             </div>
              <!-- Modal Footer -->
            <div class="modal-footer border-0">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-dark px-4">Post Listing</button>
            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector(".sidebar");
    const toggleButton = document.getElementById("sidebarToggle");

    // Sidebar toggle
    toggleButton.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
    });

    // Initialize interactive behavior for injected sections (payment toggles, etc.)
    function initializeInjectedContent() {
        // Payment form toggles (existing behavior)
        const select = document.getElementById('paymentMethodSelect');
        if (select) {
            const bankFields = document.getElementById('bankFields');
            const mpesaFields = document.getElementById('mpesaFields');
            const paypalFields = document.getElementById('paypalFields');
            const cashFields = document.getElementById('cashFields');

            function updatePaymentFields() {
                const v = select.value;
                if (bankFields) bankFields.classList.toggle('d-none', v !== 'Bank Transfer');
                if (mpesaFields) mpesaFields.classList.toggle('d-none', v !== 'M-Pesa');
                if (paypalFields) paypalFields.classList.toggle('d-none', v !== 'PayPal');
                if (cashFields) cashFields.classList.toggle('d-none', v !== 'Cash');

                if (bankFields) document.querySelectorAll('#bankFields input').forEach(i => i.required = (v === 'Bank Transfer'));
                if (mpesaFields) document.querySelectorAll('#mpesaFields input').forEach(i => i.required = (v === 'M-Pesa'));
                if (paypalFields) document.querySelectorAll('#paypalFields input').forEach(i => i.required = (v === 'PayPal'));
            }

            select.addEventListener('change', updatePaymentFields);
            updatePaymentFields();
        }

        // If the manage_reservations section is injected, wire up its tabs and action forms
        const tabPill = document.querySelector('.tab-pill');
        if (tabPill) {
            // Attach tab click handlers
            document.querySelectorAll('.tab-pill .nav-link').forEach(tab => {
                // Avoid double-binding by removing previous listeners using a namespaced handler stored on element
                if (tab.__manageReservationsBound) return;
                tab.__manageReservationsBound = true;

                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const status = this.dataset.status;

                    // Update active state
                    document.querySelectorAll('.tab-pill .nav-link').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Show loading state
                    const tableSection = document.querySelector('.card.card-table');
                    if (tableSection) tableSection.style.opacity = '0.5';

                    // Fetch partial HTML for the selected status
                    fetch(`sections/manage_reservations.php?status=${status}&partial=1`)
                        .then(res => res.text())
                        .then(html => {
                            if (tableSection && html) {
                                tableSection.outerHTML = html;
                                // After replacing content, re-run this initializer to bind new elements
                                initializeInjectedContent();
                            }
                        })
                        .catch(err => {
                            console.error('Error fetching reservations:', err);
                            if (tableSection) tableSection.style.opacity = '1';
                        });
                });
            });

            // Attach action-form submit handlers (approve/reject)
            function bindActionForms() {
                document.querySelectorAll('.action-form').forEach(form => {
                    if (form.__manageReservationsFormBound) return;
                    form.__manageReservationsFormBound = true;

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(form);
                        const btn = form.querySelector('button');
                        const originalText = btn ? btn.innerHTML : '';
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                        }

                        fetch('sections/manage_reservations.php', { method: 'POST', body: formData })
                            .then(res => res.text())
                            .then(() => {
                                // First refresh full section to update counts
                                return fetch('sections/manage_reservations.php');
                            })
                            .then(res => res.text())
                            .then(html => {
                                // Replace entire section content to update counts
                                document.getElementById("content").innerHTML = html;
                                // Re-bind handlers for newly injected content
                                initializeInjectedContent();
                            })
                            .then(() => {
                                // Then refresh just the table for current status
                                const activeTab = document.querySelector('.tab-pill .nav-link.active');
                                const status = activeTab ? activeTab.dataset.status : 'pending';
                                return fetch(`sections/manage_reservations.php?status=${status}&partial=1`);
                            })
                            .then(res => res.text())
                            .then(html => {
                                // Update just the table portion
                                const tableSection = document.querySelector('.card.card-table');
                                if (tableSection && html) {
                                    tableSection.outerHTML = html;
                                    // Re-bind handlers for new content
                                    initializeInjectedContent();
                                }
                            })
                            .catch(err => {
                                console.error('Error processing action:', err);
                                if (btn) {
                                    btn.disabled = false;
                                    btn.innerHTML = originalText;
                                }
                                alert('Error processing request. Please try again.');
                            });
                    });
                });
            }

            bindActionForms();
        }
    }

    // Load section dynamically
    function loadSection(section, el = null) {
        fetch(`sections/${section}.php`)
            .then(res => res.text())
            .then(html => {
                document.getElementById("content").innerHTML = html; // This line is correct

                // Initialize any scripts/behaviors for the injected HTML
                try { initializeInjectedContent(); } catch (e) { console.error('init error', e); }

                // Update active link
                document.querySelectorAll(".sidebar a").forEach(a => a.classList.remove("active"));
                if (el) el.classList.add("active");
            })
            .catch(err => console.error("Error loading section:", err));
    }

    // Attach click events to sidebar links
    document.querySelectorAll(".sidebar a[onclick]").forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault();
            const section = this.getAttribute("onclick").match(/'(.+)'/)[1];
            loadSection(section, this);
        });
    });

    // Load overview by default
    loadSection("overview");
});
</script>


</body>
</html>
