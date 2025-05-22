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

function getCustomerBalance($conn, $email) {
    $result = mysqli_query($conn, "SELECT balance FROM customer WHERE email='$email'");
    $row = mysqli_fetch_assoc($result);
    return $row['balance'];
}

function getRoomStatus($conn, $room_id) {
    $result = mysqli_query($conn, "SELECT status FROM room WHERE room_id='$room_id'");
    $row = mysqli_fetch_assoc($result);
    return $row['status'];
}

$customer_query = mysqli_query($conn, "SELECT customer_id FROM customer WHERE email = '$email'");
if ($customer_query) {
    $customer_data = mysqli_fetch_assoc($customer_query);
    $customer_id = $customer_data['customer_id'];
} else {
    die('Failed to fetch customer data.');
}

if (isset($_POST['reserve'])) {
    $room_id = $_POST['room_id'];
    $room_price = $_POST['room_price'];
    $room_image = $_POST['room_image'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $reserve_days = $_POST['reserve_days'];

    $customer_balance = getCustomerBalance($conn, $email);
    $room_status = getRoomStatus($conn, $room_id);

    $select_reservation = mysqli_query($conn, "SELECT * FROM reservation WHERE room_id = '$room_id' AND customer_id = '$customer_id'") or die('query failed');
    
    if ($room_status != 'Available') {
        $message[] = 'ROOM ALREADY RESERVED.';
    } elseif ($customer_balance >= $room_price) {
        mysqli_query($conn, "INSERT INTO reservation(customer_id, room_id, payment_amount, image, pickup_date, return_date) 
        VALUES('$customer_id', '$room_id', '$room_price', '$room_image','$pickup_date' ,'$return_date')") or die('query failed');
        $new_balance = $customer_balance - $room_price;
        mysqli_query($conn, "UPDATE customer SET balance = '$new_balance' WHERE email = '$email'");
        mysqli_query($conn, "UPDATE room SET status= 'Reserved' WHERE room_id = '$room_id'");
        $message[] = 'Room reserved successfully! Balance updated.';
    } else {
        $message[] = 'Insufficient balance! Please add more money to your account.';
    }
}

$search_condition = "1=1";

if (isset($_POST['search_room']) || isset($_POST['apply_filters'])) {
    if (isset($_POST['search_model']) && !empty($_POST['search_model'])) {
        $search_query = mysqli_real_escape_string($conn, $_POST['search_model']);
        $search_condition .= " AND (room_id LIKE '%$search_query%' OR price LIKE '%$search_query%')";
    }
    
    if (isset($_POST['status_filter']) && !empty($_POST['status_filter'])) {
        $status = mysqli_real_escape_string($conn, $_POST['status_filter']);
        $search_condition .= " AND status = '$status'";
    }
    
    if (isset($_POST['min_price'], $_POST['max_price']) && 
        $_POST['min_price'] !== '' && $_POST['max_price'] !== '') {
        $min_price = floatval($_POST['min_price']);
        $max_price = floatval($_POST['max_price']);
        $search_condition .= " AND price BETWEEN $min_price AND $max_price";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/stylehome.css">
    <title>Room Reservation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;1,200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/brands.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>
        .filter-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            width: 300px;
        }

        .filter-modal h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .filter-modal .filter-group {
            margin-bottom: 15px;
        }

        .filter-modal label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .filter-modal select,
        .filter-modal input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filter-modal .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .filter-modal button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter-modal .apply-btn {
            background: #4CAF50;
            color: white;
        }

        .filter-modal .cancel-btn {
            background: #f44336;
            color: white;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .filter-btn {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<div class="message" onclick="this.remove();">' . $msg . '</div>';
    }
}
?>

<header>
    <a href="#" class="logo"><img src="images/logo1.jpg" alt="Logo"></a>
    <u1 class="navmenu">
        <li><a href="#">Home</a></li>
        <li><a href="customer.php">Account Info</a></li>
    </u1>

    <div class="nav-icon">
       <div class="flexx">
          <a href="login.php" class="btn">login</a>
          <a href="register.php" class="option-btn">register</a>
          <a href="customer.php?logout=<?php echo $email; ?>" onclick="return confirm('are your sure you want to logout?');" class="delete-btn">logout</a>
       </div>
    </div>

    <div class="search-section">
        <form method="post" class="search-form" action="">
            <input type="text" id="searchModel" name="search_model" placeholder="Search rooms...">
            <input type="submit" value="Search" name="search_room" class="btn">
            <button type="button" class="filter-btn" onclick="openFilterModal()">
                <i class='bx bx-filter-alt'></i> Filter
            </button>
        </form>
    </div>
</header>

<!-- Filter Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeFilterModal()"></div>
<div class="filter-modal" id="filterModal">
    <h3>Filter Rooms</h3>
    <form method="post" id="filterForm">
        <div class="filter-group">
            <label for="status_filter">Status:</label>
            <select name="status_filter" id="status_filter">
                <option value="">All Statuses</option>
                <option value="Available">Available</option>
                <option value="Reserved">Reserved</option>
                <option value="Maintenance">Under Maintenance</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="min_price">Minimum Price:</label>
            <input type="number" id="min_price" name="min_price" min="0">
        </div>

        <div class="filter-group">
            <label for="max_price">Maximum Price:</label>
            <input type="number" id="max_price" name="max_price" min="0">
        </div>

        <div class="buttons">
            <button type="button" class="cancel-btn" onclick="closeFilterModal()">Cancel</button>
            <button type="submit" name="apply_filters" class="apply-btn">Apply Filters</button>
        </div>
    </form>
</div>

<section class="main-home">
    <div class="main-text">
        <h5>Luxury Rooms</h5>
        <h1>New Rooms <br> new year 2025</h1>
        <p>Experience the best stays with us</p>
        <a href="#" class="main-btn">Let's Start <i class='bx bx-right-arrow-alt'></i></a>
    </div>
    <div class="down-arrow">
        <a href="#trending" class="down"><i class='bx bx-down-arrow-alt'></i></a>
    </div>
</section>

<section class="trending-product" id="trending">
    <div class="center-text">
        <h2>Our Comfortable <span>Rooms</span></h2>
    </div>
    <div class="products">
    <?php
        $select_room = mysqli_query($conn, "SELECT * FROM room WHERE $search_condition") or die('query failed');
        if (mysqli_num_rows($select_room) > 0) {
            while ($fetch_room = mysqli_fetch_assoc($select_room)) {
    ?>
    <div class="row" id="room_<?php echo $fetch_room['room_id']; ?>">
        <form method="post" class="box" action="">
            <img src="image/<?php echo $fetch_room['image']; ?>" alt="">
            <div class="product-text"></div>
            <div class="heart-icon">
                <i class='bx bx-heart'></i>
            </div>
            <div class="ratting">
                <i class='bx bx-star'></i>
                <i class='bx bx-star'></i>
                <i class='bx bx-star'></i>
                <i class='bx bx-star'></i>
                <i class='bx bxs-star-half'></i>
            </div>
            <div class="info">       
                <div class="room_id">Room ID: <?php echo $fetch_room['room_id']; ?></div>
                <div class="status">Status: <?php echo $fetch_room['status']; ?></div>
                <div class="price">Price: $<?php echo $fetch_room['price']; ?>/-</div>

                <div class="date-picker">
                    <label for="pickupDate">Pickup Date:</label>
                    <input type="date" id="pickupDate" name="pickup_date" required>
                    <i class="bx bx-calendar"></i>
                </div>

                <div class="date-picker">
                    <label for="returnDate">Return Date:</label>
                    <input type="date" id="returnDate" name="return_date" required>
                    <i class="bx bx-calendar"></i>
                </div>

                <div class="reserve-days-difference">
                    <label for="reserveDays">Reserve Days:</label>
                    <input type="text" id="reserveDays" name="reserve_days" readonly>
                    <i class="bx bx-time"></i>
                </div>

                <input type="hidden" name="room_image" value="<?php echo $fetch_room['image']; ?>">
                <input type="hidden" name="room_id" value="<?php echo $fetch_room['room_id']; ?>">
                <input type="hidden" name="room_price" value="<?php echo $fetch_room['price']; ?>">
                <input type="submit" value="Reserve" name="reserve" class="btn">
            </div>
        </form>
    </div>
    <?php
            }
        }
    ?>
    </div>
</section>

<script src="java.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.date-picker #pickupDate, .date-picker #returnDate').forEach(function (dateInput) {
        dateInput.addEventListener("change", updateRentDays);
    });

    function updateRentDays() {
        document.querySelectorAll('form').forEach(function (form) {
            const pickupDateInput = form.querySelector(".date-picker #pickupDate");
            const returnDateInput = form.querySelector(".date-picker #returnDate");
            const reserveDaysInput = form.querySelector(".reserve-days-difference #reserveDays");

            if (pickupDateInput && returnDateInput && reserveDaysInput) {
                const pickupDate = new Date(pickupDateInput.value);
                const returnDate = new Date(returnDateInput.value);

                if (!isNaN(pickupDate.getTime()) && !isNaN(returnDate.getTime()) && returnDate >= pickupDate) {
                    const timeDiff = returnDate.getTime() - pickupDate.getTime();
                    const rentDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
                    reserveDaysInput.value = rentDays;
                } else {
                    reserveDaysInput.value = '';
                }
            }
        });
    }
});

function openFilterModal() {
    document.getElementById('filterModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

function closeFilterModal() {
    document.getElementById('filterModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}
</script>

</body>
</html>