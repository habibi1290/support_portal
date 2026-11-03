# Supportportal

Ein webbasiertes Ticketsystem zur Verwaltung von Supportanfragen,
entwickelt mit Laravel, PHP, Blade und Bootstrap.

Kunden können ihre Anliegen als Tickets erfassen und den Verlauf
verfolgen. Supportmitarbeiter erhalten eine gemeinsame Übersicht
und können Anfragen beantworten und zuständige Personen zuweisen.

## Funktionen

### Kundenbereich

- Registrierung, Anmeldung und Abmeldung
- Übersicht über die eigenen Tickets
- Tickets mit Betreff, Beschreibung, Kategorie und Priorität erstellen
- Bestehende Ticketdaten bearbeiten
- Nach Ticketnummer oder Betreff suchen
- Tickets nach Status filtern
- Nachrichten zum Ticket schreiben
- Nachrichtenverlauf mit Verfasser und Zeitpunkt anzeigen
- Tickets schließen und wieder öffnen
- Statistiken über die eigenen Tickets anzeigen

### Supportbereich

- Übersicht über alle Tickets
- Suche nach Ticketnummer, Betreff oder Kundenname
- Filter nach Status, Priorität und zuständiger Person
- Tickets an Supportmitarbeiter zuweisen
- Zuweisungen aufheben
- Tickets bearbeiten, beantworten, schließen und wieder öffnen
- Statistiken über alle Tickets anzeigen

## Technologien

| Bereich | Technologie |
| --- | --- |
| Backend | Laravel 13, PHP 8.4 |
| Frontend | Blade, HTML, Bootstrap 5, CSS |
| Datenzugriff | Laravel Eloquent |
| Datenbankschema | Laravel-Migrationen |
| Frontend-Build | Vite und npm |
| Lokale Entwicklungsumgebung | DDEV und Docker |

Die konkreten Abhängigkeiten und Versionen stehen in
`composer.json`, `composer.lock`, `package.json` und `package-lock.json`.

## Rollen und Berechtigungen

### Kunde

Ein normales Benutzerkonto kann auf die eigenen Tickets zugreifen.
Die Zuordnung erfolgt über `tickets.user_id`.

### Supportmitarbeiter

Ein Benutzer mit `is_support = true` erhält Zugriff auf den
Supportbereich. Die Berechtigung wird durch das Laravel-Gate
`access-support` geprüft.

Die Übersicht „Meine Tickets“ zeigt auch für Supportmitarbeiter
ausschließlich ihre eigenen Tickets. Die gemeinsame Übersicht
befindet sich unter „Supportbereich“.

Supportrechte werden gezielt vergeben und sind kein auswählbarer
Bestandteil der Registrierung.

## Ticketstatus

| Wert | Anzeige |
| --- | --- |
| `open` | Offen |
| `in_progress` | In Bearbeitung |
| `resolved` | Erledigt |

Neue Tickets erhalten den Status `open`.

Beim Schließen wird der Status auf `resolved` gesetzt. Auf erledigte
Tickets kann erst nach dem Wiederöffnen erneut geantwortet werden.

Beim Wiederöffnen wird der Status auf `open` gesetzt und der Zeitpunkt
in `reopened_at` gespeichert. „Wieder geöffnet“ ist kein eigener Statuswert.

Der Status `in_progress` wird angezeigt und kann gefiltert werden.
Das Zuweisen eines Tickets oder das Schreiben einer Antwort setzt
diesen Status derzeit nicht automatisch.

## Prioritäten

| Wert | Anzeige |
| --- | --- |
| `low` | Niedrig |
| `normal` | Normal |
| `high` | Hoch |

## Lokal installieren

### Voraussetzungen

- Git
- Docker mit laufender Docker-Umgebung
- DDEV

Die folgenden Schritte gelten für eine neue lokale Installation.

### 1. Repository herunterladen

`DEIN_GITHUB_NAME` durch den tatsächlichen GitHub-Benutzernamen ersetzen:

```bash
git clone https://github.com/DEIN_GITHUB_NAME/supportportal.git
cd supportportal
```

### 2. Umgebung vorbereiten

Die Datei `.env.example` als `.env` kopieren.

Falls das Repository noch keine `.ddev/config.yaml` enthält,
DDEV einmalig konfigurieren:

```bash
ddev config --project-type=laravel --docroot=public --php-version=8.4
```

Anschließend DDEV starten und die PHP-Abhängigkeiten installieren:

```bash
ddev start
ddev composer install
```

DDEV richtet beim Projekttyp Laravel die lokalen Datenbankwerte ein.
Für eine übliche DDEV-Konfiguration mit MySQL oder MariaDB lauten sie:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=db
DB_USERNAME=db
DB_PASSWORD=db
```

Diese Zugangsdaten gelten ausschließlich für die lokale DDEV-Umgebung.

### 3. Anwendung einrichten

```bash
ddev artisan key:generate
ddev artisan migrate
ddev npm install
ddev npm run build
ddev launch
```

Die lokale Adresse lässt sich auch mit folgendem Befehl anzeigen:

```bash
ddev describe
```

### 4. Benutzerkonto erstellen

Die Anwendung im Browser öffnen und über „Registrieren“ ein Konto
anlegen. Anschließend mit diesem Konto anmelden.

## Ein Supportkonto einrichten

Zuerst das gewünschte Konto regulär registrieren.

Danach im Projektterminal Tinker starten:

```bash
ddev artisan tinker
```

In der folgenden Zeile `support@example.com` durch die tatsächlich
registrierte E-Mail-Adresse ersetzen. Die gesamte Zeile zusammen eingeben:

```php
$user = \App\Models\User::where('email', 'support@example.com')->firstOrFail(); $user->is_support = true; $user->save();
```

Berechtigung prüfen:

```php
\Illuminate\Support\Facades\Gate::forUser($user)->allows('access-support');
```

Das Ergebnis muss `true` sein.

Tinker beenden:

```php
exit
```

Mit diesem Konto im Browser anmelden beziehungsweise die Seite
neu laden. Der Link „Supportbereich“ erscheint in der Navigation.

## Bedienung

1. Als Kunde anmelden und „Neues Ticket“ auswählen.
2. Betreff, Beschreibung, Kategorie und Priorität eingeben.
3. Das Ticket speichern und über den Pfeil in der Übersicht öffnen.
4. Über „Bearbeiten“ die Ticketdaten ändern.
5. Über das Antwortformular Nachrichten zum Ticket hinzufügen.
6. Nach Klärung des Anliegens das Ticket schließen.
7. Bei weiterem Klärungsbedarf das erledigte Ticket wieder öffnen.

Supportmitarbeiter verwenden „Supportbereich“, um alle Anfragen
zu durchsuchen, zuzuweisen und zu beantworten.

## Technischer Aufbau

| Bestandteil | Aufgabe |
| --- | --- |
| `routes/web.php` | Routen, HTTP-Methoden und Middleware |
| `app/Http/Controllers/TicketController.php` | Ticketverwaltung, Antworten, Suche, Filter und Zuweisungen |
| `app/Models/User.php` | Benutzerkonten und Supportkennzeichen |
| `app/Models/Ticket.php` | Ticketdaten und Beziehungen |
| `app/Models/TicketMessage.php` | Nachrichten eines Tickets |
| `app/Providers/AppServiceProvider.php` | Definition des Gates `access-support` |
| `resources/views/` | Blade-Ansichten |
| `resources/css/app.css` | Styles der Anwendung |
| `resources/js/app.js` | JavaScript-Einstiegspunkt |
| `database/migrations/` | Aufbau und Änderungen des Datenbankschemas |

### Datenmodell

- `users`: Benutzerkonten mit dem Berechtigungsfeld `is_support`.
- `tickets`: Anliegen mit Besitzer, Betreff, Beschreibung, Kategorie,
  Priorität, Status, zuständiger Person und Wiederöffnungszeitpunkt.
- `ticket_messages`: Nachrichten mit Ticketzuordnung und Verfasser.

Ein Benutzer kann mehrere Tickets erstellen.
Ein Ticket kann mehrere Nachrichten enthalten.
Über `assigned_to` kann einem Ticket ein Supportmitarbeiter zugewiesen werden.

### Wichtige Routen

| Methode | Pfad | Zweck |
| --- | --- | --- |
| GET | `/ticket` | Eigene Tickets |
| GET | `/create-ticket` | Formular für ein neues Ticket |
| POST | `/create-ticket` | Ticket speichern |
| GET | `/open-ticket/{id}` | Ticketdetails anzeigen |
| GET | `/edit/{id}` | Ticket bearbeiten |
| PUT | `/edit/{id}` | Ticketänderungen speichern |
| POST | `/open-ticket/{id}/reply` | Antwort speichern |
| PATCH | `/open-ticket/{id}/close` | Ticket schließen |
| PATCH | `/done/{id}/reopen` | Ticket wieder öffnen |
| GET | `/support` | Übersicht für den Support |
| PATCH | `/support/tickets/{id}/assign` | Zuständigkeit speichern |

### Zugriffskontrolle

Die Ticketfunktionen setzen eine Anmeldung voraus.

Die Methode `accessibleTickets()` begrenzt den Zugriff normaler
Benutzer auf eigene Tickets. Benutzer mit Supportberechtigung können
auch Tickets anderer Kunden bearbeiten.

Supportfunktionen werden zusätzlich über `access-support` geschützt.
Die Sichtbarkeit eines Links ersetzt keine serverseitige Zugriffskontrolle.

Formulare verwenden CSRF-Schutz. Eingaben werden serverseitig
validiert. Antworten, Schließen und Wiederöffnen verwenden
Datenbanktransaktionen und sperren den betroffenen Ticketdatensatz
während der Änderung.

## Manuelle Funktionsprüfung

Die folgenden Punkte dienen als Prüfliste, nicht als Testbericht:

- [ ] Registrierung, Anmeldung und Abmeldung funktionieren.
- [ ] Ein Kunde kann ein Ticket erstellen und bearbeiten.
- [ ] Ein zweites Kundenkonto kann dieses Ticket nicht aufrufen oder ändern.
- [ ] Ein Kundenkonto erhält keinen Zugriff auf `/support`.
- [ ] Ein Supportkonto kann fremde Tickets öffnen und beantworten.
- [ ] Zuweisungen, Suche, Filter und Seitennavigation funktionieren.
- [ ] Antworten erscheinen mit dem richtigen Verfasser.
- [ ] Ein geschlossenes Ticket akzeptiert keine weiteren Antworten.
- [ ] Nach dem Wiederöffnen sind Antworten wieder möglich.

## Aktueller Entwicklungsstand

Die Anwendung befindet sich in Entwicklung.

Dateianhänge sind noch nicht umgesetzt. Entsprechende Eingabefelder
sind deaktiviert.

Mögliche Erweiterungen:

- Dateianhänge mit Uploadvalidierung und Zugriffsschutz
- E-Mail-Benachrichtigungen
- Expliziter Wechsel zum Status „In Bearbeitung“
- Automatisierte Tests für Berechtigungen und Ticketabläufe
- Änderungsverlauf für Status und Zuständigkeiten

## Konfiguration und Zugangsdaten

Die Datei `.env`, lokale Datenbanken, Datenbankexporte und persönliche
Zugangsdaten gehören nicht ins Repository.

Die Datei `.env.example` enthält ausschließlich Beispielwerte.
