<?php
    include('connect.php');

    $error = array();
    // Check if the form is submitted
    if (isset($_POST['barber-register'])) {
        // Get form data
        $barber_role = $_POST['role'];
        $barber_fname = $_POST['fname'];
        $barber_lname = $_POST['lname'];
        $barber_password = $_POST['pass'];
        $barber_repassword = $_POST['re_enter_pass'];
        $barber_phoneNumber = $_POST['phone'];
        $barber_exp = $_POST['experience'];
        $barber_DOB = $_POST['dob'];
        $barber_email = $_POST['email'];
        
    
        
        if($barber_role != "Barber" && $barber_role != "Admin"){
            $error[] = "Please select a role for the account.";
        }

        if($barber_role == "Barber"){
            if (isset($_FILES['formal_pic']) && $_FILES['formal_pic']['error'] == 0) {
                $barber_image = file_get_contents($_FILES['formal_pic']['tmp_name']);
            } else {
                $error[] = "Please upload a valid image.";
            }
        
            // Input validation
            if (strlen($barber_fname) < 3) {
                $error[] = "Your First Name seems incorrect.";
            } else if (strlen($barber_lname) < 3) {
                $error[] = "Your Last Name seems incorrect.";
            } else if (strpos($barber_email, "@") === false || strpos($barber_email, ".") === false) {
                $error[] = "Invalid email address";
            } else if (strlen($barber_phoneNumber) < 8 || strlen($barber_phoneNumber) > 17) {
                $error[] = "Invalid phone number";
            } else if (strlen($barber_password) <= 3) {
                $error[] = "Your Password cannot be less than 3 characters.";
            } else if ($barber_password != $barber_repassword) {
                $error[] = "Both passwords are not the same.";
            } else if ($barber_exp > 49) {
                $error[] = "The number exceeds the limit.";
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
                            $error[] = "The email " . $barber_email . " has been taken.";
                        }
                        if ($barber_phoneNumber == $row['barber_phoneNumber']) {
                            $error[] = "The phone number " . $barber_phoneNumber . " has been taken.";
                        }
                    }
                }
                $stmt->close();
            }
        }
        
        else{
            if (strlen($barber_fname) < 3) {
                $error[] = "Your First Name seems incorrect.";
            } else if (strlen($barber_lname) < 3) {
                $error[] = "Your Last Name seems incorrect.";
            } else if (strpos($barber_email, "@") === false || strpos($barber_email, ".") === false) {
                $error[] = "Invalid email address";
            } else if (strlen($barber_phoneNumber) < 8 || strlen($barber_phoneNumber) > 17) {
                $error[] = "Invalid phone number";
            } else if (strlen($barber_password) <= 3) {
                $error[] = "Your Password cannot be less than 3 characters.";
            } else if ($barber_password != $barber_repassword) {
                $error[] = "Both passwords are not the same.";
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
                            $error[] = "The email " . $barber_email . " has been taken.";
                        }
                        if ($barber_phoneNumber == $row['barber_phoneNumber']) {
                            $error[] = "The phone number " . $barber_phoneNumber . " has been taken.";
                        }
                    }
                }
                $stmt->close();
            }
        }
    
        // If there are no errors, proceed to insert the data
        if (empty($error)) {
            // Hash the password
            $hashedPassword = password_hash($barber_password, PASSWORD_DEFAULT);
    
            // Prepare and bind
            if ($barber_role == "Barber") {
                $stmt = $conn->prepare("INSERT INTO barber (barber_fname, barber_lname, barber_image, barber_password, barber_phoneNumber, barber_exp, barber_DOB, barber_email, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssbssisss", $barber_fname, $barber_lname, $barber_image, $hashedPassword, $barber_phoneNumber, $barber_exp, $barber_DOB, $barber_email, $barber_role);
                $stmt->send_long_data(2, $barber_image);
            } else {
                $stmt = $conn->prepare("INSERT INTO barber (barber_fname, barber_lname, barber_password, barber_phoneNumber, barber_email, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $barber_fname, $barber_lname, $hashedPassword, $barber_phoneNumber, $barber_email, $barber_role);
            }
    
            // Execute the statement
            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!');</script>";
                echo "<script>window.location.href = 'admin-createbarber.php';</script>";
                exit(); // Ensure no further code is executed after redirection
            } else {
                $error[] = "Error: " . $stmt->error;
            }
    
            // Close the statement
            $stmt->close();
        } else {
            // Display errors
            foreach ($error as $err) {
                echo "<script>alert('$err');</script>";
            }
        }
    }





    if (isset($_POST['delete_btn'])) {
        // Get the email from the form
        $userEmail = $_POST['user_email'];
    
        // Input validation
        if (empty($userEmail)) {
            $error[] = "Email field cannot be empty.";
        } else if (strpos($userEmail, "@") === false || strpos($userEmail, ".") === false) {
            $error[] = "Invalid email address.";
        } else {
            // Prepare the DELETE statement for customer
            $sqlCustomer = "DELETE FROM customer WHERE customer_email = ?";
            $stmtCustomer = $conn->prepare($sqlCustomer);
            $stmtCustomer->bind_param("s", $userEmail);
    
            // Execute the statement for customer
            if ($stmtCustomer->execute()) {
                if ($stmtCustomer->affected_rows > 0) {
                    echo "<script>alert('Customer account deleted successfully!');</script>";
                } else {
                    echo "<script>alert('No customer account found with that email.');</script>";
                }
            } else {
                echo "<script>alert('Error deleting customer account: " . $stmtCustomer->error . "');</script>";
            }
    
            // Close the customer statement
            $stmtCustomer->close();
    
            // Prepare the DELETE statement for barber
            $sqlBarber = "DELETE FROM barber WHERE barber_email = ?";
            $stmtBarber = $conn->prepare($sqlBarber);
            $stmtBarber->bind_param("s", $userEmail);
    
            // Execute the statement for barber
            if ($stmtBarber->execute()) {
                if ($stmtBarber->affected_rows > 0) {
                    echo "<script>alert('Barber / Admin account deleted successfully!');</script>";
                } else {
                    echo "<script>alert('No barber account found with that email.');</script>";
                }
            } else {
                echo "<script>alert('Error deleting barber account: " . $stmtBarber->error . "');</script>";
            }
    
            // Close the barber statement
            $stmtBarber->close();
        }
    
        // Display errors if any
        if (!empty($error)) {
            $errorMessage = implode("\\n", $error); // Join errors with newline
            echo "<script>alert('Errors:\\n$errorMessage');</script>";
        }
    }

    if (isset($_POST['submit-btn'])) {
        $operation = $_POST['operation'];
        $serviceId = $_POST['service-id'] ?? null;
        $serviceName = $_POST['service-name'] ?? null;
        $servicePrice = $_POST['service-price'] ?? null;
        $serviceImage = null;

        // Handle file upload
        if (isset($_FILES['service_pic']) && $_FILES['service_pic']['error'] == UPLOAD_ERR_OK) {
            $serviceImage = file_get_contents($_FILES['service_pic']['tmp_name']);
        }

        if ($operation === 'create') {
            if (empty($serviceName) || empty($servicePrice) || $serviceImage === null) {
                $error = "All fields are required for creating a service.";
            } else {
                $createQuery = "INSERT INTO service (service_name, service_price, service_image) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($createQuery);
                if ($stmt) {
                    $stmt->bind_param("sdb", $serviceName, $servicePrice, $serviceImage);
                    $stmt->send_long_data(2, $serviceImage);
                    if ($stmt->execute()) {
                        $error = "Service created successfully.";
                    } else {
                        $error = "Failed to create service: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare query: " . htmlspecialchars($conn->error);
                }
            }
        } elseif ($operation === 'update') {
            if (empty($serviceId) || empty($serviceName) || empty($servicePrice) || $serviceImage === null) {
                $error = "All fields are required for updating a service.";
            } else {
                $updateQuery = "UPDATE service SET service_name = ?, service_price = ?, service_image = ? WHERE service_id = ?";
                $stmt = $conn->prepare($updateQuery);
                if ($stmt) {
                    $stmt->bind_param("sdsi", $serviceName, $servicePrice, $serviceImage, $serviceId);
                    $stmt->send_long_data(2, $serviceImage);
                    if ($stmt->execute()) {
                        $error = "Service updated successfully.";
                    } else {
                        $error = "Failed to update service: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare query: " . htmlspecialchars($conn->error);
                }
            }
        } elseif ($operation === 'delete') {
            if (empty($serviceId)) {
                $error = "Service ID is required for deletion.";
            } else {
                $deleteQuery = "DELETE FROM service WHERE service_id = ?";
                $stmt = $conn->prepare($deleteQuery);
                if ($stmt) {
                    $stmt->bind_param("i", $serviceId);
                    if ($stmt->execute()) {
                        $error = "Service deleted successfully.";
                    } else {
                        $error = "Failed to delete service: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                } else {
                    $error = "Failed to prepare query: " . htmlspecialchars($conn->error);
                }
            }
        } else {
            $error = "Invalid operation.";
        }
    }

    if (isset($_POST['update-availability-btn'])) {
        $operation = $_POST['operation'];
        $vacantId = $_POST['vacant_id'] ?? null;
        $barberEmail = $_POST['ulEmail'] ?? null;
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;

        if ($operation === 'create' || $operation === 'update') {
            if (empty($barberEmail) || empty($startDate) || empty($endDate)) {
                $error[] = "All fields are required.";
            } elseif ($startDate > $endDate) {
                $error[] = "Start date cannot be later than end date.";
            } else {
                // Fetch barber ID based on email
                $sql = "SELECT barber_id FROM barber WHERE barber_email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $barberEmail);
                $stmt->execute();
                $stmt->bind_result($barber_id);
                $stmt->fetch();
                $stmt->close();

                if ($barber_id) {
                    if ($operation === 'create') {
                        // Insert into barber_vacant table
                        $sql = "INSERT INTO barber_vacant (barber_id, vacant_startdate, vacant_enddate) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iss", $barber_id, $startDate, $endDate);
                        if ($stmt->execute()) {
                            $success = "Barber availability created successfully.";
                        } else {
                            $error[] = "Error creating availability: " . $stmt->error;
                        }
                        $stmt->close();
                    } elseif ($operation === 'update') {
                        if (empty($vacantId)) {
                            $error[] = "Vacant ID is required for updating.";
                        } else {
                            // Update barber_vacant table
                            $sql = "UPDATE barber_vacant SET vacant_startdate = ?, vacant_enddate = ? WHERE vacant_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("issi", $barber_id, $startDate, $endDate, $vacantId);
                            if ($stmt->execute()) {
                                $success = "Barber availability updated successfully.";
                            } else {
                                $error[] = "Error updating availability: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                } else {
                    $error[] = "Barber not found.";
                }
            }
        } elseif ($operation === 'delete') {
            if (empty($vacantId)) {
                $error[] = "Vacant ID is required for deletion.";
            } else {
                // Delete from barber_vacant table
                $sql = "DELETE FROM barber_vacant WHERE vacant_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $vacantId);
                if ($stmt->execute()) {
                    $success = "Barber availability deleted successfully.";
                } else {
                    $error[] = "Error deleting availability: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $error[] = "Invalid operation.";
        }
    }

    if (isset($_POST['status'])) {
        // Get the payment ID and the status from the POST request
        $paymentId = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : null;
        $status = isset($_POST['status']) ? $_POST['status'] : null;
    
        if ($paymentId && $status) {
            // Prepare the SQL statement to update payment status
            $stmt = $conn->prepare("UPDATE payment SET payment_status = ? WHERE payment_id = ?");
            $stmt->bind_param("si", $status, $paymentId);
    
            if ($stmt->execute()) {
                echo "<script>alert('Payment status updated successfully.'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
            } else {
                echo "<script>alert('Failed to update payment status. Please try again.');</script>";
            }
    
            $stmt->close();
        } else {
            echo "<script>alert('Invalid payment ID or status.');</script>";
        }
    }
    

    // Close the database connection
    $conn->close();
?>
