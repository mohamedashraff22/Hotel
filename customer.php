<?php

include 'config.php';
session_start();
$email = $_SESSION['email'];

if (!isset($email)) {
    header('location:login.php');
}

if (isset($_GET['logout'])) {
    unset($email);
    session_destroy();
    header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>

    <link rel="stylesheet" href="CSS/style2.css">
</head>

<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<div class="message" onclick="this.remove();">' . $msg . '</div>';
    }
}
?>

<div class="user_container">

    <div class="user-profile">

        <?php
        $select_user = mysqli_query($conn, "SELECT * FROM `customer` WHERE email = '$email'") or die('query failed');
        if (mysqli_num_rows($select_user) > 0) {
            $fetch_user = mysqli_fetch_assoc($select_user);
            $customer_id = $fetch_user['customer_id'];
        } else {
            echo '<p class="error">Customer not found.</p>';
            exit();
        }
        ?>

        <p> First Name: <span><?php echo $fetch_user['fname']; ?></span> </p>
        <p> Last Name: <span><?php echo $fetch_user['lname']; ?></span> </p>
        <p> Country: <span><?php echo $fetch_user['country']; ?></span> </p>
        <p> Phone: <span><?php echo $fetch_user['phone']; ?></span> </p>
        <p> Balance: <span>$<?php echo $fetch_user['balance']; ?></span> </p>
        <p> Email: <span><?php echo $fetch_user['email']; ?></span> </p>

        <div class="flex">
            <a href="home.php" class="btn">Rooms</a>
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="option-btn">Register</a>
            <a href="customer.php?logout=<?php echo $email; ?>" onclick="return confirm('Are you sure you want to logout?');" class="delete-btn">Logout</a>
        </div>
        <button class="btn" id="toggleReservations">My Reservations</button>


        <div class="user-reservations" id="reservations" style="display: none;">
            <h2>Your Reservations</h2>
            <?php
            $select_reservations = mysqli_query($conn, "SELECT r.*, rm.price AS room_price 
                FROM `reservation` r 
                JOIN `room` rm ON r.room_id = rm.room_id 
                WHERE r.customer_id = '$customer_id'") or die('query failed');

            if (mysqli_num_rows($select_reservations) > 0) {
                while ($reservation = mysqli_fetch_assoc($select_reservations)) {
                    ?>
                    <div class="reservation">
                        <p>Reservation ID: <span><?php echo $reservation['reservation_id']; ?></span></p>
                        <p>Room Price: <span>$<?php echo $reservation['room_price']; ?></span></p>
                        <p>Payment Amount: <span>$<?php echo $reservation['payment_amount']; ?></span></p>
                        <p>Pickup Date: <span><?php echo $reservation['pickup_date']; ?></span></p>
                        <p>Return Date: <span><?php echo $reservation['return_date']; ?></span></p>
                        <p>Payment Date: <span><?php echo $reservation['payment_date']; ?></span></p>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No reservations found.</p>';
            }
            ?>
        </div>

    </div>

</div>

<script>
    document.getElementById('toggleReservations').addEventListener('click', function () {
        const reservations = document.getElementById('reservations');
        if (reservations.style.display === 'none' || reservations.style.display === '') {
            reservations.style.display = 'block';
        } else {
            reservations.style.display = 'none';
        }
    });
</script>

</body>

</html>
