<!DOCTYPE html>
<html lang="en">
    <head>  
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Housing Portal</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <a class="nav-link fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Sign In</a>

        <style>
            body {
                background-color: #f8f9fa;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                padding-top: 70px;
            }
            .navbar-brand img {
            vertical-align: middle;
            }

            .navbar {
            transition: all 0.3s ease;
            }

            .nav-link {
            color: #333 !important;
            margin: 0 10px;
            }

            .nav-link:hover {
            color: #007bff !important;
            }

            .btn-danger {
            border-radius: 20px;
            }

            /* Add Property button style */
            .add-property-btn {
                background-color: #dc3545; /* Bootstrap danger */
                color: #fff;
                padding: 8px 14px;
                border-radius: 20px;
                text-decoration: none;
                display: inline-block;
                font-weight: 600;
            }
            .add-property-btn:hover, .add-property-btn:focus {
                background-color: #c82333;
                color: #fff;
                text-decoration: none;
            }

            .hero-section {
            height: 90vh;
            background: linear-gradient(to bottom right, #ffffff, #eaf0f8);
            padding: 50px 20px;
            }
            
        </style>

    </head>
    <body>
        <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
        <div class="modal-body p-0">
            <div class="row g-0">
            <!-- Optional image side -->
            <div class="col-md-5 d-none d-md-block">
                <img src="assets/images/login-side.jpg" alt="Login" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="col-md-7 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="auth/login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="register.html" class="btn btn-outline-secondary">Register</a>
                </div>

                <div class="text-center mt-2">
                    <a href="#" class="small text-muted" onclick="event.preventDefault();">Lost your password?</a>
                </div>
                </form>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
            <div class="container-fluid px-4">
                <!-- Brand + left links -->
                <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="index.html">
                    <img src="#" alt="Logo" height="35" class="me-2" onerror="this.style.display='none'">
                    <span class="d-none d-sm-inline">HousingPortal</span>
                </a>

                <!-- Toggler for mobile -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Left-aligned nav (Home) -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active fw-semibold" aria-current="page" href="index.html">Home</a>
                        </li>
                    </ul>

                    <!-- Right-aligned actions -->
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="login.html">Sign In</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn add-property-btn" href="houses/add_house.php">Add Property</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>