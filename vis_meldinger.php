<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['type'] !== 'lecturer') {
  header("Location: login.php");
  exit();
}
require 'config.php';
require '__DIR__/../src/sandbox.php';

$emne_id = filter_var($_GET['emne_id'] ?? 0, FILTER_VALIDATE_INT);
if (!$emne_id) {
  die("❌ Ugyldig emne ID");
}

$lecturer_id = $_SESSION['lecturer_id'];

// DEBUG: Show what we're checking
echo "<!-- DEBUG: Checking emne_id=$emne_id for lecturer_id=$lecturer_id -->";

// Check if lecturer owns this course
$verify_stmt = $conn->prepare("SELECT id FROM emner WHERE id = ? AND lecturer_id = ?");
$verify_stmt->bind_param("ii", $emne_id, $lecturer_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

// DEBUG: Show result
echo "<!-- DEBUG: Found " . $verify_result->num_rows . " matching courses -->";

if ($verify_result->num_rows === 0) {
  die("❌ Ingen tilgang til dette emnet. (Emne ID: $emne_id, Lecturer ID: $lecturer_id)");
}
$verify_stmt->close();

// Fetch course info
$emne_stmt = $conn->prepare("SELECT navn FROM emner WHERE id = ?");
$emne_stmt->bind_param("i", $emne_id);
$emne_stmt->execute();
$emne = $emne_stmt->get_result()->fetch_assoc();

if (!$emne) {
  die("❌ Emne ikke funnet");
}

// Fetch all messages for this course
$msg_stmt = $conn->prepare("
  SELECT m.id, m.melding, m.created_at, m.besvart, m.svar
  FROM meldinger m
  WHERE m.emne_id = ?
  ORDER BY m.created_at DESC
");
$msg_stmt->bind_param("i", $emne_id);
$msg_stmt->execute();
$messages = $msg_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Meldinger - <?= SB_String($emne['navn']) ?></title>
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --line:#263042;
      --link:#60a5fa; --success:#22c55e;
    }
    *{ box-sizing:border-box; }
    body{ margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%); color: var(--text); }
    .container{ max-width: 900px; margin: 0 auto; padding: 28px 16px 40px; }
    .header{ display:flex; gap:12px; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; margin-bottom: 18px; }
    h1{ margin:0; font-size: 26px; }
    .sub{ margin: 8px 0 0; color: var(--muted); font-size: 14px; }
    .card{ background: rgba(17, 24, 39, .85); border: 1px solid var(--line); border-radius: 14px; padding: 14px; margin: 12px 0; box-shadow: 0 10px 30px rgba(0,0,0,.18); }
    .msg-header{ display:flex; justify-content:space-between; gap:10px; align-items:baseline; margin-bottom:8px; flex-wrap:wrap; }
    .badge{ font-weight: 900; font-size: 13px; color: var(--text); }
    .time{ color: var(--muted); font-size: 12px; }
    .text{ color: var(--text); line-height:1.55; margin:8px 0; }
    .reply-box{ background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.45); border-radius: 12px; padding: 10px; margin-top:10px; color: #bbf7d0; }
    .reply-form{ margin-top:10px; }
    textarea{ width:100%; padding:10px; border-radius:10px; border:1px solid var(--line); background:rgba(2,6,23,.45); color:var(--text); resize:vertical; min-height:80px; outline:none; font-family:inherit; }
    textarea:focus{ border-color:rgba(59,130,246,.7); box-shadow:0 0 0 3px rgba(59,130,246,.15); }
    .btn{ padding:8px 12px; border-radius:10px; font-weight:900; border:1px solid rgba(59,130,246,.55); background:rgba(59,130,246,.16); color:#bfdbfe; cursor:pointer; margin-top:8px; font-size:13px; }
    .btn:hover{ filter:brightness(1.08); }
    .empty{ padding:20px; text-align:center; color:var(--muted); border:1px solid var(--line); border-radius:14px; background:rgba(17,24,39,.65); }
    .link{ color:var(--link); text-decoration:none; font-weight:900; }
    .link:hover{ text-decoration:underline; }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>📧 Meldinger</h1>
        <div class="sub"><strong><?= SB_String($emne['navn']) ?></strong></div>
      </div>
      <a class="link" href="vis_emner.php">← Tilbake</a>
    </div>

    <?php if ($messages->num_rows > 0): ?>
      <?php while($msg = $messages->fetch_assoc()): ?>
        <div class="card">
          <div class="msg-header">
            <div class="badge"><?= $msg['besvart'] ? '✅ Besvart' : '📝 Ny melding' ?></div>
            <div class="time"><?= SB_String($msg['created_at']) ?></div>
          </div>

          <div class="text">
            <strong>Melding fra student:</strong><br>
            <?= SB_String($msg['melding']) ?>
          </div>

          <?php if ($msg['besvart']): ?>
            <div class="reply-box">
              <strong>Ditt svar:</strong><br>
              <?= SB_String($msg['svar']) ?>
            </div>
          <?php else: ?>
            <form class="reply-form" method="post" action="add_reply.php">
              <input type="hidden" name="message_id" value="<?= (int)$msg['id'] ?>">
              <input type="hidden" name="emne_id" value="<?= (int)$emne_id ?>">
              <textarea name="svar" placeholder="Skriv ditt svar her..." required></textarea>
              <button class="btn" type="submit">📤 Send svar</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty">Ingen meldinger i dette emnet ennå.</div>
    <?php endif; ?>

  </div>
</body>
</html>
