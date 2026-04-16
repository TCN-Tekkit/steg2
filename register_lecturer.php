<?php
require 'config.php';
require '__DIR__/../src/sandbox.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';
  $email = trim($_POST['email'] ?? '');
  $navn = trim($_POST['navn'] ?? '');
  $emne_navn = trim($_POST['emne_navn'] ?? '');
  $emne_beskrivelse = trim($_POST['emne_beskrivelse'] ?? '');
  $pin = trim($_POST['pin'] ?? '');

  if ($user === "" || $pass === "" || $email === "" || $navn === "" || $emne_navn === "" || $pin === "") {
    $msg = "Alle felt må fylles ut.";
  } elseif (strlen($pass) < 8) {
    $msg = "Passordet må være minst 8 tegn langt.";
  } 
  elseif (!preg_match('/[A-Za-z]/', $pass) || !preg_match('/[0-9]/', $pass) || !preg_match('/[^A-Za-z0-9]/', $pass)) {
    $msg = "Passordet må inneholde minst én bokstav, ett tall og ett spesialtegn.";
  }
  elseif (!preg_match('/^\d{4}$/', $pin)) {
    $msg = "PIN må være 4 siffer.";
  } else {
    $checkEmail = $conn->prepare("SELECT id FROM lecturers WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows > 0) {
      $msg = "Noe ved registrering har gått feil.";
    }
    $checkEmail->close();
  }

  if ($msg === "") {
    $bildePath = null;

if (isset($_FILES['bilde']) && $_FILES['bilde']['error'] === UPLOAD_ERR_OK) {
      $tmp = $_FILES['bilde']['tmp_name'];
      $orig = $_FILES['bilde']['name'];
      $size = $_FILES['bilde']['size'];
      
      //2 MB max
      $max_size = 2 * 1024 * 1024; 
      if ($size > $max_size) {
        $msg = "Bildet er for stort.";
      } else {
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime, $allowed_mimes)) {
          $msg = "Ugyldig bildeformat. Bruk et ekte jpg/jpeg/png/gif bilde.";
        } else {
          
          $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));  
          $uploadDir = __DIR__ . "/uploads/lecturers/";
          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
          }

          $filename = "lecturer_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
          $dest = $uploadDir . $filename;
          $bildePath = "uploads/lecturers/" . $filename;

          if (!move_uploaded_file($tmp, $dest)) {
            $msg = "Kunne ikke lagre bildet på serveren.";
          }
        }
      }
    } elseif (isset($_FILES['bilde']) && $_FILES['bilde']['error'] !== UPLOAD_ERR_NO_FILE) {
      $msg = "Feil ved bildeopplasting.";
    }

    if ($msg === "") {
      $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO lecturers (username, password_hash, email, navn, bilde) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $user, $hashed_password, $email, $navn, $bildePath);

      if (!$stmt->execute()) {
        $msg = "Det oppstod en feil ved registrering av foreleser. Prøv igjen.";
      } else {
        $lecturer_id = $stmt->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO emner (navn, beskrivelse, pin, lecturer_id) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("sssi", $emne_navn, $emne_beskrivelse, $pin, $lecturer_id);

        if (!$stmt2->execute()) {
          $msg = "Foreleser lagret, men det oppstod en feil ved opprettelse av emne.";
        } else {
          $emne_id = $stmt2->insert_id;
          $update_stmt = $conn->prepare("UPDATE lecturers SET emne_id = ? WHERE id = ?");
          $update_stmt->bind_param("ii", $emne_id, $lecturer_id);
          $update_stmt->execute();
          
          $msg = "Foreleser + emne opprettet! Du kan nå logge inn.";
        }
      }
    }
  }
}

function msg_class(string $m): string {
  $mLower = mb_strtolower($m);
  if (str_contains($mLower, 'opprettet') || str_contains($mLower, 'du kan nå logge inn')) return 'success';
  return 'error';
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrer foreleser</title>
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
    .container{ max-width: 760px; margin: 0 auto; padding: 28px 16px 40px; }
    .header{
      display:flex; gap:12px; align-items:flex-end; justify-content:space-between; flex-wrap:wrap;
      margin-bottom: 18px;
    }
    h2{ margin:0; font-size: 24px; letter-spacing:.2px; }
    .sub{ margin: 8px 0 0; color: var(--muted); font-size: 14px; line-height: 1.45; }

    .card{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }

    .grid{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    @media (max-width: 720px){
      .grid{ grid-template-columns: 1fr; }
    }

    label{
      display:block;
      margin: 12px 0 6px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 800;
    }
    input, textarea{
      width:100%;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .45);
      color: var(--text);
      outline:none;
      font-family: inherit;
    }
    input:focus, textarea:focus{
      border-color: rgba(59,130,246,.7);
      box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    textarea{
      resize: vertical;
      min-height: 80px;
    }

    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding: 10px 12px;
      border-radius: 10px;
      font-weight: 900;
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

    .alert{
      border-radius: 12px;
      padding: 10px 12px;
      margin: 0 0 12px;
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .35);
      font-weight: 900;
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
    .footer{
      display:flex;
      justify-content:space-between;
      align-items:center;
      flex-wrap:wrap;
      gap:10px;
      color: var(--muted);
      font-size: 13px;
    }
    .link{ color: var(--link); text-decoration:none; font-weight: 900; }
    .link:hover{ text-decoration: underline; }
    .hint{ color: var(--muted); font-size: 12px; margin-top: 6px; }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <div>
        <h2>Registrer foreleser + emne</h2>
        <div class="sub">Opprett en foreleser og et emne med 4-sifret PIN (steg 1).</div>
      </div>
      <div>
        <a class="link" href="index.php">Tilbake</a>
      </div>
    </div>

    <div class="card">
      <?php if ($msg): ?>
        <div class="alert <?= msg_class($msg) ?>"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <div class="grid">
          <div>
            <label for="username">Username</label>
            <input id="username" type="text" name="username" required>
          </div>

          <div>
            <label for="password">Passord</label>
            <input id="password" type="password" name="password" required>
          </div>

          <div>
            <label for="email">E-post</label>
            <input id="email" type="email" name="email" required>
          </div>

          <div>
            <label for="navn">Navn</label>
            <input id="navn" type="text" name="navn" required>
          </div>

          <div>
            <label for="bilde">Bilde (valgfritt)</label>
            <input id="bilde" type="file" name="bilde" accept="image/*">
            <div class="hint">Tillatt: jpg/jpeg/png/gif</div>
          </div>

          <div>
            <label for="emne_navn">Emne navn</label>
            <input id="emne_navn" type="text" name="emne_navn" required>
          </div>

          <div style="grid-column: 1 / -1;">
            <label for="emne_beskrivelse">Emne beskrivelse (valgfritt)</label>
            <textarea id="emne_beskrivelse" name="emne_beskrivelse" placeholder="Forklar emnet kort..."></textarea>
            <div class="hint">Beskriv hva emnet handler om</div>
          </div>

          <div style="grid-column: 1 / -1;">
            <label for="pin">PIN (4 siffer)</label>
            <input id="pin" type="text" name="pin" maxlength="4" pattern="\d{4}" required>
            <div class="hint">Eksempel: 1234</div>
          </div>
        </div>

        <input class="btn" type="submit" value="Registrer">
      </form>

      <hr>

      <div class="footer">
        <a class="link" href="login.php">Til login</a>
        <a class="link" href="index.php">Til forsiden</a>
      </div>
    </div>
  </div>
</body>
</html>
