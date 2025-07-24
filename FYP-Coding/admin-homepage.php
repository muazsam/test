<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="homepage-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php
        session_start();
        include("connect.php");
        if (isset($_SESSION['blEmail'])) {
            $sql = "SELECT * FROM barber WHERE barber_email='" . $_SESSION['blEmail'] . "'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
        }

        // Total Bookings
        $sqltotalbookings = "SELECT COUNT(*) AS total_bookings
                     FROM booking 
                     INNER JOIN payment ON booking.booking_id = payment.booking_id
                     WHERE payment.payment_status = 'accepted'";
        $result = $conn->query($sqltotalbookings);
        if ($result && $row = $result->fetch_assoc()) {
            $totalbookings = $row['total_bookings'];
        } else {
            $totalbookings = 0; 
        }

        // Total Sales
        $sqltotalsales = "SELECT SUM(payment_amount) AS total_sales
                            FROM payment
                            WHERE payment_status = 'accepted'";
        $result = $conn->query($sqltotalsales);
        if ($result && $row = $result->fetch_assoc()) {
            $totalsales = $row['total_sales'];
        } else {
            $totalsales = 0; 
        }

        // Pending Payment
        $sqlpendingpayment = "SELECT COUNT(*) AS total_pending_payment
                                FROM booking
                                WHERE booking_status = ''";
        $result = $conn->query($sqlpendingpayment);
        if ($result && $row = $result->fetch_assoc()) {
            $pendingpayment = $row['total_pending_payment'];
        } else {
            $pendingpayment = 0; 
        }

        // Total Customer Accounts
        $sqltotalcustomers = "SELECT COUNT(*) AS total_customers
                                FROM customer";
        $result = $conn->query($sqltotalcustomers);
        if ($result && $row = $result->fetch_assoc()) {
            $totalcustomers = $row['total_customers'];
        } else {
            $totalcustomers = 0; 
        }

        // Pending Approval
        $sqlpendingapproval = "SELECT COUNT(*) AS total_pending_approval
                                FROM payment
                                WHERE payment_status = 'Process'";
        $result = $conn->query($sqlpendingapproval);
        if ($result && $row = $result->fetch_assoc()) {
            $pendingapproval = $row['total_pending_approval'];
        } else {
            $pendingapproval = 0; 
        }

        // Sales Trends
        $timeFrame = isset($_GET['timeFrame']) ? $_GET['timeFrame'] : 'monthly';
        $sqlSalesTrends = "";
        if ($timeFrame === 'daily') {
            $sqlSalesTrends = "SELECT DATE(payment_date) AS date, SUM(payment_amount) AS sales
                               FROM payment
                               WHERE payment_status = 'accepted'
                               GROUP BY DATE(payment_date)
                               ORDER BY DATE(payment_date)";
        } elseif ($timeFrame === 'weekly') {
            $sqlSalesTrends = "SELECT YEAR(payment_date) AS year, WEEK(payment_date) AS week, SUM(payment_amount) AS sales
                               FROM payment
                               WHERE payment_status = 'accepted'
                               GROUP BY YEAR(payment_date), WEEK(payment_date)
                               ORDER BY YEAR(payment_date), WEEK(payment_date)";
        } elseif ($timeFrame === 'yearly') {
            $sqlSalesTrends = "SELECT YEAR(payment_date) AS year, SUM(payment_amount) AS sales
                               FROM payment
                               WHERE payment_status = 'accepted'
                               GROUP BY YEAR(payment_date)
                               ORDER BY YEAR(payment_date)";
        } else {
            $sqlSalesTrends = "SELECT MONTH(payment_date) AS month, SUM(payment_amount) AS sales
                               FROM payment
                               WHERE payment_status = 'accepted'
                               GROUP BY MONTH(payment_date)
                               ORDER BY MONTH(payment_date)";
        }

        $result = $conn->query($sqlSalesTrends);
        $labels = [];
        $sales = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if ($timeFrame === 'daily') {
                    $labels[] = $row['date'];
                } elseif ($timeFrame === 'weekly') {
                    $labels[] = 'Week ' . $row['week'] . ' of ' . $row['year'];
                } elseif ($timeFrame === 'yearly') {
                    $labels[] = $row['year'];
                } else {
                    $labels[] = date('F', mktime(0, 0, 0, $row['month'], 10));
                }
                $sales[] = $row['sales'];
            }
        }

        // Most Selected Services
        $serviceTimeFrame = isset($_GET['serviceTimeFrame']) ? $_GET['serviceTimeFrame'] : 'monthly';
        $selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
        $startDate = null;
        $endDate = null;

        if ($serviceTimeFrame === 'daily') {
            // Get services by day
            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            $startDate = $date;
            $endDate = $date;
        } elseif ($serviceTimeFrame === 'weekly') {
            // Get services by week
            $week = isset($_GET['week']) ? $_GET['week'] : date('W');
            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
            $startDate = date('Y-m-d', strtotime($year . 'W' . $week));
            $endDate = date('Y-m-d', strtotime($startDate . ' +6 days'));
        } elseif ($serviceTimeFrame === 'yearly') {
            // Get services by year
            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
            $startDate = $year . '-01-01';
            $endDate = $year . '-12-31';
        } else {
            // Default to monthly
            $startDate = date('Y-' . $selectedMonth . '-01');
            $endDate = date('Y-' . $selectedMonth . '-t');
        }

        $sqlServices = "SELECT s.service_name, COUNT(bs.service_id) AS count
                        FROM booking_service bs
                        JOIN service s ON bs.service_id = s.service_id
                        JOIN booking b ON bs.booking_id = b.booking_id
                        JOIN payment p ON b.booking_id = p.booking_id
                        WHERE b.booking_date BETWEEN ? AND ? AND payment_status = 'accepted'
                        GROUP BY bs.service_id
                        ORDER BY count DESC";
        $stmt = $conn->prepare($sqlServices);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $resultServices = $stmt->get_result();

        $serviceNames = [];
        $serviceCounts = [];
        if ($resultServices) {
            while ($row = $resultServices->fetch_assoc()) {
                $serviceNames[] = $row['service_name'];
                $serviceCounts[] = $row['count'];
            }
        }

        // Today's Report
        $today = date('Y-m-d');
        $sqlTodayReport = "SELECT s.service_name, COUNT(bs.service_id) AS count, SUM(p.payment_amount) AS total_sales, COUNT(b.booking_id) AS total_bookings
                           FROM booking_service bs
                           JOIN service s ON bs.service_id = s.service_id
                           JOIN booking b ON bs.booking_id = b.booking_id
                           JOIN payment p ON b.booking_id = p.booking_id
                           WHERE DATE(b.booking_date) = ? AND p.payment_status = 'accepted'
                           GROUP BY bs.service_id
                           ORDER BY count DESC
                           LIMIT 1";
        $stmt = $conn->prepare($sqlTodayReport);
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $resultTodayReport = $stmt->get_result();

        $todayReport = $resultTodayReport->fetch_assoc();

        $conn->close();
    ?>
    <header id="header">
        <div class="sidebar">
            <nav class="nav-bar">
                <a href="admin-homepage.php" class="logo"><span>MS Barber</span></a>
                <ul>
                    <li><a href="admin-createbarber.php" class="<?php echo ($currentPage == 'admin-createbarber.php') ? 'active' : ''; ?>">Create Staff Account</a></li>
                    <li><a href="admin-deleteaccount.php" class="<?php echo ($currentPage == 'admin-deleteaccount.php') ? 'active' : ''; ?>">Delete Account</a></li>
                    <li><a href="admin-payapproval.php" class="<?php echo ($currentPage == 'admin-payapproval.php') ? 'active' : ''; ?>">Payment Approval</a></li>
                    <li><a href="admin-pendingpayment.php" class="<?php echo ($currentPage == 'admin-pendingpayment.php') ? 'active' : ''; ?>">Pending Payments</a></li>
                    <li><a href="admin-updateavailable.php" class="<?php echo ($currentPage == 'admin-updateavailable.php') ? 'active' : ''; ?>">Barber Vacant</a></li>
                    <li><a href="admin-barberstatus.php" class="<?php echo ($currentPage == 'admin-barberstatus.php') ? 'active' : ''; ?>">Barber Status</a></li>
                    <li><a href="admin-editservice.php" class="<?php echo ($currentPage == 'admin-editservice.php') ? 'active' : ''; ?>">Edit Services</a></li>
                    <li><a href="admin-reviewcust.php" class="<?php echo ($currentPage == 'admin-reviewcust.php') ? 'active' : ''; ?>">Customer Reviews</a></li>
                    <li><a href="admin-barbermonitor.php" class="<?php echo ($currentPage == 'admin-barbermonitor.php') ? 'active' : ''; ?>">Booking Status</a></li>
                    <li><a href="user-logout.php">Log Out</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <h1>Administrator Dashboard</h1>
        <div class="dashboard">
            <div class="card">
                <h2>Sales Summary</h2>
                <p><?php echo "RM " . number_format((float)$totalsales, 2, '.', ','); ?></p>
            </div>
            <div class="card">
                <h2>Total Bookings</h2>
                <p><?php echo $totalbookings?></p>
            </div>
            <div class="card">
                <h2>Total Customers</h2>
                <p><?php echo $totalcustomers?></p>
            </div>
            <div class="card">
                <h2>Pending Payments</h2>
                <p><?php echo $pendingpayment?></p>
            </div>
            <div class="card">
                <h2>Pending Approval</h2>
                <p><?php echo $pendingapproval?></p>
            </div>
        </div>

        <div class="graph-section">
            <div class="graph-container">
                <h2>Sales Trends</h2>
                <select id="timeFrameSelect">
                    <option value="daily" <?php echo $timeFrame === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo $timeFrame === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo $timeFrame === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="yearly" <?php echo $timeFrame === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                </select>
                <canvas id="salesChart"></canvas>
            </div>
            <div class="graph-container">
                <h2>Selected Services</h2>
                <select id="serviceTimeFrameSelect" onchange="updateServiceTimeFrame()">
                    <option value="daily" <?php echo $serviceTimeFrame === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo $serviceTimeFrame === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo $serviceTimeFrame === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="yearly" <?php echo $serviceTimeFrame === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                </select>
                <canvas id="serviceChart"></canvas>
            </div>
        </div>

        <div class="report-container">
            <h2 class="report-title">Today's Report</h2>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Most Selected Service</th>
                        <th>Total Sales (RM)</th>
                        <th>Total Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($todayReport): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($todayReport['service_name']); ?></td>
                            <td><?php echo number_format($todayReport['total_sales'], 2); ?></td>
                            <td><?php echo $todayReport['total_bookings']; ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No data available for today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const serviceNames = <?php echo json_encode($serviceNames); ?>;
        const serviceCounts = <?php echo json_encode($serviceCounts); ?>;
        const salesLabels = <?php echo json_encode($labels); ?>;
        const salesData = <?php echo json_encode($sales); ?>;

        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Sales (RM)',
                    data: salesData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        const serviceChart = new Chart(serviceCtx, {
            type: 'bar',
            data: {
                labels: serviceNames,
                datasets: [{
                    label: 'Number of Selections',
                    data: serviceCounts,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        document.getElementById('timeFrameSelect').addEventListener('change', function() {
            const selectedTimeFrame = this.value;
            const currentUrl = new URL(window.location.href);
            
            // Set the new timeFrame in the URL and preserve the serviceTimeFrame
            currentUrl.searchParams.set('timeFrame', selectedTimeFrame);

            // Check if serviceTimeFrame already exists, if not, preserve the existing one
            const serviceTimeFrame = new URLSearchParams(window.location.search).get('serviceTimeFrame') || 'monthly';
            currentUrl.searchParams.set('serviceTimeFrame', serviceTimeFrame);

            // Reload with the updated URL
            window.location.href = currentUrl.toString();
        });

        // Handle the change in the 'Selected Services' time frame
        document.getElementById('serviceTimeFrameSelect').addEventListener('change', function() {
            const selectedServiceTimeFrame = this.value;
            const currentUrl = new URL(window.location.href);
            
            // Set the new serviceTimeFrame and preserve the timeFrame for the sales trend
            currentUrl.searchParams.set('serviceTimeFrame', selectedServiceTimeFrame);
            
            // Preserve the existing timeFrame in the URL
            const timeFrame = new URLSearchParams(window.location.search).get('timeFrame') || 'monthly';
            currentUrl.searchParams.set('timeFrame', timeFrame);
            
            // Reload with the updated URL
            window.location.href = currentUrl.toString();
        });
    </script>
</body>
</html>