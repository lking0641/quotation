<?php
session_start();
include 'design/side.php';
include '../connection/connection.php';

$result = $conn->query("SELECT COUNT(*) AS count FROM step_updates");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Reset AUTO_INCREMENT to 1 if the table is empty
    $conn->query("ALTER TABLE step_updates AUTO_INCREMENT = 1");
}

// Get client ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch client info including clientname
$query = "SELECT id, clientname, updatestatus FROM user_info WHERE id = $id";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$currentStatus = $row['updatestatus'] ?? 1;
$clientName = $row['clientname']; // Get client name

// Steps definition
$steps = [
    1 => 'Site Visit',
    2 => 'Quotation',
    3 => '3D Presentation',
    4 => 'Payment (downpayment)',
    5 => 'Final Measurement',
    6 => '2D Drawing',
    7 => 'Order Processing',
    8 => 'Production',
    9 => 'Completed'
];

// Get update_time per step
function getStepUpdateTime($conn, $clientId, $step)
{
    $stmt = $conn->prepare("SELECT update_time FROM step_updates WHERE client_id = ? AND step = ? ORDER BY update_time DESC LIMIT 1");
    $stmt->bind_param("ii", $clientId, $step);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return date("F j, Y, g:i a", strtotime($row['update_time']));
    }
    return null;
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
<body class="flex h-full bg-gray-50">
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Main Section -->
        <main class="p-6 flex flex-col items-center justify-center flex-1">
            <!-- Simple Notification Elements -->
            <div id="notification" class="hidden fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white w-50">
                <p id="notificationText" class="text-sm"></p>
            </div>


            <div class="w-full max-w-sm bg-white rounded-lg shadow-lg p-4">
                <h2 class="text-lg font-semibold text-center text-gray-800 mb-4">Client Progress Tracker</h2>
                <p class="text-lg font-semibold text-gray-800">Clientname: <?= htmlspecialchars($clientName) ?></p>

                <div class="space-y-4">
                    <?php foreach ($steps as $step => $label): 
                        $time = getStepUpdateTime($conn, $id, $step);
                        $hasTime = $time !== null;
                        static $firstPendingStep = null;
                        if (!$hasTime && $firstPendingStep === null) {
                            $firstPendingStep = $step;
                        }
                        $isCurrent = $step === $firstPendingStep;
                    ?>
                        <div class="flex items-center space-x-2">
                            <div class="w-5 h-5 flex justify-center items-center">
                                <?php if ($hasTime): ?>
                                    <span class="text-green-500 text-xs">✔️</span>
                                <?php elseif ($isCurrent): ?>
                                    <span class="text-yellow-500 text-xs">➤</span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">⬜</span>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1">
                                <div class="p-2 rounded-lg border 
                                <?= $hasTime ? 'bg-green-100 border-green-500' : ($isCurrent ? 'bg-yellow-100 border-yellow-500' : 'bg-gray-100 border-gray-300') ?>">
                                    <p class="text-xs font-medium 
                                    <?= $hasTime ? 'text-green-700' : ($isCurrent ? 'text-yellow-700' : 'text-gray-700') ?>">
                                        <?= $label ?>
                                    </p>
                                    <?php if ($hasTime): ?>
                                        <p class="text-xs text-gray-500"><?= $time ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- BUTTON TO OPEN UPDATE MODAL -->
                <div class="flex justify-between items-center mt-4">
                    <button id="openUpdateModal" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 text-xs">
                        Go to Update Page
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Single Modal (Update Status Form) -->
    <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm mx-auto mt-[100px]">
            <h2 class="text-lg font-semibold text-center text-gray-800 mb-4">Update Client Status</h2>

            <form action="save_update.php" method="POST" class="space-y-4">
                <input type="hidden" name="client_id" value="<?= $id ?>">

                <div>
                    <label class="block text-gray-700 text-sm mb-2">Select Step:</label>
                    <select name="step" class="w-full border rounded-lg p-2">
                        <?php foreach ($steps as $step => $label): 
                            $time = getStepUpdateTime($conn, $id, $step);
                            $hasTime = $time !== null;
                        ?>
                            <option value="<?= $step ?>" <?= $hasTime ? 'disabled' : '' ?>>
                                <?= $label ?> <?= $hasTime ? '(Updated)' : '(Pending)' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-4">
                    <label for="update_date" class="block text-sm font-medium">Update Date:</label>
                    <input type="date" name="update_date" id="update_date" class="w-full p-2 border border-gray-300 rounded-md" required />

                    <label for="update_time" class="block text-sm font-medium">Update Time:</label>
                    <input type="time" name="update_time" id="update_time" class="w-full p-2 border border-gray-300 rounded-md" required />
                </div>

                <div class="flex justify-between items-center mt-6">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm">
                        Save
                    </button>
                    <button type="button" id="closeUpdateModal" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Function to show notification and auto-close after a few seconds
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');

            // Set message and style based on notification type (success or error)
            notificationText.textContent = message;
            if (type === 'success') {
                notification.classList.remove('bg-red-500', 'hidden');
                notification.classList.add('bg-green-500');
            } else if (type === 'error') {
                notification.classList.remove('bg-green-500', 'hidden');
                notification.classList.add('bg-red-500');
            }

            // Show notification
            notification.classList.remove('hidden');

            // Auto-close notification after 3 seconds
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 3000);
        }

        // Check if the session has an error or success message and show the notification
        <?php if (isset($_SESSION['error'])): ?>
            showNotification("<?= $_SESSION['error']; ?>", "error");
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            showNotification("<?= $_SESSION['success']; ?>", "success");
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        // Modal toggle functionality
        const openUpdateModal = document.getElementById('openUpdateModal');
        const updateModal = document.getElementById('updateModal');
        const closeUpdateModal = document.getElementById('closeUpdateModal');

        openUpdateModal.addEventListener('click', () => {
            updateModal.classList.remove('hidden');
        });

        closeUpdateModal.addEventListener('click', () => {
            updateModal.classList.add('hidden');
        });
    </script>
</body>
