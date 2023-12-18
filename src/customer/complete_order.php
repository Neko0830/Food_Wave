<?php
session_start();
@include "../conn.php";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'], $_SESSION['restaurant_id'])) {
    $user_id = $_SESSION['user_id'];
    $restaurant_id = $_SESSION['restaurant_id'];

    // Create an order record in the Orders table
    $create_order_query = "INSERT INTO Orders (customer_id, restaurant_id) VALUES (?, ?)";
    $create_order_stmt = $conn->prepare($create_order_query);

    if ($create_order_stmt) {
        $create_order_stmt->bind_param("ii", $user_id, $restaurant_id);
        $create_order_stmt->execute();

        // Get the last inserted order ID
        $order_id = $create_order_stmt->insert_id;

        // Move items from cart to order details
        $move_cart_items_query = "INSERT INTO Order_Details (order_id, food_item_id, quantity) 
                                  SELECT ?, food_item_id, quantity FROM carts WHERE customer_id = ?";
        $move_cart_items_stmt = $conn->prepare($move_cart_items_query);

        if ($move_cart_items_stmt) {
            $move_cart_items_stmt->bind_param("ii", $order_id, $user_id);
            $move_cart_items_stmt->execute();

            // Clear the user's cart after placing the order
            $clear_cart_query = "DELETE FROM carts WHERE customer_id = ?";
            $clear_cart_stmt = $conn->prepare($clear_cart_query);

            if ($clear_cart_stmt) {
                $clear_cart_stmt->bind_param("i", $user_id);
                $clear_cart_stmt->execute();

                // Redirect to a success page or any other desired page
                header("Location: order_success.php");
                exit();
            } else {
                echo "Error clearing cart: " . $conn->error;
                exit();
            }
        } else {
            echo "Error moving items from cart: " . $conn->error;
            exit();
        }
    } else {
        echo "Error creating order: " . $conn->error;
        exit();
    }
} else {
    // Handle the case where the expected session data is missing
    echo "Session data missing or invalid request.";
    exit();
}
?>
