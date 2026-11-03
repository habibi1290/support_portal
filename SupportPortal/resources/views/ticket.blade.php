<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meine Tickets | Supportportal</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="tickets-page">

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

    $canCreateTickets = \Illuminate\Support\Facades\Route::has('create-ticket');
    $canOpenTickets = \Illuminate\Support\Facades\Route::has('tickets.show');
    $canEditTickets = \Illuminate\Support\Facades\Route::has('edit');
@endphp

    <!-- Wiederverwendbare Icons -->
<svg class="icon-definitions"
     xmlns="http://www.w3.org/2000/svg"
     aria-hidden="true">

    <symbol id="icon-ticket" viewBox="0 0 24 24">
        <path
            d="M4 7h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V7Z"
            transform="rotate(-18 12 12)"
        />
    </symbol>

    <symbol id="icon-list" viewBox="0 0 24 24">
        <path d="M9 6h12M9 12h12M9 18h12M3 6h1M3 12h1M3 18h1"/>
    </symbol>

    <symbol id="icon-plus" viewBox="0 0 24 24">
        <path d="M12 5v14M5 12h14"/>
    </symbol>

    <symbol id="icon-clock" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="9"/>
        <path d="M12 7v5l4 2"/>
    </symbol>

    <symbol id="icon-check" viewBox="0 0 24 24">
        <path d="m5 12 4 4L19 6"/>
    </symbol>

    <symbol id="icon-search" viewBox="0 0 24 24">
        <circle cx="10.5" cy="10.5" r="6.5"/>
        <path d="m16 16 5 5"/>
    </symbol>

    <symbol id="icon-arrow" viewBox="0 0 24 24">
        <path d="m9 5 7 7-7 7"/>
    </symbol>

    <symbol id="icon-logout" viewBox="0 0 24 24">
        <path d="M10 4H4v16h6M10 12h11m-4-4 4 4-4 4"/>
    </symbol>

    <symbol id="icon-menu" viewBox="0 0 24 24">
        <path d="M4 6h16M4 12h16M4 18h16"/>
    </symbol>
</svg>

<!-- Seitenleiste -->
<aside class="sidebar">
    <a class="sidebar-brand" href="{{ route('tickets.index') }}">
        <svg class="icon" aria-hidden="true">
            <use href="#icon-ticket"/>
        </svg>
        Supportportal
    </a>

    <p class="sidebar-heading">Kundenbereich</p>

    <nav class="sidebar-nav" aria-label="Hauptnavigation">
        <a class="sidebar-link active"
           href="{{ route('tickets.index') }}"
           aria-current="page">

            <svg class="icon" aria-hidden="true">
                <use href="#icon-list"/>
            </svg>

            Meine Tickets
        </a>

        @if ($canCreateTickets)
            <a class="sidebar-link" href="{{ route('create-ticket') }}">
                <svg class="icon" aria-hidden="true">
                    <use href="#icon-plus"/>
                </svg>

                Neues Ticket
            </a>
        @else
            <button type="button"
                    class="sidebar-link opacity-50"
                    disabled>

                <svg class="icon" aria-hidden="true">
                    <use href="#icon-plus"/>
                </svg>

                Neues Ticket
            </button>
        @endif

        @can('access-support')
            <a class="sidebar-link" href="{{ route('support') }}">
                <svg class="icon" aria-hidden="true">
                    <use href="#icon-ticket"/>
                </svg>
                Supportbereich
            </a>
        @endcan
    </nav>

    <div class="sidebar-bottom">
        <div class="customer-profile">
            <span class="avatar" aria-hidden="true">
                {{ $initials }}
            </span>

            <div>
                <strong>{{ $user->name }}</strong>
                <small>Kunde</small>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf

            <button type="submit" class="sidebar-link">
                <svg class="icon" aria-hidden="true">
                    <use href="#icon-logout"/>
                </svg>

                Abmelden
            </button>
        </form>
    </div>
</aside>

<div class="page-content">
    <!-- Kopfzeile -->
    <header class="topbar">
        <details class="mobile-menu">
            <summary>
                <svg class="icon" aria-hidden="true">
                    <use href="#icon-menu"/>
                </svg>

                Menü
            </summary>

            <nav aria-label="Mobile Navigation">
                <a href="{{ route('tickets.index') }}" aria-current="page">
                    Meine Tickets
                </a>

                @if ($canCreateTickets)
                    <a href="{{ route('create-ticket') }}">
                        Neues Ticket
                    </a>
                @else
                    <button type="button" class="opacity-50" disabled>
                        Neues Ticket
                    </button>
                @endif

                @can('access-support')
                    <a href="{{ route('support') }}">
                        Supportbereich
                    </a>
                @endcan

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit">
                        Abmelden
                    </button>
                </form>
            </nav>
        </details>

        <nav class="page-breadcrumb" aria-label="Seitenpfad">
            <span class="d-none d-sm-inline">Kundenbereich /</span>
            <span aria-current="page">Meine Tickets</span>
        </nav>

        <span class="avatar" title="{{ $user->name }}">
            {{ $initials }}
        </span>
    </header>

    <main class="main-content">
        <!-- Überschrift -->
        <div class="page-heading">
            <div>
                <h1>Meine Tickets</h1>
                <p>Alle deine Anfragen auf einen Blick.</p>
            </div>

            @if ($canCreateTickets)
                <a href="{{ route('create-ticket') }}"
                   class="btn btn-primary new-ticket-btn">

                    <svg class="icon" aria-hidden="true">
                        <use href="#icon-plus"/>
                    </svg>

                    Neues Ticket
                </a>
            @else
                <button type="button"
                        class="btn btn-primary new-ticket-btn"
                        disabled>

                    <svg class="icon" aria-hidden="true">
                        <use href="#icon-plus"/>
                    </svg>

                    Neues Ticket
                </button>
            @endif
        </div>

        <!-- Erfolgsmeldung -->
        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        <!-- Fehlermeldungen -->
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Statuskarten -->
        <section class="row g-3 mb-4" aria-label="Ticketübersicht">
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <span class="stat-label">
                            <span class="status-dot gray"></span>
                            Gesamt
                        </span>

                        <strong>{{ $stats['total'] }}</strong>
                    </div>

                    <span class="stat-icon gray">
                        <svg class="icon" aria-hidden="true">
                            <use href="#icon-ticket"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <span class="stat-label">
                            <span class="status-dot yellow"></span>
                            Offen
                        </span>

                        <strong>{{ $stats['open'] }}</strong>
                    </div>

                    <span class="stat-icon yellow">
                        <svg class="icon" aria-hidden="true">
                            <use href="#icon-clock"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <span class="stat-label">
                            <span class="status-dot blue"></span>
                            In Bearbeitung
                        </span>

                        <strong>{{ $stats['in_progress'] }}</strong>
                    </div>

                    <span class="stat-icon blue">
                        <svg class="icon" aria-hidden="true">
                            <use href="#icon-list"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <span class="stat-label">
                            <span class="status-dot green"></span>
                            Erledigt
                        </span>

                        <strong>{{ $stats['resolved'] }}</strong>
                    </div>

                    <span class="stat-icon green">
                        <svg class="icon" aria-hidden="true">
                            <use href="#icon-check"/>
                        </svg>
                    </span>
                </div>
            </div>
        </section>

        <!-- Ticketübersicht -->
        <section class="tickets-panel" aria-label="Deine Tickets">
            <!-- Suche und Statusfilter -->
            <form method="GET"
                  action="{{ route('tickets.index') }}"
                  class="ticket-filters flex-wrap"
                  role="search"
                  aria-label="Tickets suchen">

                <div class="search-field">
                    <label for="ticket-search" class="visually-hidden">
                        Ticketnummer oder Betreff
                    </label>

                    <svg class="icon" aria-hidden="true">
                        <use href="#icon-search"/>
                    </svg>

                    <input
                        type="search"
                        id="ticket-search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        maxlength="120"
                        class="form-control"
                        placeholder="Ticketnummer oder Betreff suchen"
                    >
                </div>

                <div class="status-filter">
                    <label for="ticket-status" class="visually-hidden">
                        Status
                    </label>

                    <select id="ticket-status"
                            name="status"
                            class="form-select">

                        <option value="" @selected($filters['status'] === '')>
                            Alle Status
                        </option>

                        @foreach ($statuses as $value => $status)
                            <option
                                value="{{ $value }}"
                                @selected($filters['status'] === $value)
                            >
                                {{ $status['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-outline-primary">
                    Filtern
                </button>

                @if ($filters['q'] !== '' || $filters['status'] !== '')
                    <a href="{{ route('tickets.index') }}"
                       class="btn btn-outline-secondary">
                        Zurücksetzen
                    </a>
                @endif
            </form>

            <!-- Tickettabelle -->
            <div class="table-responsive ticket-table-wrapper"
                 tabindex="0"
                 role="region"
                 aria-label="Tickettabelle, horizontal scrollbar">

                <table class="table ticket-table mb-0">
                    <thead>
                    <tr>
                        <th scope="col">Ticket</th>
                        <th scope="col">Betreff</th>
                        <th scope="col">Status</th>
                        <th scope="col">Priorität</th>
                        <th scope="col">Aktualisiert</th>
                        <th scope="col" class="text-end">Aktionen</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($tickets as $ticket)
                        <tr data-ticket-id="{{ $ticket->id }}">
                            <th scope="row">
                                #{{ $ticket->id }}
                            </th>

                            <td>
                                {{ $ticket->subject }}
                            </td>

                            <td>
                                <span class="ticket-status {{ $statuses[$ticket->status]['color'] ?? 'gray' }}">
                                    <span class="status-dot"></span>
                                    {{ $statuses[$ticket->status]['label'] ?? 'Unbekannt' }}
                                </span>
                            </td>

                            <td>
                                {{ $priorities[$ticket->priority] ?? '–' }}
                            </td>

                            <td>
                                {{ $ticket->updated_at?->format('d.m.Y, H:i') ?? '–' }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <!-- Bearbeitungsseite -->
                                    @if ($canEditTickets)
                                        <a href="{{ route('edit', ['id' => $ticket->id]) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           aria-label="Ticket {{ $ticket->id }} bearbeiten">
                                            Bearbeiten
                                        </a>
                                    @endif

                                    <!-- Ticket-Detailseite -->
                                    @if ($canOpenTickets)
                                        <a href="{{ route('tickets.show', ['id' => $ticket->id]) }}"
                                           class="open-ticket"
                                           title="Ticket öffnen"
                                           aria-label="Ticket {{ $ticket->id }} öffnen">

                                            <svg
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true"
                                            >
                                                <path d="m9 5 7 7-7 7"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                Keine Tickets gefunden.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Anzahl und Seitennavigation -->
            <footer class="tickets-footer d-flex flex-wrap align-items-center justify-content-between gap-3">
                <span>
                    {{ $tickets->total() }}
                    {{ $tickets->total() === 1 ? 'Ticket' : 'Tickets' }}
                </span>

                @if ($tickets->hasPages())
                    <div>
                        {{ $tickets->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </footer>
        </section>
    </main>
</div>

</body>
</html>
