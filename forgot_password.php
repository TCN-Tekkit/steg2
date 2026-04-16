<?php
require 'config.php';
require '__DIR__/../src/sandbox.php';
$message = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($email) || empty($role)) {
        $message = "<div class='alert error'>Vennligst fyll ut alle felt.</div>";
    } elseif (!in_array($role, ['student', 'lecturer'])) {
        $message = "<div class='alert error'>Ugyldig rolle.</div>";
    } else {
        // Redirect to the appropriate forgot password page
        header("Location: forgot_password_$role.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Glemt Passord</title>
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
      h1, h2{
        margin:0;
        letter-spacing:.2px;
      }
      h2{ font-size: 24px; }
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
      .role-row{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        margin-top: 8px;
        margin-bottom: 6px;
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
      .actions{
        margin-top: 14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
      }
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
      hr{
        border:0;
        border-top: 1px solid var(--line);
        margin: 16px 0;
        opacity:.8;
      }
      .muted{
        color: var(--muted);
        font-size: 13px;
        margin: 10px 0 0;
      }
    </style>
</head>
<body>
  <div class="container">

    <div class="header">
      <div>
        <h2>Glemt Passord</h2>
        <div class="sub">Velg din rolle for å tilbakestille passord.</div>
      </div>
      <div>
        <a class="link" href="index.php">Tilbake</a>
      </div>
    </div>

    <div class="card">
      <?php echo $message; ?>

      <form method="post">
        <label for="email">E-post</label>
        <input id="email" type="email" name="email" required>

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

        <input class="btn" type="submit" value="Fortsett">
      </form>

      <hr>
      <div class="actions">
        <span class="muted">Angret du?</span>
        <a class="link" href="index.php">Til hovedsiden</a>
      </div>
    </div>

  </div>
</body>
</html>
