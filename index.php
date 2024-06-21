<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check in vendors table
    $sql = "SELECT * FROM vendors WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            if ($row['is_admin']) {
                $_SESSION['admin'] = true;
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $_SESSION['vendor_id'] = $row['id'];
                header("Location: punch_in.php");
                exit();
            }
        } else {
            $error_message = "Invalid Password!";
        }
    } else {
        $error_message = "No user found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
       body {
            background-image: url('canteen.jpg'); /* Replace 'path_to_your_image.jpg' with your image path */
            background-size: cover; /* Cover the entire background */
            background-position: center; /* Center the background image */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
        }
        .card {
            background-color: rgba(255, 255, 255, 0.8); /* White background with transparency */
            backdrop-filter: blur(0px); /* Optional: adds blur effect to the background */
            border-radius: 10px; /* Rounded corners for the card */
            padding: 20px;
            max-width: 400px; /* Adjust as needed */
            width: 100%;
            margin-top: -200px; /* Adjust top margin as needed */
        }
        .card-header {
            background-color: transparent; /* Make card header transparent */
            border-bottom: none; /* Remove border */
        }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="text-center">Login Page</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
            <a href="register.php" class="btn btn-secondary btn-block">Register as Vendor</a>
            <a href="register_admin.php" class="btn btn-secondary btn-block">Register as Admin</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
