<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ticket #{{ $ticket->id }} bearbeiten – Supportportal</title>

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

    $statuses = [
        'open' => [
            'label' => 'Offen',
            'class' => 'bg-warning text-dark',
        ],
        'in_progress' => [
            'label' => 'In Bearbeitung',
            'class' => 'bg-primary text-white',
        ],
        'resolved' => [
            'label' => 'Erledigt',
            'class' => 'bg-success text-white',
        ],
    ];

    $currentStatus = $statuses[$ticket->status] ?? [
        'label' => 'Unbekannt',
        'class' => 'bg-secondary text-white',
    ];

    $isResolved = $ticket->status === 'resolved';

    $messages = $ticket->messages;
    $messageCount = $messages->count() + 1;
@endphp

    <!-- Seitenleiste -->
<aside class="sidebar">
    <a href="{{ route('tickets.index') }}" class="brand">
        Supportportal
    </a>

    <p class="nav-heading">Kundenbereich</p>

    <nav class="sidebar-nav" aria-label="Hauptnavigation">
        <a href="{{ route('tickets.index') }}">
            Meine Tickets
        </a>

        <a href="{{ route('create-ticket') }}">
            Neues Ticket
        </a>
    </nav>

    <div class="account">
        <span class="avatar" aria-hidden="true">
            {{ $initials }}
        </span>

        <div>
            <strong>{{ $user->name }}</strong>
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
                Ticket #{{ $ticket->id }}
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

        <a href="{{ route('create-ticket') }}">
            Neues Ticket
        </a>
    </nav>

    <main class="content">
        <!-- Überschrift -->
        <div class="page-heading">
            <p class="ticket-number">
                Ticket #{{ $ticket->id }}
            </p>

            <h1>{{ $ticket->subject }}</h1>

            <div class="heading-meta">
                <span class="status-badge {{ $currentStatus['class'] }}">
                    {{ $currentStatus['label'] }}
                </span>

                <span>Erstellt von {{ $user->name }}</span>

                @if ($ticket->created_at)
                    <time datetime="{{ $ticket->created_at->format('c') }}">
                        {{ $ticket->created_at->format('d.m.Y, H:i') }} Uhr
                    </time>
                @endif
            </div>

            <div class="page-actions">
                @if ($isResolved)
                    <button
                        type="button"
                        id="write-reply-button"
                        class="button button-primary"
                        disabled
                    >
                        Antwort schreiben
                    </button>
                @else
                    <a
                        href="#reply-message"
                        id="write-reply-button"
                        class="button button-primary"
                    >
                        Antwort schreiben
                    </a>
                @endif

                <a href="#ticket-settings" class="button button-secondary">
                    Ticketdaten bearbeiten
                </a>
            </div>
        </div>

        <!-- Erfolgsmeldung für Ticketänderungen -->
        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        <!-- Erfolgsmeldung für Antworten -->
        @if (session('reply_success'))
            <div class="alert alert-success" role="status">
                {{ session('reply_success') }}
            </div>
        @endif

        <!-- Fehler beim Bearbeiten der Ticketdaten -->
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="ticket-layout">
            <!-- Anfrage und gespeicherte Antworten -->
            <section class="card conversation" aria-labelledby="conversation-heading">
                <div class="card-heading">
                    <h2 id="conversation-heading">Unterhaltung</h2>

                    <span class="message-count">
                        {{ $messageCount }}
                        {{ $messageCount === 1 ? 'Nachricht' : 'Nachrichten' }}
                    </span>
                </div>

                <div class="messages">
                    <!-- Ursprüngliche Anfrage -->
                    <article class="message">
                        <header class="message-header">
                            <div class="message-author">
                                <span class="avatar" aria-hidden="true">
                                    {{ $initials }}
                                </span>

                                <div>
                                    <h3>{{ $user->name }}</h3>
                                    <span class="author-role">Kunde</span>
                                </div>
                            </div>

                            @if ($ticket->created_at)
                                <time datetime="{{ $ticket->created_at->format('c') }}">
                                    {{ $ticket->created_at->format('d.m.Y') }}
                                    ·
                                    {{ $ticket->created_at->format('H:i') }}
                                </time>
                            @endif
                        </header>

                        <div class="message-body">
                            <p style="white-space: pre-wrap;">{{ $ticket->description }}</p>
                        </div>
                    </article>

                    <!-- Gespeicherte Antworten -->
                    @foreach ($messages as $reply)
                        @php
                            $replyName = $reply->user?->name ?? 'Gelöschtes Konto';

                            $replyNameParts = preg_split(
                                '/\s+/u',
                                trim($replyName),
                                -1,
                                PREG_SPLIT_NO_EMPTY
                            );

                            $replyInitials = '';

                            foreach (array_slice($replyNameParts, 0, 2) as $part) {
                                $replyInitials .= mb_strtoupper(mb_substr($part, 0, 1));
                            }
                        @endphp

                        <article class="message">
                            <header class="message-header">
                                <div class="message-author">
                                    <span class="avatar" aria-hidden="true">
                                        {{ $replyInitials }}
                                    </span>

                                    <div>
                                        <h3>{{ $replyName }}</h3>
                                        <span class="author-role">Antwort</span>
                                    </div>
                                </div>

                                @if ($reply->created_at)
                                    <time datetime="{{ $reply->created_at->format('c') }}">
                                        {{ $reply->created_at->format('d.m.Y') }}
                                        ·
                                        {{ $reply->created_at->format('H:i') }}
                                    </time>
                                @endif
                            </header>

                            <div class="message-body">
                                <p style="white-space: pre-wrap;">{{ $reply->message }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Antwort schreiben und senden -->
                <section
                    id="ticket-answer"
                    class="reply-section"
                    aria-labelledby="reply-heading"
                >
                    <h2 id="reply-heading">Antwort schreiben</h2>

                    @if ($isResolved)
                        <p class="section-description">
                            Dieses Ticket ist erledigt. Öffne es erneut, um eine Antwort zu schreiben.
                        </p>
                    @else
                        <p class="section-description">
                            Ergänze weitere Informationen zu deiner Anfrage.
                        </p>
                    @endif

                    <!-- Fehler des Antwortformulars -->
                    @if ($errors->reply->any())
                        <div id="reply-errors" class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->reply->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        id="ticket-reply-form"
                        action="{{ route('edit.reply', ['id' => $ticket->id]) }}"
                        method="POST"
                    >
                        @csrf

                        <div class="field">
                            <label for="reply-message" class="field-label">
                                Nachricht
                                <span class="required" aria-hidden="true">*</span>
                            </label>

                            <textarea
                                id="reply-message"
                                name="message"
                                class="form-control reply-textarea @error('message', 'reply') is-invalid @enderror"
                                rows="8"
                                maxlength="5000"
                                placeholder="Schreibe deine Antwort …"
                                aria-describedby="reply-help{{ $errors->reply->has('message') ? ' reply-errors' : '' }}"
                                aria-invalid="{{ $errors->reply->has('message') ? 'true' : 'false' }}"
                                required
                                @disabled($isResolved)
                            >{{ old('message') }}</textarea>

                            <p id="reply-help" class="field-help">
                                Maximal 5.000 Zeichen.
                            </p>
                        </div>

                        <!-- Der Anhang-Upload ist noch nicht angebunden. -->
                        <div class="attachment-box">
                            <label for="reply-attachments" class="field-label">
                                Anhänge
                                <span class="optional">(optional)</span>
                            </label>

                            <input
                                type="file"
                                id="reply-attachments"
                                name="attachments[]"
                                class="file-input"
                                accept=".pdf,.png,.jpg,.jpeg"
                                aria-describedby="attachment-help"
                                multiple
                                disabled
                            >

                            <p id="attachment-help" class="field-help">
                                Dateianhänge sind derzeit noch nicht verfügbar.
                            </p>
                        </div>

                        <div class="reply-footer">
                            <label class="checkbox-label" for="resolve-after-reply">
                                <input
                                    type="checkbox"
                                    id="resolve-after-reply"
                                    name="resolve_after_reply"
                                    value="1"
                                    @checked(old('resolve_after_reply'))
                                    @disabled($isResolved)
                                >

                                <span>
                                    Ticket nach dem Senden als erledigt markieren
                                </span>
                            </label>

                            <button
                                type="submit"
                                class="button button-primary"
                                @disabled($isResolved)
                            >
                                Antwort senden
                            </button>
                        </div>
                    </form>
                </section>
            </section>

            <!-- Ticket bearbeiten -->
            <aside
                id="ticket-settings"
                class="card settings-card"
                aria-labelledby="settings-heading"
            >
                <div class="card-heading">
                    <h2 id="settings-heading">Ticketdaten bearbeiten</h2>
                </div>

                <div class="settings-body">
                    <div class="customer-summary">
                        <span class="avatar" aria-hidden="true">
                            {{ $initials }}
                        </span>

                        <div>
                            <span class="customer-label">Kunde</span>
                            <strong>{{ $user->name }}</strong>
                        </div>
                    </div>

                    <form
                        id="ticket-edit-form"
                        class="settings-form"
                        method="POST"
                        action="{{ route('edit.update', ['id' => $ticket->id]) }}"
                    >
                        @csrf
                        @method('PUT')

                        <div class="field">
                            <label for="edit-subject" class="field-label">
                                Betreff
                                <span class="required" aria-hidden="true">*</span>
                            </label>

                            <input
                                type="text"
                                id="edit-subject"
                                name="subject"
                                class="form-control @error('subject') is-invalid @enderror"
                                value="{{ old('subject', $ticket->subject) }}"
                                maxlength="255"
                                aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}"
                                required
                            >
                        </div>

                        <div class="field">
                            <label for="edit-description" class="field-label">
                                Beschreibung
                                <span class="required" aria-hidden="true">*</span>
                            </label>

                            <textarea
                                id="edit-description"
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="8"
                                maxlength="10000"
                                aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                                required
                            >{{ old('description', $ticket->description) }}</textarea>
                        </div>

                        <div class="field">
                            <label for="edit-status" class="field-label">
                                Status
                                <span class="optional">(nur Anzeige)</span>
                            </label>

                            <input
                                type="text"
                                id="edit-status"
                                class="form-control"
                                value="{{ $currentStatus['label'] }}"
                                readonly
                            >
                        </div>

                        <div class="field">
                            <label for="edit-priority" class="field-label">
                                Priorität
                                <span class="required" aria-hidden="true">*</span>
                            </label>

                            <select
                                id="edit-priority"
                                name="priority"
                                class="form-control @error('priority') is-invalid @enderror"
                                aria-invalid="{{ $errors->has('priority') ? 'true' : 'false' }}"
                                required
                            >
                                <option
                                    value="low"
                                    @selected(old('priority', $ticket->priority) === 'low')
                                >
                                    Niedrig
                                </option>

                                <option
                                    value="normal"
                                    @selected(old('priority', $ticket->priority) === 'normal')
                                >
                                    Normal
                                </option>

                                <option
                                    value="high"
                                    @selected(old('priority', $ticket->priority) === 'high')
                                >
                                    Hoch
                                </option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit-category" class="field-label">
                                Kategorie
                                <span class="optional">(optional)</span>
                            </label>

                            <select
                                id="edit-category"
                                name="category"
                                class="form-control @error('category') is-invalid @enderror"
                                aria-invalid="{{ $errors->has('category') ? 'true' : 'false' }}"
                            >
                                <option
                                    value=""
                                    @selected(! old('category', $ticket->category))
                                >
                                    Keine Kategorie
                                </option>

                                <option
                                    value="general"
                                    @selected(old('category', $ticket->category) === 'general')
                                >
                                    Allgemeine Anfrage
                                </option>

                                <option
                                    value="technical"
                                    @selected(old('category', $ticket->category) === 'technical')
                                >
                                    Technisches Problem
                                </option>

                                <option
                                    value="billing"
                                    @selected(old('category', $ticket->category) === 'billing')
                                >
                                    Rechnung und Zahlung
                                </option>

                                <option
                                    value="account"
                                    @selected(old('category', $ticket->category) === 'account')
                                >
                                    Konto und Anmeldung
                                </option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="edit-assignee" class="field-label">
                                Zuständig
                            </label>

                            <select
                                id="edit-assignee"
                                name="assignee"
                                class="form-control"
                                disabled
                            >
                                <option value="" selected>
                                    Zuweisung noch nicht verfügbar
                                </option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            class="button button-primary save-button"
                        >
                            Änderungen speichern
                        </button>

                        <a
                            href="{{ route('tickets.index') }}"
                            class="button button-secondary"
                        >
                            Zurück zu meinen Tickets
                        </a>
                    </form>

                    <dl class="ticket-meta">
                        <div>
                            <dt>Ticketnummer</dt>
                            <dd>#{{ $ticket->id }}</dd>
                        </div>

                        <div>
                            <dt>Letzte Aktivität</dt>
                            <dd>
                                @if ($ticket->updated_at)
                                    <time datetime="{{ $ticket->updated_at->format('c') }}">
                                        {{ $ticket->updated_at->format('d.m.Y, H:i') }} Uhr
                                    </time>
                                @else
                                    –
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </main>
</div>

</body>
</html>
