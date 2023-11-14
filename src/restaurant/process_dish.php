<?php
session_start();
@include "../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dish_name = $_POST['dish_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

    // Assuming the owner is logged in and you have the user_id (owner_id) and restaurant_id in the session
    $owner_id = $_SESSION['user_id'];
    $restaurant_id = $_SESSION['restaurant_id'];

    // Insert the new dish into the FoodItems table
    $insert_dish_query = "INSERT INTO FoodItems (restaurant_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_dish_query);

    if ($stmt) {
        $stmt->bind_param("isssd", $restaurant_id, $dish_name, $description, $price, $image_url);

        if ($stmt->execute()) {
            echo "Dish added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Add New Dish</title>
</head>

<body>
    <h1>Add New Dish</h1>

    <form method="post" action="process_dish.php">
        <label for="dish_name">Dish Name:</label>
        <input type="text" id="dish_name" name="dish_name" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4" cols="50"></textarea><br><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <label for="image_url">Image URL:</label>
        <input type="text" id="image_url" name="image_url"><br><br>

        <input type="submit" value="Add Dish">
    </form>
</body>

</html>