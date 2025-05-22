<?php
include 'config.php';
session_start();
$email = $_SESSION['email'];

if (!isset($_SESSION['email']) || $_SESSION['email'] != 'admin@admin.com') {
    header('location: login.php');
}

function getCustomerReservations($conn, $customer_id)
{
    $query = "SELECT * FROM `reservation` WHERE customer_id = '$customer_id'";
    $result = mysqli_query($conn, $query) or die('Query failed: ' . mysqli_error($conn));
    return $result;
}

function getDailyPayments($conn, $search_date)
{
    $query = "SELECT DATE(payment_date) AS payment_date, SUM(payment_amount) AS total_payment FROM `reservation` WHERE DATE(payment_date) = '$search_date' GROUP BY DATE(payment_date)";
    $result = mysqli_query($conn, $query) or die('Query failed: ' . mysqli_error($conn));
    return $result;
}

if (isset($_POST['submit'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $reservations = getCustomerReservations($conn, $customer_id);
}

if (isset($_POST['submit_date'])) {
    $search_date = mysqli_real_escape_string($conn, $_POST['search_date']);
    $dailyPaymentsQuery = getDailyPayments($conn, $search_date);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <title>Admin Dashboard</title>
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">

                        <a href="add_room.php" class="btn">Go to Add Room</a>

                        <li class="nav-item">
                            <button class="nav-link btn btn-link" onclick="toggleVisibility('customer_section')">Customers</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn btn-link" onclick="toggleVisibility('room_section')">Rooms</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn btn-link" onclick="toggleVisibility('reservation_section')">Reservations</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn btn-link" onclick="toggleVisibility('payment_section')">Payments</button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-primary" onclick="toggleAllSections()">Toggle Reports</button>
                        </li>
                        <a href="login.php" class="btn">Go to Login</a>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

                <div id="customer_section" class="query-section" style="display: none;">
                    <h2>All Customers</h2>
                    <?php
                    $customerQuery = mysqli_query($conn, "SELECT * FROM `customer`") or die('Customer query failed');

                    if (mysqli_num_rows($customerQuery) > 0) {
                        echo '<ul>';
                        while ($customerData = mysqli_fetch_assoc($customerQuery)) {
                            echo '<li>';
                            echo '<strong>Name:</strong> ' . $customerData['fname'] . ' ' . $customerData['lname'] . '<br>';
                            echo '<strong>Email:</strong> ' . $customerData['email'] . '<br>';
                            echo '<strong>Phone:</strong> ' . $customerData['phone'] . '<br>';
                            echo '<strong>Country:</strong> ' . $customerData['country'] . '<br>';
                            echo '<strong>Balance:</strong> $' . $customerData['balance'] . '<br>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No customers found.</p>';
                    }
                    ?>
                </div>

                <div id="room_section" class="query-section" style="display: none;">
                    <h2>All Rooms</h2>
                    <?php
                    $roomQuery = mysqli_query($conn, "SELECT * FROM `room`") or die('Room query failed');
                    if (mysqli_num_rows($roomQuery) > 0) {
                        echo '<ul>';
                        while ($roomData = mysqli_fetch_assoc($roomQuery)) {
                            echo '<li>';
                            echo '<strong>Room Number:</strong> ' . $roomData['room_id'] . '<br>';
                            echo '<strong>Price:</strong> $' . $roomData['price'] . '<br>';
                            echo '<strong>Status:</strong> ' . $roomData['status'] . '<br>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No rooms found.</p>';
                    }
                    ?>
                </div>

                <div id="reservation_section" class="query-section" style="display: none;">
                    <h2>All Reservations</h2>
                    <?php
                    $reservationQuery = mysqli_query($conn, "SELECT * FROM `reservation` JOIN `room` ON room.room_id=reservation.room_id JOIN customer ON customer.customer_id=reservation.customer_id") or die('Reservation query failed');
                    if (mysqli_num_rows($reservationQuery) > 0) {
                        echo '<ul>';
                        while ($reservationData = mysqli_fetch_assoc($reservationQuery)) {
                            echo '<li>';
                            echo '<strong>Name:</strong> ' . $reservationData['fname'] . ' ' . $reservationData['lname'] . '<br>';
                            echo '<strong>Country:</strong> ' . $reservationData['country'] . '<br>';
                            echo '<strong>Room Number:</strong> ' . $reservationData['room_id'] . '<br>';
                            echo '<strong>Price:</strong> $' . $reservationData['price'] . '<br>';
                            echo '<strong>Payment Amount:</strong> ' . $reservationData['payment_amount'] . '<br>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No reservations found.</p>';
                    }
                    ?>
                </div>

                <div id="payment_section" class="query-section" style="display: none;">
                    <h2>All Payments</h2>
                    <?php
                    $paymentQuery = mysqli_query($conn, "SELECT * FROM `reservation` JOIN customer ON customer.customer_id=reservation.customer_id") or die('Payment query failed');
                    if (mysqli_num_rows($paymentQuery) > 0) {
                        echo '<ul>';
                        while ($paymentData = mysqli_fetch_assoc($paymentQuery)) {
                            echo '<li>';
                            echo '<strong>Name:</strong> ' . $paymentData['fname'] . ' ' . $paymentData['lname'] . '<br>';
                            echo '<strong>Payment Amount:</strong> ' . $paymentData['payment_amount'] . '<br>';
                            echo '<strong>Date:</strong>' . $paymentData['payment_date'] . '<br>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No payments found.</p>';
                    }
                    ?>
                </div>

                <div id="reservations_report" class="query-section" style="display: block;">
                    <h2>Reservations Report</h2>
                    <form method="post">
                        <label for="start_date">Enter Start Date:</label>
                        <input type="date" name="start_date" id="start_date" required>
                        <label for="end_date">Enter End Date:</label>
                        <input type="date" name="end_date" id="end_date" required>
                        <input type="submit" name="submit_date_range" value="Search Period">
                    </form>

                    <?php
                    if (isset($_POST['submit_date_range'])) {
                        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
                        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);

                        $reservationsQuery = "SELECT * FROM `reservation` 
                                             JOIN `room` ON room.room_id=reservation.room_id 
                                             JOIN customer ON customer.customer_id=reservation.customer_id
                                             WHERE pickup_date BETWEEN '$start_date' AND '$end_date'";
                        $reservationsResult = mysqli_query($conn, $reservationsQuery) or die('Reservation query failed');

                        if (mysqli_num_rows($reservationsResult) > 0) {
                            echo '<table class="table">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Name</th>';
                            echo '<th>Country</th>';
                            echo '<th>Room Number</th>';
                            echo '<th>Price</th>';
                            echo '<th>Payment Amount</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            while ($reservationData = mysqli_fetch_assoc($reservationsResult)) {
                                echo '<tr>';
                                echo '<td>' . $reservationData['fname'] . ' ' . $reservationData['lname'] . '</td>';
                                echo '<td>' . $reservationData['country'] . '</td>';
                                echo '<td>' . $reservationData['room_id'] . '</td>';
                                echo '<td>$' . $reservationData['price'] . '</td>';
                                echo '<td>' . $reservationData['payment_amount'] . '</td>';
                                echo '</tr>';
                            }

                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            echo '<p>No reservations found for the selected date range.</p>';
                        }
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        function toggleVisibility(sectionId) {
            const sections = document.querySelectorAll('.query-section');
            sections.forEach(section => {
                if (section.id === sectionId) {
                    section.style.display = section.style.display === 'none' ? 'block' : 'none';
                } else {
                    section.style.display = 'none';
                }
            });
        }

        function toggleAllSections() {
            const sections = document.querySelectorAll('.query-section');
            sections.forEach(section => {
                section.style.display = section.style.display === 'none' ? 'block' : 'none';
            });
        }
    </script>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <span class="text-muted">&copy; 2024 Your Hotel Management System. All rights reserved.</span>
        </div>
    </footer>

</body>
</html>
