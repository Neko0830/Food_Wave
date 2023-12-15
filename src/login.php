<?php
session_start();
include "conn.php";

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];

  $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
  $stmt->bind_param("s", $username);

  if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      if (password_verify($password, $row["password"])) {
        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["role"] = $row["role"];

        if ($row['role'] === 'owner') {
          if ($row['approved_status'] == 1) {
            header("Location: restaurant/dashboard.php");
            exit();
          } else {
            $errorMessage = "Account is not yet approved";
          }
        } elseif ($row['role'] === 'admin') {
          header("Location: admin/dashboard.php");
          exit();
        } elseif ($row['role'] === 'customer') {
          header("Location: customer/dash.php");
          exit();
        }
      } else {
        $errorMessage = "Invalid password";
      }
    } else {
      $errorMessage = "User not found or not authorized";
    }
  } else {
    $errorMessage = "Error executing SQL statement";
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