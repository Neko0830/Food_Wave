<?php
session_start();
@include "../conn.php";

// Check if the user is logged in as a restaurant owner
if ($_SESSION['role'] !== 'restaurant_owner') {
    // Redirect to the login page or another appropriate page
    header('Location: login.php');
    exit();
}

// Fetch the user's restaurant details
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

// Function to get the list of food items for this restaurant
function getRestaurantFoodItems($restaurant_id, $conn)
{
    $food_items = array();

    $food_query = "SELECT * FROM FoodItems WHERE restaurant_id = ?";
    $food_stmt = $conn->prepare($food_query);
    $food_stmt->bind_param("i", $restaurant_id);

    if ($food_stmt->execute()) {
        $food_result = $food_stmt->get_result();

        while ($food_row = $food_result->fetch_assoc()) {
            $food_items[] = $food_row;
        }

        $food_stmt->close();
    } else {
        // Handle query execution error
        echo "Error executing food query: " . $food_stmt->error;
    }

    return $food_items;
}

// Check if the form for adding a new dish is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dish_name = $_POST['dish_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

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

// Get the list of food items for this restaurant
$food_items = getRestaurantFoodItems($restaurant_id, $conn);
?>

<!DOCTYPE html>
<html lang="en" data-theme="mytheme">

<head>
    <meta charset=" UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Dashboard</title>
    <link rel="stylesheet" href="../../dist/output.css">
</head>
<nav class="container flex space-x-4 p-4 w-full justify-end">
    <a class="btn btn-disabled btn-sm" <a href="edit_profile.php">Edit Profile</a>
    <a class="btn btn-primary btn-sm" href="../logout.php">Logout</a>
</nav>
<div class="divider -mt-2"></div>


<body>
    <h1>Welcome to Your Restaurant Dashboard, <?php echo $restaurant['name']; ?></h1>
    <h2>Add New Dish</h2>

    <form method="post" action="dashboard.php">
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

    <h2>Your Food Items</h2>
    <table class="table">
        <tr>
            <th>Food Item</th>
            <th>Description</th>
            <th>Price</th>
        </tr>
        <?php
        foreach ($food_items as $food_item) {
            echo '<tr>';
            echo "<td>{$food_item['name']}</td>";
            echo "<td>{$food_item['description']}</td>";
            echo "<td>₱{$food_item['price']}</td>";
        }
        ?>
    </table>

    <h2>Orders</h2>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Order Status</th>
                    <th>Order Time</th>
                    <th>Food Items</th>
                </tr>
            </thead>
            <tbody>
                <?php

                // Query the orders associated with the restaurant along with food items
                $orders_query = "
    SELECT o.order_id, o.total_price, o.status AS order_status, o.order_date, u.username, GROUP_CONCAT(f.name SEPARATOR ', ') AS food_items
    FROM orders AS o
    JOIN users AS u ON o.customer_id = u.user_id
    JOIN order_details AS od ON o.order_id = od.order_id
    JOIN FoodItems AS f ON od.food_item_id = f.food_id
    WHERE o.restaurant_id = ?
    GROUP BY o.order_id;
";

                $orders_stmt = $conn->prepare($orders_query);
                if (!$orders_stmt) {
                    die('Query preparation failed: ' . $conn->error);
                }

                $orders_stmt->bind_param("i", $restaurant_id);

                if (!$orders_stmt->execute()) {
                    die('Query execution failed: ' . $orders_stmt->error);
                }

                $orders_result = $orders_stmt->get_result();

                if (!$orders_result) {
                    die('Get result failed: ' . $orders_stmt->error);
                }

                if ($orders_result->num_rows === 0) {
                    echo "No orders found for this restaurant.";
                } else {
                    // Fetch and display orders
                    while ($order_row = $orders_result->fetch_assoc()) {
                        // Display order details
                        // ...
                    }
                }

                ?>
                </ul>
</body>

</html>