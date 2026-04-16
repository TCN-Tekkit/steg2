<?php
session_start();
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'lecturer') {
  header("Location: login.php");
  exit();
}
require 'config.php';
require '__DIR__/../src/sandbox.php';
$msg = "";
$lecturerId = (int)($_SESSION['lecturer_id'] ?? 0);

if ($lecturerId <= 0) {
  header("Location: login.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $current = $_POST['current'] ?? '';
  $new1 = $_POST['new1'] ?? '';
  $new2 = $_POST['new2'] ?? '';

  if ($new1 === "" || $new2 === "") {
    $msg = "Du må fylle ut nytt passord to ganger.";
  } elseif ($new1 !== $new2) {
    $msg = "Nytt passord matcher ikke.";
  } else {
    // 1) Hent nåværende passord fra DB (klartekst i steg 1)
    $stmt = $conn->prepare("SELECT password FROM lecturers WHERE id = ?");
    $stmt->bind_param("i", $lecturerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
      $msg = "Fant ikke foreleserbruker.";
    } elseif ($current !== $row['password']) {
      $msg = "Feil nåværende passord.";
    } else {
      // 2) Oppdater til nytt passord (klartekst)
      $stmt2 = $conn->prepare("UPDATE lecturers SET password = ? WHERE id = ?");
      $stmt2->bind_param("si", $new1, $lecturerId);

      if ($stmt2->execute()) {
        $msg = "Passord oppdatert.";
      } else {
        $msg = "Kunne ikke oppdatere passord: " . $stmt2->error;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bytt passord</title>
  <style>
    :root{
      --bg:#0f172a;
      --card:#111827;
      --muted:#94a3b8;
      --text:#e5e7eb;
      --line:#263042;
      --link:#60a5fa;
      --blue:#3b82f6;
      --success:#22c55e;
      --danger:#ef4444;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%);
      color: var(--text);
    }
    .container{
      max-width: 700px;
      margin: 0 auto;
      padding: 28px 16px 40px;
    }
    .header{
      display:flex;
      gap:12px;
      align-items:flex-end;
      justify-content:space-between;
      flex-wrap:wrap;
      margin-bottom: 18px;
    }
    h2{
      margin:0;
      font-size: 24px;
      letter-spacing: .2px;
    }
    .sub{
      margin: 8px 0 0;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.4;
    }
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
      font-weight: 700;
    }
    input{
      width:100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
    }
    input:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      padding: 10px 12px;
      border-radius: 10px;
      text-decoration:none;
      font-weight: 800;
      font-size: 14px;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      cursor:pointer;
      user-select:none;
      width:100%;
      margin-top: 14px;
    }
    .btn:hover{ filter: brightness(1.08); }
    .link{
      color: var(--link);
      text-decoration:none;
      font-weight: 800;
    }
    .link:hover{ text-decoration: underline; }
    .alert{
      border-radius: 12px;
      padding: 10px 12px;
      margin: 0 0 12px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .35);
      font-weight: 700;
      font-size: 14px;
    }
    .alert.success{
      border-color: rgba(34,197,94,.5);
      background: rgba(34,197,94,.12);
      color: #bbf7d0;
    }
    .alert.error{
      border-color: rgba(239,68,68,.55);
      background: rgba(239,68,68,.10);
      color: #fecaca;
    }
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
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h2>Bytt passord (foreleser)</h2>
        <div class="sub">Oppdater passordet ditt. Du må skrive inn nåværende passord.</div>
      </div>
      <div>
        <a class="link" href="index.php">Tilbake</a>
      </div>
    </div>

    <div class="card">
      <?php if ($msg): ?>
        <div class="alert <?= (stripos($msg, 'oppdatert') !== false) ? 'success' : 'error' ?>">
          <?= SB_String($msg) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <label for="current">Nåværende passord</label>
        <input id="current" type="password" name="current" required>

        <label for="new1">Nytt passord</label>
        <input id="new1" type="password" name="new1" required>

        <label for="new2">Gjenta nytt passord</label>
        <input id="new2" type="password" name="new2" required>

        <input class="btn" type="submit" value="Oppdater">
      </form>

      <div class="footer">
        <span>Steg 1: passord lagres i klartekst.</span>
        <a class="link" href="vis_emner.php">Til emner</a>
      </div>
    </div>
  </div>
</body>
</html>
