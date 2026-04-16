<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}
require 'config.php';
require '__DIR__/../src/sandbox.php';
// Only show courses for THIS lecturer
if ($_SESSION['type'] === 'lecturer') {
  $lecturer_id = $_SESSION['lecturer_id'];
  $sql = "SELECT * FROM emner WHERE lecturer_id = ? ORDER BY navn";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $lecturer_id);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  // Students see all courses
  $sql = "SELECT * FROM emner ORDER BY navn";
  $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Emner</title>
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --line:#263042;
      --link:#60a5fa; --blue:#3b82f6;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%);
      color: var(--text);
    }
    .container{ max-width: 1200px; margin: 0 auto; padding: 28px 16px 40px; }
    .header{
      display:flex; gap:12px; align-items:flex-end; justify-content:space-between; flex-wrap:wrap;
      margin-bottom: 18px;
    }
    h1{ margin:0; font-size: 26px; letter-spacing:.2px; }
    .sub{ margin: 8px 0 0; color: var(--muted); font-size: 14px; line-height: 1.45; }
    .nav-menu{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-bottom: 20px;
    }
    .nav-btn{
      display:inline-flex;
      align-items:center;
      padding: 8px 12px;
      border-radius: 10px;
      font-weight: 900;
      font-size: 13px;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      text-decoration:none;
      cursor:pointer;
      user-select:none;
    }
    .nav-btn:hover{ filter: brightness(1.08); }
    .nav-btn.danger{
      border-color: rgba(239, 68, 68, .55);
      background: rgba(239, 68, 68, .16);
      color: #fecaca;
    }
    .grid{
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 16px;
      margin-top: 14px;
    }
    .card{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 14px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
      display:flex;
      flex-direction:column;
    }
    .card h3{
      margin: 0 0 6px;
      font-size: 18px;
      letter-spacing:.2px;
    }
    .card p{
      color: var(--muted);
      font-size: 13px;
      margin: 0 0 8px;
      line-height: 1.45;
    }
    .card-footer{
      margin-top:auto;
      display:flex;
      gap:8px;
      flex-wrap:wrap;
    }
    .btn{
      flex:1;
      min-width:120px;
      padding: 8px 10px;
      border-radius: 10px;
      font-weight: 900;
      font-size: 13px;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      text-decoration:none;
      cursor:pointer;
      text-align:center;
      user-select:none;
    }
    .btn:hover{ filter: brightness(1.08); }
    .empty{
      padding: 28px;
      border-radius: 14px;
      border: 1px solid var(--line);
      color: var(--muted);
      text-align:center;
      background: rgba(17, 24, 39, .65);
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>📚 Emner</h1>
        <div class="sub">Logget inn som <strong><?= SB_String($_SESSION['user']) ?></strong> (<?= $_SESSION['type'] === 'lecturer' ? 'foreleser' : 'student' ?>)</div>
      </div>
      <a class="nav-btn danger" href="logout.php">🚪 Logg ut</a>
    </div>

    <div class="nav-menu">
      <?php if ($_SESSION['type'] === 'student'): ?>
        <a class="nav-btn" href="mine_meldinger.php">📝 Mine meldinger</a>
        <a class="nav-btn" href="change_password_student.php">🔑 Bytt passord</a>
      <?php elseif ($_SESSION['type'] === 'lecturer'): ?>
        <a class="nav-btn" href="change_password_lecturer.php">🔑 Bytt passord</a>
      <?php endif; ?>
    </div>

    <div class="grid">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($emne = $result->fetch_assoc()): ?>
          <div class="card">
            <h3><?= SB_String($emne['navn']) ?></h3>
            <p><?= SB_String($emne['beskrivelse']) ?></p>
            
            <div class="card-footer">
              <?php if ($_SESSION['type'] === 'lecturer'): ?>
                <a class="btn" href="vis_meldinger.php?emne_id=<?= (int)$emne['id'] ?>">📧 Se meldinger</a>
              <?php else: ?>
                <a class="btn" href="send_melding.php?emne_id=<?= (int)$emne['id'] ?>">📤 Send melding</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty">
          <?php if ($_SESSION['type'] === 'lecturer'): ?>
            Du har ingen emner ennå.
          <?php else: ?>
            Ingen emner tilgjengelig.
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</body>
</html>
