<?php
session_start();
require 'config.php';
require '__DIR__/../src/sandbox.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? 'student';

  if ($role === 'student') {
    // Passwords are hashed in the database
    $stmt = $conn->prepare("SELECT id, password_hash FROM students WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    usleep(500000);

    if ($result->num_rows > 0) {
      $user_data = $result->fetch_assoc();
      // Verify hashed password
      if (password_verify($pass, $user_data['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['student_id'] = $user_data['id'];
        $_SESSION['user'] = $user;
        $_SESSION['type'] = 'student';
        header("Location: vis_emner.php");
        exit();
      } else {
        $error = "Feil brukernavn eller passord";
      }
    } else {
      $error = "Feil brukernavn eller passord";
    }
    $stmt->close();

  } elseif ($role === 'lecturer') {
    // Passwords are hashed in the database
    $stmt = $conn->prepare("SELECT id, password_hash FROM lecturers WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    usleep(500000);

    if ($result->num_rows > 0) {
      $user_data = $result->fetch_assoc();
      // Verify hashed password
      if (password_verify($pass, $user_data['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['lecturer_id'] = $user_data['id'];
        $_SESSION['user'] = $user;
        $_SESSION['type'] = 'lecturer';
        header("Location: vis_emner.php");
        exit();
      } else {
        $error = "Feil brukernavn eller passord";
      }
    } else {
      $error = "Feil brukernavn eller passord";
    }
    $stmt->close();

  } else {
    $error = "Ugyldig rolle.";
  }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Logg inn</title>
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
    input[type="text"], input[type="password"]{
      width:100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
    }
    input[type="text"]:focus, input[type="password"]:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }

    .role-row{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-top: 8px;
      margin-bottom: 6px;
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
    .role{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 10px;
      border-radius: 999px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .35);
      color: var(--text);
      font-weight: 800;
      font-size: 13px;
      cursor:pointer;
      user-select:none;
    }
    .role input{ transform: translateY(1px); }

    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
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

    .alert{
      border-radius: 12px;
      padding: 10px 12px;
      margin: 0 0 12px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .35);
      font-weight: 800;
      font-size: 14px;
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
    .link{
      color: var(--link);
      text-decoration:none;
      font-weight: 900;
    }
    .link:hover{ text-decoration: underline; }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Logg inn</h1>
        <div class="sub">Student eller foreleser</div>
      </div>
      <div>
        <a class="link" href="index.php">Tilbake</a>
      </div>
    </div>

    <div class="card">
      <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <label for="username">Brukernavn</label>
        <input id="username" type="text" name="username" required>

        <label for="password">Passord</label>
        <input id="password" type="password" name="password" required>

        <label>Velg rolle:</label>
        <div class="role-row">
          <label class="role">
            <input type="radio" name="role" value="student" checked>
            Student
          </label>
          <label class="role">
            <input type="radio" name="role" value="lecturer">
            Foreleser
          </label>
        </div>

        <button class="btn" type="submit">Logg inn</button>
      </form>

      <div class="footer">
        <span>Ny bruker?</span>
        <a class="link" href="register_student.php">Registrer student</a>
      </div>
    </div>
  </div>
</body>
</html>
