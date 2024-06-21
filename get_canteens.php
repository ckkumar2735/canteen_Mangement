<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['location_id'])) {
    $location_id = $_GET['location_id'];
    
    $canteens_sql = "SELECT * FROM canteens WHERE location_id = $location_id";
    $canteens_result = $conn->query($canteens_sql);
    if (!$canteens_result) {
        echo json_encode(['error' => $conn->error]);
        exit;
    }
    
    $canteens = [];
    while($row = $canteens_result->fetch_assoc()) {
        $canteen = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
        $canteens[] = $canteen;
    }
    echo json_encode($canteens);
} else {
    // Invalid request or missing location_id
    echo json_encode([]);
}
?>
