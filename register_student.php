<?php
require 'config.php';
require '__DIR__/../src/sandbox.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $email = trim($_POST['email'] ?? '');
  $navn = trim($_POST['navn'] ?? '');
  $studieretning = trim($_POST['studieretning'] ?? '');
  $studiekull = trim($_POST['studiekull'] ?? '');

  if (empty($username) || empty($password) || empty($email) || empty($navn) || empty($studieretning) || empty($studiekull)) {
    $msg = "❌ Alle felt må fylles ut.";
  } 
  elseif (strlen($password) < 8) {
    $msg = "❌ Passordet må være minst 8 tegn langt.";
  } 
  elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
    $msg = "Passordet må inneholde minst én bokstav, ett tall og ett spesialtegn.";
  }
  else {
    $checkEmail = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows > 0) {
      $msg = "Noe ved registrering har gått feil.";
    }
    $checkEmail->close();
  }

  if ($msg === "") {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO students (username, password_hash, email, navn, studieretning, studiekull) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $hashed_password, $email, $navn, $studieretning, $studiekull);

    if ($stmt->execute()) {
      $msg = "✅ Student registrert! Du kan nå logge inn.";
    } else {
      $msg = "Det oppstod en teknisk feil. Vennligst prøv igjen."; 
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
  <title>Registrer student</title>
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --line:#263042;
      --link:#60a5fa; --blue:#3b82f6;
    }
    *{ box-sizing:border-box; }
    body{ margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%); color: var(--text); }
    .container{ max-width: 900px; margin: 0 auto; padding: 28px 16px 40px; }
    .header{ display:flex; gap:12px; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; margin-bottom: 18px; }
    h1{ margin:0; font-size: 26px; letter-spacing:.2px; }
    .sub{ margin: 8px 0 0; color: var(--muted); font-size: 14px; line-height: 1.45; }
    .card{ background: rgba(17, 24, 39, .85); border: 1px solid var(--line); border-radius: 14px; padding: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
    label{ display:block; margin: 12px 0 6px; color: var(--muted); font-size: 13px; font-weight: 800; }
    input[type="text"], input[type="password"], input[type="email"], input[type="number"], select{
      width:100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
    }
    input:focus, select:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    .btn{
      width:100%;
      padding: 10px 12px;
      border-radius: 10px;
      font-weight: 900;
      font-size: 14px;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      cursor:pointer;
      margin-top: 14px;
    }
    .btn:hover{ filter: brightness(1.08); }
    .alert{ border-radius: 12px; padding: 10px 12px; margin: 0 0 12px; border: 1px solid var(--line); background: rgba(2, 6, 23, .35); font-weight: 800; font-size: 14px; }
    .footer{ margin-top: 12px; color: var(--muted); font-size: 13px; }
    .link{ color: var(--link); text-decoration:none; font-weight: 900; }
    .link:hover{ text-decoration: underline; }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Registrer student</h1>
        <div class="sub">Opprett en ny studentkonto</div>
      </div>
      <a class="link" href="index.php">Tilbake</a>
    </div>

    <div class="card">
      <?php if ($msg): ?>
        <div class="alert"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="post">
        <label for="navn">Navn</label>
        <input id="navn" type="text" name="navn" required>

        <label for="username">Brukernavn</label>
        <input id="username" type="text" name="username" required>

        <label for="email">E-post</label>
        <input id="email" type="email" name="email" required>

        <label for="password">Passord</label>
        <input id="password" type="password" name="password" required>

        <label for="studieretning">Studieretning</label>
        <input id="studieretning" type="text" name="studieretning" placeholder="f.eks. Informatikk" required>

        <label for="studiekull">Studiekull</label>
        <input id="studiekull" type="number" name="studiekull" min="2020" max="2030" required>

        <button class="btn" type="submit">Registrer</button>
      </form>

      <div class="footer">
        <p>Allerede bruker? <a class="link" href="login.php">Logg inn her</a></p>
      </div>
    </div>
  </div>
</body>
</html>
