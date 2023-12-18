<?php
session_start();
@include "../conn.php";
$errors = array();

// Fetch the restaurant's existing information
$restaurant = array(
    'name' => '',
    'location' => '',
    'contact_email' => '',
    'contact_phone' => '',
    'opening_hours' => '',
    'delivery_radius' => ''
);

if (isset($_SESSION['restaurant_id'])) {
    $restaurant_id = $_SESSION['restaurant_id'];
    $fetch_query = "SELECT * FROM Restaurants WHERE restaurant_id = ?";
    $stmt = $conn->prepare($fetch_query);
    $stmt->bind_param("i", $restaurant_id);

    if ($stmt->execute()) {
        $restaurant_result = $stmt->get_result();
        if ($restaurant_result->num_rows > 0) {
            $restaurant = $restaurant_result->fetch_assoc();
        } else {
            $errors[] = "Restaurant not found.";
        }
        $stmt->close();
    } else {
        $errors[] = "Error fetching restaurant data.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $restaurant_id = $_SESSION['restaurant_id'];
    $restaurant_name = $_POST['name'];
    $restaurant_location = $_POST['location'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $opening_hours = $_POST['opening_hours'];
    $delivery_radius = $_POST['delivery_radius'];

    // Update the restaurant's data in the database
    $update_query = "UPDATE Restaurants 
                     SET name = ?, location = ?, contact_email = ?, contact_phone = ?, opening_hours = ?, delivery_radius = ? 
                     WHERE restaurant_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssi", $restaurant_name, $restaurant_location, $contact_email, $contact_phone, $opening_hours, $delivery_radius, $restaurant_id);

    if ($stmt->execute()) {
        // Data updated successfully

        // Handle new profile image upload
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $new_image_tmp_name = $_FILES['new_image']['tmp_name'];
            $new_image_name = $_FILES['new_image']['name'];

            // Specify the directory to save the uploaded new image
            $new_image_upload_path = "xampp/htdocs/food_wave/food_wave/uploads/" . $new_image_name;

            if (move_uploaded_file($new_image_tmp_name, $new_image_upload_path)) {
                // Update the restaurant's new image path in the database
                $update_new_image_query = "UPDATE Restaurants SET new_image = ? WHERE restaurant_id = ?";
                $stmt_new_image = $conn->prepare($update_new_image_query);
                $stmt_new_image->bind_param("si", $new_image_upload_path, $restaurant_id);

                if ($stmt_new_image->execute()) {
                    // New image path updated successfully
                } else {
                    $errors[] = "Error updating new image path.";
                }
                $stmt_new_image->close();
            } else {
                $errors[] = "Failed to move uploaded new image.";
            }
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $errors[] = "Error updating restaurant data.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html data-theme="dark">

<head>
    <title>Edit Restaurant Profile</title>
    <link rel="stylesheet" href="../../dist/output.css" />
</head>

<body>
    <div class="bg-base-100 h-screen flex justify-center items-center shadow-lg">
        <div class="w-1/2 card bg-neutral text-neutral-content p-12">
            <h2 class="text-3xl mb-4">Edit Restaurant Profile</h2>
            <div class="error-messages">
                <?php foreach ($errors as $error) { ?>
                    <p class="error text-xs text-red-600"><?php echo "*" . $error; ?></p>
                <?php } ?>
                <form action="edit_profile.php" method="post" enctype="multipart/form-data">
                    <div class="mt-5">
                        <label for="name">Restaurant Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $restaurant['name']; ?>">
                    </div>
                    <div class="mt-5">
                        <label for="location">Restaurant Location</label>
                        <input type="text" id="location" name="location" value="<?php echo $restaurant['location']; ?>">
                    </div>
                    <div class="mt-5">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" value="<?php echo $restaurant['contact_email']; ?>">
                    </div>
                    <div class="mt-5">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="text" id="contact_phone" name="contact_phone" value="<?php echo $restaurant['contact_phone']; ?>">
                    </div>
                    <div class="mt-5">
                        <label for="opening_hours">Opening Hours</label>
                        <input type="text" id="opening_hours" name="opening_hours" value="<?php echo $restaurant['opening_hours']; ?>">
                    </div>
                    <div class="mt-5">
                        <label for="delivery_radius">Delivery Radius (in kilometers)</label>
                        <input type="number" id="delivery_radius" name="delivery_radius" value="<?php echo $restaurant['delivery_radius']; ?>">
                    </div>
                    <div class="mt-5">
                        <label for="new_image">New Profile Image</label>
                        <input type="file" id="new_image" name="new_image">
                    </div>
                    <div class="mt-5">
                        <label for="banner_image">Banner Image</label>
                        <input type="file" id="banner_image" name="banner_image">
                    </div>
                    <div class="mt-5">
                        <input type="submit" value="Save Changes">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>


</html>
