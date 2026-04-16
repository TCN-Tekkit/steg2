<?php
session_start();
require 'config.php';
require '__DIR__/../src/sandbox.php';
$emne_id = (int)($_GET['id'] ?? 0);
$pin_input = $_GET['pin'] ?? '';

if ($emne_id <= 0) {
  die("Bruk: emne.php?id=1&pin=1234");
}

/* 1) Hent emne + foreleser (navn + bilde) */
$stmt = $conn->prepare("
  SELECT e.*,
         COALESCE(l.navn, 'Ukjent foreleser') AS lecturer_navn,
         l.bilde
  FROM emner e
  LEFT JOIN lecturers l ON e.lecturer_id = l.id
  WHERE e.id = ?
");
$stmt->bind_param("i", $emne_id);
$stmt->execute();
$emne = $stmt->get_result()->fetch_assoc();

/* 2) Hvis feil PIN -> vis PIN-form */
if (!$emne || $emne['pin'] != $pin_input) {
  ?>
  <!DOCTYPE html>
<html lang='no'>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <title>PIN kreves</title>
  <style>
    :root{
      --bg:#0f172a; --card:#111827; --muted:#94a3b8; --text:#e5e7eb; --line:#263042;
      --link:#60a5fa; --blue:#3b82f6; --danger:#ef4444;
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
      text-align:center;
    }
    h2{ margin:0; font-size:24px; letter-spacing:.2px; }
    .sub{ margin-top:8px; color: var(--muted); font-size:14px; line-height:1.4; }
    input{
      width: 220px;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
      margin-top: 12px;
      font-size: 14px;
    }
    input:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding: 10px 14px;
      border-radius: 12px;
      font-weight: 900;
      font-size: 14px;
      border: 1px solid rgba(59, 130, 246, .55);
      background: rgba(59, 130, 246, .16);
      color: #bfdbfe;
      cursor:pointer;
      user-select:none;
      margin-top: 12px;
    }
    .btn:hover{ filter: brightness(1.08); }
    .link{
      color: var(--link);
      text-decoration:none;
      font-weight: 900;
      display:inline-block;
      margin-top: 14px;
    }
    .link:hover{ text-decoration: underline; }
    .pill{
      display:inline-flex;
      align-items:center;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid var(--line);
      color: var(--muted);
      font-size: 13px;
      background: rgba(2, 6, 23, .35);
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class='container'>
    <div class='card'>
      <h2>🔒 PIN kreves for emne</h2>
      <div class='sub'><strong>Emne ID:</strong> <?= htmlspecialchars($emne_id) ?></div>
      <?php if ($emne): ?>
        <div class='pill'><?= SB_String($emne['navn']) ?></div>
      <?php endif; ?>
      <form method='get' style='margin-top:16px;'>
        <input type='hidden' name='id' value='<?= htmlspecialchars($emne_id) ?>'>
        <input type='number' name='pin' min='1000' max='9999' placeholder='4-siffer PIN' required>
        <br>
        <button class='btn' type='submit'>🔓 Åpne emne</button>
      </form>
      <a class='link' href='emne_oversikt.php'>← Til alle emner</a>
    </div>
  </div>
</body>
</html>
  <?php
  exit();
}

/* 3) Hent meldinger fra meldinger table */
$meldinger = $conn->query("
  SELECT id, melding, created_at, svar, besvart
  FROM meldinger
  WHERE emne_id = $emne_id
  ORDER BY created_at DESC
");

/* 4) Hent alle kommentarer for meldinger i emnet */
$commentsRes = $conn->query("
  SELECT id, message_id, comment_text, created_at
  FROM comments
  WHERE message_id IN (SELECT id FROM meldinger WHERE emne_id = $emne_id)
  ORDER BY created_at ASC
");

$commentsByMsg = [];
if ($commentsRes) {
  while ($c = $commentsRes->fetch_assoc()) {
    $mid = (int)$c['message_id'];
    $commentsByMsg[$mid][] = $c;
  }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= SB_String($emne['navn']) ?></title>
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
      --amber:#f59e0b;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%);
      color: var(--text);
    }
    .container{
      max-width: 980px;
      margin: 0 auto;
      padding: 28px 16px 40px;
    }
    .header{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
      display:flex;
      justify-content:space-between;
      gap:14px;
      flex-wrap:wrap;
      align-items:center;
      margin-bottom: 18px;
    }
    h1{ margin:0; font-size: 26px; letter-spacing:.2px; }
    .sub{ margin-top:6px; color: var(--muted); font-size: 14px; line-height:1.45; }
    .meta{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:nowrap;      /* hindrer rare wraps */
    margin-left:auto;      /* skyver meta helt til høyre */
  }

  .avatar{
    display:block;         /* fjerner “inline image” quirks */
    flex: 0 0 auto;
    width:72px;
    height:72px;
    border-radius:50%;
    object-fit:cover;
    border: 1px solid var(--line);
    background: rgba(2, 6, 23, .35);
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
    .pin{
      background: rgba(245,158,11,.14);
      border-color: rgba(245,158,11,.55);
      color: #fde68a;
      font-weight: 900;
    }
    .avatar{
      width:72px; height:72px;
      border-radius:50%;
      object-fit:cover;
      border: 1px solid var(--line);
    }

    .section-title{
      margin: 0 0 8px;
      font-size: 18px;
      letter-spacing:.2px;
    }

    .msg{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 14px;
      box-shadow: 0 10px 30px rgba(0,0,0,.18);
      margin: 12px 0;
    }
    .msg.reply{
      border-color: rgba(34,197,94,.45);
      background: rgba(34,197,94,.08);
    }
    .msg-head{
      display:flex;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
      align-items:baseline;
      margin-bottom: 6px;
    }
    .badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      font-weight: 900;
      font-size: 13px;
      color: var(--text);
    }
    .time{
      color: var(--muted);
      font-size: 12px;
      white-space:nowrap;
    }
    .text{
      color: var(--text);
      line-height:1.55;
      font-size: 14px;
      margin-top: 6px;
      word-wrap: break-word;
    }

    .row{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-top: 10px;
      align-items:center;
    }
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      padding: 8px 10px;
      border-radius: 12px;
      font-weight: 900;
      font-size: 13px;
      border: 1px solid transparent;
      text-decoration:none;
      user-select:none;
      cursor:pointer;
    }
    .btn-danger{
      background: rgba(239,68,68,.12);
      border-color: rgba(239,68,68,.45);
      color: #fecaca;
    }
    .btn-blue{
      background: rgba(59,130,246,.16);
      border-color: rgba(59,130,246,.55);
      color: #bfdbfe;
    }
    .btn:hover{ filter: brightness(1.08); }

    .comment-form{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      align-items:center;
      margin-top: 10px;
    }
    .comment-form input[type="text"]{
      flex: 1 1 260px;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
      min-width: 220px;
    }
    .comment-form input[type="text"]:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }

    .comments{
      margin-top: 12px;
      border-top: 1px solid rgba(255,255,255,.08);
      padding-top: 10px;
    }
    .comments-title{
      color: var(--muted);
      font-weight: 900;
      font-size: 12px;
      margin-bottom: 8px;
    }
    .comment{
      border: 1px solid rgba(255,255,255,.08);
      background: rgba(2, 6, 23, .35);
      border-radius: 12px;
      padding: 10px 12px;
      margin-top: 8px;
    }
    .comment .ctime{
      margin-top: 6px;
      color: var(--muted);
      font-size: 12px;
    }

    .footer{
      margin-top: 18px;
      padding: 14px;
      border: 1px dashed var(--line);
      border-radius: 14px;
      background: rgba(2, 6, 23, .35);
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
      flex-wrap:wrap;
    }
    .link{
      color: var(--link);
      text-decoration:none;
      font-weight: 900;
    }
    .link:hover{ text-decoration: underline; }
    .empty{
      margin-top: 14px;
      padding: 14px;
      border-radius: 14px;
      border: 1px solid var(--line);
      color: var(--muted);
      background: rgba(17, 24, 39, .65);
      text-align:center;
    }
    
    .reply-box{
      background: rgba(34,197,94,.08);
      border: 1px solid rgba(34,197,94,.45);
      border-radius: 12px;
      padding: 10px 12px;
      margin-top: 10px;
      color: #bbf7d0;
      font-size: 13px;
    }
    
    .reply-box small{
      display: block;
      margin-top: 6px;
      color: var(--muted);
    }
  </style>
</head>

<body>
  <div class="container">

    <div class="header">
      <div>
        <h1><?= SB_String($emne['navn']) ?></h1>
        <div class="sub">
          <strong>Foreleser:</strong> <?= SB_String($emne['lecturer_navn']) ?>
        </div>
        <div class="sub">
          
          <span class="pill pin">PIN: <?= htmlspecialchars($emne['pin']) ?></span>
        </div>
      </div>

      <div class="meta">
        <?php if (!empty($emne['bilde'])): ?>
          <img class="avatar" src="<?= htmlspecialchars($emne['bilde']) ?>" alt="Foreleserbilde">
        <?php endif; ?>
        <a class="link" href="emne_oversikt.php">← Til alle emner</a>
      </div>
    </div>

    <div class="section-title">📨 Meldinger og svar</div>

    <?php if ($meldinger && $meldinger->num_rows > 0): ?>
      <?php while($m = $meldinger->fetch_assoc()): ?>
        <?php
          $mid = (int)$m['id'];
          $hasReply = !empty($m['svar']) && $m['besvart'];
          $cs = $commentsByMsg[$mid] ?? [];
        ?>

        <div class="msg <?= $hasReply ? 'reply' : '' ?>">
          <div class="msg-head">
            <div class="badge">
              <?= $hasReply ? '✅ Svar fra foreleser' : '📝 Anonym melding' ?>
            </div>
            <div class="time"><?= htmlspecialchars($m['created_at']) ?></div>
          </div>

          <div class="text">
            <strong>Student:</strong><br>
            <?= nl2br(htmlspecialchars($m['melding'])) ?>
          </div>

          <?php if ($hasReply): ?>
            <div class="reply-box">
              <strong>Svar fra foreleser:</strong><br>
              <?= nl2br(htmlspecialchars($m['svar'])) ?>
            </div>
          <?php endif; ?>

          <div class="row" style="margin-top:12px;">
            <a class="btn btn-danger"
               href="report.php?message_id=<?= $mid ?>&emne_id=<?= (int)$emne_id ?>&pin=<?= urlencode($pin_input) ?>">
              🚨 Rapporter
            </a>
          </div>

          <form class="comment-form" method="post" action="comment.php">
            <input type="hidden" name="message_id" value="<?= $mid ?>">
            <input type="hidden" name="emne_id" value="<?= (int)$emne_id ?>">
            <input type="hidden" name="pin" value="<?= htmlspecialchars($pin_input) ?>">

            <input type="text" name="comment_text" maxlength="255" placeholder="Legg til kommentar..." required>
            <button class="btn btn-blue" type="submit">💬 Send</button>
          </form>

          <?php if (!empty($commentsByMsg[$mid])): ?>
            <div class="comments">
              <div class="comments-title">Kommentarer (<?= count($commentsByMsg[$mid]) ?>)</div>
              <?php foreach ($commentsByMsg[$mid] as $c): ?>
                <div class="comment">
                  <?= nl2br(SB_String($c['comment_text'])) ?>
                  <div class="ctime"><?= htmlspecialchars($c['created_at']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty">Ingen meldinger i emnet ennå.</div>
    <?php endif; ?>

    <div class="footer">
      <div>
        <a class="link" href="index.php">Login</a>
        <span style="margin:0 10px; color:var(--muted);">|</span>
        <a class="link" href="emne_oversikt.php">Til emne-oversikt</a>
      </div>
      <div style="color:var(--muted); font-size:13px;">
        Du ser emnet fordi du kjenner PIN-koden.
      </div>
    </div>

  </div>
</body>
</html>
