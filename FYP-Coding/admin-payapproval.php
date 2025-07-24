<?php
// Include database connection
    include('connect.php');
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    $filterCondition = "";
    if ($filter === 'today') {
        $filterCondition = "WHERE DATE(p.payment_date) = CURDATE()";
    } elseif ($filter === 'last7') {
        $filterCondition = "WHERE DATE(p.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter === 'last30') {
        $filterCondition = "WHERE DATE(p.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }
    // Fetch payments data
    $sql = "SELECT c.customer_email, b.barber_fname, b.barber_lname, p.payment_id, p.booking_id, p.payment_amount, p.payment_date, p.payment_references, p.payment_status
            FROM payment p
            JOIN booking bk ON p.booking_id = bk.booking_id
            JOIN customer c ON bk.customer_id = c.customer_id
            JOIN barber b ON bk.barber_id = b.barber_id
            $filterCondition";
    $result = $conn->query($sql);
    $payments = $result->fetch_all(MYSQLI_ASSOC);

    // Handle form submission for approval
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id']) && isset($_POST['status'])) {
        $paymentId = $_POST['payment_id'];
        $status = $_POST['status'];
        $updateSql = "UPDATE payment SET payment_status = ? WHERE payment_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $status, $paymentId);
        $stmt->execute();
        $stmt->close();
        header("Location: admin-payapproval.php");
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
    <title>Payment Approval</title>
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
        </select>
    </form>
    <table>
        <thead>
            <tr>
                <th>Customer Email</th>
                <th>Barber Name</th>
                <th>Payment ID</th>
                <th>Booking ID</th>
                <th>Payment Amount</th>
                <th>Payment Date</th>
                <th>Payment References</th>
                <th>Approval</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="8">No payments found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars($payment['barber_fname']) . " " . htmlspecialchars($payment['barber_lname']); ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                        <td>RM <?php echo number_format($payment['payment_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        <td>
                            <button><a href="admin-payimage.php?payment_id=<?php echo htmlspecialchars($payment['payment_id']); ?>" target="_blank">
                                View Image
                            </a></button>
                        </td>
                        <td>
                            <?php if ($payment['payment_status'] === 'accepted'): ?>
                                <span class="status-icon accepted">&#x2714;</span> 
                            <?php elseif ($payment['payment_status'] === 'rejected'): ?>
                                <span class="status-icon rejected">&#x2716;</span> 
                            <?php else: ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirmAction(this);">
                                    <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment['payment_id']); ?>">
                                    <button type="submit" name="status" value="accepted">Accept</button>
                                    <button type="submit" name="status" value="rejected">Reject</button>
                                </form>
                            <?php endif; ?>
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

    function confirmAction(form) {
        const status = form.querySelector('button[type="submit"][name="status"]').value;
        const message = status === 'accepted' ? 'Are you sure you want to accept this payment?' : 'Are you sure you want to reject this payment?';
        return confirm(message);
    }
</script>
</html>