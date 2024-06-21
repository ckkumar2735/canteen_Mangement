<?php
session_start();
include('db.php'); // Include your database connection script

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST['vendor_id'];

    // Fetch vendor details from the database
    $sql_fetch_vendor = "SELECT * FROM vendors WHERE id = $vendor_id";
    $result_vendor = $conn->query($sql_fetch_vendor);

    if ($result_vendor->num_rows > 0) {
        $vendor_data = $result_vendor->fetch_assoc();
        // Display a form with the existing vendor data for editing
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vendor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Edit Vendor</h2>
        <form action="update_vendor.php" method="post">
            <input type="hidden" name="vendor_id" value="<?php echo $vendor_data['id']; ?>">
            <div class="form-group">
                <label for="vendor_name">Vendor Name</label>
                <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="<?php echo $vendor_data['name']; ?>">
            </div>
            <!-- Other fields to edit -->
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>
<?php
    } else {
        echo "Vendor not found.";
    }
}
?>
