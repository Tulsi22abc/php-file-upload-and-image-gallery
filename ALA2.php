<?php
session_start();

// Create uploads folder if not exists
if (!file_exists("uploads")) {
    mkdir("uploads", 0777, true);
}

$message = "";
$max_size = 2 * 1024 * 1024; // 2MB
$allowed_types = ["jpg", "jpeg", "png", "gif"];

// Handle Upload
if (isset($_POST["upload"])) {

    if (isset($_FILES["image"])) {

        $file_name = $_FILES["image"]["name"];
        $file_tmp = $_FILES["image"]["tmp_name"];
        $file_size = $_FILES["image"]["size"];
        $file_error = $_FILES["image"]["error"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validation
        if ($file_error !== 0) {
            $message = "Error uploading file.";
        }
        elseif (!in_array($file_ext, $allowed_types)) {
            $message = "Only JPG, JPEG, PNG, GIF files allowed.";
        }
        elseif ($file_size > $max_size) {
            $message = "File size must be less than 2MB.";
        }
        else {

            $new_name = uniqid("IMG_", true) . "." . $file_ext;
            $target = "uploads/" . $new_name;

            if (move_uploaded_file($file_tmp, $target)) {

                // Get custom image title
                $image_title = htmlspecialchars($_POST["image_title"]);

                // Save filename + title
                $data = $new_name . "|" . $image_title . "\n";
                file_put_contents("uploads/data.txt", $data, FILE_APPEND);

                $_SESSION["upload_count"] = ($_SESSION["upload_count"] ?? 0) + 1;

                $message = "Image uploaded successfully!";
            } else {
                $message = "Failed to upload image.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image Upload & Gallery App</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            background: #f4f4f4;
        }

        form {
            background: white;
            padding: 20px;
            margin: 20px auto;
            width: 300px;
            box-shadow: 0 0 10px gray;
        }

        input[type="text"], input[type="file"] {
            width: 90%;
            padding: 5px;
        }

        input[type="submit"] {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .message {
            font-weight: bold;
            margin-top: 10px;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .image-box {
            margin: 10px;
            padding: 10px;
            background: white;
            box-shadow: 0 0 5px gray;
            border-radius: 5px;
        }

        .image-box img {
            width: 200px;
            height: auto;
        }

        .image-name {
            margin-top: 5px;
            font-size: 14px;
            color: #333;
            font-weight: bold;
        }

        .session-info {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Image Upload & Gallery Application</h1>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="image_title" placeholder="Enter Image Name" required><br><br>
    <input type="file" name="image" required><br><br>
    <input type="submit" name="upload" value="Upload Image">
</form>

<div class="message">
    <?php echo $message; ?>
</div>

<div class="session-info">
    <?php
    if (isset($_SESSION["upload_count"])) {
        echo "Images uploaded this session: " . $_SESSION["upload_count"];
    }
    ?>
</div>

<h2>Gallery</h2>

<div class="gallery">
<?php

$titles = [];

// Read stored image titles
if (file_exists("uploads/data.txt")) {
    $lines = file("uploads/data.txt");
    foreach ($lines as $line) {
        list($file, $title) = explode("|", trim($line));
        $titles[$file] = $title;
    }
}

// Display images
$images = scandir("uploads");

foreach ($images as $img) {
    if ($img != "." && $img != ".." && $img != "data.txt") {

        echo "<div class='image-box'>";
        echo "<img src='uploads/$img'>";

        if (isset($titles[$img])) {
            echo "<div class='image-name'>" . $titles[$img] . "</div>";
        }

        echo "</div>";
    }
}
?>
</div>

</body>
</html>