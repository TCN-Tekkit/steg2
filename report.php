<?php
require 'config.php';
require '__DIR__/../src/sandbox.php';
$messageId = (int)($_GET['message_id'] ?? 0);
$emneId    = (int)($_GET['emne_id'] ?? 0);
$pin       = $_GET['pin'] ?? '';

if ($messageId <= 0 || $emneId <= 0 || $pin === '') {
  die("Ugyldig forespørsel.");
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $reason = trim($_POST['reason'] ?? '');

  // valgfri tekst – men begrens lengde litt
  if (mb_strlen($reason) > 255) {
    $reason = mb_substr($reason, 0, 255);
  }

  // Sjekk at meldingen faktisk finnes og tilhører emnet
  $stmt = $conn->prepare("SELECT id FROM meldinger WHERE id = ? AND emne_id = ?");
  $stmt->bind_param("ii", $messageId, $emneId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();

  if (!$row) {
    $msg = "Fant ikke meldingen (eller feil emne).";
  } else {
    $stmt2 = $conn->prepare("INSERT INTO reports (message_id, emne_id, reason) VALUES (?, ?, ?)");
    $stmt2->bind_param("iis", $messageId, $emneId, $reason);


    if ($stmt2->execute()) {
      $msg = "Rapport mottatt. Takk!";
    } else {
      $msg = "Kunne ikke lagre rapporten: " . $stmt2->error;
    }
  }
}

function msg_class(string $m): string {
  return (stripos($m, 'takk') !== false || stripos($m, 'mottatt') !== false) ? 'success' : 'error';
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rapporter melding</title>
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
    .container{ max-width: 700px; margin:0 auto; padding: 28px 16px 40px; }
    .header{
      display:flex; gap:12px; align-items:flex-end; justify-content:space-between; flex-wrap:wrap;
      margin-bottom: 18px;
    }
    h1{ margin:0; font-size: 24px; letter-spacing:.2px; }
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
    textarea{
      width:100%;
      min-height: 140px;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
      resize: vertical;
    }
    textarea:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    .report{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding: 6px 10px;
      border-radius: 999px;
      text-decoration:none;
      font-weight: 800;
      font-size: 12px;
      border: 1px solid rgba(239,68,68,.45);
      background: rgba(239,68,68,.10);
      color: #fecaca;
    }
.report:hover{ filter: brightness(1.08); }

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
      width:100%;
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
    .link{
      color: var(--link);
      text-decoration:none;
      font-weight: 900;
    }
    .link:hover{ text-decoration: underline; }
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
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <h1>Rapporter melding</h1>
        <div class="sub">Rapportering lagres for gjennomgang.</div>
      </div>
      <div>
        <a class="link" href="emne.php?id=<?= $emneId ?>&pin=<?= urlencode($pin) ?>">Tilbake</a>
      </div>
    </div>

    <div class="card">
      <?php if ($msg): ?>
        <div class="alert <?= msg_class($msg) ?>"><?= SB_String($msg) ?></div>
      <?php endif; ?>

      <?php if (!$msg || msg_class($msg) === 'error'): ?>
        <form method="post">
          <label for="reason">Hvorfor rapporterer du? (valgfritt)</label>
          <textarea id="reason" name="reason" maxlength="255"
            placeholder="F.eks. trakassering, personopplysninger, hatprat, spam..."></textarea>

          <button class="btn" type="submit">🚨 Send rapport</button>
        </form>
      <?php endif; ?>

      <div class="footer">
        <span>Melding-ID: <?= (int)$messageId ?></span>
        <a class="link" href="emne.php?id=<?= $emneId ?>&pin=<?= urlencode($pin) ?>">Til emnet</a>
      </div>
    </div>
  </div>
</body>
</html>
