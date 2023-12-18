<?php
session_start();
@include "../conn.php";

// Check if the user is logged in as a restaurant owner
if ($_SESSION['role'] !== 'owner') {
    // Redirect to the login page or another appropriate page
    header('Location: /login.php');
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

    $food_query = "SELECT * FROM food_items WHERE restaurant_id = ?";
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


// Get the list of food items for this restaurant
$food_items = getRestaurantFoodItems($restaurant_id, $conn);
?>

<!DOCTYPE html>
<html lang="en" data-theme="mytheme">

<head>
    <meta charset="UTF-8">
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
    <table class="table mt-6">
        <tr>
            <th></th>
            <th>Food Items</th>
            <th>Description</th>
            <th>Price</th>
        </tr>
        <?php
        foreach ($food_items as $food_item) {
            echo '<tr>';
            echo "<td>{$food_item['image_url']}</td>";
            echo "<td>{$food_item['name']}</td>";
            echo "<td>{$food_item['description']}</td>";
            echo "<td>₱{$food_item['price']}</td>";
        }
        ?>
    </table>

    <div class="overflow-x-auto mt-6">
        <table class="table">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Order ID</th>
                    <th>Order Status</th>
                    <th>Order Time</th>
                    <th>Food Items</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php

                // Query the orders associated with the restaurant along with food items
                $orders_query = "
                SELECT o.order_id, o.status AS order_status, o.order_date, u.username, 
                GROUP_CONCAT(f.name SEPARATOR ', ') AS food_items,
                SUM(f.price * od.quantity) AS total_price
                FROM orders AS o
                JOIN users AS u ON o.customer_id = u.user_id
                JOIN order_details AS od ON o.order_id = od.order_id
                JOIN Food_items AS f ON od.food_item_id = f.food_id
                WHERE o.restaurant_id = ? AND o.status != 'Completed'
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
                        echo "<tr>";
                        echo "<td>{$order_row['username']}</td>";
                        echo "<td>{$order_row['order_id']}</td>";
                        echo "<td>{$order_row['order_status']}</td>";
                        echo "<td>{$order_row['order_date']}</td>";
                
                        // Query to fetch quantities for each order
                        $quantity_query = "SELECT od.quantity FROM order_details AS od WHERE od.order_id = ?";
                        $quantity_stmt = $conn->prepare($quantity_query);
                        $quantity_stmt->bind_param("i", $order_row['order_id']);
                        $quantity_stmt->execute();
                        $quantity_result = $quantity_stmt->get_result();
                
                        if ($quantity_result) {
                            $quantities = array();
                            while ($quantity_row = $quantity_result->fetch_assoc()) {
                                $quantities[] = $quantity_row['quantity'];
                            }
                        } else {
                            // Handle the case where quantity retrieval failed
                            $quantities = array(); // Set a default value or handle appropriately
                        }
                
                        // Separate food items and quantities
                        $foodItems = explode(", ", $order_row['food_items']);
                
                        echo "<td>";
                        foreach ($foodItems as $key => $foodItem) {
                            echo $foodItem . " (Qty: " . $quantities[$key] . ")<br/>";
                        }
                        echo "</td>";
                
                        // Display quantities
                        echo "<td>";
                        foreach ($quantities as $quantity) {
                            echo $quantity . "<br/>";
                        }
                        echo "</td>";
                
                        // Buttons for changing order status
                        echo "<td>";
                        echo "<form action='change_status.php' method='POST'>";
                        echo "<input type='hidden' name='order_id' value='{$order_row['order_id']}'>";
                
                        if ($order_row['order_status'] === 'Pending') {
                            echo "<input type='hidden' name='change_to' value='Processing'>";
                            echo "<input class='btn btn-primary btn-sm' type='submit' name='process_order' value='Process'>";
                        }
                
                        if ($order_row['order_status'] === 'Processing') {
                            echo "<input type='hidden' name='change_to' value='Completed'>";
                            echo "<input class='btn btn-primary btn-sm 'type='submit' name='complete_order' value='Complete'>";
                        }
                        echo "</form>";
                        echo "</td>";
                
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
