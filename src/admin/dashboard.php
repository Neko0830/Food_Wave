<?php
@include "../conn.php";
session_start();

// Check if the admin is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html data-theme="mytheme">

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../dist/output.css">
</head>

<header>
    <div class="navbar bg-base-100 mt-2" style="justify-content: space-between;">
        <div class="basis-1/4">
            <a class="btn btn-ghost normal-case text-xl">FoodWave</a>
        </div>
        <div class="space-x-6">
            <a href="dashboard.php">Dashboard</a>
            <a href="pending_registrations.php">Pending Restaurants</a>
            <a href="#"></a>
        </div>
        <div>
            <button class="btn btn-secondary btn-sm btn-outline px-3"><a href="../logout.php">Logout</a></button>
        </div>
    </div>
</header>
<div class="divider -mt-1">DASHBOARD</div>

<body>
    <div class="grid grid-cols-4 w-3/4 m-auto mt-24">
        <div class='card w-40 shadow-xl text-center'>
            <div class="card-header">
                <h2 class="text-lg font-semibold">Restaurants</h2>
                <?php $sql = "SELECT * from restaurants";
                $result = mysqli_query($conn, $sql);
                $rws = mysqli_num_rows($result);

                echo $rws; ?>
            </div>
        </div>
        <div class='card w-40 shadow-xl text-center'>
            <div class="card-header">
                <h2 class="text-lg font-semibold">Dishes</h2>
                <?php $sql = "SELECT * from food_items";
                $result = mysqli_query($conn, $sql);
                $rws = mysqli_num_rows($result);

                echo $rws; ?>
            </div>
        </div>
        <div class='card w-40 shadow-xl text-center'>
            <div class="card-header">
                <h2 class="text-lg font-semibold">Orders</h2>
                <?php $sql = "SELECT status from orders";
                $result = mysqli_query($conn, $sql);
                $rws = mysqli_num_rows($result);

                echo $rws; ?>
            </div>
        </div>
        <div class='card w-40 shadow-xl text-center'>
            <div class="card-header">
                <h2 class="text-lg font-semibold">Pending</h2>
                <?php $sql = "SELECT * from pendingregistrations WHERE approval_status IN ('pending')";
                $result = mysqli_query($conn, $sql);
                $rws = mysqli_num_rows($result);

                echo $rws; ?>
            </div>
        </div>
    </div>
</body>

</html>