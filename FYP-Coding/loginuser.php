<?php
    session_start();
    // Initialize the session
    if (!isset($_SESSION['ulEmail'])) {
        $_SESSION['ulEmail'] = $_GET['ulEmail'];
        $_SESSION['ulPassword'] = $_GET['ulPassword'];
    }
    include("connect.php");
    $sql = "SELECT * FROM customer WHERE customer_email='".$_SESSION['ulEmail']."'";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        session_unset();
        echo "<meta http-equiv=\"refresh\" content=\"0;URL=user-login.php\">";
    } else {
        while ($row = $result->fetch_assoc()) {
            if (password_verify($_GET["ulPassword"], $row["customer_password"])) {
                echo "<meta http-equiv=\"refresh\" content=\"0;URL=user-homepage.php\">";
            }
            else {
                session_unset();
                echo "<meta http-equiv=\"refresh\" content=\"2;URL=user-login.php\">";
            }
        }
    }
?>