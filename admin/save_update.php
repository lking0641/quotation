<?php
session_start();
include '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the client ID and the step from the form
    $clientId = $_POST['client_id'];
    $step = $_POST['step'];
    $updateDate = $_POST['update_date'];
    $updateTime = $_POST['update_time'];

    // Check if the client has already completed the previous step
    $stmt = $conn->prepare("SELECT MAX(step) AS last_completed_step FROM step_updates WHERE client_id = ?");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastStep = $result->fetch_assoc()['last_completed_step'];

    // If no steps are completed yet, lastStep will be NULL, set it to 0
    if ($lastStep === NULL) {
        $lastStep = 0;
    }

    // Check if the client is trying to skip steps
    if ($step !== $lastStep + 1) {
        $_SESSION['error'] = "You must complete the previous step before updating this one.";
        header("Location: client_update.php?id=$clientId");
        exit;
    }

    // Check if the step has already been updated for this client
    $stmt = $conn->prepare("SELECT * FROM step_updates WHERE client_id = ? AND step = ?");
    $stmt->bind_param("ii", $clientId, $step);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the step has already been updated, show an error and prevent update
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "This step has already been updated.";
        header("Location: client_update.php?id=$clientId");
        exit;
    }

    // Otherwise, proceed with saving the update
    $stmt = $conn->prepare("INSERT INTO step_updates (client_id, step, update_time) VALUES (?, ?, ?)");
    $updateTimeFull = $updateDate . ' ' . $updateTime;
    $stmt->bind_param("iis", $clientId, $step, $updateTimeFull);
    
    if ($stmt->execute()) {
        // Update the client's status if the step is the last step
        $lastStep = 9; // Assuming the last step is 'Completed'
        if ($step === $lastStep) {
            $updateStatusStmt = $conn->prepare("UPDATE user_info SET updatestatus = 2 WHERE id = ?");
            $updateStatusStmt->bind_param("i", $clientId);
            $updateStatusStmt->execute();
        }
        $_SESSION['success'] = "Step updated successfully!";
    } else {
        $_SESSION['error'] = "There was an error updating the step.";
    }

    header("Location: client_update.php?id=$clientId");
}
?>

