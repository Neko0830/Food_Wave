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

    $validationCriteria = [
        [!empty($name), "Full Name must not be empty."],
        [!empty($username), "Username must not be empty."],
        [!empty($password), "Password must not be empty."],
        [strlen($password) >= 6, "Password must be at least 6 characters long."],
        [preg_match('/[a-z]/', $password), "Password must include at least one lowercase letter."],
        [preg_match('/[A-Z]/', $password), "Password must include at least one uppercase letter."],
        [filter_var($email, FILTER_VALIDATE_EMAIL), "Email address is not valid."]
    ];
    // Validation Criteria
    foreach ($validationCriteria as $criterion) {
        if (!$criterion[0]) {
            $errors[] = $criterion[1];
        }
    }
    // (Include your validation criteria here)

    if (empty($errors)) {
        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Use a prepared statement to insert user data into the Users table
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, password, email, phone_number, address, role) VALUES (?, ?, ?, ?, ?, ?, 'admin')");
        $stmt->bind_param("ssssss", $name, $username, $hashedPassword, $email, $phone_number, $address);

        if ($stmt->execute()) {
            echo "Customer registration successful!";
            header('Location: login.php'); // Redirect to login page
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html data-theme="dark">

<head>
    <title>Customer Sign Up</title>
    <link rel="stylesheet" href="../dist/output.css" />
</head>

<body>
    <div class="bg-base-100 h-screen flex justify-center items-center shadow-lg">
        <div class="w-1/2 card bg-neutral text-neutral-content p-12">
            <h2 class="text-3xl mb-4">Customer Sign Up</h2>
            <div class="error-messages">
                <?php foreach ($errors as $error) { ?>
                    <p class="error text-xs text-red-600"><?php echo "*" . $error; ?></p>
                <?php } ?>
                <form action="hashadmin.php" method="post">
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
                        <input type="text" placeholder="Phone Number" name="phone_number" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <input type="text" placeholder="Address" name="address" class="border border-gray-400 py-1 px-2 w-full">
                    </div>
                    <div class="mt-5">
                        <button class="w-full bg-purple-500 py-3 text-center text-white">Sign Up as Customer</button>
                    </div>
                </form>
            </div>
        </div>
</body>

</html>