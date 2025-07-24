<?php
    include('connect.php');
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    $filterCondition = "";
    if ($filter === 'today') {
        $filterCondition = "WHERE DATE(bk.booking_date) = CURDATE()";
    } elseif ($filter === 'last7') {
        $filterCondition = "WHERE DATE(bk.booking_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter === 'last30') {
        $filterCondition = "WHERE DATE(bk.booking_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($filter === 'Incoming') {
        $filterCondition = "WHERE bk.booking_status = '' OR bk.booking_status = 'Incoming'";
    } elseif (in_array($filter, ['Cancelled', 'no-show', 'Done'])) {
        $filterCondition = "WHERE bk.booking_status = '$filter'";
    }

    // Fetch bookings data
    $sql = "SELECT c.customer_fname, c.customer_lname, b.barber_fname, b.barber_lname, bk.booking_date, bk.booking_time, bk.booking_status
            FROM booking bk
            JOIN customer c ON bk.customer_id = c.customer_id
            JOIN barber b ON bk.barber_id = b.barber_id
            $filterCondition";
    $result = $conn->query($sql);
    $bookings = $result->fetch_all(MYSQLI_ASSOC);

    function getCurrentPage() {
        return basename($_SERVER['PHP_SELF']);
    }

    $currentPage = getCurrentPage();

    // Dynamic Highlight for Active Page
    function isActive($pageName) {
        global $currentPage;
        return $currentPage === $pageName ? 'active' : '';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber Monitor</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
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
    <form method="GET" id="filter-form" class="filter-form">
        <label for="filter" class="filter-label">Filter by:</label>
        <select name="filter" id="filter" class="filter-select" onchange="document.getElementById('filter-form').submit();">
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
            <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
            <option value="last7" <?php echo $filter === 'last7' ? 'selected' : ''; ?>>Last 7 Days</option>
            <option value="last30" <?php echo $filter === 'last30' ? 'selected' : ''; ?>>Last 30 Days</option>
            <option value="Incoming" <?php echo $filter === 'Incoming' ? 'selected' : ''; ?>>Incoming</option>
            <option value="Cancelled" <?php echo $filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            <option value="no-show" <?php echo $filter === 'no-show' ? 'selected' : ''; ?>>No Show</option>
            <option value="Done" <?php echo $filter === 'Done' ? 'selected' : ''; ?>>Done</option>
        </select>
    </form>
    <table>
        <thead>
            <tr>
                <th>Barber Name</th>
                <th>Customer Name</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Booking Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="5">No bookings found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['barber_fname']) . " " . htmlspecialchars($booking['barber_lname']); ?></td>
                        <td><?php echo htmlspecialchars($booking['customer_fname']) . " " . htmlspecialchars($booking['customer_lname']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_status']) ? htmlspecialchars($booking['booking_status']) : 'Incoming'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </main>
</body>
<script>
    window.addEventListener('scroll', function () {
        const header = document.getElementById('header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
</script>
</html>
