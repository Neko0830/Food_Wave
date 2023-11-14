<?php
@include "conn.php";
session_start();

if (isset($_SESSION['user_id'])) {
  // If the user is already logged in, redirect to the dashboard based on their role
  if ($_SESSION['role'] === 'restaurant_owner') {
    header("Location: restaurant/dashboard.php");
  } elseif ($_SESSION['role'] === 'customer') {
    header("Location: customer/dash.php");
  }
  exit();
}

$errorMessage = ""; // Initialize an empty error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
  $stmt->bind_param("s", $username);

  if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      if (password_verify($password, $row["password"])) {
        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["role"] = $row["role"]; // Store the user's role in the session

        if ($_SESSION["role"] === 'restaurant_owner') {
          // Retrieve the restaurant_id associated with this owner
          $owner_id = $row["user_id"];
          $restaurant_id_query = "SELECT restaurant_id FROM Restaurants WHERE owner_id = ?";
          $stmt2 = $conn->prepare($restaurant_id_query);
          $stmt2->bind_param("i", $owner_id);

          if ($stmt2->execute()) {
            $restaurant_id_result = $stmt2->get_result();
            if ($restaurant_id_result->num_rows > 0) {
              $restaurant_id_row = $restaurant_id_result->fetch_assoc();
              $_SESSION["restaurant_id"] = $restaurant_id_row["restaurant_id"];
              header("Location: restaurant/dashboard.php"); // Redirect to restaurant owner dashboard
            } else {
              $errorMessage = "Restaurant not found.";
            }
            $stmt2->close();
          } else {
            $errorMessage = "Error fetching restaurant data.";
          }
        } elseif ($_SESSION["role"] === 'customer') {
          header("Location: customer/dash.php"); // Redirect to customer dashboard
        }
      } else {
        $errorMessage = "Invalid password";
      }
    } else {
      $errorMessage = "User not found";
    }
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>
<!DOCTYPE html>
<html data-theme="dark">

<head>
  <title>Login</title>
  <link rel="stylesheet" href="../dist/output.css" />
</head>

<body>
  <div class="bg-base-100 h-screen flex justify-center items-center shadow-lg">
    <div class="glass w-96 card bg-neutral text-neutral-content p-12">
      <h2 class="text-3xl mb-4">Login</h2>

      <?php if (!empty($errorMessage)) : ?>
        <span class="label-text-alt error-message text-red-600"><?php echo "*" . $errorMessage; ?></span>
      <?php endif; ?>

      <form action="login.php" method="post">
        <div class="mt-5">
          <input type="text" placeholder="Username" name="username" class="border border-gray-400 py-1 px-2 w-full">
        </div>

        <div class="mt-5">
          <input type="password" placeholder="Password" name="password" class="border border-gray-400 py-1 px-2 w-full">
        </div>
        <div class="grid grid-cols-2 gap-5 mt-5">
          <button class="bg-purple-500 py-3 text-center text-white">Login</button>
          <a href="index.php" class="bg-purple-500 py-3 text-center text-white">Back</a>
        </div>
      </form>
    </div>
  </div>
</body>

</html>