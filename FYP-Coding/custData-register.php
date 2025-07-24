<?php
    include('connect.php');

    $error = array();
    // Check if the form is submitted
    if (isset($_POST['user-register'])) {
        // Get form data
        $firstName = $_POST['user-fname'];
        $lastName = $_POST['user-lname'];
        $uemail = $_POST['uemail'];
        $upassword = $_POST['upassword'];
        $reEnteruPassword = $_POST['re-enter-upassword'];
        $uphone = $_POST['uphone'];

        // Input validation
        if (strlen($firstName) < 3) {
            $error[] = "<script>alert('Your First Name seems incorrect.');</script>";
        } else if (strlen($lastName) < 3) {
            $error[] = "<script>alert('Your Last Name seems incorrect.');</script>";
        } else if (strpos($uemail, "@") === false || strpos($uemail, ".") === false) {
            $error[] = "<script>alert('Invalid email address');</script>";
        } else if (strlen($uphone) < 8 || strlen($uphone) > 17) {
            $error[] = "<script>alert('Invalid phone number');</script>";
        } else if (strlen($upassword) <= 3) {
            $error[] = "<script>alert(' Your Password cannot be less than 3 words. ');</script>";
        } else if ($upassword != $reEnteruPassword) {
            $error[] = "<script>alert('Both passwords are not the same.');</script>";
        } else { // Check if email and phone already exist
            $sql = "SELECT * FROM customer WHERE customer_email = ? OR customer_phone = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $uemail, $uphone);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($uemail == $row['customer_email']) {
                        $error[] = "<script>alert(' The email " . $uemail . " has been taken');</script>";
                    }
                    if ($uphone == $row['customer_phone']) {
                        $error[] = "<script>alert(' The phone number " . $uphone . " has been taken');</script>";
                    }
                }
            }
            $stmt->close(); 
        }

    // If there are no errors, proceed to insert the data
        if (empty($error)) {
            // Hash the password
            $hashedPassword = password_hash($upassword, PASSWORD_DEFAULT);

            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO customer (customer_fname, customer_lname, customer_email, customer_phone, customer_password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $firstName, $lastName, $uemail, $uphone, $hashedPassword);

            // Execute the statement
            if ($stmt->execute()) {
                echo "<script>alert('Your account succesfully registered'); window.location.href = 'user-login.php';</script>";
            } else {
                $stmt->error;
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


    //review page
    if (isset($_POST['review-btn'])) {
        // Check if the user is logged in
        if (!isset($_SESSION['customer_id'])) {
            echo "<script>alert('Please log in first to write a review'); window.location.href = 'user-login.php';</script>";
            exit(); // Stop further execution
        } else {
            $customerId = $_SESSION['customer_id'];
            $barberId = isset($_GET['barber_id']) ? intval($_GET['barber_id']) : null;
            $rating = isset($_POST['rating']) ? $_POST['rating'] : null;
            $comment = isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '';
    
            // Validate inputs
            if (empty($barberId)) {
                die("Invalid Barber ID.");
            }
            if (empty($rating) || empty($comment)) {
                echo "<script>alert('Please fill in the rate and comment');</script>";
            } else {
                // Prepare and bind
                $sql = "INSERT INTO review (barber_id, customer_id, rating, comments, review_date) VALUES (?, ?, ?, ?, curdate())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiss", $barberId, $customerId, $rating, $comment);
                
    
                if ($stmt->execute()) {
                    echo "<script>alert('Your review has been sent'); window.location.href = 'user-homepage.php';</script>";
                    exit(); // Stop further execution
                } else {
                    $error[] = "Error submitting review.";
                }

                
                //$datequery = "INSERT INTO review SET review_date = CURRENT_TIMESTAMP";
                //$datesql = mysql_query($datequery) or die(mysql_error());
            }
        }
    }

    if (isset($_POST['submit-payment-btn'])) {
        $bookingId = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
        $paymentAmount = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0.00;
    
        // Check if a file was uploaded
        if (isset($_FILES['paymentfile']) && $_FILES['paymentfile']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['paymentfile']['tmp_name'];
            $fileName = $_FILES['paymentfile']['name'];
            $fileSize = $_FILES['paymentfile']['size'];
            $fileType = $_FILES['paymentfile']['type'];
    
            // Read the file content
            $fileContent = file_get_contents($fileTmpPath);
    
            if ($bookingId !== null && $paymentAmount > 0) {
                $sql = "INSERT INTO payment (booking_id, payment_date, payment_status, payment_amount, payment_references) VALUES (?, now(), 'Process', ?, ?)";
                $stmt = $conn->prepare($sql);
                
                // Bind the actual values to the placeholders
                $stmt->bind_param("ids", $bookingId, $paymentAmount, $fileContent);
    
                // Use send_long_data to send the file content
                $stmt->send_long_data(2, $fileContent); // 2 is the index of the payment_file parameter
    
                if ($stmt->execute()) {
                    $updateSql = "UPDATE booking SET booking_status = 'incoming' WHERE booking_id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("i", $bookingId);
                    $updateStmt->execute();
                    $updateStmt->close();
                    echo "<script>alert('Your proof of payment has been sent. Please wait for admin approval.'); window.location.href = 'user-homepage.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error submitting payment. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Invalid booking ID or payment amount.');</script>";
            }
        } else {
            echo "<script>alert('Error uploading file. Please try again.');</script>";
        }
    }

// Close the database connection
    $conn->close();
?>