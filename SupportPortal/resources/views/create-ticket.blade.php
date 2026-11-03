<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Neues Ticket | Supportportal</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

@php
    $user = auth()->user();

    $nameParts = preg_split(
        '/\s+/u',
        trim($user->name),
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    $initials = '';

    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
@endphp

<aside class="sidebar">
    <a href="{{ route('tickets.index') }}" class="brand">
        Supportportal
    </a>

    <p class="nav-heading">Kundenbereich</p>

    <nav class="sidebar-nav" aria-label="Hauptnavigation">
        <a href="{{ route('tickets.index') }}">
            Meine Tickets
        </a>

        <a href="{{ route('create-ticket') }}" aria-current="page">
            Neues Ticket
        </a>
    </nav>

    <div class="account">
        <span class="avatar" aria-hidden="true">
            {{ $initials }}
        </span>

        <div>
            <strong class="account-name">{{ $user->name }}</strong>
            <span class="account-role">Kundenkonto</span>
        </div>
    </div>
</aside>

<div class="page">

    <header class="topbar">
        <nav class="breadcrumb" aria-label="Brotkrumennavigation">
            <a href="{{ route('tickets.index') }}">
                Meine Tickets
            </a>

            <span aria-hidden="true">/</span>

            <strong aria-current="page">
                Neues Ticket
            </strong>
        </nav>

        <span class="avatar" aria-label="{{ $user->name }}">
            {{ $initials }}
        </span>
    </header>

    <nav class="mobile-nav" aria-label="Mobile Hauptnavigation">
        <a href="{{ route('tickets.index') }}">
            Meine Tickets
        </a>

        <a href="{{ route('create-ticket') }}" aria-current="page">
            Neues Ticket
        </a>
    </nav>

    <main class="content">

        <div class="page-heading">
            <h1>Neues Ticket erstellen</h1>
            <p>Beschreibe dein Anliegen, damit wir dir weiterhelfen können.</p>
        </div>

        <section class="ticket-card" aria-labelledby="ticket-details-heading">

            <div class="card-heading">
                <h2 id="ticket-details-heading">Ticketdetails</h2>
                <p>Mit * markierte Felder sind Pflichtfelder.</p>
            </div>

            <form
                id="create-ticket-form"
                action="{{ route('create-ticket.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="form-body">

                    @if ($errors->any())
                        <div class="field field-full">
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="field field-full">
                        <label for="ticket-subject" class="field-label">
                            Betreff
                            <span class="required" aria-hidden="true">*</span>
                        </label>

                        <input
                            type="text"
                            id="ticket-subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            class="form-control @error('subject') is-invalid @enderror"
                            placeholder="Zum Beispiel: Rechnung lässt sich nicht herunterladen"
                            maxlength="255"
                            aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="ticket-category" class="field-label">
                            Kategorie
                            <span class="optional">(optional)</span>
                        </label>

                        <select
                            id="ticket-category"
                            name="category"
                            class="form-control @error('category') is-invalid @enderror"
                            aria-invalid="{{ $errors->has('category') ? 'true' : 'false' }}"
                        >
                            <option value="" @selected(old('category', '') === '')>
                                Bitte auswählen
                            </option>

                            <option value="general" @selected(old('category') === 'general')>
                                Allgemeine Anfrage
                            </option>

                            <option value="technical" @selected(old('category') === 'technical')>
                                Technisches Problem
                            </option>

                            <option value="billing" @selected(old('category') === 'billing')>
                                Rechnung und Zahlung
                            </option>

                            <option value="account" @selected(old('category') === 'account')>
                                Konto und Anmeldung
                            </option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="ticket-priority" class="field-label">
                            Priorität
                            <span class="required" aria-hidden="true">*</span>
                        </label>

                        <select
                            id="ticket-priority"
                            name="priority"
                            class="form-control @error('priority') is-invalid @enderror"
                            aria-invalid="{{ $errors->has('priority') ? 'true' : 'false' }}"
                            required
                        >
                            <option value="low" @selected(old('priority', 'normal') === 'low')>
                                Niedrig
                            </option>

                            <option value="normal" @selected(old('priority', 'normal') === 'normal')>
                                Normal
                            </option>

                            <option value="high" @selected(old('priority', 'normal') === 'high')>
                                Hoch
                            </option>
                        </select>
                    </div>

                    <div class="field field-full">
                        <label for="ticket-description" class="field-label">
                            Beschreibung
                            <span class="required" aria-hidden="true">*</span>
                        </label>

                        <textarea
                            id="ticket-description"
                            name="description"
                            class="form-control @error('description') is-invalid @enderror"
                            rows="8"
                            maxlength="10000"
                            placeholder="Was ist passiert und wobei benötigst du Unterstützung?"
                            aria-describedby="description-help"
                            aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                            required
                        >{{ old('description') }}</textarea>

                        <p id="description-help" class="field-help">
                            Nenne möglichst die einzelnen Schritte und eine eventuell
                            angezeigte Fehlermeldung.
                        </p>
                    </div>

                    <!--
                        Der bisherige TicketController speichert noch keine Dateien.
                        Das Uploadfeld erst aktivieren, wenn Anhänge serverseitig
                        validiert und gespeichert werden.
                    -->
                    <div class="field field-full">
                        <label for="ticket-attachments" class="field-label">
                            Anhänge
                            <span class="optional">(optional)</span>
                        </label>

                        <div class="attachment-box">
                            <input
                                type="file"
                                id="ticket-attachments"
                                name="attachments[]"
                                class="file-input"
                                accept=".pdf,.jpg,.jpeg,.png"
                                aria-describedby="attachments-help"
                                multiple
                                disabled
                            >

                            <p id="attachments-help" class="field-help">
                                Anhänge sind derzeit noch nicht verfügbar.
                            </p>
                        </div>
                    </div>

                </div>

                <div class="form-footer">
                    <p>
                        Du findest deine Anfrage anschließend unter „Meine Tickets“.
                    </p>

                    <div class="form-actions">
                        <a href="{{ route('tickets.index') }}"
                           class="button button-secondary">
                            Abbrechen
                        </a>

                        <button type="submit" class="button button-primary">
                            Ticket erstellen
                        </button>
                    </div>
                </div>

            </form>
        </section>
    </main>
</div>

</body>
</html>
