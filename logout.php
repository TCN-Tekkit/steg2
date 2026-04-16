<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Logger ut…</title>
  <meta http-equiv="refresh" content="1;url=index.php">
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --line:#263042;
      --link:#60a5fa;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%);
      color: var(--text);
    }
    .container{ max-width:650px; margin:0 auto; padding: 28px 16px 40px; }
    .card{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    h2{ margin:0; font-size:24px; letter-spacing:.2px; }
    .sub{ margin-top:8px; color: var(--muted); font-size:14px; line-height:1.4; }
    .link{ color: var(--link); text-decoration:none; font-weight:800; }
    .link:hover{ text-decoration: underline; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>Du er logget ut</h2>
      <div class="sub">Sender deg tilbake til forsiden…</div>
      <div class="sub"><a class="link" href="index.php">Gå nå</a></div>
    </div>
  </div>
</body>
</html>
