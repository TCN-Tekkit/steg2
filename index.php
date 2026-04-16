<?php session_start(); 
require 'config.php';
require '__DIR__/../src/sandbox.php'; ?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Steg 2</title>
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
      max-width: 900px;
      margin: 0 auto;
      padding: 28px 16px 40px;
    }
    .hero{
      border: 1px solid var(--line);
      border-radius: 18px;
      padding: 18px 16px;
      background: rgba(17, 24, 39, .85);
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    h1{
      margin: 0;
      font-size: 28px;
      letter-spacing: .2px;
    }
    .sub{
      margin: 8px 0 0;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.45;
    }
    .row{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-top: 14px;
    }
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      padding: 10px 12px;
      border-radius: 12px;
      text-decoration:none;
      font-weight: 800;
      font-size: 14px;
      border: 1px solid transparent;
      user-select:none;
    }
    .btn-green{
      background: rgba(34,197,94,.14);
      border-color: rgba(34,197,94,.45);
      color: #bbf7d0;
    }
    .btn-blue{
      background: rgba(59,130,246,.16);
      border-color: rgba(59,130,246,.55);
      color: #bfdbfe;
    }
    .btn-red{
      background: rgba(239,68,68,.12);
      border-color: rgba(239,68,68,.45);
      color: #fecaca;
    }
    .btn:hover{ filter: brightness(1.08); }

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
    .meta{
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--line);
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
      margin-right: 10px;
    }
    .link:hover{ text-decoration: underline; }
  </style>
</head>

<body>
  <div class="container">

    <div class="hero">
      <h1>Velkommen til Steg 2</h1>
      <div class="sub">
        Enkel portal for emner, meldinger og tilbakemeldinger.
      </div>

      <div class="row">
        <a class="btn btn-green" href="emne_oversikt.php">👀 Se alle emner (gjest)</a>

        <?php if (isset($_SESSION['user'])) { ?>
          <a class="btn btn-blue" href="vis_emner.php">Vis emner</a>
          <a class="btn btn-red" href="logout.php">Logg ut</a>
        <?php } else { ?>
          <a class="btn btn-blue" href="login.php">Logg inn</a>
          <a class="btn btn-blue" href="register_student.php">Registrer student</a>
          <a class="btn btn-blue" href="register_lecturer.php">Registrer foreleser</a>
        <?php } ?>
      </div>

      <div class="meta">
        <div>
          <?php if (isset($_SESSION['user'])) { ?>
            <span class="pill">
              Logget inn som <strong style="margin-left:6px; color: var(--text);">
                <?= SB_String($_SESSION['user']) ?>
              </strong>
              <span style="margin-left:8px;">(<?= htmlspecialchars($_SESSION['type'] ?? '') ?>)</span>
            </span>
          <?php } else { ?>
            <span class="pill">Ikke innlogget</span>
          <?php } ?>
        </div>

        <div>
          <a class="link" href="forgot_password.php">Glemt passord?</a>
          <a class="link" href="dokumentasjon.php">Dokumentasjon</a>
        </div>
      </div>

    </div>

  </div>
</body>
</html>
