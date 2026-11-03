<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Alle Tickets – Supportbereich</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
@php
    $initials = '';

    $nameParts = preg_split(
        '/\s+/u',
        trim($viewer->name),
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
@endphp

    <!-- Seitenleiste -->
<aside class="sidebar">
    <a href="{{ route('support') }}" class="brand">
        Supportportal
    </a>

    <p class="nav-heading">Supportbereich</p>

    <nav class="sidebar-nav" aria-label="Hauptnavigation">
        <a href="{{ route('support') }}" aria-current="page">
            Alle Tickets
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
            <strong class="account-name">{{ $viewer->name }}</strong>
            <span class="account-role">Support</span>
        </div>
    </div>
</aside>

<div class="page">
    <!-- Kopfbereich -->
    <header class="topbar">
        <nav class="breadcrumb" aria-label="Brotkrumennavigation">
            <span>Supportbereich</span>
            <span aria-hidden="true">/</span>
            <strong aria-current="page">Alle Tickets</strong>
        </nav>

        <span class="avatar" aria-label="{{ $viewer->name }}">
            {{ $initials }}
        </span>
    </header>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav" aria-label="Mobile Hauptnavigation">
        <a href="{{ route('support') }}" aria-current="page">
            Alle Tickets
        </a>

        <a href="{{ route('create-ticket') }}">
            Neues Ticket
        </a>
    </nav>

    <main class="content">
        <!-- Erfolgsmeldung -->
        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        <!-- Fehlermeldungen -->
        @foreach (['default', 'assignment'] as $bagName)
            @if ($errors->getBag($bagName)->any())
                <div
                    id="support-errors-{{ $bagName }}"
                    class="alert alert-danger"
                    role="alert"
                >
                    <ul class="mb-0">
                        @foreach ($errors->getBag($bagName)->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach

        <div class="page-heading">
            <div>
                <h1>Alle Tickets</h1>

                <p>
                    Behalte Kundenanfragen, Zuständigkeiten
                    und den Bearbeitungsstand im Blick.
                </p>
            </div>

            <a href="{{ route('create-ticket') }}" class="button button-primary">
                <span aria-hidden="true">+</span>
                Neues Ticket
            </a>
        </div>

        <!-- Statistik über alle Tickets -->
        <section class="stats-grid" aria-label="Ticketstatistik für alle Tickets">
            <div class="stat-card">
                <div>
                    <p>Tickets insgesamt</p>
                    <strong>{{ $stats['total'] }}</strong>
                </div>

                <span class="stat-dot dot-total" aria-hidden="true"></span>
            </div>

            <div class="stat-card">
                <div>
                    <p>Offen</p>
                    <strong>{{ $stats['open'] }}</strong>
                </div>

                <span class="stat-dot dot-open" aria-hidden="true"></span>
            </div>

            <div class="stat-card">
                <div>
                    <p>In Bearbeitung</p>
                    <strong>{{ $stats['in_progress'] }}</strong>
                </div>

                <span class="stat-dot dot-progress" aria-hidden="true"></span>
            </div>

            <div class="stat-card">
                <div>
                    <p>Erledigt</p>
                    <strong>{{ $stats['resolved'] }}</strong>
                </div>

                <span class="stat-dot dot-resolved" aria-hidden="true"></span>
            </div>
        </section>

        <section class="ticket-panel" aria-labelledby="ticket-list-heading">
            <div class="panel-heading">
                <h2 id="ticket-list-heading">Ticketübersicht</h2>

                <span class="ticket-count">
                    {{ $tickets->total() }}
                    {{ $tickets->total() === 1 ? 'Ticket' : 'Tickets' }}
                </span>
            </div>

            <!-- Suche und Filter -->
            <form
                class="filters"
                id="ticket-filter-form"
                action="{{ route('support') }}"
                method="GET"
                role="search"
                aria-label="Supporttickets suchen und filtern"
            >
                <div class="filter-field filter-search">
                    <label for="ticket-search">Suche</label>

                    <input
                        type="search"
                        id="ticket-search"
                        name="q"
                        class="form-control"
                        maxlength="200"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Ticketnummer, Betreff oder Kunde"
                    >
                </div>

                <div class="filter-field">
                    <label for="ticket-status">Status</label>

                    <select id="ticket-status" name="status" class="form-control">
                        <option value="">Alle Status</option>

                        @foreach ($statusLabels as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(($filters['status'] ?? '') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field">
                    <label for="ticket-priority">Priorität</label>

                    <select
                        id="ticket-priority"
                        name="priority"
                        class="form-control"
                    >
                        <option value="">Alle Prioritäten</option>

                        @foreach ($priorityLabels as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(($filters['priority'] ?? '') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field">
                    <label for="ticket-assignee">Zuständig</label>

                    <select
                        id="ticket-assignee"
                        name="assignee"
                        class="form-control"
                    >
                        <option value="">Alle Mitarbeitenden</option>

                        <option
                            value="unassigned"
                            @selected(($filters['assignee'] ?? '') === 'unassigned')
                        >
                            Nicht zugewiesen
                        </option>

                        @foreach ($agents as $agent)
                            <option
                                value="{{ $agent->id }}"
                                @selected((string) ($filters['assignee'] ?? '') === (string) $agent->id)
                            >
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="button button-primary filter-button"
                >
                    Filtern
                </button>

                <a href="{{ route('support') }}" class="button button-secondary">
                    Zurücksetzen
                </a>
            </form>

            <!-- Tickettabelle -->
            <div
                class="table-wrapper"
                role="region"
                aria-labelledby="ticket-list-heading"
                tabindex="0"
            >
                <table class="tickets-table">
                    <caption class="visually-hidden">
                        Supporttickets mit Kunde, Status, Priorität und Zuständigkeit.
                    </caption>

                    <thead>
                    <tr>
                        <th scope="col">Ticket</th>
                        <th scope="col">Kunde</th>
                        <th scope="col">Status</th>
                        <th scope="col">Priorität</th>
                        <th scope="col">Zuständig</th>
                        <th scope="col">Aktualisiert</th>
                        <th scope="col">
                            <span class="visually-hidden">Aktion</span>
                        </th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($tickets as $ticket)
                        @php
                            $statusClass = match ($ticket->status) {
                                'open' => 'status-open',
                                'in_progress' => 'status-progress',
                                'resolved' => 'status-resolved',
                                default => '',
                            };
                        @endphp

                        <tr data-ticket-id="{{ $ticket->id }}">
                            <th scope="row" class="ticket-cell">
                                <span class="ticket-id">
                                    #{{ $ticket->id }}
                                </span>

                                <span class="ticket-subject">
                                    {{ $ticket->subject }}
                                </span>

                                <span class="ticket-category">
                                    {{ $categoryLabels[$ticket->category] ?? 'Sonstiges' }}
                                </span>
                            </th>

                            <td>
                                {{ $ticket->user?->name ?? 'Gelöschtes Konto' }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusLabels[$ticket->status] ?? 'Unbekannt' }}
                                </span>
                            </td>

                            <td>
                                <span class="priority priority-{{ $ticket->priority }}">
                                    {{ $priorityLabels[$ticket->priority] ?? 'Unbekannt' }}
                                </span>
                            </td>

                            <td>
                                <form
                                    action="{{ route('support.assign', ['id' => $ticket->id]) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <label
                                        for="assignee-{{ $ticket->id }}"
                                        class="visually-hidden"
                                    >
                                        Zuständigkeit für Ticket {{ $ticket->id }}
                                    </label>

                                    <select
                                        id="assignee-{{ $ticket->id }}"
                                        name="assigned_to"
                                        class="form-control"
                                    >
                                        <option
                                            value=""
                                            @selected($ticket->assigned_to === null)
                                        >
                                            Nicht zugewiesen
                                        </option>

                                        @if ($ticket->assigned_to && ! $agents->contains('id', $ticket->assigned_to))
                                            <option
                                                value="{{ $ticket->assigned_to }}"
                                                selected
                                                disabled
                                            >
                                                {{ $ticket->assignee?->name ?? 'Nicht verfügbar' }}
                                                – nicht mehr im Support
                                            </option>
                                        @endif

                                        @foreach ($agents as $agent)
                                            <option
                                                value="{{ $agent->id }}"
                                                @selected((string) $ticket->assigned_to === (string) $agent->id)
                                            >
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-secondary mt-2"
                                        aria-label="Zuständigkeit für Ticket {{ $ticket->id }} speichern"
                                    >
                                        Zuweisen
                                    </button>
                                </form>
                            </td>

                            <td>
                                @if ($ticket->updated_at)
                                    <time datetime="{{ $ticket->updated_at->toIso8601String() }}">
                                        {{ $ticket->updated_at->format('d.m.Y') }}

                                        <span class="time-detail">
                                            {{ $ticket->updated_at->format('H:i') }} Uhr
                                        </span>
                                    </time>
                                @else
                                    –
                                @endif
                            </td>

                            <td class="action-cell">
                                <a
                                    href="{{ route('tickets.show', ['id' => $ticket->id]) }}"
                                    class="open-button text-decoration-none"
                                    aria-label="Ticket {{ $ticket->id }} öffnen"
                                >
                                    Öffnen
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                @if ($tickets->total() > 0)
                                    <p>Auf dieser Seite wurden keine Tickets gefunden.</p>

                                    <a
                                        href="{{ $tickets->url(1) }}"
                                        class="button button-secondary"
                                    >
                                        Zur ersten Seite
                                    </a>
                                @else
                                    Keine Tickets gefunden.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Seitennavigation -->
            <footer class="table-footer">
                <p>
                    {{ $tickets->firstItem() ?? 0 }}–{{ $tickets->lastItem() ?? 0 }}
                    von {{ $tickets->total() }}
                    {{ $tickets->total() === 1 ? 'Ticket' : 'Tickets' }}
                </p>

                <nav class="pagination" aria-label="Seitennavigation">
                    @if ($tickets->onFirstPage())
                        <button type="button" class="page-button" disabled>
                            Zurück
                        </button>
                    @else
                        <a
                            href="{{ $tickets->previousPageUrl() }}"
                            class="page-button"
                            rel="prev"
                        >
                            Zurück
                        </a>
                    @endif

                    <span class="page-button current-page" aria-current="page">
                        {{ $tickets->currentPage() }}
                    </span>

                    @if ($tickets->hasMorePages())
                        <a
                            href="{{ $tickets->nextPageUrl() }}"
                            class="page-button"
                            rel="next"
                        >
                            Weiter
                        </a>
                    @else
                        <button type="button" class="page-button" disabled>
                            Weiter
                        </button>
                    @endif
                </nav>
            </footer>
        </section>
    </main>
</div>
</body>
</html>
