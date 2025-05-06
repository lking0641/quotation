<?php
include 'design/side.php';
require '../connection/connection.php';
$result = $conn->query("SELECT COUNT(*) AS count FROM user_info");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Reset AUTO_INCREMENT to 1 if the table is empty
    $conn->query("ALTER TABLE user_info AUTO_INCREMENT = 1");
}
// Avoid form resubmission on page refresh using redirection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clientname = $_POST['clientname'];
    $status = $_POST['status'];
    $nameproject = $_POST['nameproject'];  // Capture the name of the project

    // Handle file upload
    $targetDir = "../uploads/";
    $fileName = basename($_FILES["picture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFilePath)) {
            // Get current date and time
            $updateTime = date('Y-m-d H:i:s');

            // Insert into database with updatestatus and update_time
            $stmt = $conn->prepare("INSERT INTO user_info (clientname, status, picture, nameproject, updatestatus, update_time) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $clientname, $status, $fileName, $nameproject, $status, $updateTime);

            if ($stmt->execute()) {
                // Redirect to avoid form resubmission on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500 text-center'>Error: " . $stmt->error . "</p>";
            }

            $stmt->close();
        } else {
            echo "<p class='text-red-500 text-center'>Error uploading the file.</p>";
        }
    } else {
        echo "<p class='text-red-500 text-center'>Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.</p>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Real Living Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="flex h-screen bg-gray-50">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">

        <!-- Main Section -->
        <main class="p-6 flex flex-col items-center justify-center flex-1">
            <form method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Submit Information</h2>

                <!-- Client Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Client Name</label>
                    <input type="text" name="clientname" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring" />
                </div>

                <!-- Name of the Project -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Project Name</label>
                    <input type="text" name="nameproject" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring" />
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select name="status" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring">
                        <option value="">Select status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <!-- Picture -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Upload Picture</label>
                    <input type="file" name="picture" accept="image/*" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring" />
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-[#219ebc] hover:bg-[#197aa0] text-white font-bold py-2 px-4 rounded">
                        Submit
                    </button>
                </div>
            </form>
        </main>

    </div>

    <script>
        const menuBtn = document.getElementById("menu-btn");
        const mobileMenu = document.getElementById("mobile-menu");

        menuBtn.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
    </script>
</body>

</html>
