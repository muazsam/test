<?php
    include('admin-database.php');
    include('connect.php');

    // Query for barber accounts
    $queryBarber = "SELECT barber_fname, barber_lname, barber_id, barber_email, role FROM barber WHERE role = 'barber'";
    $resultBarber = mysqli_query($conn, $queryBarber);

    // Query for admin accounts
    $queryAdmin = "SELECT barber_fname, barber_lname, barber_id, barber_email, role FROM barber WHERE role = 'admin'";
    $resultAdmin = mysqli_query($conn, $queryAdmin);

    $queryCustomer = "SELECT customer_fname, customer_lname, customer_id, customer_email FROM customer";
    $resultCustomer = mysqli_query($conn, $queryCustomer);

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
        <title>Delete Barber/Admin Account</title>
        <link rel="stylesheet" href="createbarber-admin.css">
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
        <h1>Delete Account</h1>
        <div class="login-container">
            <div class="login-form">
                <form id="deleteForm" action="" method="post">
                    <b>Insert barber/customer Email to delete an account</b>
                    <input name="user_email" type="text" class="rounded-textbox" required>
                    <center><button type="submit" id="delete_btn" name="delete_btn" value="submitted" class="rounded-button" onclick="return confirm('Are you sure you want to delete the account?')">Delete Account</button></center>
                </form>
                <p>Note: Only one account can be deleted at a time.</p>
            </div>
        </div>

        <h2>Current Available Barber Accounts</h2>
        <div class="search-container">
            <input type="text" id="searchBarber" onkeyup="filterTable('barberTable', this.value)" placeholder="Search for barber by name...">
        </div>
        <table id="barberTable">
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Email</th>
        </tr>
            <?php
                // Display barber accounts
                if ($resultBarber && mysqli_num_rows($resultBarber) > 0) {
                    while ($row = mysqli_fetch_assoc($resultBarber)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['barber_id']) . "</td>
                                <td>" . htmlspecialchars($row['barber_fname']) . " " . htmlspecialchars($row['barber_lname']) . "</td>
                                <td>" . htmlspecialchars($row['barber_email']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No barber accounts found.</td></tr>";
                }
            ?>
        </table>

        <h2>Current Available Admin Accounts</h2>
        <div class="search-container">
            <input type="text" id="searchAdmin" onkeyup="filterTable('adminTable', this.value)" placeholder="Search for admin by name...">
        </div>
        <table id="adminTable">
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Email</th>
            </tr>
            <?php
                // Display admin accounts
                if ($resultAdmin && mysqli_num_rows($resultAdmin) > 0) {
                    while ($row = mysqli_fetch_assoc($resultAdmin)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['barber_id']) . "</td>
                                <td>" . htmlspecialchars($row['barber_fname']) . " " . htmlspecialchars($row['barber_lname']) . "</td>
                                <td>" . htmlspecialchars($row['barber_email']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No admin accounts found.</td></tr>";
                }
            ?>
        </table>

        <h2>Current Available Customer Accounts</h2>
        <div class="search-container">
            <input type="text" id="searchCustomer" onkeyup="filterTable('customerTable', this.value)" placeholder="Search for customer by name...">
        </div>
        <table id="customerTable">
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Email</th>
            </tr>
            <?php
                if ($resultCustomer && mysqli_num_rows($resultCustomer) > 0) {
                    while ($row = mysqli_fetch_assoc($resultCustomer)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['customer_id']) . "</td>
                                <td>" . htmlspecialchars($row['customer_fname']) . " " . htmlspecialchars($row['customer_lname']) . "</td>
                                <td>" . htmlspecialchars($row['customer_email']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No admin accounts found.</td></tr>";
                }
            ?>
        </table>
    </main>
    </body>
    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) { // Change 50 to the scroll distance you want
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

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
    </script>
</html>
