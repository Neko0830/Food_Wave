<?php
session_start();
@include "../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['food_item_id'], $_POST['return_page'])) {
    $food_item_id = $_POST['food_item_id'];
    $user_id = $_SESSION['user_id'];
    $return_page = $_POST['return_page']; // Get the return page from the form

    // Remove the selected item from the cart
    $remove_from_cart_query = "DELETE FROM carts WHERE customer_id = ? AND food_item_id = ?";
    $remove_from_cart_stmt = $conn->prepare($remove_from_cart_query);

    if ($remove_from_cart_stmt) {
        $remove_from_cart_stmt->bind_param("ii", $user_id, $food_item_id);
        $remove_from_cart_stmt->execute();

        // Redirect back to the previous page
        header("Location: $return_page");
        exit();
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
?>
