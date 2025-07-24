<?php
    session_start();
    include('connect.php');

    // Check if the barber is logged in
    if (!isset($_SESSION['blEmail'])) {
        header("Location: barber-login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $booking_id = $_POST['booking_id'];
        $status = $_POST['status'];
    
        // Update the booking status based on the selected value
        if (in_array($status, ['done', 'cancelled', 'no-show'])) {
            $sql = "UPDATE booking SET booking_status = ? WHERE booking_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $booking_id);
    
            if ($stmt->execute()) {
                header("Location: barber-todayschedule.php?status=success");
                exit();
            } else {
                header("Location: barber-todayschedule.php?status=error");
                exit();
            }
        } else {
            header("Location: barber-todayschedule.php?status=invalid_status");
            exit();
        }
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

    // Fetch today's bookings for the logged-in barber
    $today = date('Y-m-d');
    $bookings = [];
    $sql = "SELECT b.booking_id, b.booking_time, b.booking_totalprice, 
                c.customer_fname, c.customer_lname, 
                GROUP_CONCAT(s.service_name SEPARATOR ', ') AS services, b.booking_status
            FROM booking b
            JOIN customer c ON b.customer_id = c.customer_id
            JOIN booking_service bs ON b.booking_id = bs.booking_id
            JOIN service s ON bs.service_id = s.service_id
            JOIN payment p on b.booking_id = p.booking_id
            WHERE b.barber_id = ? AND b.booking_date = ? AND p.payment_status = 'accepted'
            GROUP BY b.booking_id
            ORDER BY b.booking_time ASC"; 

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $barber_id, $today);
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
        <title>Barber's Schedule</title>
        <link rel="stylesheet" href="barber-schedule.css">
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

        <main>
            <center><h1>Today's Schedule</h1>
            <?php echo $today?></center>
            <div class="schedule-container">
                <?php if (empty($bookings)): ?>
                        <h2>No Bookings Today</h2>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <h2><?php echo htmlspecialchars($booking['customer_fname'] . " " . $booking['customer_lname']); ?></h2>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($booking['booking_time']); ?></p>
                            <p><strong>Services:</strong> <?php echo htmlspecialchars($booking['services']); ?></p>
                            <p><strong>Total Price:</strong> RM <?php echo number_format($booking['booking_totalprice'], 2); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['booking_status']); ?></p>
                            <form method="POST" action="barber-todayschedule.php">
                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                <label for="status">Update Status:</label>
                                <select name="status" id="status" required>
                                    <option value="">Select</option>
                                    <option value="done">Done</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="no-show">No Show</option>
                                </select>
                                <button type="submit" class="update-btn">Update</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if (isset($_GET['status'])): ?>
                <div class="status-message">
                    <?php
                    if ($_GET['status'] === 'success') {
                        echo '<p class="success">Booking status updated successfully!</p>';
                    } elseif ($_GET['status'] === 'error') {
                        echo '<p class="error">Failed to update booking status. Please try again.</p>';
                    } elseif ($_GET['status'] === 'invalid_status') {
                        echo '<p class="error">Invalid status selected. Please choose a valid option.</p>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </main>

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
    </body>
</html>
