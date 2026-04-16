<?php
require 'config.php';
require '__DIR__/../src/sandbox.php';
$messageId = (int)($_POST['message_id'] ?? 0);
$emneId    = (int)($_POST['emne_id'] ?? 0);
$pin       = $_POST['pin'] ?? '';
$text      = trim($_POST['comment_text'] ?? '');

if ($messageId <= 0 || $emneId <= 0 || $pin === '') {
    die("Ugyldig forespørsel.");
}

if ($text === '') {
    header("Location: emne.php?id=$emneId&pin=" . urlencode($pin));
    exit;
}

if (mb_strlen($text) > 255) {
    $text = mb_substr($text, 0, 255);
}

// Sjekk PIN for emnet
$stmt = $conn->prepare("SELECT pin FROM emner WHERE id = ?");
$stmt->bind_param("i", $emneId);
$stmt->execute();
$emne = $stmt->get_result()->fetch_assoc();

if (!$emne || $emne['pin'] != $pin) {
    die("Feil PIN.");
}

// Sjekk at melding finnes og tilhører emnet  ← bruk TABELL `meldinger`
$stmt2 = $conn->prepare("SELECT id FROM meldinger WHERE id = ? AND emne_id = ?");
if (!$stmt2) {
    die("Prepare 2 feilet: " . $conn->error);
}
$stmt2->bind_param("ii", $messageId, $emneId);
$stmt2->execute();
$row = $stmt2->get_result()->fetch_assoc();

if (!$row) {
    die("Fant ikke meldingen.");
}

// Lagre kommentar i `comments`
$stmt3 = $conn->prepare("INSERT INTO comments (message_id, comment_text) VALUES (?, ?)");
if (!$stmt3) {
    die("Prepare 3 feilet: " . $conn->error);
}
$stmt3->bind_param("is", $messageId, $text);

if (!$stmt3->execute()) {
    die("Kunne ikke lagre kommentar: " . $stmt3->error);
}

header("Location: emne.php?id=$emneId&pin=" . urlencode($pin) . "#m" . $messageId);
exit;