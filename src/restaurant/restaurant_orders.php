<?php
session_start();
@include "conn.php";

if (isset($_SESSION['restaurant_id'])) {
    $restaurant_id = $_SESSION['restaurant_id'];

    // Retrieve orders placed at this restaurant
    $restaurant_orders_query = "SELECT O.order_id, O.customer_id, U.username AS customer_name, O.total_price, O.order_date
        FROM orders O
        INNER JOIN users U ON O.customer_id = U.user_id
        WHERE O.restaurant_id = ?
        ORDER BY O.order_date DESC";
    $stmt = $conn->prepare($restaurant_orders_query);
    $stmt->bind_param("i", $restaurant_id);

    if ($stmt->execute()) {
        $restaurant_orders_result = $stmt->get_result();

        if ($restaurant_orders_result->num_rows > 0) {
            while ($order = $restaurant_orders_result->fetch_assoc()) {
                // Display order details to the restaurant
                echo "Order ID: " . $order['order_id'] . "<br>";
                echo "Customer: " . $order['customer_name'] . "<br>";
                echo "Total Price: $" . $order['total_price'] . "<br>";
                echo "Order Date: " . $order['order_date'] . "<br>";
                echo "<hr>";
            }
        } else {
            echo "No orders found for this restaurant.";
        }
    }
}
