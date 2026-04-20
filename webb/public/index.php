<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>CoA - Crisis Containment Service Report</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body prefix="schema: http://schema.org">
<article resource="#" typeof="schema:ScholarlyArticle">
    <header>
        <h1>CoA (Crisis Containment Service)</h1>
        <div role="contentinfo">
            <address>
                Echipa: Gheoca Razvan si Stupu Eduard <br>
                Universitatea "Alexandru Ioan Cuza", Facultatea de Informatică Iasi
            </address>
        </div>
    </header>

    <section role="doc-abstract">
        <h2>Abstract</h2>
        <p>CoA este o platformă Web pentru gestionarea situațiilor de urgență, oferind alerte în timp real, localizarea adăposturilor și rute de salvare, utilizând protocolul CAP și arhitectura SOA.</p>
    </section>

    <section id="introduction">
        <h2>1. Introducere</h2>
        <p>Scopul aplicației este de a centraliza informațiile despre calamități și de a facilita comunicarea rapidă între autorități și populație.</p>
    </section>

    <section id="user-interaction">
        <h2>2. Interacțiunea cu Utilizatorul</h2>
        <ul>
            <li><strong>Cetățean:</strong> Vizualizează harta alertelor, caută adăposturi, primește notificări.</li>
            <li><strong>Autoritate:</strong> Autentificare, publicare alerte (CAP), gestionare rute de evacuare.</li>
        </ul>
    </section>

    <section id="architecture">
        <h2>3. Arhitectura Sistemului</h2>
        <p>Sistemul urmează modelul <strong>Service-Oriented Architecture (SOA)</strong>. Backend-ul PHP funcționează ca un API REST, servind date JSON către frontend prin AJAX.</p>
        <figure typeof="sa:image">
            <img src="../../images/C4.png" alt="C4 Diagram">
            <figcaption>Arhitectura de ansamblu conform modelului C4.</figcaption>
        </figure>
    </section>

    <section id="requirements">
        <h2>4. Cerințe Tehnice</h2>
        <ul>
            <li><strong>Backend:</strong> Vanilla PHP (fără framework-uri).</li>
            <li><strong>Bază de date:</strong> MySQL (gestionată via PDO).</li>
            <li><strong>Frontend:</strong> HTML5, CSS3 (Responsive), Vanilla JavaScript.</li>
            <li><strong>Protocoale:</strong> Common Alerting Protocol (CAP) pentru alerte.</li>
        </ul>
    </section>

    <section id="data-management">
        <h2>5. Managementul Datelor</h2>
        <p>Datele sunt preluate prin scraping/fetching de la INFP și stocate local. Sistemul permite exportul rapoartelor în formate <strong>CSV</strong> și <strong>JSON</strong>.</p>
    </section>

    <section id="security">
        <h2>6. Securitate</h2>
        <p>Implementarea include tehnici împotriva SQL Injection (prepared statements) și XSS (sanitizarea datelor de ieșire).</p>
    </section>
</article>
</body>
</html>