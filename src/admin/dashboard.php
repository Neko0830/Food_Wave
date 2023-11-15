<?php
session_start();

// Check if the admin is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <!-- Include necessary CSS and JS files -->
</head>

<body>
    <h1>Welcome, Admin!</h1>

    <!-- Add various admin functionalities and navigation links -->
    <ul>
        <li><a href="pending_registrations.php">Pending Registrations</a></li>
        <li><a href="../logout.php">logout</a></li>
        <!-- Add other admin functionalities -->
        <!-- For example: User management, statistics, etc. -->
    </ul>
</body>

</html>