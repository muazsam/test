<?php
// Include database connection
include('connect.php');

$sql = "SELECT barber_id, barber_fname, barber_lname, barber_email, barber_phoneNumber, barber_status FROM barber WHERE role = 'barber'";
$result = $conn->query($sql);
$barbers = $result->fetch_all(MYSQLI_ASSOC);

// Handle form submission for status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barber_id']) && isset($_POST['status'])) {
    $barberId = $_POST['barber_id'];
    $status = $_POST['status'];
    $updateSql = "UPDATE barber SET barber_status = ? WHERE barber_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ii", $status, $barberId);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-barberstatus.php");
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
    <title>Barber Status</title>
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
    <div class="search-container">
        <input type="text" id="searchBarber" onkeyup="filterTable('barberTable', this.value)" placeholder="Search for barber by name...">
    </div>
    <table id="barberTable">
        <thead>
            <tr>
                <th>Barber Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($barbers)): ?>
                <tr>
                    <td colspan="7">No barbers found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($barbers as $barber): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($barber['barber_fname']) . " " . htmlspecialchars($barber['barber_lname']) ; ?></td>
                        <td><?php echo htmlspecialchars($barber['barber_email']); ?></td>
                        <td><?php echo htmlspecialchars($barber['barber_phoneNumber']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="barber_id" value="<?php echo htmlspecialchars($barber['barber_id']); ?>">
                                <button type="submit" name="status" value="<?php echo $barber['barber_status'] == 0 ? 1 : 0; ?>" 
                                    class="status-button <?php echo $barber['barber_status'] == 0 ? 'available' : 'unavailable'; ?>">
                                    <?php echo $barber['barber_status'] == 0 ? 'Available' : 'Unavailable'; ?>
                                </button>
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
    function filterTable(tableId, query) {
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName('tr');
            query = query.toLowerCase();

            for (let i = 1; i < rows.length; i++) {
                const nameCell = rows[i].getElementsByTagName('td')[1];
                if (nameCell) {
                    const name = nameCell.textContent.toLowerCase();
                    rows[i].style.display = name.includes(query) ? '' : 'none';
                }
            }
        }
    
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
