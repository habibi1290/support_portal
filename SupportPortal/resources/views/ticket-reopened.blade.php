<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ticket #{{ $ticket->id }} wieder geöffnet – Supportportal</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
@php
    $initialsFor = static function (string $name): string {
        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials;
    };

    $initials = $initialsFor($customer->name);
    $messages = $ticket->messages;
    $messageCount = $messages->count() + 1;

    $statusClass = $ticket->status === 'in_progress'
        ? 'status-progress'
        : 'status-open';
@endphp

<aside class="sidebar">
    <a href="{{ route('tickets.index') }}" class="brand">
        Supportportal
    </a>

    <p class="nav-heading">Kundenbereich</p>

    <nav class="sidebar-nav" aria-label="Hauptnavigation">
        <a href="{{ route('tickets.index') }}" aria-current="true">
            Meine Tickets
        </a>

        <a href="{{ route('create-ticket') }}">
            Neues Ticket
        </a>
    </nav>

    <div class="account">
        <span class="avatar" aria-hidden="true">{{ $initials }}</span>

        <div>
            <strong>{{ $customer->name }}</strong>
            <span class="account-role">Kundenkonto</span>
        </div>
    </div>
</aside>

<div class="page">
    <header class="topbar">
        <nav class="breadcrumb" aria-label="Brotkrumennavigation">
            <a href="{{ route('tickets.index') }}">Meine Tickets</a>
            <span aria-hidden="true">/</span>
            <strong aria-current="page">Ticket #{{ $ticket->id }}</strong>
        </nav>

        <span class="avatar" aria-label="{{ $customer->name }}">
            {{ $initials }}
        </span>
    </header>

    <nav class="mobile-nav" aria-label="Mobile Hauptnavigation">
        <a href="{{ route('tickets.index') }}" aria-current="true">
            Meine Tickets
        </a>

        <a href="{{ route('create-ticket') }}">
            Neues Ticket
        </a>
    </nav>

    <main class="content" data-ticket-id="{{ $ticket->id }}">
        @foreach (['success', 'reply_success'] as $messageKey)
            @if (session($messageKey))
                <div class="alert alert-success" role="status">
                    {{ session($messageKey) }}
                </div>
            @endif
        @endforeach

        @if ($errors->reply->any())
            <div id="reply-errors" class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->reply->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Überschrift -->
        <div class="page-heading">
            <div class="heading-content">
                <p class="ticket-number">Ticket #{{ $ticket->id }}</p>

                <h1>{{ $ticket->subject }}</h1>

                <div class="heading-meta">
                    <span class="status-badge {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                    <span>Erstellt von {{ $customer->name }}</span>
                </div>
            </div>

            <div class="page-actions">
                <a href="#reply-message" class="button button-primary">
                    Antwort schreiben
                </a>

                <a
                    href="{{ route('edit', ['id' => $ticket->id]) }}"
                    class="button button-secondary"
                >
                    Ticket bearbeiten
                </a>
            </div>
        </div>

        <!-- Gespeicherte Wiederöffnung -->
        @if ($ticket->reopened_at)
            <section class="reopened-banner" aria-labelledby="reopened-heading">
                <span class="reopened-icon" aria-hidden="true">↻</span>

                <div>
                    <h2 id="reopened-heading">
                        Dieses Ticket wurde wieder geöffnet
                    </h2>

                    <p>
                        Zuletzt wieder geöffnet am
                        <time datetime="{{ $ticket->reopened_at->toIso8601String() }}">
                            {{ $ticket->reopened_at->format('d.m.Y') }}
                            um {{ $ticket->reopened_at->format('H:i') }} Uhr.
                        </time>
                        Du kannst weitere Informationen zu deinem Anliegen senden.
                    </p>
                </div>
            </section>
        @endif

        <div class="ticket-layout">
            <!-- Unterhaltung -->
            <section class="card conversation" aria-labelledby="conversation-heading">
                <div class="card-heading">
                    <h2 id="conversation-heading">Unterhaltung</h2>

                    <span class="message-count">
                        {{ $messageCount }}
                        {{ $messageCount === 1 ? 'Nachricht' : 'Nachrichten' }}
                    </span>
                </div>

                <div class="conversation-body">
                    <details class="previous-history" open>
                        <summary>
                            Nachrichtenverlauf
                            <span class="history-count">
                                {{ $messageCount }}
                                {{ $messageCount === 1 ? 'Nachricht' : 'Nachrichten' }}
                            </span>
                        </summary>

                        <div class="history-messages">
                            <!-- Ursprüngliche Ticketnachricht -->
                            <article class="message">
                                <header class="message-header">
                                    <div class="message-author">
                                        <span class="avatar" aria-hidden="true">
                                            {{ $initials }}
                                        </span>

                                        <div>
                                            <h3>{{ $customer->name }}</h3>
                                            <span class="author-role">Kunde</span>
                                        </div>
                                    </div>

                                    @if ($ticket->created_at)
                                        <time datetime="{{ $ticket->created_at->toIso8601String() }}">
                                            {{ $ticket->created_at->format('d.m.Y · H:i') }}
                                        </time>
                                    @endif
                                </header>

                                <div class="message-body">
                                    {!! nl2br(e($ticket->description)) !!}
                                </div>
                            </article>

                            <!-- Gespeicherte Antworten -->
                            @foreach ($messages as $reply)
                                @php
                                    $replyAuthor = $reply->user?->name ?? 'Gelöschtes Konto';
                                @endphp

                                <article class="message" id="message-{{ $reply->id }}">
                                    <header class="message-header">
                                        <div class="message-author">
                                            <span class="avatar" aria-hidden="true">
                                                {{ $initialsFor($replyAuthor) }}
                                            </span>

                                            <div>
                                                <h3>{{ $replyAuthor }}</h3>
                                            </div>
                                        </div>

                                        @if ($reply->created_at)
                                            <time datetime="{{ $reply->created_at->toIso8601String() }}">
                                                {{ $reply->created_at->format('d.m.Y · H:i') }}
                                            </time>
                                        @endif
                                    </header>

                                    <div class="message-body">
                                        {!! nl2br(e($reply->message)) !!}
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </details>
                </div>

                <!-- Antwortformular -->
                <section
                    id="ticket-reply"
                    class="reply-section"
                    aria-labelledby="reply-heading"
                >
                    <h2 id="reply-heading">Deine Antwort</h2>

                    <p class="section-description">
                        Ergänze weitere Informationen zu deinem Anliegen.
                    </p>

                    <form
                        id="ticket-reply-form"
                        action="{{ route('tickets.reply', ['id' => $ticket->id]) }}"
                        method="POST"
                    >
                        @csrf

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
                            aria-describedby="reply-help{{ $errors->reply->any() ? ' reply-errors' : '' }}"
                            aria-invalid="{{ $errors->reply->has('message') ? 'true' : 'false' }}"
                            required
                        >{{ old('message') }}</textarea>

                        <p id="reply-help" class="field-help">
                            Maximal 5.000 Zeichen.
                        </p>

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
                                Dateianhänge sind noch nicht verfügbar.
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
                                >

                                <span>
                                    Ticket nach dem Senden als erledigt markieren
                                </span>
                            </label>

                            <button type="submit" class="button button-primary">
                                Antwort senden
                            </button>
                        </div>
                    </form>
                </section>
            </section>

            <!-- Ticketinformationen -->
            <aside class="card ticket-info" aria-labelledby="ticket-info-heading">
                <h2 id="ticket-info-heading">Ticketinformationen</h2>

                <dl class="info-list">
                    <div>
                        <dt>Ticketnummer</dt>
                        <dd>#{{ $ticket->id }}</dd>
                    </div>

                    <div>
                        <dt>Status</dt>
                        <dd>
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt>Priorität</dt>
                        <dd>
                            <span class="priority-badge">{{ $priorityLabel }}</span>
                        </dd>
                    </div>

                    <div>
                        <dt>Kategorie</dt>
                        <dd>{{ $categoryLabel }}</dd>
                    </div>

                    <div>
                        <dt>Erstellt von</dt>
                        <dd>{{ $customer->name }}</dd>
                    </div>

                    <div>
                        <dt>Erstellt am</dt>
                        <dd>
                            {{ $ticket->created_at?->format('d.m.Y, H:i') ?? '–' }}
                        </dd>
                    </div>

                    <div>
                        <dt>Wieder geöffnet am</dt>
                        <dd>
                            {{ $ticket->reopened_at?->format('d.m.Y, H:i') ?? '–' }}
                        </dd>
                    </div>

                    <div>
                        <dt>Letzte Aktivität</dt>
                        <dd>
                            {{ $ticket->updated_at?->format('d.m.Y, H:i') ?? '–' }}
                        </dd>
                    </div>
                </dl>
            </aside>
        </div>
    </main>
</div>
</body>
</html>
