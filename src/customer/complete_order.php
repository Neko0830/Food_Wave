<?php
session_start();
var_dump($_SESSION);
@include "../conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $restaurant_id = $_SESSION['restaurant_id']; // Assuming the restaurant ID is stored in the session

    // Retrieve the user's cart items
    $cart_query = "SELECT * FROM cart WHERE customer_id = ?";
    $cart_stmt = $conn->prepare($cart_query);
    $cart_stmt->bind_param("i", $user_id);

    if ($cart_stmt->execute()) {
        $cart_result = $cart_stmt->get_result();

        if ($cart_result->num_rows === 0) {
            echo "Your cart is empty.";
            exit();
        }
    } else {
        echo "Error retrieving cart items: " . $cart_stmt->error;
        exit();
    }

    // Insert an order record into the orders table with the restaurant_id
    $insert_order_query = "INSERT INTO orders (customer_id, restaurant_id) VALUES (?, ?)";
    $order_stmt = $conn->prepare($insert_order_query);
    $order_stmt->bind_param("ii", $user_id, $restaurant_id);

    if ($order_stmt->execute()) {
        $order_id = $order_stmt->insert_id;

        // Move items from the cart to the order_details table
        $move_items_query = "INSERT INTO order_details (order_id, food_item_id, quantity) SELECT ?, food_item_id, quantity FROM cart WHERE customer_id = ?";
        $move_items_stmt = $conn->prepare($move_items_query);
        $move_items_stmt->bind_param("ii", $order_id, $user_id);

        if ($move_items_stmt->execute()) {
            // Clear the user's cart
            $clear_cart_query = "DELETE FROM cart WHERE customer_id = ?";
            $clear_cart_stmt = $conn->prepare($clear_cart_query);
            $clear_cart_stmt->bind_param("i", $user_id);

            if ($clear_cart_stmt->execute()) {
                echo "Order completed successfully. Order ID: " . $order_id;
            } else {
                echo "Error clearing cart: " . $clear_cart_stmt->error;
            }
        } else {
            echo "Error moving items to order: " . $move_items_stmt->error;
        }
    } else {
        echo "Error creating order: " . $order_stmt->error;
    }

    $cart_stmt->close();
    $order_stmt->close();
    $move_items_stmt->close();
    $clear_cart_stmt->close();
}
