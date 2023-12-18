<?php
session_start();
@include "../conn.php";

if ($_SESSION['role'] !== 'owner') {
    header('Location: /login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['order_id']) && isset($_POST['change_to'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['change_to'];

        // Update order status based on the received value
        $update_status_query = "UPDATE orders SET status = ? WHERE order_id = ?";
        $update_status_stmt = $conn->prepare($update_status_query);
        $update_status_stmt->bind_param("si", $new_status, $order_id);

        if ($update_status_stmt->execute()) {
            // Redirect back to the dashboard or order page with a success message
            header('Location: dashboard.php');
            exit();
        } else {
            echo "Error updating order status: " . $update_status_stmt->error;
        }
    } else {
        echo "Invalid request.";
    }
}
?>