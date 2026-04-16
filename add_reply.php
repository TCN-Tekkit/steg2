<?php
session_start();
require 'config.php';
require '__DIR__/../src/sandbox.php';
// Check if lecturer is logged in
if (!isset($_SESSION['lecturer_id']) || $_SESSION['type'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

// Validate input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['svar']) || empty($_POST['message_id'])) {
    die("❌ Mangler påkrevde felt");
}

$message_id = filter_var($_POST['message_id'], FILTER_VALIDATE_INT);
$emne_id = filter_var($_POST['emne_id'], FILTER_VALIDATE_INT);
$svar = trim($_POST['svar']);
$lecturer_id = $_SESSION['lecturer_id'];

if (!$message_id || !$emne_id || empty($svar)) {
    die("❌ Ugyldig input");
}

// Validate message belongs to lecturer's course
$check_stmt = $conn->prepare("
    SELECT m.id 
    FROM meldinger m
    JOIN emner e ON m.emne_id = e.id
    WHERE m.id = ? AND e.lecturer_id = ? AND e.id = ?
");
$check_stmt->bind_param("iii", $message_id, $lecturer_id, $emne_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    die("❌ Ingen tilgang til denne meldingen");
}
$check_stmt->close();

// Check if already replied
$reply_check = $conn->prepare("SELECT id FROM meldinger WHERE id = ? AND besvart = 1");
$reply_check->bind_param("i", $message_id);
$reply_check->execute();
$reply_result = $reply_check->get_result();

if ($reply_result->num_rows > 0) {
    die("❌ Det er allerede svart på denne meldingen.");
}
$reply_check->close();

// Update message with reply
$update_stmt = $conn->prepare("
    UPDATE meldinger 
    SET svar = ?, besvart = 1 
    WHERE id = ?
");

$update_stmt->bind_param("si", $svar, $message_id);

if ($update_stmt->execute()) {
    header("Location: vis_meldinger.php?emne_id=" . $emne_id);
    exit();
} else {
    die("❌ Feil ved lagring av svar: " . $conn->error);
}

$update_stmt->close();
$conn->close();
?>