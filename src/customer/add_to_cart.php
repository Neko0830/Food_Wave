<?php
session_start();
@include "../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['restaurant_id'], $_POST['food_id'], $_POST['quantity'])) {
        $restaurant_id = $_POST['restaurant_id'];
        $food_id = $_POST['food_id'];
        $quantity = $_POST['quantity'];

        // Insert the cart item into the cart table

        $insert_cart_item_query = "INSERT INTO cart (customer_id, restaurant_id, food_item_id, quantity) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_cart_item_query);

        if ($stmt) {
            $stmt->bind_param("iiii", $_SESSION['user_id'], $restaurant_id, $food_id, $quantity);

            if ($stmt->execute()) {
                // Redirect back to the restaurant menu page or any other desired page
                header("Location: view_restaurant.php?id=$restaurant_id");
                exit();
            } else {
                // Handle the case where the query execution failed
                echo "Error adding item to cart: " . $stmt->error;
                exit();
            }

            $stmt->close(); // Close the prepared statement
        } else {
            // Handle the case where the statement preparation failed
            echo "Error preparing statement: " . $conn->error;
            exit();
        }
    } else {
        // Handle the case where the expected POST data is missing
        echo "Missing POST data.";
        exit();
    }
}
