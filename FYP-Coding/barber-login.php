<?php
session_start();
include('connect.php'); // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['blEmail'];
    $password = $_POST['blPass'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("SELECT * FROM barber WHERE barber_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if($user['role'] === 'Admin'){
            if (password_verify($password, $user['barber_password'])) {
                // Password is correct, set session variables
                $_SESSION['blEmail'] = $user['barber_email'];
                header("Location: admin-homepage.php"); // Redirect to user homepage
                exit();
            } else {
                $error = "The password is incorrect.";
            }
        }
        else {
            if(password_verify($password, $user['barber_password'])) {
                // Password is correct, set session variables
                $_SESSION['blEmail'] = $user['barber_email'];
                header("Location: user-homepage.php"); // Redirect to user homepage
                exit();
            } else {
                $error = "The password is incorrect.";
            }
        }
    } else {
        $error = "The email does not exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
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
                    if (isset($_SESSION['ulEmail']) || isset($_SESSION['blEmail'])) {
                        echo "<a href='user-logout.php' class='profile'>Log Out</a>";
                    } else {
                        echo "<li>";
                        echo "<a href=''>Sign In ▼</a>";
                        echo "<ul class='dropdown'>"; // Dropdown menu
                        echo "<li><a href='user-login.php'>As Customer</a></li>";
                        echo "<li><a href='barber-login.php'>As Barber</a></li>";
                        echo "</ul>"; // Close the dropdown
                        echo "</li>";
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </header>
    <h1>Barber Log In</h1>
    <div class="login-container">
        <div class="login-form">
            <form method="post">
                <input type="email" class="rounded-textbox" placeholder="Email" name="blEmail" required>
                <input type="password" class="rounded-textbox" placeholder="Password" name="blPass" required>
                <center><input type="submit" class="rounded-button" value="Log in" name="user-logIn-btn"></center>
                <?php if (!empty($error)): ?>
                    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
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
