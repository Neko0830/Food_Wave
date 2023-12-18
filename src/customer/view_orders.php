<?php
session_start();
@include '../conn.php';

function getOrderHistory($customer_id)
{
    global $conn; // Assuming $conn is your database connection

    $orders = array();

    // Query to fetch order history with quantity, timestamp, total for the day, status, and completed timestamp
    $order_query = "SELECT o.order_id, f.name AS name, f.price AS price, 
                    od.quantity AS quantity, 
                    DATE_FORMAT(o.order_date, '%Y-%m-%d') AS order_date,
                    o.status AS status,
                    o.completed_timestamp AS completed_time,
                    SUM(f.price * od.quantity) AS total 
                    FROM Orders o 
                    JOIN Order_Details od ON o.order_id = od.order_id
                    JOIN Food_Items f ON od.food_item_id = f.food_id
                    WHERE o.customer_id = ?
                    GROUP BY o.order_id, name, price, quantity, order_date, status, completed_time
                    ORDER BY o.order_date DESC"; // Adjust the columns according to your schema

    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("i", $customer_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    // Fetch order details and add them to the $orders array
    while ($order_row = $order_result->fetch_assoc()) {
        $order = array(
            'order_id' => $order_row['order_id'],
            'name' => $order_row['name'],
            'price' => $order_row['price'],
            'quantity' => $order_row['quantity'],
            'order_date' => $order_row['order_date'],
            'status' => $order_row['status'],
            'completed_time' => $order_row['completed_time'],
            'total' => $order_row['total']
        );
        $orders[] = $order;
    }

    $order_stmt->close();

    return $orders;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme='dark'>

<head>
    <title>Order History</title>
    <link rel="stylesheet" type="text/css" href="../../dist/output.css">
</head>
<?php
@include 'header.html';
?>
<body>
    <h1 class='text-lg font-semibold'>Order History</h1>
    <div class="overflow-x-auto">
    <table class='table'>
        <thead class='table-header-group'>
            <tr>
                <th>Order ID</th>
                <th>Food Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Order Status</th>
                <th>Completed Time</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query database to fetch order history
            $orders = getOrderHistory($_SESSION['user_id']); // Implement this function
            $previous_date = null;

            foreach ($orders as $order) {
                // Display divider when the date changes
                if ($order['order_date'] !== $previous_date) {
                    $previous_date = $order['order_date'];
                }

                echo "<tr>";
                echo "<td>" . $order['order_id'] . "</td>";
                echo "<td>" . $order['name'] . "</td>";
                echo "<td>$" . $order['price'] . "</td>";
                echo "<td>" . $order['quantity'] . "</td>";
                echo "<td>" . $order['order_date'] . "</td>";
                echo "<td>" . $order['status'] . "</td>";
                echo "<td>" . $order['completed_time'] . "</td>";
                echo "<td>$" . $order['total'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
</body>

</html>
