<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ticket #{{ $ticket->id }} erledigt – Supportportal</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
@php
    $nameParts = preg_split('/\s+/u', trim($customer->name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = '';

    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }

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
        <a href="{{ route('tickets.index') }}" aria-current="true">
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

            <strong aria-current="page">
                Ticket #{{ $ticket->id }}
            </strong>
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
        <!-- Erfolgsmeldungen -->
        @foreach (['success', 'reply_success'] as $messageKey)
            @if (session($messageKey))
                <div class="alert alert-success" role="status">
                    {{ session($messageKey) }}
                </div>
            @endif
        @endforeach

        <!-- Fehler bei einem Antwortversuch auf ein erledigtes Ticket -->
        @if ($errors->reply->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->reply->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Ticketüberschrift -->
        <div class="page-heading">
            <div class="heading-content">
                <p class="ticket-number">
                    Ticket #{{ $ticket->id }}
                </p>

                <h1>{{ $ticket->subject }}</h1>

                <div class="heading-meta">
                    <span class="status-badge status-resolved">
                        {{ $statusLabel }}
                    </span>

                    <span>Erstellt von {{ $customer->name }}</span>
                </div>
            </div>

            <div class="page-actions">
                <a
                    href="{{ route('tickets.index') }}"
                    class="button button-secondary"
                >
                    Zur Übersicht
                </a>

                <form
                    action="{{ route('tickets.reopen', ['id' => $ticket->id]) }}"
                    method="POST"
                >
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="button button-primary">
                        Ticket wieder öffnen
                    </button>
                </form>
            </div>
        </div>

        <!-- Abschlussmeldung -->
        <section class="resolution-banner" aria-labelledby="resolution-heading">
            <span class="resolution-icon" aria-hidden="true">✓</span>

            <div>
                <h2 id="resolution-heading">Dieses Ticket ist erledigt</h2>

                <p>
                    Bei weiteren Fragen kannst du das Ticket wieder öffnen
                    und eine neue Antwort schreiben.
                </p>
            </div>
        </section>

        <div class="ticket-layout">
            <!-- Nachrichtenverlauf -->
            <section class="card conversation" aria-labelledby="conversation-heading">
                <div class="card-heading">
                    <h2 id="conversation-heading">Unterhaltung</h2>

                    <span class="message-count">
                        {{ $messageCount }}
                        {{ $messageCount === 1 ? 'Nachricht' : 'Nachrichten' }}
                    </span>
                </div>

                <div class="messages">
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

                            $replyNameParts = preg_split(
                                '/\s+/u',
                                trim($replyAuthor),
                                -1,
                                PREG_SPLIT_NO_EMPTY
                            );

                            $replyInitials = '';

                            foreach (array_slice($replyNameParts, 0, 2) as $part) {
                                $replyInitials .= mb_strtoupper(mb_substr($part, 0, 1));
                            }
                        @endphp

                        <article class="message" id="message-{{ $reply->id }}">
                            <header class="message-header">
                                <div class="message-author">
                                    <span class="avatar" aria-hidden="true">
                                        {{ $replyInitials }}
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

                <div class="closed-notice">
                    <h3>Unterhaltung abgeschlossen</h3>

                    <p>
                        Öffne das Ticket wieder, um eine weitere Antwort
                        zu schreiben.
                    </p>
                </div>
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
                            <span class="status-badge status-resolved">
                                {{ $statusLabel }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt>Priorität</dt>
                        <dd>
                            <span class="priority-badge">
                                {{ $priorityLabel }}
                            </span>
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
                            @if ($ticket->created_at)
                                <time datetime="{{ $ticket->created_at->toIso8601String() }}">
                                    {{ $ticket->created_at->format('d.m.Y, H:i') }} Uhr
                                </time>
                            @else
                                –
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt>Letzte Aktivität</dt>
                        <dd>
                            @if ($ticket->updated_at)
                                <time datetime="{{ $ticket->updated_at->toIso8601String() }}">
                                    {{ $ticket->updated_at->format('d.m.Y, H:i') }} Uhr
                                </time>
                            @else
                                –
                            @endif
                        </dd>
                    </div>
                </dl>
            </aside>
        </div>
    </main>
</div>
</body>
</html>
