<?php
session_start();
if (!isset($_SESSION['student_id']) || $_SESSION['type'] != 'student') {
  header("Location: login.php");
  exit();
}

require 'config.php';
require '__DIR__/../src/sandbox.php';
$student_id = $_SESSION['student_id'];

// Fetch student's sent messages
$stmt = $conn->prepare("
  SELECT m.id, m.melding, m.created_at, m.svar, m.besvart, e.navn as emne_navn
  FROM meldinger m
  JOIN emner e ON m.emne_id = e.id
  WHERE m.student_id = ?
  ORDER BY m.created_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mine meldinger</title>
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
    .card{ background: rgba(17, 24, 39, .85); border: 1px solid var(--line); border-radius: 14px; padding: 16px; margin: 12px 0; }
    .msg-content{ margin: 10px 0; padding: 10px; background: rgba(2, 6, 23, .35); border-radius: 12px; border-left: 3px solid rgba(59,130,246,.45); }
    .reply-box{ margin-top: 12px; padding: 10px; background: rgba(34,197,94,.12); border-left: 3px solid rgba(34,197,94,.45); border-radius: 8px; color: #bbf7d0; }
    .empty{ padding: 14px; border-radius: 14px; border: 1px solid var(--line); color: var(--muted); text-align: center; }
    .link{ color: var(--link); text-decoration:none; font-weight: 900; }
    .link:hover{ text-decoration: underline; }
    .meta{ color: var(--muted); font-size: 12px; margin-top: 4px; }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>📝 Mine meldinger</h1>
        <div class="sub">Meldinger du har sendt til forelesere</div>
      </div>
      <a class="link" href="vis_emner.php">← Tilbake</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="card">
          <div>
            <strong>📤 Sendt til: <?= SB_String($row['emne_navn']) ?></strong>
            <div class="meta"><?= SB_String($row['created_at']) ?></div>
          </div>

          <div class="msg-content">
            <?= nl2br(SB_String($row['melding'])) ?>
          </div>

          <?php if ($row['besvart']): ?>
            <div class="reply-box">
              <strong>✅ Svar fra foreleser:</strong><br>
              <?= nl2br(SB_String($row['svar'])) ?>
            </div>
          <?php else: ?>
            <div style="color: var(--muted); font-size: 13px; margin-top: 10px;">⏳ Venter på svar fra foreleser...</div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty">Du har ikke sendt noen meldinger ennå.</div>
    <?php endif; ?>

  </div>
</body>
</html>