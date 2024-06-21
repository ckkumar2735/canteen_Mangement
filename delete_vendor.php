<?php
session_start();
include('db.php'); // Include your database connection script

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_POST['vendor_id'];

    // Delete vendor from the database
    $sql_delete_vendor = "DELETE FROM vendors WHERE id = $vendor_id";

    if ($conn->query($sql_delete_vendor) === TRUE) {
        // Redirect back to admin_dashboard.php or wherever appropriate
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error deleting vendor: " . $conn->error;
    }
}
?>
