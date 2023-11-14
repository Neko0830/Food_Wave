<?php
// Connect to the database
session_start();
@include '../conn.php';

// Fetch data from the database
$sql = "SELECT restaurant_id, name, profile_image_url FROM restaurants";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  // Output data of each row
  $restaurants = array();
  while ($row = mysqli_fetch_assoc($result)) {
    $restaurant = array(
      "id" => $row['restaurant_id'],
      "name" => $row['name'],
      "image" => $row['profile_image_url']
    );
    array_push($restaurants, $restaurant);
  }
} else {
  echo "0 results";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html data-theme="mytheme">

<head>
  <title>Customer Homepage</title>
  <link rel="stylesheet" type="text/css" href="../../dist/output.css">
</head>
<nav class="container flex space-x-4 p-4 w-full justify-end">
  <a class="btn btn-disabled btn-sm" href=" view_orders.php">Orders</a>
  <a class="btn btn-primary btn-sm" href=" ../logout.php">Logout</a>
</nav>
<div class="divider -mt-2"></div>

<body>
  <h1>Restaurants</h1>
  <div>
    <div class="grid grid-cols-4 gap-10">
      <!-- Loop through restaurants and generate restaurant cards -->
      <?php
      foreach ($restaurants as $restaurant) {
        echo "<div class='card w-42 shadow-xl'>";
        echo "<figure><img src='" . $restaurant['image'] . "' alt='" . $restaurant['name'] . "'></figure>";

        echo "<h2 class='card-title'>" . $restaurant['name'] . "</h2>";
        echo "<a class='btn btn-primary btn-outline btn-md' href='view_restaurant.php?id=" . $restaurant['id'] . "'>View Menu</a>";
        echo "</div>";
      }
      ?>
    </div>
  </div>
</body>

</html>