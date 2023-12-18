<?php
session_start();
@include "../conn.php";

// Check if the user is logged in as a restaurant owner
if ($_SESSION['role'] !== 'owner') {
    // Redirect to the login page or another appropriate page
    header('Location: /login.php');
    exit();
}
$owner_id = $_SESSION['user_id'];
$restaurant_query = "SELECT * FROM Restaurants WHERE owner_id = ?";
$restaurant_stmt = $conn->prepare($restaurant_query);
$restaurant_stmt->bind_param("i", $owner_id);

if ($restaurant_stmt->execute()) {
    $restaurant_result = $restaurant_stmt->get_result();

    if ($restaurant_result->num_rows > 0) {
        $restaurant = $restaurant_result->fetch_assoc();
        $restaurant_id = $restaurant['restaurant_id'];
    } else {
        // Handle the case where no restaurant is found for the owner
        echo "No restaurant found for this owner.";
        exit();
    }
} else {
    // Handle query execution error for fetching restaurant details
    echo "Error executing restaurant query: " . $restaurant_stmt->error;
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dish_name = $_POST['dish_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

    // Insert the new dish into the FoodItems table
    $insert_dish_query = "INSERT INTO food_items (restaurant_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?)";
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
<html lang="en" data-theme="mytheme">

<head>
    <meta charset=" UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Dashboard</title>
    <link rel="stylesheet" href="../../dist/output.css">
</head>
<?php
include 'header.html';
?>
<div class="divider -mt-1"></div>


<body>
    <h1 class="font-semibold">Welcome to Your Restaurant Dashboard, <?php echo $restaurant['name']; ?></h1>
    <div class="flex justify-center">
        <div class="card w-auto text-left">
            <h2 class="card-title">Add New Dish</h2>

            <form method="post" action="dishes.php">
                <label for="dish_name">Dish Name:</label>
                <input type="text" id="dish_name" name="dish_name" required><br><br>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="2" cols="20"></textarea><br><br>

                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required><br><br>

                <label for="image_url">Image URL:</label>
                <input type="text" id="image_url" name="image_url"><br><br>

                <input class="btn btn-outline btn-primary" type="submit" value="Add Dish">
            </form>
        </div>
    </div>