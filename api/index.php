<?php
declare(strict_types=1);

use App\AuthMiddleware;
use App\Config;
use App\Database;
use App\JwtHandler;
use App\Response;
use App\Validator;

require __DIR__ . '/../vendor/autoload.php';

Config::load(dirname(__DIR__));

function method(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return rtrim($uri, '/');
}

function jsonBody(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function bearerPayloadOrNull(): ?array
{
    $token = JwtHandler::bearerToken();
    if (!$token) {
        return null;
    }

    try {
        return JwtHandler::decode($token);
    } catch (Throwable $e) {
        return null;
    }
}

function requireCourseOwnership(PDO $pdo, int $emneId, int $lecturerId): array
{
    $stmt = $pdo->prepare('
        SELECT id, navn, lecturer_id
        FROM emner
        WHERE id = :id AND lecturer_id = :lecturer_id
    ');
    $stmt->execute([
        ':id' => $emneId,
        ':lecturer_id' => $lecturerId,
    ]);

    $emne = $stmt->fetch();
    if (!$emne) {
        Response::error('No access to this course', 403);
    }

    return $emne;
}

function requireEmneWithPin(PDO $pdo, int $emneId, string $pin): array
{
    $stmt = $pdo->prepare('SELECT id, navn, pin FROM emner WHERE id = :id');
    $stmt->execute([':id' => $emneId]);
    $emne = $stmt->fetch();

    if (!$emne) {
        Response::error('Emne not found', 404);
    }

    if ((string)$emne['pin'] !== $pin) {
        Response::error('Invalid emne or pin', 403);
    }

    return $emne;
}

function ensureMessageBelongsToCourse(PDO $pdo, int $messageId, int $emneId): void
{
    $stmt = $pdo->prepare('SELECT id FROM meldinger WHERE id = :id AND emne_id = :emne_id');
    $stmt->execute([
        ':id' => $messageId,
        ':emne_id' => $emneId,
    ]);

    if (!$stmt->fetch()) {
        Response::error('Message not found', 404);
    }
}

$method = method();
$path = path();


if ($method === 'GET' && $path === '/steg2/api/health') {
    Response::json(['ok' => true]);
}

$pdo = Database::connect();


if ($method === 'POST' && $path === '/steg2/api/register/student') {
    $body = jsonBody();

    $username = trim((string)($body['username'] ?? ''));
    $password = (string)($body['password'] ?? '');
    $email = trim((string)($body['email'] ?? ''));
    $navn = trim((string)($body['navn'] ?? ''));
    $studieretning = trim((string)($body['studieretning'] ?? ''));
    $studiekull = trim((string)($body['studiekull'] ?? ''));

    if (
        !Validator::username($username) ||
        !Validator::password($password) ||
        !Validator::email($email) ||
        !Validator::name($navn) ||
        !Validator::text($studieretning, 2, 100) ||
        !Validator::text($studiekull, 1, 20)
    ) {
        Response::error('Invalid input data', 400);
    }

    $check = $pdo->prepare('
        SELECT id
        FROM students
        WHERE username = :username OR email = :email
    ');
    $check->execute([
        ':username' => $username,
        ':email' => $email,
    ]);

    if ($check->fetch()) {
        Response::error('Username or email already exists', 409);
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('
        INSERT INTO students (username, password_hash, email, navn, studieretning, studiekull)
        VALUES (:username, :password_hash, :email, :navn, :studieretning, :studiekull)
    ');
    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $hashedPassword,
        ':email' => $email,
        ':navn' => $navn,
        ':studieretning' => $studieretning,
        ':studiekull' => $studiekull,
    ]);

    Response::json([
        'ok' => true,
        'message' => 'Student registrert.',
    ], 201);
}


if ($method === 'POST' && $path === '/steg2/api/register/lecturer') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $email = trim((string)($_POST['email'] ?? ''));
    $navn = trim((string)($_POST['navn'] ?? ''));
    $emneNavn = trim((string)($_POST['emne_navn'] ?? ''));
    $emneBeskrivelse = trim((string)($_POST['emne_beskrivelse'] ?? ''));
    $pin = trim((string)($_POST['pin'] ?? ''));

    if (
        !Validator::username($username) ||
        !Validator::password($password) ||
        !Validator::email($email) ||
        !Validator::name($navn) ||
        !Validator::text($emneNavn, 2, 100) ||
        ($emneBeskrivelse !== '' && !Validator::text($emneBeskrivelse, 0, 1000)) ||
        !Validator::pin($pin)
    ) {
        Response::error('Invalid input data', 400);
    }

    $check = $pdo->prepare('
        SELECT id
        FROM lecturers
        WHERE username = :username OR email = :email
    ');
    $check->execute([
        ':username' => $username,
        ':email' => $email,
    ]);

    if ($check->fetch()) {
        Response::error('Username or email already exists', 409);
    }

    $bildePath = null;

    if (isset($_FILES['bilde']) && $_FILES['bilde']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['bilde']['tmp_name'];
        $orig = $_FILES['bilde']['name'] ?? '';
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowedExt, true)) {
            Response::error('Ugyldig bildeformat. Bruk jpg/jpeg/png/gif.', 400);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmp) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
        if (!$mime || !in_array($mime, $allowedMime, true)) {
            Response::error('Ugyldig filtype.', 400);
        }

        if (filesize($tmp) > 5 * 1024 * 1024) {
            Response::error('Bildet er for stort. Maks 5MB.', 400);
        }

        if (@getimagesize($tmp) === false) {
            Response::error('Filen er ikke et gyldig bilde.', 400);
        }

        $uploadDir = dirname(__DIR__) . '/uploads/lecturers/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            Response::error('Kunne ikke opprette opplastingsmappe.', 500);
        }

        $filename = 'lecturer_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $uploadDir . $filename;
        $bildePath = 'uploads/lecturers/' . $filename;

        if (!move_uploaded_file($tmp, $dest)) {
            Response::error('Kunne ikke lagre bildet på serveren.', 500);
        }
    } elseif (isset($_FILES['bilde']) && $_FILES['bilde']['error'] !== UPLOAD_ERR_NO_FILE) {
        Response::error('Feil ved bildeopplasting.', 400);
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('
            INSERT INTO lecturers (username, password_hash, email, navn, bilde)
            VALUES (:username, :password_hash, :email, :navn, :bilde)
        ');
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => $hashedPassword,
            ':email' => $email,
            ':navn' => $navn,
            ':bilde' => $bildePath,
        ]);

        $lecturerId = (int)$pdo->lastInsertId();

        $stmt2 = $pdo->prepare('
            INSERT INTO emner (navn, beskrivelse, pin, lecturer_id)
            VALUES (:navn, :beskrivelse, :pin, :lecturer_id)
        ');
        $stmt2->execute([
            ':navn' => $emneNavn,
            ':beskrivelse' => $emneBeskrivelse,
            ':pin' => $pin,
            ':lecturer_id' => $lecturerId,
        ]);

        $emneId = (int)$pdo->lastInsertId();

        $stmt3 = $pdo->prepare('UPDATE lecturers SET emne_id = :emne_id WHERE id = :id');
        $stmt3->execute([
            ':emne_id' => $emneId,
            ':id' => $lecturerId,
        ]);

        $pdo->commit();

        Response::json([
            'ok' => true,
            'lecturer_id' => $lecturerId,
            'emne_id' => $emneId,
        ], 201);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        Response::error('Feil ved registrering av foreleser', 500);
    }
}


if ($method === 'POST' && $path === '/steg2/api/login') {
    $body = jsonBody();

    $username = trim((string)($body['username'] ?? ''));
    $password = (string)($body['password'] ?? '');
    $role = trim((string)($body['role'] ?? 'student'));

    if ($username === '' || $password === '' || !in_array($role, ['student', 'lecturer'], true)) {
        Response::error('Invalid input', 400);
    }

    $table = $role === 'lecturer' ? 'lecturers' : 'students';

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM {$table} WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string)$user['password_hash'])) {
        Response::error('Wrong username or password', 401);
    }

    $token = JwtHandler::create((int)$user['id'], (string)$user['username'], $role);

    Response::json([
        'ok' => true,
        'token' => $token,
        'role' => $role,
        'username' => $user['username'],
    ]);
}

// important to understand that this doesn't log the user out. We could add a black list of JWT tokens upon log out. But it is easier and works good enough to just let the token expire. 
if ($method === 'POST' && $path === '/steg2/api/logout') {
    Response::json([
        'ok' => true,
    ]);
}


if ($method === 'GET' && $path === '/steg2/api/me') {
    $payload = AuthMiddleware::requireAuth();

    Response::json([
        'ok' => true,
        'user' => [
            'id' => $payload['sub'] ?? null,
            'username' => $payload['username'] ?? null,
            'role' => $payload['role'] ?? null,
        ]
    ]);
}


if ($method === 'GET' && $path === '/steg2/api/emner') {
    $payload = AuthMiddleware::requireAuth();

    if (($payload['role'] ?? '') === 'lecturer') {
        $stmt = $pdo->prepare('
            SELECT id, navn, beskrivelse
            FROM emner
            WHERE lecturer_id = :lecturer_id
            ORDER BY navn
        ');
        $stmt->execute([
            ':lecturer_id' => (int)$payload['sub'],
        ]);
    } else {
        $stmt = $pdo->query('SELECT id, navn, beskrivelse FROM emner ORDER BY navn');
    }

    Response::json([
        'emner' => $stmt->fetchAll(),
    ]);
}

// GET meldinger som viser meldinger. hvis det er en lærer rolle med valid JWT token, trenger ikke PIN
if ($method === 'GET' && preg_match('#^/steg2/api/emner/(\d+)/meldinger$#', $path, $m)) {
    $emneId = (int)$m[1];
    $payload = bearerPayloadOrNull();

    if ($payload && (($payload['role'] ?? '') === 'lecturer')) {
        $emne = requireCourseOwnership($pdo, $emneId, (int)$payload['sub']);

        $stmt = $pdo->prepare('
            SELECT id, melding, created_at, besvart, svar
            FROM meldinger
            WHERE emne_id = :emne_id
            ORDER BY created_at DESC
        ');
        $stmt->execute([':emne_id' => $emneId]);

        Response::json([
            'meldinger' => $stmt->fetchAll(),
            'emne' => [
                'id' => $emne['id'],
                'navn' => $emne['navn'],
            ],
        ]);
    }

    $pin = trim((string)($_GET['pin'] ?? ''));
    if (!Validator::pin($pin)) {
        Response::error('pin is required and must be 4 digits', 400);
    }

    $emne = requireEmneWithPin($pdo, $emneId, $pin);

    $stmt = $pdo->prepare('
        SELECT id, melding, created_at, besvart, svar
        FROM meldinger
        WHERE emne_id = :emne_id
        ORDER BY created_at DESC
    ');
    $stmt->execute([':emne_id' => $emneId]);

    Response::json([
        'meldinger' => $stmt->fetchAll(),
        'emne' => [
            'id' => $emne['id'],
            'navn' => $emne['navn'],
        ],
    ]);
}


// POST melding, bare for studenter
if ($method === 'POST' && preg_match('#^/steg2/api/emner/(\d+)/meldinger$#', $path, $m)) {
    $payload = AuthMiddleware::requireAuth('student');

    $emneId = (int)$m[1];
    $body = jsonBody();
    $melding = trim((string)($body['melding'] ?? ''));

    if (!Validator::text($melding, 1, 1000)) {
        Response::error('Invalid message', 400);
    }

    $check = $pdo->prepare('SELECT id FROM emner WHERE id = :id');
    $check->execute([':id' => $emneId]);
    if (!$check->fetch()) {
        Response::error('Emne not found', 404);
    }

    $stmt = $pdo->prepare('
        INSERT INTO meldinger (student_id, emne_id, melding)
        VALUES (:student_id, :emne_id, :melding)
    ');
    $stmt->execute([
        ':student_id' => (int)$payload['sub'],
        ':emne_id' => $emneId,
        ':melding' => $melding,
    ]);

    Response::json([
        'ok' => true,
        'message_id' => (int)$pdo->lastInsertId(),
    ], 201);
}

// POST svar, bare for forelesere. 
if ($method === 'POST' && preg_match('#^/steg2/api/meldinger/(\d+)/svar$#', $path, $m)) {
    $payload = AuthMiddleware::requireAuth('lecturer');

    $messageId = (int)$m[1];
    $body = jsonBody();
    $svar = trim((string)($body['svar'] ?? ''));

    if (!Validator::text($svar, 1, 1000)) {
        Response::error('svar is required', 400);
    }

    $check = $pdo->prepare('
        SELECT m.id
        FROM meldinger m
        JOIN emner e ON m.emne_id = e.id
        WHERE m.id = :message_id AND e.lecturer_id = :lecturer_id
    ');
    $check->execute([
        ':message_id' => $messageId,
        ':lecturer_id' => (int)$payload['sub'],
    ]);

    if (!$check->fetch()) {
        Response::error('No access to this message', 403);
    }

    $upd = $pdo->prepare('
        UPDATE meldinger
        SET svar = :svar, besvart = 1
        WHERE id = :id
    ');
    $upd->execute([
        ':svar' => $svar,
        ':id' => $messageId,
    ]);

    Response::json(['ok' => true]);
}

// POST kommentar, for gjest med PIN
if ($method === 'POST' && preg_match('#^/steg2/api/meldinger/(\d+)/comment$#', $path, $m)) {
    $messageId = (int)$m[1];
    $body = jsonBody();

    $comment = trim((string)($body['comment'] ?? ''));
    $emneId = (int)($body['emne_id'] ?? 0);
    $pin = trim((string)($body['pin'] ?? ''));

    if ($emneId <= 0 || !Validator::pin($pin) || !Validator::text($comment, 1, 1000)) {
        Response::error('comment, emne_id, pin required', 400);
    }

    requireEmneWithPin($pdo, $emneId, $pin);
    ensureMessageBelongsToCourse($pdo, $messageId, $emneId);

    $stmt = $pdo->prepare('
        INSERT INTO comments (message_id, comment_text)
        VALUES (:message_id, :comment_text)
    ');
    $stmt->execute([
        ':message_id' => $messageId,
        ':comment_text' => $comment,
    ]);

    Response::json([
        'ok' => true,
        'comment_id' => (int)$pdo->lastInsertId(),
    ], 201);
}

// POST rapport, for gjest med pin
if ($method === 'POST' && preg_match('#^/steg2/api/meldinger/(\d+)/report$#', $path, $m)) {
    $messageId = (int)$m[1];
    $body = jsonBody();

    $reason = trim((string)($body['reason'] ?? ''));
    $emneId = (int)($body['emne_id'] ?? 0);
    $pin = trim((string)($body['pin'] ?? ''));

    if ($emneId <= 0 || !Validator::pin($pin) || !Validator::text($reason, 1, 255)) {
        Response::error('reason, emne_id, pin required', 400);
    }

    requireEmneWithPin($pdo, $emneId, $pin);
    ensureMessageBelongsToCourse($pdo, $messageId, $emneId);

    $stmt = $pdo->prepare('
        INSERT INTO reports (message_id, reason)
        VALUES (:message_id, :reason)
    ');
    $stmt->execute([
        ':message_id' => $messageId,
        ':reason' => $reason,
    ]);

    Response::json([
        'ok' => true,
        'report_id' => (int)$pdo->lastInsertId(),
    ], 201);
}

// POST rapport
if ($method === 'POST' && preg_match('#^/steg2/api/emner/(\d+)/meldinger/(\d+)/report$#', $path, $m)) {
    $emneId = (int)$m[1];
    $messageId = (int)$m[2];
    $body = jsonBody();

    $pin = trim((string)($body['pin'] ?? ''));
    $reason = trim((string)($body['reason'] ?? ''));

    if ($emneId <= 0 || $messageId <= 0) {
        Response::error('Invalid emne_id or message_id', 400);
    }

    if (!Validator::pin($pin)) {
        Response::error('pin is required', 400);
    }

    if ($reason !== '' && !Validator::text($reason, 0, 255)) {
        Response::error('Invalid reason', 400);
    }

    requireEmneWithPin($pdo, $emneId, $pin);
    ensureMessageBelongsToCourse($pdo, $messageId, $emneId);

    $stmt = $pdo->prepare('
        INSERT INTO reports (message_id, emne_id, reason)
        VALUES (:message_id, :emne_id, :reason)
    ');
    $stmt->execute([
        ':message_id' => $messageId,
        ':emne_id' => $emneId,
        ':reason' => $reason,
    ]);

    Response::json([
        'ok' => true,
        'report_id' => (int)$pdo->lastInsertId(),
    ], 201);
}


if ($method === 'POST' && $path === '/steg2/api/forgot-password/student') {
    $body = jsonBody();
    $email = trim((string)($body['email'] ?? ''));

    if (!Validator::email($email)) {
        Response::error('Email is required', 400);
    }

    Response::json([
        'ok' => true,
        'message' => 'If the account exists, a password reset process will be started.'
    ]);
}


if ($method === 'POST' && $path === '/steg2/api/forgot-password/lecturer') {
    $body = jsonBody();
    $email = trim((string)($body['email'] ?? ''));

    if (!Validator::email($email)) {
        Response::error('Email is required', 400);
    }

    Response::json([
        'ok' => true,
        'message' => 'If the account exists, a password reset process will be started.'
    ]);
}

Response::error('Not found', 404);