<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['type'] != 'student') {
  header("Location: login.php");
  exit();
}

require 'config.php';
require '__DIR__/../src/sandbox.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $old_pass = $_POST['old_password'] ?? '';
  $new_pass = $_POST['new_password'] ?? '';
  $confirm_pass = $_POST['confirm_password'] ?? '';

  if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
    $msg = "❌ Alle felt må fylles ut.";
  } elseif ($new_pass != $confirm_pass) {
    $msg = "❌ Nye passord stemmer ikke overens.";
  } elseif (strlen($new_pass) < 6) {
    $msg = "❌ Passord må være minst 6 tegn.";
  } else {
    // Verify old password
    $stmt = $conn->prepare("SELECT password FROM students WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['user']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($old_pass, $result['password'])) {
      // Update password
      $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
      $update_stmt = $conn->prepare("UPDATE students SET password = ? WHERE username = ?");
      $update_stmt->bind_param("ss", $hashed_new_pass, $_SESSION['user']);
      
      if ($update_stmt->execute()) {
        $msg = "✅ Passord endret!";
      } else {
        $msg = "❌ Feil ved oppdatering.";
      }
      $update_stmt->close();
    } else {
      $msg = "❌ Feil gammelt passord.";
    }
    $stmt->close();
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
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --line:#263042;
      --link:#60a5fa;
    }
    *{ box-sizing:border-box; }
    body{ margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%); color: var(--text); }
    .container{ max-width: 900px; margin: 0 auto; padding: 28px 16px 40px; }
    .header{ display:flex; gap:12px; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; margin-bottom: 18px; }
    h1{ margin:0; font-size: 26px; }
    .card{ background: rgba(17, 24, 39, .85); border: 1px solid var(--line); border-radius: 14px; padding: 16px; }
    label{ display:block; margin: 12px 0 6px; color: var(--muted); font-size: 13px; font-weight: 800; }
    input[type="password"]{
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
      width:100%;
      padding: 10px 12px;
      border-radius: 10px;
      font-weight: 900;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      cursor:pointer;
      margin-top: 14px;
    }
    .btn:hover{ filter: brightness(1.08); }
    .alert{ border-radius: 12px; padding: 10px 12px; margin: 0 0 12px; border: 1px solid var(--line); background: rgba(2, 6, 23, .35); font-weight: 800; }
    .link{ color: var(--link); text-decoration:none; font-weight: 900; }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <h1>Bytt passord</h1>
      <a class="link" href="vis_emner.php">← Tilbake</a>
    </div>

    <div class="card">
      <?php if ($msg): ?>
        <div class="alert"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="post">
        <label for="old_password">Gammelt passord</label>
        <input id="old_password" type="password" name="old_password" required>

        <label for="new_password">Nytt passord</label>
        <input id="new_password" type="password" name="new_password" required>

        <label for="confirm_password">Bekreft nytt passord</label>
        <input id="confirm_password" type="password" name="confirm_password" required>

        <button class="btn" type="submit">Bytt passord</button>
      </form>
    </div>
  </div>
</body>
</html>