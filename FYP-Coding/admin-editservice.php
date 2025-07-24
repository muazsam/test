<?php
    include('admin-database.php');
    include('connect.php');
    $sql = "SELECT service_id, service_name, service_price FROM service";
    $result = mysqli_query($conn, $sql);

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
    <title>Set Up Barber Availability</title>
    <link rel="stylesheet" href="createbarber-admin.css">
    <script>
        function updateForm() {
            const operation = document.getElementById('operation').value;
            const serviceFields = document.getElementById('service-fields');
            const idField = document.getElementById('id-field');

            if (operation === 'delete') {
                idField.style.display = 'block';
                serviceFields.style.display = 'none';
            } 
            else if(operation === 'update'){
                idField.style.display = 'block';
                serviceFields.style.display = 'block';
            }
            else {
                idField.style.display = 'none';
                serviceFields.style.display = 'block';
            }

            const submitButton = document.getElementById('submit-button');
            submitButton.value = operation.charAt(0).toUpperCase() + operation.slice(1);
        }
    </script>
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
    <h1>Edit Services</h1>
    <div class="login-container">
        <div class="login-form">
            <form method="POST" enctype="multipart/form-data">
                <label for="operation">Choose Operation:</label>
                <select id="operation" name="operation" onchange="updateForm()" required>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                </select>

                <div id="id-field" style="margin-top: 10px;">
                    <input type="text" class="rounded-textbox" placeholder="Service ID" name="service-id" >
                </div>

                <div id="service-fields">
                    <input type="text" class="rounded-textbox" placeholder="Service Name" name="service-name">
                    <input type="text" class="rounded-textbox" placeholder="Insert the price for the service" name="service-price">
                    <label>Insert a picture of the service:</label>
                    <input name="service_pic" type="file" class="rounded-textbox" accept="image/*">
                </div>

                <div>
                    <input type="submit" id="submit-button" class="rounded-button" value="Create" name="submit-btn">
                </div>

                <?php if (!empty($error)): ?>
                    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <h2>Current Available Services</h2>
    <table border="1" cellspacing="0" cellpadding="10">
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Price</th>
        </tr>
        <?php
            // Display services
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['service_id']) . "</td>
                            <td>" . htmlspecialchars($row['service_name']) . "</td>
                            <td>" . htmlspecialchars($row['service_price']) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No services found.</td></tr>";
            }
        ?>
    </table>
    </main>
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

    // Initialize form state
    updateForm();
</script>
</html>