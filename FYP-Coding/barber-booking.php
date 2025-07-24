<?php
session_start();
include('connect.php');

// Check if the barber is logged in
if (!isset($_SESSION['blEmail'])) {
    header("Location: barber-login.php");
    exit();
}

// Fetch barber ID based on email
$barber_email = $_SESSION['blEmail'];
$sql = "SELECT barber_id FROM barber WHERE barber_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $barber_email);
$stmt->execute();
$stmt->bind_result($barber_id);
$stmt->fetch();
$stmt->close();

if (!$barber_id) {
    echo "Barber not found.";
    exit();
}

// Fetch bookings for the logged-in barber
$bookings = [];
$sql = "SELECT p.payment_status, b.booking_id, b.booking_date, b.booking_time, b.booking_totalprice, 
               c.customer_fname, c.customer_lname, GROUP_CONCAT(s.service_name SEPARATOR ', ') AS services
        FROM booking b
        JOIN customer c ON b.customer_id = c.customer_id
        JOIN booking_service bs ON b.booking_id = bs.booking_id
        JOIN service s ON bs.service_id = s.service_id
        JOIN payment p on b.booking_id = p.booking_id
        WHERE b.barber_id = ? AND p.payment_status = 'accepted'
        GROUP BY b.booking_id
        ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $barber_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bookings</title>
    <link rel="stylesheet" href="bookingHistory.css">
</head>
<body>
    <header id="header">
        <div class="top-bar">
            <nav class="nav-bar">
                <a href="user-homepage.php" class="logo"><span>MS Barber</span></a>
                <ul>
                    <li><a href="barber-booking.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'barber-booking.php' ? 'active' : ''; ?>">All Bookings</a></li>
                    <li><a href="barber-todayschedule.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'barber-todayschedule.php' ? 'active' : ''; ?>">Today's Schedule</a></li>
                    <?php
                        if (isset($_SESSION['blEmail'])) {
                            echo "<li>";
                            echo "<a href='' class='profile'>Signed ▼</a>";
                            echo "<ul class='dropdown'>"; 
                            echo "<li><a href='user-logout.php'>Log Out</a></li>";
                            echo "<li><a href='barber-editprofile.php'>Edit Profile</a></li>";
                            echo "</ul>";
                            echo "</li>";
                        } else {
                            echo "<li>";
                            echo "<a href=''>Sign In ▼</a>";
                            echo "<ul class='dropdown'>";
                            echo "<li><a href='barber-login.php'>As Barber</a></li>";
                            echo "</ul>";
                            echo "</li>";
                        }
                    ?>
                </ul>
            </nav>
        </div>
    </header>

    <h1>Customer Bookings On Your Service</h1>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Services</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="6">No bookings found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['customer_fname']) . " " . htmlspecialchars($booking['customer_lname']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                        <td><?php echo htmlspecialchars($booking['services']); ?></td>
                        <td>RM <?php echo number_format($booking['booking_totalprice'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
<script>
    window.addEventListener('scroll', function() {
        const header = document.getElementById('header');
        if (window.scrollY > 50) { 
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
</script>
</html>