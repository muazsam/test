<?php
    session_start();
    include('connect.php');

    if (!isset($_SESSION['blEmail'])) {
        header("Location: barber-login.php");
        exit();
    }

    $email = $_SESSION['blEmail'];
    $sql = "SELECT * FROM barber WHERE barber_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $barber = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fname = $_POST['barber-fname'];
        $lname = $_POST['barber-lname'];
        $phone = $_POST['bphone'];
        $exp = $_POST['barber-exp'];

        $update_sql = "UPDATE barber SET barber_fname = ?, barber_lname = ?, barber_phoneNumber = ?, barber_exp = ? WHERE barber_email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssis", $fname, $lname, $phone, $exp, $email);

        if ($update_stmt->execute()) {
            echo "<script>alert('Profile updated successfully');</script>";
        } else {
            echo "<script>alert('Error updating profile');</script>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="editprofile.css">
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
                        echo "<li><a href='user-login.php'>As Customer</a></li>";
                        echo "<li><a href='barber-login.php'>As Barber</a></li>";
                        echo "</ul>"; 
                        echo "</li>";
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </header>
    <h1>Your Account</h1>
    <div class="login-container">
        <div class="login-form">
            <form method="post" action="">
                <div class="container-name">
                    <label>First Name : </label><input id="barber-fname" name="barber-fname" type="text" class="rounded-textbox" placeholder="First Name" value="<?php echo htmlspecialchars($barber['barber_fname']); ?>" readonly required>
                    <label>Last Name : </label><input id="barber-lname" name="barber-lname" type="text" class="rounded-textbox" placeholder="Last Name" value="<?php echo htmlspecialchars($barber['barber_lname']); ?>" readonly required>
                </div>
                <label>Email Address : </label><input type="text" class="rounded-textbox" name="bemail" placeholder="Email Address" value="<?php echo htmlspecialchars($barber['barber_email']); ?>" readonly>
                <label>Phone Number : </label><input type="text" class="rounded-textbox" name="bphone" placeholder="Phone Number" value="<?php echo htmlspecialchars($barber['barber_phoneNumber']); ?>" readonly required>
                <label>Experience (years) : </label><input type="number" class="rounded-textbox" name="barber-exp" placeholder="Experience" value="<?php echo htmlspecialchars($barber['barber_exp']); ?>" readonly required>
                <center>
                    <button id="edit-btn" type="button" class="rounded-button">Edit</button>
                    <button id="done-btn" name="update-profile" class="rounded-button" type="submit" style="display: none;">Done</button>
                </center>
            </form>
        </div>
    </div>
</body>
<script>
    const editBtn = document.getElementById('edit-btn');
    const doneBtn = document.getElementById('done-btn');
    const inputs = document.querySelectorAll('.rounded-textbox');

    editBtn.addEventListener('click', () => {
        inputs.forEach(input => input.removeAttribute('readonly'));
        editBtn.style.display = 'none';
        doneBtn.style.display = 'inline-block';
    });

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