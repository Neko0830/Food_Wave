<!DOCTYPE html>
<html>

<head>
    <title>Pending Registrations</title>
</head>

<body>
    <h1>Pending Registrations</h1>
    <table border="1">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Restaurant Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../conn.php';
            session_start();

            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                header("Location: admin_login.php");
                exit();
            }

            $query = "SELECT * FROM pendingregistrations WHERE approval_status = 'pending'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                $pendingRegistrations = $result->fetch_all(MYSQLI_ASSOC);

                foreach ($pendingRegistrations as $registration) {
                    $restaurantId = $registration['restaurant_id'];
                    $restaurantQuery = "SELECT name, contact_email FROM restaurants WHERE restaurant_id = $restaurantId";
                    $restaurantResult = $conn->query($restaurantQuery);

                    if ($restaurantResult->num_rows > 0) {
                        $restaurantData = $restaurantResult->fetch_assoc();
                        $restaurantName = $restaurantData['name'];
                        $restaurantEmail = $restaurantData['contact_email'];
                    } else {
                        $restaurantName = 'N/A';
                        $restaurantEmail = 'N/A';
                    }
            ?>
                    <tr>
                        <td><?php echo $registration['user_id']; ?></td>
                        <td><?php echo $restaurantName; ?></td>
                        <td><?php echo $restaurantEmail; ?></td>
                        <td><?php echo $registration['approval_status']; ?></td>
                        <td>
                            <form action="admin_approval_process.php" method="post">
                                <input type="hidden" name="user_id" value="<?php echo $registration['user_id']; ?>">
                                <input type="hidden" name="restaurant_id" value="<?php echo $registration['restaurant_id']; ?>">
                                <input type="hidden" name="email" value="<?php echo $registration['approval_status']; ?>">
                                <select name="approval_status">
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                </select>
                                <input type="submit" value="Submit">
                            </form>
                        </td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='5'>No pending registrations found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>

</html>