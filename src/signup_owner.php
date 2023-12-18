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
        $insertUserQuery = "INSERT INTO Users (full_name, username, password, email, phone_number, address, role, approved_status) VALUES (?, ?, ?, ?, ?, ?, 'owner', 0)";
        $insertUserStmt = $conn->prepare($insertUserQuery);
        $insertUserStmt->bind_param("ssssss", $name, $username, $hashedPassword, $email, $phone_number, $address);

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
<html data-theme='dark'>

<head>
    <title>Restaurant Owner Sign Up</title>
    <link rel="stylesheet" href="../dist/output.css">
</head>
<?php if (!empty($errors)) : ?>
    <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<body>
<div class="bg-base-100 h-screen flex justify-center items-center shadow-lg">
        <div class="w-1/2 card bg-neutral text-neutral-content p-12">
        <h2 class="text-3xl mb-4">Restaurant Owner Sign Up</h2>
            <div class="error-messages">
                <?php foreach ($errors as $error) { ?>
                    <p class="error text-xs text-red-600"><?php echo "*" . $error; ?></p>
                <?php } ?>
                <form action="signup_owner.php" method="post">
                    <div class="mt-5">
                        <input type="text" name="full_name" placeholder="Full Name" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-5 mt-5">
                        <input type="text" placeholder="Username" name="username" class="border border-gray-400 py-1 px-2">
                        <input type="password" placeholder="Password" name="password" class="border border-gray-400 py-1 px-2">
                    </div>
                    <div class="mt-5">
                        <input type="text" name="company_name" placeholder="Company Name" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="email" placeholder="Email" name="email" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="text" placeholder="Phone Number" name="phone_number" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="time" name="opening_hours" placeholder="Opening Hours" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="text" name="delivery_radius" placeholder="Delivery Radius" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="text" placeholder="Address" name="address" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <button class="w-full bg-purple-500 py-3 text-center text-white">Sign Up</button>
                    </div>
                </form>
            </div>
        </div>
    
</body>

</html>