<?php
session_start();
@include '../conn.php';
function getOrderHistory($customer_id)
{
    global $conn; // Assuming $conn is your database connection

    $orders = array();

    // Query to fetch order history for the given customer_id
    $order_query = "SELECT * FROM Orders WHERE customer_id = ?";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("i", $customer_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    // Fetch order details and add them to the $orders array
    while ($order_row = $order_result->fetch_assoc()) {
        $order = array(
            'order_id' => $order_row['order_id'],
            'food_name' => $order_row['food_name'], // Adjust column names accordingly
            'food_price' => $order_row['food_price'] // Adjust column names accordingly
        );
        $orders[] = $order;
    }

    $order_stmt->close();

    return $orders;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Order History</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body>
    <h1>Order History</h1>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Food Item</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query database to fetch order history
            $orders = getOrderHistory($_SESSION['customer_id']); // Implement this function
            foreach ($orders as $order) {
                echo "<tr>";
                echo "<td>" . $order['order_id'] . "</td>";
                echo "<td>" . $order['food_name'] . "</td>";
                echo "<td>$" . $order['food_price'] . "</td>";
                echo "</tr>";
            }

            ?>
        </tbody>
    </table>
</body>

</html>