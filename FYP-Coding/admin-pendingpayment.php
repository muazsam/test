<?php
    include('connect.php');

    $sql = "SELECT b.booking_id, br.barber_fname, br.barber_lname, b.customer_id, b.booking_date, b.booking_time
            FROM booking b
            JOIN barber br ON b.barber_id = br.barber_id
            WHERE b.booking_status = 'not paid'";
    $result = $conn->query($sql);
    $pendingPayments = $result->fetch_all(MYSQLI_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
        $bookingId = $_POST['booking_id'];
        $deleteSql = "DELETE FROM booking WHERE booking_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $stmt->close();
        header("Location: admin-pendingpayment.php");
        exit();
    }

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
    <title>Pending Payments</title>
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
    <h1>Pending Payments</h1>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Barber Name</th>
                <th>Customer ID</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pendingPayments)): ?>
                <tr>
                    <td colspan="6">No pending payments found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pendingPayments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['barber_fname']) . " " . htmlspecialchars($payment['barber_lname']); ?></td>
                        <td><?php echo htmlspecialchars($payment['customer_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($payment['booking_time']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($payment['booking_id']); ?>">
                                <button type="submit" class="payment-delete-button" onclick="return confirm('Are you sure you want to delete the payment?')">Delete</button>
                            </form>
                        </td>
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