<?php
include 'config.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['email'] != 'admin@admin.com') {
    header('location: login.php');
    exit();
}

function generateUniqueFilename($originalFilename)
{
    $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
    $uniqueFilename = uniqid() . '.' . $extension;
    return $uniqueFilename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $checkQuery = "SELECT room_id FROM `room` WHERE room_id = '$room_id'";
    $result = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($result) > 0) {
        $error = "Room ID already exists. Please use a different ID.";
    } else {
        $imageFilename = $_FILES['image']['name'];
        $imageTempPath = $_FILES['image']['tmp_name'];
        $imageUniqueFilename = generateUniqueFilename($imageFilename);

        $uploadDirectory = 'image/'; 

        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $imageUploadPath = $uploadDirectory . $imageUniqueFilename;

        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        $fileExtension = strtolower(pathinfo($imageFilename, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            if (move_uploaded_file($imageTempPath, $imageUploadPath)) {
                $insertQuery = "INSERT INTO `room` (room_id, price, status, image) VALUES ('$room_id', '$price', '$status', '$imageUniqueFilename')";
                if (mysqli_query($conn, $insertQuery)) {
                    $message = "Room added successfully!";
                } else {
                    $error = "Error adding room: " . mysqli_error($conn);
                }
            } else {
                $error = "Error uploading image. Path: " . $imageUploadPath;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $roomId = mysqli_real_escape_string($conn, $_POST['room_id']);
    $newStatus = mysqli_real_escape_string($conn, $_POST['new_status']);

    $updateQuery = "UPDATE `room` SET status = '$newStatus' WHERE room_id = '$roomId'";
    if (mysqli_query($conn, $updateQuery)) {
        $messageUpdateStatus = "Room status updated successfully!";
    } else {
        $errorUpdateStatus = "Error updating room status: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/modify_room.css">
    <title>Manage Rooms</title>
</head>

<body>

    <header>
        <h1>Welcome, Admin!</h1>
        <a href="admin_dashboard.php">Go to Admin Dashboard</a>
    </header>

    <div class="wrapper">
        <div id="content">
            <h2>Add or Update Room Status</h2>

            <?php
            if (isset($message)) {
                echo '<div class="alert alert-success" role="alert">' . $message . '</div>';
            } elseif (isset($error)) {
                echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            }

            if (isset($messageUpdateStatus)) {
                echo '<div class="alert alert-success" role="alert">' . $messageUpdateStatus . '</div>';
            } elseif (isset($errorUpdateStatus)) {
                echo '<div class="alert alert-danger" role="alert">' . $errorUpdateStatus . '</div>';
            }
            ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="room_id">Room ID:</label>
                    <input type="text" class="form-control" id="room_id" name="room_id" required>
                </div>

                <div class="form-group">
                    <label for="price">Room Price:</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>

                <div class="form-group">
                    <label for="status">Room Status:</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">Room Image:</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                    <small class="form-text text-muted">Allowed formats: JPG, JPEG, PNG, GIF</small>
                </div>
                <button type="submit" class="btn btn-primary" name="add_room">Add Room</button>
            </form>

            <hr>

            <h3>Update Room Status</h3>
            <form method="post">
                <div class="form-group">
                    <label for="room_id_update">Select Room to Update:</label>
                    <select class="form-control" id="room_id_update" name="room_id" required>
                        <?php
                        $selectQuery = "SELECT * FROM `room`";
                        $result = mysqli_query($conn, $selectQuery);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="' . $row['room_id'] . '">Room ID: ' . $row['room_id'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="new_status">New Status:</label>
                    <select class="form-control" id="new_status" name="new_status" required>
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="update_status">Update Status</button>
            </form>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

</body>

</html>