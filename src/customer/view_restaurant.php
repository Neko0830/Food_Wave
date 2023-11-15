<?php
session_start();
var_dump($_SESSION);
@include "../conn.php";
?>

<!DOCTYPE html>
<html data-theme="dark">

<head>
    <title>View Restaurant Menu</title>
    <link rel="stylesheet" href="../../dist/output.css">
</head>
<nav class="container flex space-x-4 p-4 w-full justify-end">
    <a class="btn btn-disabled btn-sm" href=" view_orders.php">Orders</a>
    <a class="btn btn-primary btn-sm" href=" ../logout.php">Logout</a>
</nav>
<div class="divider -mt-2"></div>

<body>
    <div class="overflow-x-auto">
        <h1>Restaurant Menu</h1>
        <table class=" table">
            <thead>
                <tr>
                    <th>Food Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
                    $restaurant_id = $_GET['id'];

                    // Fetch restaurant details
                    $restaurant_query = "SELECT * FROM Restaurants WHERE restaurant_id = ?";
                    $restaurant_stmt = $conn->prepare($restaurant_query);
                    $restaurant_stmt->bind_param("i", $restaurant_id);

                    if ($restaurant_stmt->execute()) {
                        $restaurant_result = $restaurant_stmt->get_result();

                        if ($restaurant_result->num_rows > 0) {
                            $restaurant = $restaurant_result->fetch_assoc();

                            // Set restaurant_id in session
                            $_SESSION['restaurant_id'] = $restaurant_id; // Assuming you need this ID for further processing
                        } else {
                            echo "Restaurant not found.";
                            exit();
                        }
                    } else {
                        echo "Error executing restaurant query: " . $restaurant_stmt->error;
                        exit();
                    }

                    // Function to get the list of food items for this restaurant
                    function getRestaurantFoodItems($restaurant_id, $conn)
                    {
                        $food_items = array();

                        $food_query = "SELECT * FROM Food_Items WHERE restaurant_id = ?";
                        $food_stmt = $conn->prepare($food_query);
                        $food_stmt->bind_param("i", $restaurant_id);

                        if ($food_stmt->execute()) {
                            $food_result = $food_stmt->get_result();

                            while ($food_row = $food_result->fetch_assoc()) {
                                $food_items[] = $food_row;
                            }

                            $food_stmt->close();
                        } else {
                            echo "Error executing food query: " . $food_stmt->error;
                        }

                        return $food_items;
                    }

                    // Get the list of food items for this restaurant
                    $food_items = getRestaurantFoodItems($restaurant_id, $conn);
                } else {
                    echo "Invalid request.";
                    exit();
                }

                // Loop through food items retrieved from your database
                foreach ($food_items as $foodItem) {
                    echo '<tr>';
                    echo '<td>' . $foodItem['name'] . '</td>';
                    echo '<td>₱' . $foodItem['price'] . '</td>';
                    echo '<td>';


                    echo '<form method="post" action="add_to_cart.php">';
                    echo '<input type="hidden" name="restaurant_id" value="' . $restaurant_id . '">';
                    echo '<input type="hidden" name="food_id" value="' . $foodItem['food_id'] . '">';
                    echo '<input type="number" name="quantity" value="1" min="1">';
                    echo '<td>';
                    echo '<button type="submit" class="add-to-cart-button btn btn-secondary btn-outline btn-sm">Add to Cart</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- User's Cart Section -->
    <div id="cart-section" class="overflow-x-auto">
        <h2>Your Cart</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Food Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if user is logged in
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];

                    // Query the database to retrieve items in the user's cart
                    $cart_query = "SELECT c.food_item_id, f.name, f.price, c.quantity, (f.price * c.quantity) AS total 
                              FROM carts AS c
                              JOIN Food_Items AS f ON c.food_item_id = f.food_id
                              WHERE c.customer_id = ?";

                    $cart_stmt = $conn->prepare($cart_query);
                    $cart_stmt->bind_param("i", $user_id);

                    if ($cart_stmt->execute()) {
                        $cart_result = $cart_stmt->get_result();

                        $cart_total = 0; // Initialize the cart total

                        while ($cart_row = $cart_result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $cart_row['name'] . '</td>';
                            echo '<td>$' . $cart_row['price'] . '</td>';
                            echo '<td>' . $cart_row['quantity'] . '</td>';
                            echo '<td>$' . $cart_row['total'] . '</td>';
                            echo '</tr>';

                            // Add the item total to the cart total
                            $cart_total += $cart_row['total'];
                        }

                        // Display the cart total row
                        echo '<tr class="divider">';
                        echo '<td colspan"1">Cart Total:</td>';
                        echo '<td>$' . $cart_total . '</td>';
                        echo '</tr>';
                    } else {
                        echo "Error retrieving cart items: " . $cart_stmt->error;
                    }
                } else {
                    echo '<tr><td colspan="4">Please log in to view your cart.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- Complete Order Button -->
    <form method=" post" action="complete_order.php">
        <button type="submit" class="btn btn-primary">Complete Order</button>
    </form>
</body>

</html>