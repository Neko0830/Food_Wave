<?php
include '../conn.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_POST['user_id'];
    $restaurantId = $_POST['restaurant_id'];
    $approvalStatus = $_POST['approval_status'];

    // Update approval status in pendingregistrations table
    $updatePendingQuery = "UPDATE pendingregistrations SET approval_status = ? WHERE user_id = ? AND restaurant_id = ?";
    $updatePendingStmt = $conn->prepare($updatePendingQuery);
    $updatePendingStmt->bind_param("sii", $approvalStatus, $userId, $restaurantId);

    if ($updatePendingStmt->execute()) {
        if ($approvalStatus === 'approved') {
            // Update 'approved' status in the users table to 1
            $updateUserQuery = "UPDATE users SET approved_status = 1 WHERE user_id = ?";
            $updateUserStmt = $conn->prepare($updateUserQuery);
            $updateUserStmt->bind_param("i", $userId);

            if ($updateUserStmt->execute()) {
                // Insert default food item for the restaurant into the fooditems table
                $insertDefaultFoodQuery = "INSERT INTO food_items (restaurant_id, name, description, price) VALUES (?, 'Default Item', 'Description', 9.99)";
                $insertDefaultFoodStmt = $conn->prepare($insertDefaultFoodQuery);
                $insertDefaultFoodStmt->bind_param("i", $restaurantId);
                $insertDefaultFoodStmt->execute();

                // Redirect back to the pending registrations page
                header("Location: pending_registrations.php");
                exit();
            } else {
                // Handle update failure for the users table
                echo "Error updating approval status in the users table: " . $conn->error;
            }

            $updateUserStmt->close();
        } else {
            // Redirect back to the pending registrations page for rejections
            header("Location: pending_registrations.php");
            exit();
        }
    } else {
        // Handle update failure for the pendingregistrations table
        echo "Error updating approval status in the pendingregistrations table: " . $conn->error;
    }

    $updatePendingStmt->close();
}
