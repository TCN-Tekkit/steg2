<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dokumentasjon - Steg 2</title>
  <style>
    :root{
      --bg:#0f172a;
      --card:#111827;
      --muted:#94a3b8;
      --text:#e5e7eb;
      --line:#263042;
      --link:#60a5fa;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(180deg, #0b1022 0%, #0f172a 100%);
      color: var(--text);
    }
    .wrap{
      max-width: 900px;
      margin: 0 auto;
      padding: 28px 16px 40px;
    }
    .card{
      background: rgba(17, 24, 39, .85);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    h1{
      margin:0 0 8px;
      font-size: 28px;
      letter-spacing: .2px;
    }
    h2{
      margin: 18px 0 10px;
      font-size: 20px;
      letter-spacing: .2px;
    }
    p{
      margin: 10px 0;
      color: var(--muted);
      line-height: 1.55;
      font-size: 14px;
    }
    strong{ color: var(--text); }
    ul{
      margin: 8px 0 0;
      padding-left: 18px;
      color: var(--muted);
    }
    li{
      margin: 6px 0;
      line-height: 1.45;
      font-size: 14px;
    }
    hr{
      border:0;
      border-top: 1px solid var(--line);
      margin: 18px 0;
      opacity:.9;
    }
    .meta{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 12px;
      margin-top: 12px;
    }
    .meta .box{
      border: 1px solid var(--line);
      background: rgba(2, 6, 23, .25);
      border-radius: 14px;
      padding: 14px;
    }
    .back-nav {
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid var(--line);
    display: flex;
    justify-content: flex-start;
    }
    a{
      color: var(--link);
      text-decoration:none;
      font-weight: 800;
    }
    a:hover{ text-decoration: underline; }
    code{
      color:#bfdbfe;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
      font-size: 13px;
    }

    table {
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 20px;
}

th {
  background-color: #333030;
}

th, td {
  border: 1px solid #ccc;
  text-align: left;
  padding: 8px;
}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Steg 2 Dokumentasjon</h1>

      <div class="meta">
        <div class="box">
          <p><strong>Laget med:</strong><br>
            PHP 8.3, MySQL 8.0, Apache 2.4, phpMyAdmin<br>
            <strong>Frontend:</strong> HTML + CSS (integrert i PHP-sider)
          </p>
        </div>

        <div class="box">
          <p><strong>Server:</strong><br>
            <code>158.39.188.224/steg2</code>
          </p>
        </div>

        <div class="box">
          <p><strong>Gruppe:</strong><br>
            Are Sebastian Haugs-Eilertsen (<code>are.s.haugs-eilertsen@hiof.no</code>)<br>
            Isak Valdersnes Olsvold (<code>isak.v.olsvold@hiof.no</code>)<br>
            Sayotika U. Trulsen (<code>sayotikt@hiof.no</code>)<br>
            André Moore (<code>andremoo@hiof.no</code>)
          </p>
        </div>
      </div>

      <h2>Funksjoner implementert</h2>
      <ul>
        <li>Student/foreleser registrering (navn, passord, email)</li>
        <li>Login (session-basert)</li>
        <li>Vis emner (fra DB)</li>
        <li>Logout</li>
      </ul>

      <hr>

      <h2>Hvordan vi har jobbet</h2>
      <h3>1. Endringer fra Steg 1 til Steg 2</h3>
      <p>
        Vi skulle ønsket å implementere IP-blokkering, men på grunn av VPN så kunne vi ikke jobbe effektivt med dette, og har derfor valgt å prioritere andre sikkerhetstiltak som sentral logging og isolert kodekjøring.
      </p>
      <p>Endret passordkrav til å ha minimum 8 tegn og inneholde både store og små bokstaver, tall og spesialtegn.</p>
      <p>Samme epost kan ikke registreres to ganger.</p>
      
      <h3>2. Risk Management Framework (RMF)</h3>
      <p>
        Før vi startet kodingen i Steg 2, gjennomførte vi en forenklet risikoanalyse basert på RMF-prinsipper for å planlegge tiltakene våre.
      </p>

      <h4>A. Sikkerhetskrav og Abuse Cases</h4>
      <h2>Abuse Cases</h2>

    <table border="1" cellpadding="8" cellspacing="0">
      <tr>
        <th>ID</th>
        <th>Use Case</th>
        <th>Attacker</th>
        <th>Abuse Case</th>
        <th>Description</th>
        <th>Impact</th>
        <th>Mitigation</th>
      </tr>

      <tr>
        <td>1</td>
        <td>phpMyAdmin Login</td>
        <td>Hacker</td>
        <td>Brute-force login</td>
        <td>Attacker attempts many username/password combinations.</td>
        <td>Unauthorized access to database.</td>
        <td>Rate limiting, account lockout, 2FA, IP blocking.</td>
      </tr>

      <tr>
        <td>2</td>
        <td>File upload</td>
        <td>Hacker / Malicious user</td>
        <td>Malicious file upload</td>
        <td>Upload of files with hidden PHP/SQL code (e.g. double extensions).</td>
        <td>Remote code execution or database compromise.</td>
        <td>Whitelist file types, MIME validation, block double extensions, rename files.</td>
      </tr>

      <tr>
        <td>3</td>
        <td>System availability</td>
        <td>Hacker</td>
        <td>Denial of Service</td>
        <td>Flooding system with requests or botnets.</td>
        <td>Service unavailable.</td>
        <td>Rate limiting, resource limits, load balancing.</td>
      </tr>

      <tr>
        <td>4</td>
        <td>Picture upload</td>
        <td>Hacker / User</td>
        <td>Resource exhaustion</td>
        <td>Uploading many or large files to fill disk.</td>
        <td>Disk full → system failure.</td>
        <td>File size limits, upload limits, storage monitoring.</td>
      </tr>

      <tr>
        <td>5</td>
        <td>Password reset</td>
        <td>Hacker</td>
        <td>Account hijacking</td>
        <td>Exploiting weak reset mechanism.</td>
        <td>Full account takeover.</td>
        <td>Secure tokens, expiration, 2FA, verification.</td>
      </tr>

      <tr>
        <td>6</td>
        <td>User input</td>
        <td>Hacker</td>
        <td>SQL Injection</td>
        <td>Malicious input alters SQL queries.</td>
        <td>Data leak or manipulation.</td>
        <td>Prepared statements, input validation, least privilege.</td>
      </tr>

      <tr>
        <td>7</td>
        <td>User input</td>
        <td>Hacker</td>
        <td>Stored XSS</td>
        <td>Malicious script stored and executed in browser.</td>
        <td>Session theft, phishing.</td>
        <td>Output encoding, CSP, input validation.</td>
      </tr>

      <tr>
        <td>8</td>
        <td>Session handling</td>
        <td>Hacker</td>
        <td>Session hijacking</td>
        <td>Stealing session cookies.</td>
        <td>Account takeover.</td>
        <td>HttpOnly, Secure cookies, session rotation.</td>
      </tr>

      <tr>
        <td>9</td>
        <td>Authentication</td>
        <td>System flaw</td>
        <td>Sensitive data exposure</td>
        <td>System returns sensitive data (e.g. passwords).</td>
        <td>Full compromise.</td>
        <td>Never return passwords, use hashing and tokens.</td>
      </tr>

    </table>

    <h2>Probability Scale</h2>

    <table>
      <tr>
        <th>Rating</th>
        <th>Title</th>
        <th>Probability</th>
      </tr>
      <tr><td>1</td><td>Unlikely</td><td>&lt; 10%</td></tr>
      <tr><td>2</td><td>Seldom</td><td>10–50%</td></tr>
      <tr><td>3</td><td>Possible</td><td>~50%</td></tr>
      <tr><td>4</td><td>Likely</td><td>60–80%</td></tr>
      <tr><td>5</td><td>Almost Certain</td><td>80–90%</td></tr>
      <tr><td>6</td><td>Certain</td><td>&gt; 90%</td></tr>
    </table>

    <h2>Risk Assessment</h2>

    <table>
      <tr>
        <th>Threat</th>
        <th>Probability</th>
        <th>Data Loss</th>
        <th>Code Attack</th>
        <th>DoS</th>
        <th>Data Exfiltration</th>
      </tr>

      <tr>
        <td>Exposed phpMyAdmin</td>
        <td>Seldom</td>
        <td>High</td>
        <td>Medium</td>
        <td>Medium</td>
        <td>High</td>
      </tr>

      <tr>
        <td>Weak password policy</td>
        <td>Possible</td>
        <td>Medium</td>
        <td>Medium</td>
        <td>Low</td>
        <td>High</td>
      </tr>

      <tr>
        <td>Duplicate users</td>
        <td>Possible</td>
        <td>Medium</td>
        <td>Medium</td>
        <td>Low</td>
        <td>High</td>
      </tr>

      <tr>
        <td>XSS</td>
        <td>Likely</td>
        <td>Low</td>
        <td>Medium</td>
        <td>Low</td>
        <td>High</td>
      </tr>

      <tr>
        <td>SQL Injection</td>
        <td>Likely</td>
        <td>High</td>
        <td>High</td>
        <td>Low</td>
        <td>High</td>
      </tr>

      <tr>
        <td>Open file upload</td>
        <td>Likely</td>
        <td>Medium</td>
        <td>High</td>
        <td>Low</td>
        <td>High</td>
      </tr>

      <tr>
        <td>No rate limiting</td>
        <td>Almost Certain</td>
        <td>Low</td>
        <td>Medium</td>
        <td>High</td>
        <td>Medium</td>
      </tr>

      <tr>
        <td>Exposed .env file</td>
        <td>Almost Certain</td>
        <td>High</td>
        <td>Medium</td>
        <td>Low</td>
        <td>High</td>
      </tr>

      <tr>
        <td>Brute force login</td>
        <td>Almost Certain</td>
        <td>Medium</td>
        <td>Low</td>
        <td>High</td>
        <td>Medium</td>
      </tr>

      <tr>
        <td>Overprivileged DB user</td>
        <td>Almost Certain</td>
        <td>High</td>
        <td>High</td>
        <td>Low</td>
        <td>High</td>
      </tr>

      <tr>
        <td>DoS attack</td>
        <td>Almost Certain</td>
        <td>Low</td>
        <td>Low</td>
        <td>High</td>
        <td>Low</td>
      </tr>

    </table>

      <h4>C. Code Review og Testing</h4>
      <p>
        Vi har gjennomført en manuell <strong>Code Review</strong> med fokus på inndatavalidering. Som en del av vår <strong>Risk-based security test</strong> har vi utført følgende:
      </p>
      <p>
        For å effektivisere utviklingen og ivareta sikkerheten i dette steget, har vi benyttet flere hjelpemidler og metoder:
      </p>
      <ul>
        <li><strong>Remote SSH:</strong> VS Code-utvidelse for å jobbe direkte mot servermiljøet, noe som sikrer at koden testes i samme miljø som den kjører på.</li>
        <li><strong>Graylog:</strong> Implementert som sentral loggløsning for å overvåke systemhendelser og fange opp feil (Error 500) i sanntid. Sikrer rask feilsøking og overvåking. Også implementert alerts for flere feilede innlogginger.</li>
        <li><strong>PHPSandbox:</strong> Brukt for å kjøre brukerdata i et isolert miljø, som for eksempel beskrivelser, kommentarer og navn, for å beskytte brukerene.</li>
        <li><strong>POST-metodikk:</strong> Vi har gått bort fra GET til POST for sensitive data for å hindre at PIN-koder eksponeres i nettleserens adresselinje.</li>
        <li><strong>KI-støtte:</strong> ChatGPT og Gemini som støtte til avansert feilsøking, tolking av loggfiler og optimalisering av PHP-mønstre.</li>
        <li><strong>phpMyAdmin:</strong> Brukt til å administrere tabeller, justere kolonnenavn og verifisere databaseinnholdet under migrering.</li>
      </ul>

      <h3> API dokkumentasjon </h3>

      <h4>Generiske feilmeldinger </h4>
          <p>Feilmeldingene som sendes da APIet ikke kunne fullføre handlingene er generiske slik at de ikke gir ut informasjon som røper backend informasjon. Et eksempel på dette er API-endepunktet som lar en bruker bytte passord hvis de glemt det. Når en bruker skriver inn en e-post, så svarer applikasjonen bare med «If the account exists, a password reset process will be started.» Tanken er at brukeren da får en engangs-link sendt, som lar dem endre passordet deres. For at prosjektet ikke skal bli for stort, så har vi ikke implementert funksjonalitet som sender en sånn link til brukerens mail. </p>

      <h4>JWT </h4>
          <p>Vi valgte å benytte oss av JWT tokens istedenfor sessions. Dette begrunnes ved at stateless autentisering er enklere da tokens inneholder «roles» for autentisering av forskjellige roller og deres rettigheter (foreleser, student og gjest). Dette gjør det tydeligere hva de forskjellige rollene har tilgang til. Sessions krever at serveren lagrer session data og må sjekke med brukerens session for hver request server-side, som tar opp ressurser. Dette gjør serveren mer sårbar for DOS og DDOS angrep. Med JWT tokens, så sendes det bare en hashet token til brukeren som spares lokalt, ikke server side. Serveren trenger ikke å slå opp en aktiv sesjon for hver forespørsel. I stedet kan den verifisere en hash eller et token som klienten sender med. Dette reduserer avhengigheten av server-side sesjonslagring og gjør det vanskeligere å kapre eller manipulere en brukers sesjon.  </p>
          <p>Vi planla også å implementere refresh tokens, men rakk ikke å integrere dette med databasen og eksisterende kode. I tillegg var det tenkt å lagre utloggede tokens i en svarteliste frem til de utløper, for å forhindre videre bruk. Slik løsningen fungerer nå, fjernes tokenet kun lokalt hos brukeren ved utlogging, og blir deretter ugyldig når det utløper automatisk etter kort tid. </p>

      <h4>Sanitering og prepared statements  </h4>
          <p>For å sikre oss for angrep gjennom input parameter fra brukere, så benytter vi oss av et kombinert sikkerhets lag for å sanitere og validere dataen. Preg_match med regex bruker vi for å svartliste farlige tegn, dataen som passerer denne sanitering spares så i prepared statements som validerer SQL spørringen før den sendes til databasen. Dette gjør det vanskeligere å bli utsatt for SQL injection.   </p>

      <h4>HTTPS, SSL og 2FA </h4>
          <p>Vi brukte oss av et lokalt SSL sertifikat og key som vi opprettet selv. Dette gjorde vi får å muliggjøre å bytte fra http til https. Motivasjonen til dette var for at vi ville bruke oss av 2fa for å logge inn på vår PHPMyAdmin SQL database. Vi begynte å forberede webserveren for å støtte yubi-keys, men vi fikk dessverre ikke tak på noen, og dem er veldig dyre å kjøpe.   </p>
      
      <h4>Request delay </h4>
          <p>Vi planla å bruke oss av request delay og counters for å motarbeide brute-force og skraping attakker. Dessverre så glemte vi helt å iverksette dette.    </p>
      
      <h4>Bilder </h4>
          <p>Vi har begrensninger på fil opplastning for registrering av foreleser. Disse sikrer for at alle filene har jpg, jpeg, png eller gif som file extention. Det sikrer dessverre ikke for angrep som bruker seg av double extention, eks. Payload.php.jpg. Vi har no-sniff som sikrer for noen double extension angrep, men ikke alle. Vi genererer også unike navn til alle filer opplastet for å videre sikre oss for double extention og oppdelte angrep. Vi har også begrenset størrelsen på bildet, så bilder større enn 1024x1024 og 5MB blir ikke godkjennt. </p>
      

      <div class="back-nav">
        <a href="index.php">← Tilbake til forsiden</a>
      </div>
    </div> </div> </body>
    </div>
  </div>
</body>
</html>
