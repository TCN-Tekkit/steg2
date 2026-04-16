<?php
session_start();
require 'config.php';
require '__DIR__/../src/sandbox.php';
?>

<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Alle emner</title>
  <style>
    :root{
      --bg:#0f172a;
      --card:#111827;
      --muted:#94a3b8;
      --text:#e5e7eb;
      --line:#263042;
      --link:#60a5fa;
      --blue:#3b82f6;
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
    .header{
      display:flex;
      gap:12px;
      align-items:flex-end;
      justify-content:space-between;
      flex-wrap:wrap;
      margin-bottom: 18px;
    }
    h1{
      margin:0;
      font-size: 28px;
      letter-spacing: .2px;
    }
    .sub{
      margin: 6px 0 0;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.4;
    }
    .grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 14px;
      margin-top: 14px;
    }
    .card{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 14px 14px 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    .card h3{
      margin: 0 0 8px;
      font-size: 18px;
    }
    .card p{
      margin: 0 0 12px;
      color: var(--muted);
      line-height: 1.45;
      font-size: 14px;
    }
    .btn{
      display:block;
      text-align:center;
      padding: 10px 12px;
      border-radius: 10px;
      text-decoration:none;
      font-weight: 800;
      font-size: 14px;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      user-select:none;
    }
    .btn:hover{ filter: brightness(1.08); }

    .actions{
      margin-top: 18px;
      padding: 14px;
      border: 1px dashed var(--line);
      border-radius: 14px;
      background: rgba(2, 6, 23, .35);
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      align-items:center;
      justify-content:space-between;
    }
    .link{
      color: var(--link);
      text-decoration:none;
      font-weight: 800;
    }
    .link:hover{ text-decoration: underline; }

    .empty{
      margin-top: 16px;
      padding: 14px;
      border-radius: 14px;
      border: 1px solid var(--line);
      color: var(--muted);
      background: rgba(17, 24, 39, .65);
    }
    
    .footer{
      margin-top:40px;
      display:flex;
      gap:20px;
      justify-content:center;
      flex-wrap:wrap;
    }
  </style>
</head>

<body>
  <div class="container">

    <div class="header">
      <div>
        <h1>📚 Alle emner</h1>
        <div class="sub">Klikk emne + skriv riktig PIN for å se meldinger.</div>
      </div>
    </div>

    <?php 
    $emner = $conn->query("
      SELECT e.id, e.navn, e.beskrivelse, l.navn as foreleser 
      FROM emner e
      LEFT JOIN lecturers l ON e.lecturer_id = l.id 
      ORDER BY e.navn
    ");
    ?>

     <?php if ($emner && $emner->num_rows > 0): ?>
      <div class="grid">
        <?php while ($emne = $emner->fetch_assoc()): ?>
          <div class="card">
            <h3><?= SB_String($emne['navn']) ?></h3>
            <p><?= SB_String($emne['beskrivelse']) ?></p>
            
            <div class="card-footer">
              <?php if ($_SESSION['type'] === 'lecturer'): ?>
                <a class="btn" href="vis_meldinger.php?emne_id=<?= (int)$emne['id'] ?>">📧 Se meldinger</a>
              <?php elseif ($_SESSION['type'] === 'student'): ?>
                <a class="btn" href="send_melding.php?emne_id=<?= (int)$emne['id'] ?>">📤 Send melding</a>
              <?php else: ?>
                <a class="btn"
               href="emne.php?id=<?= $emne['id']; ?>&pin=<?= $emne['pin']; ?>">
              👀 Se meldinger (PIN: <?= htmlspecialchars($emne['pin']); ?>)
            </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty">Ingen emner opprettet ennå.</div>
    <?php endif; ?>
    <div class="footer">
        <a class="link" href="login.php">Til logg inn</a>
        <a class="link" href="index.php">Til forsiden</a>
      </div>
  </div>
</body>
</html>
