<?php
include 'components/connect.php';

$current_user_id = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : 'NOT SET';

echo "<h1>Property Debug Report</h1>";

echo "<h2>1. Current Logged-In Seller:</h2>";
echo "Cookie user_id = <strong>" . htmlspecialchars($current_user_id) . "</strong>";

echo "<h2>2. All Properties in Database:</h2>";
$all_query = $conn->prepare("SELECT id, user_id, property_name, address, price, approved FROM property ORDER BY id DESC");
$all_query->execute();
$all_props = $all_query->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='width:100%; border-collapse:collapse;'>";
echo "<tr style='background:#ddd;'><th>ID</th><th>User ID</th><th>Property Name</th><th>Address</th><th>Price</th><th>Approved</th></tr>";

foreach($all_props as $prop) {
    $highlight = ($prop['user_id'] == $current_user_id) ? "style='background:#ffffcc;'" : "";
    echo "<tr $highlight>";
    echo "<td>" . htmlspecialchars($prop['id']) . "</td>";
    echo "<td>" . htmlspecialchars($prop['user_id']) . "</td>";
    echo "<td>" . htmlspecialchars($prop['property_name']) . "</td>";
    echo "<td>" . htmlspecialchars($prop['address']) . "</td>";
    echo "<td>" . htmlspecialchars($prop['price']) . "</td>";
    echo "<td>" . ($prop['approved'] ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>3. Summary:</h2>";
$summary = $conn->prepare("SELECT user_id, COUNT(*) as total, SUM(CASE WHEN approved=1 THEN 1 ELSE 0 END) as approved FROM property GROUP BY user_id");
$summary->execute();
$summaries = $summary->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr style='background:#ddd;'><th>User ID</th><th>Total Properties</th><th>Approved</th><th>Pending</th></tr>";

foreach($summaries as $row) {
    $highlight = ($row['user_id'] == $current_user_id) ? "style='background:#ffffcc;'" : "";
    $pending = $row['total'] - $row['approved'];
    echo "<tr $highlight>";
    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
    echo "<td><strong>" . $row['total'] . "</strong></td>";
    echo "<td>" . $row['approved'] . "</td>";
    echo "<td>" . $pending . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>4. Properties for Current User (what My Listings should show):</h2>";
if($current_user_id !== 'NOT SET') {
    $user_query = $conn->prepare("SELECT id, property_name, address, approved FROM property WHERE user_id = ? ORDER BY id DESC");
    $user_query->execute([$current_user_id]);
    $user_props = $user_query->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($user_props) > 0) {
        echo "Found <strong>" . count($user_props) . "</strong> properties<br>";
        foreach($user_props as $prop) {
            $status = $prop['approved'] ? "✓ Approved" : "✗ Pending";
            echo "- {$prop['property_name']} ({$prop['address']}) - $status<br>";
        }
    } else {
        echo "No properties found for current user ID";
    }
} else {
    echo "User not logged in";
}
?>
