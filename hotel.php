<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "10.1.2.4";
$user = "sampleuser";
$pass = "SamplePass123!";
$dbname = "sampledb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("? Connection failed: " . $conn->connect_error); }

$msg = "";
$name = $room = $phone = $email = ""; // keep form values

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $room = $conn->real_escape_string($_POST['room'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $action = $_POST['action'] ?? '';

    if ($action == "checkin") {
        $sql = "INSERT INTO hotel_guests (name, room_number, phone, email, check_in) VALUES ('$name', '$room', '$phone', '$email', NOW())";
        $msg = $conn->query($sql) ? "? Guest checked in!" : "? Error: " . $conn->error;
    } elseif ($action == "checkout") {
        $sql = "UPDATE hotel_guests SET check_out=NOW() WHERE name='$name' AND room_number='$room' AND check_out IS NULL";
        $msg = $conn->query($sql) ? "? Guest checked out!" : "? Error: " . $conn->error;
    }
}

$checked_in = $conn->query("SELECT name, room_number, phone, email, check_in FROM hotel_guests WHERE check_out IS NULL ORDER BY check_in DESC");
$checked_out = $conn->query("SELECT name, room_number, phone, email, check_in, check_out FROM hotel_guests WHERE check_out IS NOT NULL ORDER BY check_out DESC");

$occupancy_data = [];
$res = $conn->query("SELECT DATE(check_in) as date, COUNT(*) as count FROM hotel_guests WHERE check_in >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(check_in)");
while($row = $res->fetch_assoc()) { $occupancy_data[$row['date']] = $row['count']; }

$search_results = null;
if (!empty($_GET['search'])) {
    $q = $conn->real_escape_string($_GET['search']);
    $search_results = $conn->query("SELECT * FROM hotel_guests WHERE name LIKE '%$q%' OR phone LIKE '%$q%' OR email LIKE '%$q%'");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>?? Hotel Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta http-equiv="refresh" content="20">
    <script>
    // Prevent form submit on Enter
    document.addEventListener("DOMContentLoaded", function(){
        document.querySelectorAll("form").forEach(form => {
            form.addEventListener("keydown", function(event) {
                if(event.key === "Enter") event.preventDefault();
            });
        });
    });
    </script>
</head>
<body class="bg-dark text-white">
<div class="container py-4">
    <h1 class="mb-3 text-center">?? Hotel Check-In / Check-Out</h1>
    <form method="post" class="card p-3 mb-3">
        <div class="row mb-2">
            <div class="col"><input type="text" name="name" class="form-control" placeholder="Guest Name" required autocomplete="off" value="<?= htmlspecialchars($name) ?>"></div>
            <div class="col"><input type="text" name="room" class="form-control" placeholder="Room Number" required autocomplete="off" value="<?= htmlspecialchars($room) ?>"></div>
        </div>
        <div class="row mb-2">
            <div class="col"><input type="text" name="phone" class="form-control" placeholder="Phone" autocomplete="off" value="<?= htmlspecialchars($phone) ?>"></div>
            <div class="col"><input type="email" name="email" class="form-control" placeholder="Email" autocomplete="off" value="<?= htmlspecialchars($email) ?>"></div>
        </div>
        <div class="d-flex justify-content-center">
            <button type="submit" name="action" value="checkin" class="btn btn-success me-2">Check In</button>
            <button type="submit" name="action" value="checkout" class="btn btn-danger">Check Out</button>
        </div>
    </form>
    <?php if ($msg) echo "<div class='alert alert-info text-center'>$msg</div>"; ?>

    <form method="get" class="mb-3 text-center">
        <input type="text" name="search" class="form-control d-inline w-50" placeholder="?? Search by name, phone, or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit" class="btn btn-primary ms-2">Search</button>
    </form>

    <?php if ($search_results !== null): ?>
    <h4 class="mt-3">?? Search Results</h4>
    <table class="table table-dark table-striped">
        <thead><tr><th>Name</th><th>Room</th><th>Phone</th><th>Email</th><th>Check-in</th><th>Check-out</th></tr></thead>
        <tbody>
        <?php while($row = $search_results->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['room_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['check_in'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['check_out'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h4 class="mt-4">?? Currently Checked-in Guests</h4>
    <table class="table table-dark table-striped">
        <thead><tr><th>Name</th><th>Room</th><th>Phone</th><th>Email</th><th>Check-in</th></tr></thead>
        <tbody>
        <?php if ($checked_in && $checked_in->num_rows > 0): while($row = $checked_in->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['room_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['check_in'] ?? '') ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan='5' class='text-center'>No guests currently checked in.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h4 class="mt-4">?? Checkout History</h4>
    <table class="table table-dark table-striped">
        <thead><tr><th>Name</th><th>Room</th><th>Phone</th><th>Email</th><th>Check-in</th><th>Check-out</th></tr></thead>
        <tbody>
        <?php if ($checked_out && $checked_out->num_rows > 0): while($row = $checked_out->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['room_number'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['check_in'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['check_out'] ?? '') ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan='6' class='text-center'>No checkout history.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h4 class="mt-4">?? Occupancy (last 7 days)</h4>
    <canvas id="occupancyChart" height="100"></canvas>
</div>
<script>
const ctx = document.getElementById('occupancyChart').getContext('2d');
new Chart(ctx, {
type: 'bar',
data: {
labels: <?= json_encode(array_keys($occupancy_data)) ?>,
datasets: [{
label: 'Guests Checked In',
data: <?= json_encode(array_values($occupancy_data)) ?>,
backgroundColor: 'rgba(54, 162, 235, 0.7)'
}]
},
options: { scales: { y: { beginAtZero:true } } }
});
</script>
</body>
</html>

