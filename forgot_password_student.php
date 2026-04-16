<?php

require 'config.php';
require '__DIR__/../src/sandbox.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'] ?? '';

  if (empty($email)) {
    $msg = "❌ Vennligst skriv inn e-post.";
  } else {
    $stmt = $conn->prepare("SELECT username FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
      // In a real app, send email here
      // For now, show new password
      $new_pass = bin2hex(random_bytes(4));
      $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
      
      $update = $conn->prepare("UPDATE students SET password_hash = ? WHERE email = ?");
      $update->bind_param("ss", $hashed_new_pass, $email);
      
      if ($update->execute()) {
        $msg = "✅ Nytt passord er sendt til din epost.";
      } else {
        $msg = "❌ Feil ved tilbakestilling.";
      }
      $update->close();
    } else {
      $msg = "❌ E-post ikke funnet.";
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
  <title>Glemt passord</title>
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
    input[type="email"]{
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
      <h1>Glemt passord</h1>
      <a class="link" href="login.php">← Tilbake</a>
    </div>

    <div class="card">
      <?php if ($msg): ?>
        <div class="alert"><?= $msg ?></div>
      <?php endif; ?>

      <form method="post">
        <label for="email">E-post</label>
        <input id="email" type="email" name="email" required>
        <button class="btn" type="submit">Tilbakestill passord</button>
      </form>
    </div>
  </div>
</body>
</html>
<?php