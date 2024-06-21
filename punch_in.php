<?php
session_start();
include('db.php');

if (!isset($_SESSION['vendor_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vendor_id = $_SESSION['vendor_id'];
    $location_id = $_POST['location'];
    $canteen_id = $_POST['canteen'];
    $punch_type = 'in';
    
    // Insert current timestamp into time_in column
    $sql = "INSERT INTO punch_records (vendor_id, location_id, canteen_id, punch_type, time_in) 
            VALUES ('$vendor_id', '$location_id', '$canteen_id', '$punch_type', CURRENT_TIMESTAMP())";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success mt-3' role='alert'>Punch-in recorded successfully!</div>";
    } else {
        echo "<div class='alert alert-danger mt-3' role='alert'>Error: " . $sql . "<br>" . $conn->error . "</div>";
    }
}

// Hardcoded location and canteen data for initial testing
$locations = [
    ['id' => 1, 'name' => 'Jamshedpur'],
    ['id' => 2, 'name' => 'Kalinganagar'],
    ['id' => 3, 'name' => 'Kharagpur']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Punch In</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('punchin.gif'); /* Replace 'punchin.gif' with your image path */
            background-size: cover; /* Cover the entire background */
            background-position: center; /* Center the background image */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
            margin: 0;
            padding: 0;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.8); /* White background with transparency */
            backdrop-filter: blur(0px); /* Optional: adds blur effect to the background */
            border-radius: 10px; /* Rounded corners for the card */
            padding: 20px;
            max-width: 400px; /* Adjust as needed */
            width: 100%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); /* Card shadow */
        }
        .card-header {
            background-color: transparent; /* Make card header transparent */
            border-bottom: none; /* Remove border */
        }
    </style>
</head>
<body>
    <div class="card mt-5 p-4">
        <h2 class="text-center mb-4">Punch In</h2>
        <form method="post">
            <div class="form-group">
                <label for="location">Location</label>
                <select name="location" id="location" class="form-control" required>
                    <option value="" disabled selected>Select Location</option>
                    <?php foreach($locations as $location) { ?>
                        <option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="canteen">Canteen</label>
                <select name="canteen" id="canteen" class="form-control" required>
                    <option value="" disabled selected>Select Canteen</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Punch In</button>
        </form>
        <a href="punch_out.php" class="btn btn-secondary btn-block mt-3">Punch Out</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('location').addEventListener('change', function() {
            var locationId = this.value;
            var canteenSelect = document.getElementById('canteen');
            canteenSelect.innerHTML = '<option value="" disabled selected>Select Canteen</option>';
            
            fetch('get_canteens.php?location_id=' + locationId)
                .then(response => response.json())
                .then(data => {
                    data.forEach(function(canteen) {
                        var option = document.createElement('option');
                        option.value = canteen.id;
                        option.text = canteen.name;
                        canteenSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching canteens:', error);
                });
        });
    </script>
</body>
</html>
