<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['type'] != 'student') {
  header("Location: login.php");
  exit();
}

// FIX: Use emne_id not id
$emne_id = filter_var($_GET['emne_id'] ?? 0, FILTER_VALIDATE_INT);
if (!$emne_id) die("Ingen emne valgt");

require 'config.php';
require '__DIR__/../src/sandbox.php';
// Hent student_id
$stmt = $conn->prepare("SELECT id FROM students WHERE username = ?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$student_id = $student['id'] ?? 0;

if (!$student_id) die("Student ikke funnet");

// Hent emne-info
$emne_stmt = $conn->prepare("SELECT * FROM emner WHERE id = ?");
$emne_stmt->bind_param("i", $emne_id);
$emne_stmt->execute();
$emne = $emne_stmt->get_result()->fetch_assoc();
if (!$emne) die("Emne ikke funnet");

$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $melding = trim($_POST['melding'] ?? '');

  if (empty($melding)) {
    $successMsg = "❌ Meldingen kan ikke være tom!";
  } else {
    $stmt = $conn->prepare("INSERT INTO meldinger (student_id, emne_id, melding) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $student_id, $emne_id, $melding);
    if ($stmt->execute()) {
      $successMsg = "✅ Melding sendt!";
    } else {
      $successMsg = "❌ Feil ved sending: " . $stmt->error;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Send melding</title>
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --line:#263042;
      --link:#60a5fa; --blue:#3b82f6; --success:#22c55e; --danger:#ef4444;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%);
      color: var(--text);
    }
    .container{ max-width: 900px; margin: 0 auto; padding: 28px 16px 40px; }
    .header{
      display:flex; gap:12px; align-items:flex-end; justify-content:space-between; flex-wrap:wrap;
      margin-bottom: 18px;
    }
    h1{ margin:0; font-size: 26px; letter-spacing:.2px; }
    .sub{ margin: 8px 0 0; color: var(--muted); font-size: 14px; line-height: 1.45; }
    .card{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    label{
      display:block;
      margin: 12px 0 6px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 800;
    }
    textarea{
      width:100%;
      min-height: 180px;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
      resize: vertical;
    }
    textarea:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding: 10px 14px;
      border-radius: 12px;
      font-weight: 900;
      font-size: 14px;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      cursor:pointer;
      user-select:none;
      margin-top: 12px;
    }
    .btn:hover{ filter: brightness(1.08); }

    .alert{
      border-radius: 12px;
      padding: 10px 12px;
      margin: 0 0 12px;
      border: 1px solid rgba(34,197,94,.5);
      background: rgba(34,197,94,.12);
      color: #bbf7d0;
      font-weight: 800;
      font-size: 14px;
    }
    .alert.error{
      border-color: rgba(239,68,68,.55);
      background: rgba(239,68,68,.10);
      color: #fecaca;
    }
    .link{
      color: var(--link);
      text-decoration:none;
      font-weight: 800;
    }
    .link:hover{ text-decoration: underline; }
    .footer{
      margin-top: 12px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      flex-wrap:wrap;
      gap:10px;
      color: var(--muted);
      font-size: 13px;
    }
    .pill{
      display:inline-flex;
      align-items:center;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid var(--line);
      color: var(--muted);
      font-size: 13px;
      background: rgba(2, 6, 23, .35);
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Send melding til: <?= SB_String($emne['navn']) ?></h1>
        <div class="sub">Din melding blir anonym for foreleser.</div>
      </div>
      <div class="pill">Student</div>
    </div>

    <div class="card">
      <?php if ($successMsg): ?>
        <div class="alert <?= strpos($successMsg, '❌') === 0 ? 'error' : '' ?>">
          <?= SB_String($successMsg) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <label for="melding">Melding</label>
        <textarea id="melding" name="melding" maxlength="1000" required
          placeholder="Skriv din anonyme melding her... (maks 1000 tegn)"></textarea>

        <button class="btn" type="submit">📤 Send anonymt</button>
      </form>

      <div class="footer">
        <a class="link" href="vis_emner.php">← Tilbake til emner</a>
        <span>Tips: skriv konkret, ikke personlig.</span>
      </div>
    </div>
  </div>
</body>
</html>
