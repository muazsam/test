<?php
    include('connect.php');

    $error = array();
    // Check if the form is submitted
    if (isset($_POST['barber-register'])) {
        // Get form data
        $barber_fname = $_POST['fname'];
        $barber_lname = $_POST['lname'];
        $barber_password = $_POST['pass'];
        $barber_repassword = $_POST['re_enter_pass'];
        $barber_phoneNumber = $_POST['phone'];
        $barber_exp = $_POST['experience'];
        $barber_DOB = $_POST['dob'];
        $barber_email = $_POST['email'];

        // Check and process the uploaded image
        if (isset($_FILES['formal_pic']) && $_FILES['formal_pic']['error'] == 0) {
            $barber_image = file_get_contents($_FILES['formal_pic']['tmp_name']);
        } else {
            $error[] = "<script>alert('Please upload a valid image.');</script>";
        }

        // Input validation
        if (strlen($barber_fname) < 3) {
            $error[] = "<script>alert('Your First Name seems incorrect.');</script>";
        } else if (strlen($barber_lname) < 3) {
            $error[] = "<script>alert('Your Last Name seems incorrect.');</script>";
        } else if (strpos($barber_email, "@") === false || strpos($barber_email, ".") === false) {
            $error[] = "<script>alert('Invalid email address');</script>";
        } else if (strlen($barber_phoneNumber) < 8 || strlen($barber_phoneNumber) > 17) {
            $error[] = "<script>alert('Invalid phone number');</script>";
        } else if (strlen($barber_password) <= 3) {
            $error[] = "<script>alert('Your Password cannot be less than 3 characters.');</script>";
        } else if ($barber_password != $barber_repassword) {
            $error[] = "<script>alert('Both passwords are not the same.');</script>";
        } else if ($barber_exp > 49) {
            $error[] = "<script>alert('The number exceeds the limit.');</script>";
        } else { 
            // Check if email and phone already exist
            $sql = "SELECT * FROM barber WHERE barber_email = ? OR barber_phoneNumber = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $barber_email, $barber_phoneNumber);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($barber_email == $row['barber_email']) {
                        $error[] = "<script>alert('The email " . $barber_email . " has been taken.');</script>";
                    }
                    if ($barber_phoneNumber == $row['barber_phoneNumber']) {
                        $error[] = "<script>alert('The phone number " . $barber_phoneNumber . " has been taken.');</script>";
                    }
                }
            }
            $stmt->close();
        }

        // If there are no errors, proceed to insert the data
        if (empty($error)) {
            // Hash the password
            $hashedPassword = password_hash($barber_password, PASSWORD_DEFAULT);

            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO barber (barber_fname, barber_lname, barber_image, barber_password, barber_phoneNumber, barber_exp, barber_DOB, barber_email, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'barber')");
            $stmt->bind_param("ssbssiss", $barber_fname, $barber_lname, $barber_image, $hashedPassword, $barber_phoneNumber, $barber_exp, $barber_DOB, $barber_email);

            // Execute the statement
            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!');</script>";
                header('Location: admin-homepage.php');
                // Optionally redirect or display a success message
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }

            // Close the statement
            $stmt->close();
        } else {
            // Display errors
            foreach ($error as $err) {
                echo $err . "<br>";
            }
        }
    }

    // Close the database connection
    $conn->close();
?>
