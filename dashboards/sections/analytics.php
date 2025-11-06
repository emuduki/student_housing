<?php
session_start();
include("../../config/db.php");

// Ensure landlord is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.html");
    exit();
}

$landlord_id = $_SESSION['user_id'];

// Example: Fetch analytics stats (you can later replace with real queries)
$totalViews = 2018;
$viewsChange = "+18% from last month";

$totalBookings = 40;
$bookingsChange = "+23% from last month";

$avgRating = 4.8;
$ratingStatus = "Excellent";

$conversionRate = "2.0%";
$conversionChange = "+5% improvement";

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
        .analytics-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .analytics-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.2rem;
        }

        .analytics-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        }

        .analytics-card h6 {
            color: #333;
            margin-bottom: 6px;
            font-size: 0.95rem;
        }

        .analytics-card .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #111;
        }

        .analytics-card .change {
            font-size: 0.85rem;
        }

        .analytics-card .change.green { color: #28a745; }

        .analytics-icon {
            font-size: 2rem;
            opacity: 0.9;
        }

        #propertyChart {
            max-height: 350px;
            width: 100%;
        }
        .chart-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            padding: 24px 30px;
            margin-top: 2rem;
        }

    </style>
    </head>
    <body>

        <div class="analytics-container">
            <h4 class="fw-bold mb-2">Property Analytics</h4>
            <p class="text-muted mb-4">Track performance and insights for your properties</p>

            <div class="analytics-cards">
                <div class="analytics-card">
                    <div>
                        <h6>Total Views</h6>
                        <div class="value"><?= $totalViews ?></div>
                        <div class="change green"><?= $viewsChange ?></div>
                    </div>
                    <i class="bi bi-eye-fill analytics-icon text-primary"></i>
                </div>

                <div class="analytics-card">
                    <div>
                        <h6>Total Bookings</h6>
                        <div class="value"><?= $totalBookings ?></div>
                        <div class="change green"><?= $bookingsChange ?></div>
                    </div>
                    <i class="bi bi-house-fill analytics-icon text-info"></i>
                </div>

                <div class="analytics-card">
                    <div>
                        <h6>Avg Rating</h6>
                        <div class="value"><?= $avgRating ?></div>
                        <div class="change green"><?= $ratingStatus ?></div>
                    </div>
                    <i class="bi bi-star-fill analytics-icon text-warning"></i>
                </div>

                <div class="analytics-card">
                    <div>
                        <h6>Conversion Rate</h6>
                        <div class="value"><?= $conversionRate ?></div>
                        <div class="change green"><?= $conversionChange ?></div>
                    </div>
                    <i class="bi bi-graph-up-arrow analytics-icon text-success"></i>
                </div>
            </div>
            
            <!-- Chart Section -->
            <div class="chart-section mt-4">
                <h5>Property Performance</h5>
                <p class="text-muted">Views and bookings by property</p>
                <p class="text-muted fst-italic">Feature coming soon...</p>
                <canvas id="propertyChart"></canvas>
            </div>

            <!-- Chart Section -->
            <div class="chart-section mt-4">
                <h5>Property Status</h5>
                <p class="text-muted">Distribution of property availability</p>
                <p class="text-muted fst-italic">Feature coming soon...</p>
                <canvas id="propertyChart"></canvas>
            </div>

            <!-- Chart Section -->
            <div class="chart-section mt-4">
                <h5>Revenue Trends</h5>
                <p class="text-muted">Monthly revenue over the past 6 months</p>
                <p class="text-muted fst-italic">Feature coming soon...</p>
                <canvas id="propertyChart"></canvas>
            </div>

            <div class="chart-section mt-4">
                <h5>Top Performing Properties</h5>
                <p class="text-muted">Your most viewed and booked properties</p>

                <?php 
                // Fetch top properties (example data)
                $querry = "
                SELECT id, title, city
                FROM houses
                WHERE landlord_id = ?
                LIMIT 3
                ";

                $stmt = $conn->prepare($querry);


                 // Debugging: check if prepare worked
                if (!$stmt) {
                    die("Query failed: " . $conn->error); // Show the real MySQL error
                }

                
                $stmt->bind_param("i", $landlord_id);
                $stmt->execute();
                $result = $stmt->get_result();


                 // Fake analytics (will replace later with real DB values)
                $fakeStats = [
                    ['views' => 950, 'bookings' => 12, 'rating' => 4.8],
                    ['views' => 720, 'bookings' => 8, 'rating' => 4.5],
                    ['views' => 540, 'bookings' => 5, 'rating' => 4.2],
                ];

                $rank = 1;
                while ($row = $result->fetch_assoc()):
                    $stats = $fakeStats[$rank - 1]; // map fake stats to rows
                ?>
                    <div class="d-flex align-items-center mb-3">
                        <h6 class="mb-0 fw-bold me-3">#<?= $rank ?></h6>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($row['title']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($row['city']) ?></div>
                        </div>
                        <div class="ms-auto">
                            <span class="text-muted me-3"><?= $stats['views'] ?> views</span>
                            <span class="text-muted me-3"><?= $stats['bookings'] ?> bookings</span>
                            <span class="text-warning"><i class="bi bi-star-fill"></i> <?= $stats['rating'] ?></span>
                        </div>
                    </div>
                <?php 
                    $rank++;
                endwhile;
                ?>

            </div>

        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('propertyChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Luxury Loft', '3BR Family', '2BR Apartment', 'Studio Near Uni'],
                datasets: [
                    {
                        label: 'Views',
                        data: [950, 520, 320, 180],
                        backgroundColor: '#007bff'
                    },
                    {
                        label: 'Bookings',
                        data: [12, 7, 4, 2],
                        backgroundColor: '#28a745'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>

    </body>
</html>