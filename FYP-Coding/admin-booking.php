<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Barbers</title>
    <link rel="stylesheet" href="booking.css">
</head>
<body>
    <?php
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
    <h1>Manage Barbers</h1>
    <div id="barber-list" class="barber-list">
        <div class="container-barber">
            <img src="test.jpeg" class="barber-pic">
            <ul>
                <li><strong>Name:</strong> <span class="barber-name">John Doe</span></li><br>
                <li><strong>Age:</strong> <span class="barber-age">30</span></li><br>
                <li><strong>Experience:</strong> <span class="barber-description">Expert in fades and shaves.</span></li>
            </ul>
            <div class="container-button">
                <button class="rounded-button edit-button">Edit</button>
                <button class="rounded-button delete-button">Delete</button>
            </div>
        </div>
    </div>
    <center><button id="add-barber" class="button-addbarber">Add Barber</button></center>

    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) { // Change 50 to the scroll distance you want
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        document.getElementById('add-barber').addEventListener('click', function() {
            const barberList = document.getElementById('barber-list');
            const newBarber = createBarberElement('New Barber', 0o0, 'New barber description.');
            barberList.appendChild(newBarber);
            attachEditDeleteHandlers(newBarber);
        });

        function createBarberElement(name, age, description) {
            const barberElement = document.createElement('div');
            barberElement.className = 'container-barber';
            barberElement.innerHTML = `
                <img src="test.jpeg" class="barber-pic">
                <ul>
                    <li><strong>Name:</strong> <span class="barber-name">${name}</span></li><br>
                    <li><strong>Age:</strong> <span class="barber-age">${age}</span></li><br>
                    <li><strong>Experience:</strong> <span class="barber-description">${description}</span></li>
                </ul>
                <div class="container-button">
                    <button class="rounded-button edit-button">Edit</button>
                    <button class="rounded-button delete-button">Delete</button>
                </div>
            `;
            return barberElement;
        }

        function attachEditDeleteHandlers(barberElement) {
            const editButton = barberElement.querySelector('.edit-button');
            const deleteButton = barberElement.querySelector('.delete-button');

            editButton.addEventListener('click', function() {
                const name = barberElement.querySelector('.barber-name');
                const age = barberElement.querySelector('.barber-age');
                const description = barberElement.querySelector('.barber-description');

                barberElement.innerHTML = `
                    <div class="edit-container">
                        <input type="text" value="${name.textContent}" class="name-input">
                        <input type="number" value="${age.textContent}" class="age-input">
                        <textarea class="description-input">${description.textContent}</textarea>
                        <button class="rounded-button save-button">Save</button>
                    </div>
                `;

                const saveButton = barberElement.querySelector('.save-button');
                saveButton.addEventListener('click', function() {
                    name.textContent = barberElement.querySelector('.name-input').value;
                    age.textContent = barberElement.querySelector('.age-input').value;
                    description.textContent = barberElement.querySelector('.description-input').value;

                    barberElement.innerHTML = `
                        <img src="test.jpeg" class="barber-pic">
                        <ul>
                            <li><strong>Name:</strong> <span class="barber-name">${name.textContent}</span></li><br>
                            <li><strong>Age:</strong> <span class="barber-age">${age.textContent}</span></li><br>
                            <li><strong>Description:</strong> <span class="barber-description">${description.textContent}</span></li>
                        </ul>
                        <div class="container-button">
                            <button class="rounded-button edit-button">Edit</button>
                            <button class="rounded-button delete-button">Delete</button>
                        </div>
                    `;
                    attachEditDeleteHandlers(barberElement);
                });
            });

            deleteButton.addEventListener('click', function() {
                barberElement.remove();
            });
        }

        const initialBarber = document.querySelector('.container-barber');
        attachEditDeleteHandlers(initialBarber);
    </script>
</body>
</html>