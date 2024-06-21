<?php
session_start();
include('db.php');

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Delete punch record functionality
if (isset($_POST['delete_record'])) {
    $record_id = $_POST['record_id'];
    $sql_delete_record = "DELETE FROM punch_records WHERE id = $record_id";
    if ($conn->query($sql_delete_record) === TRUE) {
        // Reload page after deletion
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch punch records with location and canteen information
$filter_conditions = [];
// Start Date filter
if (!empty($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
    $filter_conditions[] = "pr.timestamp >= '$start_date'";
}

// End Date filter
if (!empty($_GET['end_date'])) {
    $end_date = $_GET['end_date'];
    $filter_conditions[] = "pr.timestamp <= '$end_date'";
}

// Location filter
if (!empty($_GET['location'])) {
    $location = $_GET['location'];
    $filter_conditions[] = "(l.name LIKE '%$location%' OR c.name LIKE '%$location%')";
}

// Construct WHERE clause for filters
$where_clause = '';
if (!empty($filter_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $filter_conditions);
}

$sql_punch_records = "
    SELECT pr.id, pr.vendor_id, pr.timestamp, pr.time_in, pr.time_out, l.name as location, c.name as canteen
    FROM punch_records pr
    LEFT JOIN locations l ON pr.location_id = l.id
    LEFT JOIN canteens c ON pr.canteen_id = c.id
    $where_clause
    ORDER BY pr.timestamp DESC";

$result_punch_records = $conn->query($sql_punch_records);

// Fetch vendor records with location information
$sql_vendors = "
    SELECT v.id, v.name, l.name as location
    FROM vendors v
    LEFT JOIN locations l ON v.location_id = l.id
    ORDER BY v.name ASC";
$result_vendors = $conn->query($sql_vendors);

// Fetch punch-in counts per month
$sql_punch_in_counts = "
    SELECT MONTH(timestamp) as month, COUNT(*) as count 
    FROM punch_records 
    WHERE time_in IS NOT NULL
    GROUP BY MONTH(timestamp)";

$result_punch_in_counts = $conn->query($sql_punch_in_counts);
$punch_in_counts = [];
while ($row = $result_punch_in_counts->fetch_assoc()) {
    $punch_in_counts[$row['month']] = $row['count'];
}

// Fetch punch-out counts per month
$sql_punch_out_counts = "
    SELECT MONTH(timestamp) as month, COUNT(*) as count 
    FROM punch_records 
    WHERE time_out IS NOT NULL
    GROUP BY MONTH(timestamp)";

$result_punch_out_counts = $conn->query($sql_punch_out_counts);
$punch_out_counts = [];
while ($row = $result_punch_out_counts->fetch_assoc()) {
    $punch_out_counts[$row['month']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js CDN -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            /* Example font family */
            color: #444;
            /* Dark gray text color */
            line-height: 1.6;
            /* Improved line height for readability */
        }

        h2 {
            font-size: 36px;
            /* Larger main heading */
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-top: 40px;
            margin-bottom: 30px;
            padding: 15px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);
        }

        h4 {
            font-size: 24px;
            /* Smaller subheading */
            font-weight: bold;
            color: #555;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f0f0f0;
            /* Light background for subheadings */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Links */
        a {
            color: #2575fc;
            /* Blue link color */
            text-decoration: none;
            transition: color 0.3s ease;
            /* Smooth color transition on hover */
        }

        a:hover {
            color: #6a11cb;
            /* Darker blue on hover */
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 10px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #2575fc;
            /* Blue button */
            color: white;
        }

        .btn-primary:hover {
            background-color: #6a11cb;
            /* Darker blue on hover */
        }


        .card {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .logout-btn {
            margin-top: 10px;
        }

        .delete-btn {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h2 class="text-center mb-4">Admin Dashboard</h2>
            <form action="?logout=true" method="post">
                <button type="submit" class="btn btn-danger btn-sm float-right logout-btn">Logout</button>
            </form>
        </div>

        <!-- Vendor Management Section -->
        <div class="card">
            <h4 class="mb-3 text-center">Admin/Vendor Login Management</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <!-- <th>Location</th> -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_vendors->num_rows > 0) : ?>
                        <?php while ($row = $result_vendors->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <!-- <td><?php echo isset($row['location']) ? $row['location'] : 'N/A'; ?></td> -->
                                <td>
                                    <!-- Add edit and delete buttons for vendor management -->
                                    <form action="edit_vendor.php" method="post" style="display:inline;">
                                        <input type="hidden" name="vendor_id" value="<?php echo $row['id']; ?>">
                                        <!-- <button type="submit" class="btn btn-warning btn-sm">Edit</button> -->
                                    </form>
                                    <form action="delete_vendor.php" method="post" style="display:inline;">
                                        <input type="hidden" name="vendor_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3">No vendors found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Punch Records Section -->
        <div class="card">
            <h4 class="mb-3">Punch Records</h4>
            <!-- Add filters for punch records -->
            <form class="form-inline mb-3" method="get">
                <label for="start_date" class="mr-2">Start Date:</label>
                <input type="date" class="form-control mr-3" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">

                <label for="end_date" class="mr-2">End Date:</label>
                <input type="date" class="form-control mr-3" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">

                <label for="location" class="mr-2">Location:</label>
                <input type="text" class="form-control mr-3" id="location" name="location" value="<?php echo isset($_GET['location']) ? $_GET['location'] : ''; ?>">

                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Location</th>
                        <th>Canteen</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <!-- <th>Timestamp</th> -->
                        <th>Action</th>

                    </tr>
                </thead>

                <tbody>
                    <?php if ($result_punch_records->num_rows > 0) : ?>
                        <?php while ($row = $result_punch_records->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['vendor_id']; ?></td>
                                <td><?php echo isset($row['location']) ? $row['location'] : 'N/A'; ?></td>
                                <td><?php echo isset($row['canteen']) ? $row['canteen'] : 'N/A'; ?></td>
                                <td><?php echo isset($row['time_in']) ? $row['time_in'] : 'N/A'; ?></td>
                                <td><?php echo isset($row['time_out']) ? $row['time_out'] : 'N/A'; ?></td>
                                <!-- <td><?php echo $row['timestamp']; ?></td> -->

                                <!-- Add delete button -->
                                <td>
                                    <form action="" method="post" style="display:inline;">
                                        <input type="hidden" name="record_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_record" class="btn btn-danger btn-sm delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7">No punch records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>


            </table>
        </div>

        <!-- Graphs for Punch-In and Punch-Out -->
         <!-- Graph for Punch Activity -->
         <div class="card">
    <h4 class="mb-3">Punch Activity</h4>
    <canvas id="punchChart" width="400" height="200"></canvas>
</div>
    </div>

   
    </div>

    <script>
    // Data fetched from PHP
    const punch_in_counts = <?php echo json_encode($punch_in_counts); ?>;
    const punch_out_counts = <?php echo json_encode($punch_out_counts); ?>;
    
    // Define labels for months (assuming you want to show all months)
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    // Prepare punch-in and punch-out data
    const punchInData = [];
    const punchOutData = [];

    // Fill data arrays for each month
    for (let i = 1; i <= 12; i++) {
        punchInData.push(punch_in_counts[i] || 0); // Use 0 if no data exists for the month
        punchOutData.push(punch_out_counts[i] || 0); // Use 0 if no data exists for the month
    }

    // Define Chart.js data
    const punchData = {
        labels: months,
        datasets: [{
            label: 'Punch-In',
            backgroundColor: 'rgb(54, 162, 235)',
            borderColor: 'rgb(54, 162, 235)',
            data: punchInData,
        }, {
            label: 'Punch-Out',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: punchOutData,
        }]
    };

    // Draw the chart
    var ctx = document.getElementById('punchChart').getContext('2d');
    var punchChart = new Chart(ctx, {
        type: 'bar',
        data: punchData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>



</body>

</html>