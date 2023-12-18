<?php
session_start();
@include "../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['restaurant_id'], $_POST['food_id'], $_POST['quantity'])) {
        $restaurant_id = $_POST['restaurant_id'];
        $food_id = $_POST['food_id'];
        $quantity = $_POST['quantity'];
        $user_id = $_SESSION['user_id'];

        // Check if the item is already in the user's cart
        $check_cart_query = "SELECT * FROM carts WHERE customer_id = ? AND food_item_id = ?";
        $check_cart_stmt = $conn->prepare($check_cart_query);
        $check_cart_stmt->bind_param("ii", $user_id, $food_id);
        $check_cart_stmt->execute();
        $cart_result = $check_cart_stmt->get_result();

        if ($cart_result->num_rows > 0) {
            // If the item exists, update the quantity
            $update_cart_query = "UPDATE carts SET quantity = quantity + ? WHERE customer_id = ? AND food_item_id = ?";
            $update_cart_stmt = $conn->prepare($update_cart_query);
            $update_cart_stmt->bind_param("iii", $quantity, $user_id, $food_id);
            $update_cart_stmt->execute();
        } else {
            // If the item is not in the cart, add it
            $add_to_cart_query = "INSERT INTO carts (customer_id, restaurant_id, food_item_id, quantity) VALUES (?, ?, ?, ?)";
            $add_to_cart_stmt = $conn->prepare($add_to_cart_query);
            $add_to_cart_stmt->bind_param("iiii", $user_id, $restaurant_id, $food_id, $quantity);
            $add_to_cart_stmt->execute();
        }

        // Redirect back to the restaurant menu page or any other desired page
        header("Location: view_restaurant.php?id=$restaurant_id");
        exit();
    } else {
        // Handle the case where the expected POST data is missing
        echo "Missing POST data.";
        exit();
    }
}
?>
