<?php
include "conn.php";
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $company_name = $_POST['company_name'];
    $opening_hours = $_POST['opening_hours'];
    $delivery_radius = $_POST['delivery_radius'];

    // Check if username or email already exists
    $checkUsernameQuery = "SELECT user_id FROM Users WHERE username = ?";
    $checkUsernameStmt = $conn->prepare($checkUsernameQuery);
    $checkUsernameStmt->bind_param("s", $username);
    $checkUsernameStmt->execute();
    $checkUsernameResult = $checkUsernameStmt->get_result();

    if ($checkUsernameResult->num_rows > 0) {
        $errors[] = "Username already in use.";
    }

    $checkEmailQuery = "SELECT user_id FROM Users WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $errors[] = "Email already in use.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $defaultApprovalStatus = 'pending'; // Default status for admin approval

        // Insert data into Users table
        $insertUserQuery = "INSERT INTO Users (full_name, username, password, email, phone_number, address, role, approved) VALUES (?, ?, ?, ?, ?, ?, 'owner', ?)";
        $insertUserStmt = $conn->prepare($insertUserQuery);
        $insertUserStmt->bind_param("sssssss", $name, $username, $hashedPassword, $email, $phone_number, $address, $defaultApprovalStatus);

        if ($insertUserStmt->execute()) {
            // Retrieve the user_id of the newly inserted user
            $newUserId = $insertUserStmt->insert_id;

            // Insert data into restaurants table
            $insertRestaurantQuery = "INSERT INTO restaurants (owner_id, name, location, contact_email, contact_phone, opening_hours, delivery_radius) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertRestaurantStmt = $conn->prepare($insertRestaurantQuery);
            $insertRestaurantStmt->bind_param("isssssi", $newUserId, $company_name, $address, $email, $phone_number, $opening_hours, $delivery_radius);

            if ($insertRestaurantStmt->execute()) {
                // Retrieve the restaurant_id of the newly inserted restaurant
                $newRestaurantId = $insertRestaurantStmt->insert_id;

                // Insert data into pendingregistrations table
                $insertPendingQuery = "INSERT INTO pendingregistrations (user_id, restaurant_id, approval_status) VALUES (?, ?, ?)";
                $insertPendingStmt = $conn->prepare($insertPendingQuery);
                $defaultApprovalStatus = 'pending'; // Set default approval status
                $insertPendingStmt->bind_param("iis", $newUserId, $newRestaurantId, $defaultApprovalStatus);

                if ($insertPendingStmt->execute()) {
                    // Redirect to a "Pending Approval" page or a success message
                    header('Location: login.php');
                    exit();
                } else {
                    $errors[] = "Error creating pending registration. Please try again.";
                }

                $insertPendingStmt->close();
            } else {
                $errors[] = "Error creating restaurant. Please try again.";
            }

            $insertRestaurantStmt->close();
        } else {
            $errors[] = "Error creating user. Please try again.";
        }

        $insertUserStmt->close();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Restaurant Owner Sign Up</title>
    <!-- Include necessary CSS files -->
</head>
<?php if (!empty($errors)) : ?>
    <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<body>
    <h1>Restaurant Owner Sign Up</h1>

    <!-- Owner registration form -->
    <form action="signup_owner.php" method="post">
        <input type="text" name="full_name" placeholder="Full Name"><br><br>
        <input type="text" name="username" placeholder="Username"><br><br>
        <input type="password" name="password" placeholder="Password"><br><br>
        <input type="email" name="email" placeholder="Email"><br><br>
        <input type="text" name="company_name" placeholder="Company Name"><br><br>
        <input type="text" name="phone_number" placeholder="Phone Number"><br><br>
        <input type="time" name="opening_hours" placeholder="Opening Hours"><br><br>
        <input type="text" name="delivery_radius" placeholder="Delivery Radius"><br><br>
        <input type="text" name="address" placeholder="Address"><br><br>
        <input type="submit" value="Sign Up">
    </form>
</body>

</html>