<?php
// Include database connection
    include('connect.php');

    // Handle sorting option
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

    switch ($sortOption) {
        case 'rating_asc':
            $orderBy = "r.rating ASC";
            break;
        case 'rating_desc':
            $orderBy = "r.rating DESC";
            break;
        case 'date_asc':
            $orderBy = "r.review_date ASC";
            break;
        case 'date_desc':
        default:
            $orderBy = "r.review_date DESC";
            break;
    }

    // Fetch reviews data
    $sql = "SELECT r.review_id, c.customer_fname, c.customer_lname, b.barber_fname, b.barber_lname, r.review_date, r.comments, r.rating
            FROM review r
            JOIN customer c ON r.customer_id = c.customer_id
            JOIN barber b ON r.barber_id = b.barber_id
            ORDER BY $orderBy";
    $result = $conn->query($sql);
    $reviews = $result->fetch_all(MYSQLI_ASSOC);

    // Handle form submission for deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
        $reviewId = $_POST['review_id'];
        $deleteSql = "DELETE FROM review WHERE review_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $stmt->close();
        header("Location: admin-reviewcust.php?sort=" . $sortOption);
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
    <title>Customer Reviews</title>
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
    <h1>Customer Reviews</h1>
    <div class="filter-container">
            <label for="sort">Sort by:</label>
            <select name="sort" id="sort" onchange="window.location.href = 'admin-reviewcust.php?sort=' + this.value;">
                <option value="date_desc" <?php echo $sortOption === 'date_desc' ? 'selected' : ''; ?>>Date (Newest First)</option>
                <option value="date_asc" <?php echo $sortOption === 'date_asc' ? 'selected' : ''; ?>>Date (Oldest First)</option>
                <option value="rating_desc" <?php echo $sortOption === 'rating_desc' ? 'selected' : ''; ?>>Rating (Highest First)</option>
                <option value="rating_asc" <?php echo $sortOption === 'rating_asc' ? 'selected' : ''; ?>>Rating (Lowest First)</option>
            </select>
        </div>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Barber Name</th>
                <th>Date</th>
                <th>Rating</th>
                <th>Comments</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reviews)): ?>
                <tr>
                    <td colspan="6">No reviews found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($review['customer_fname'] . ' ' . htmlspecialchars($review['customer_lname'])); ?></td>
                        <td><?php echo htmlspecialchars($review['barber_fname'] . ' ' . htmlspecialchars($review['barber_lname'])); ?></td>
                        <td><?php echo htmlspecialchars($review['review_date']); ?></td>
                        <td><?php echo htmlspecialchars($review['rating']); ?></td>
                        <td><?php echo htmlspecialchars($review['comments']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['review_id']); ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete the review?')">Delete</button>
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