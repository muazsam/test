<?php
    include('admin-database.php');
    include('connect.php');

    $sql = "SELECT bv.vacant_id, b.barber_fname, b.barber_lname, bv.vacant_startdate, bv.vacant_enddate 
            FROM barber_vacant bv
            JOIN barber b ON bv.barber_id = b.barber_id";
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
    <title>Update Barber Availability</title>
    <link rel="stylesheet" href="createbarber-admin.css">
    <script>
        function updateForm() {
    const operation = document.getElementById('operation').value;
    const idField = document.getElementById('id-field');
    const availabilityFields = document.getElementById('availability-fields');
    const barberEmailField = document.querySelector("input[name='ulEmail']");

    if (operation === 'delete') {
        idField.style.display = 'block';
        availabilityFields.style.display = 'none';
    } 
    else if (operation === 'update') {
        idField.style.display = 'block';
        availabilityFields.style.display = 'block';
        barberEmailField.style.display = 'none';
    }
    else {
        idField.style.display = 'none';
        availabilityFields.style.display = 'block';
        barberEmailField.style.display = 'block';
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
                    <li><a href="admin-reviewcust.php" class="<?php echo ($currentPage == 'admin-barbermonitor.php') ? 'active' : ''; ?>">Booking Status</a></li>
                    <li><a href="user-logout.php">Log Out</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <h1>Barber Vacant</h1>
        <div class="login-container">
            <div class="login-form">
                <form method="POST">
                    <label for="operation">Choose Operation:</label>
                    <select id="operation" name="operation" onchange="updateForm()" required>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                    </select>

                    <div id="id-field" style="margin-top: 10px;">
                        <input type="text" class="rounded-textbox" placeholder="Vacant ID" name="vacant_id" >
                    </div>

                    <div id="availability-fields">
                        <input type="email" class="rounded-textbox" placeholder="Barber Email" name="ulEmail" >
                        <label>
                            Start Date: 
                            <input type="date" class="rounded-textbox" name="start_date" id="start_date" min="<?php echo date('Y-m-d'); ?>" >
                        </label>
                        <label>
                            End Date: 
                            <input type="date" class="rounded-textbox" name="end_date" id="end_date" min="<?php echo date('Y-m-d'); ?>">
                        </label>
                    </div>

                    <div>
                        <input type="submit" id="submit-button" class="rounded-button" value="Create" name="update-availability-btn">
                    </div>

                    <?php if (!empty($error)): ?>
                        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <h2>List Date of Barber Vacant</h2>
        <div class="search-container">
            <input type="text" id="searchBarber" onkeyup="filterTable('barberTable', this.value)" placeholder="Search for barber by name...">
        </div>
        <table id="barberTable">
            <tr>
                <th>Vacant ID</th>
                <th>Barber Name</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
            <?php
                // Display barber availability
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['vacant_id']) . "</td>
                                <td>" . htmlspecialchars($row['barber_fname'] . ' ' . $row['barber_lname']) . "</td>
                                <td>" . htmlspecialchars($row['vacant_startdate']) . "</td>
                                <td>" . htmlspecialchars($row['vacant_enddate']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No availability found.</td></tr>";
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


    const today = new Date();
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    // Set the minimum date for the start date
    startDateInput.min = today.toISOString().split('T')[0];

    // Disable end date until a start date is selected
    endDateInput.disabled = true;

    startDateInput.addEventListener('change', function () {
        if (startDateInput.value) {
            // Enable the end date field and set its minimum date
            endDateInput.disabled = false;
            endDateInput.min = startDateInput.value;
        } else {
            // Disable the end date if no start date is selected
            endDateInput.disabled = true;
            endDateInput.value = ""; // Clear the end date value
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
    // Initialize form state
    updateForm();
</script>
</html>