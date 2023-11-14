<?php
@include "conn.php";
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $company_name = $_POST['company_name'];

    // Check if the username is already in use
    $checkUsernameQuery = "SELECT user_id FROM Users WHERE username = ?";
    $checkUsernameStmt = $conn->prepare($checkUsernameQuery);
    $checkUsernameStmt->bind_param("s", $username);
    $checkUsernameStmt->execute();
    $checkUsernameResult = $checkUsernameStmt->get_result();

    if ($checkUsernameResult->num_rows > 0) {
        $errors[] = "Username already in use.";
    }

    // Check if the email is already in use
    $checkEmailQuery = "SELECT user_id FROM Users WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $errors[] = "Email already in use.";
    }

    // Validation Criteria (as before)

    // ... (existing validation code)

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert the restaurant owner into the Users table
        $stmt = $conn->prepare("INSERT INTO Users (full_name, username, password, email, phone_number, address, role) VALUES (?, ?, ?, ?, ?, ?, 'restaurant_owner')");
        $stmt->bind_param("ssssss", $name, $username, $hashedPassword, $email, $phone_number, $address);

        if ($stmt->execute()) {
            // Registration successful
            $user_id = $stmt->insert_id;

            // Insert the restaurant owner's information into the Restaurants table
            $restaurant_name = $company_name; // Use the company name as the restaurant name
            $restaurant_location = $address; // Use the address as the location

            $restaurant_query = "INSERT INTO Restaurants (owner_id, name, location, contact_email, contact_phone, opening_hours, delivery_radius) VALUES (?, ?, ?, ?, ?, 'Your Restaurant Hours', 5)";
            $restaurant_stmt = $conn->prepare($restaurant_query);
            $restaurant_stmt->bind_param("issss", $user_id, $restaurant_name, $restaurant_location, $email, $phone_number);

            if ($restaurant_stmt->execute()) {
                echo "Restaurant Owner registration successful!";
                header('Location: login.php');
                exit();
            } else {
                $errors[] = "Error creating the restaurant entry.";
            }
        } else {
            $errors[] = "Error creating the restaurant owner.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html data-theme="dark">

<head>
    <title>Restaurant Owner Sign Up</title>
    <link rel="stylesheet" href="../dist/output.css" />
</head>

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
                        <input type="text" placeholder="Full Name" name="full_name" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-5 mt-5">
                        <input type="text" placeholder="Username" name="username" class="border border-gray-400 py-1 px-2">
                        <input type="password" placeholder="Password" name="password" class="border border-gray-400 py-1 px-2">
                    </div>
                    <div class="mt-5">
                        <input type="email" placeholder="Email" name="email" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="text" placeholder="Company Name" name="company_name" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="text" placeholder="Phone Number" name="phone_number" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="text" placeholder="Address" name="address" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <button class="w-full bg-purple-500 py-3 text-center text-white">Sign Up as Restaurant Owner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>