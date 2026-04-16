<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dokumentasjon - Steg 1</title>
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
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Steg 1 Dokumentasjon</h1>

      <div class="meta">
        <div class="box">
          <p><strong>Laget med:</strong><br>
            PHP 8.3, MySQL 8.0, Apache 2.4, phpMyAdmin<br>
            <strong>Frontend:</strong> HTML + CSS (integrert i PHP-sider)
          </p>
        </div>

        <div class="box">
          <p><strong>Server:</strong><br>
            <code>158.39.188.224/steg1</code>
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
      <p>
        I steg 1 har vi jobbet samlet som gruppe gjennom hele prosessen, med faste arbeidsøkter der vi planla neste steg,
        avklarte hva som måtte på plass, og testet flyten sammen. Samtidig har vi fordelt arbeidet slik at hvert gruppemedlem
        har hatt ansvar for sin del (for eksempel registrering, innlogging, emnevisning, meldinger og styling), før vi har
        satt sammen delene og sjekket at helheten fungerer.
      </p>

      <p>
        For å effektivisere utviklingen i en periode med kort tidsfrist har vi benyttet flere hjelpemidler:
      </p>
      <ul>
        <li>Remote SSH fra VS Code for å jobbe direkte mot servermiljøet og teste i samme miljø som innleveringen kjører på.</li>
        <li>ChatGPT og Perplexity som støtte til feilsøking, forslag til struktur og rask forståelse av PHP/MySQL-mønstre.</li>
        <li>phpMyAdmin til å opprette/justere tabeller og teste innhold i databasen under utvikling.</li>
      </ul>

      <p>
        Vi har prioritert funksjonalitet og en forståelig brukerflyt, og har bevisst holdt designet enkelt i tråd med oppgaveteksten.
        Videre forbedringer og mer systematisk arbeid med kvalitet/sikkerhet gjøres i senere steg.
      </p>
      <div class="back-nav">
        <a href="index.php">← Tilbake til forsiden</a>
      </div>
    </div> </div> </body>
    </div>
  </div>
</body>
</html>
