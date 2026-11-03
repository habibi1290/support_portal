<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = [
            'open' => ['label' => 'Offen', 'color' => 'yellow'],
            'in_progress' => ['label' => 'In Bearbeitung', 'color' => 'blue'],
            'resolved' => ['label' => 'Erledigt', 'color' => 'green'],
        ];

        $priorities = [
            'low' => 'Niedrig',
            'normal' => 'Normal',
            'high' => 'Hoch',
        ];

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys($statuses))],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $filters = [
            'q' => trim($validated['q'] ?? ''),
            'status' => $validated['status'] ?? '',
        ];

        // „Meine Tickets“ zeigt immer nur die eigenen Tickets.
        $baseQuery = Ticket::query()
            ->where('user_id', $request->user()->getAuthIdentifier());

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
        ];

        $query = clone $baseQuery;

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['q'] !== '') {
            $ticketNumber = ltrim($filters['q'], '#');

            $pattern = '%' . strtr($filters['q'], [
                    '!' => '!!',
                    '%' => '!%',
                    '_' => '!_',
                ]) . '%';

            $query->where(function (Builder $search) use ($pattern, $ticketNumber) {
                $search->whereRaw(
                    "subject LIKE ? ESCAPE '!'",
                    [$pattern]
                );

                if (ctype_digit($ticketNumber)) {
                    $search->orWhere('id', $ticketNumber);
                }
            });
        }

        $tickets = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($filters);

        return view('ticket', compact(
            'tickets',
            'stats',
            'filters',
            'statuses',
            'priorities'
        ));
    }

    public function create(): View
    {
        return view('create-ticket');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => [
                'required',
                'string',
                Rule::in(['low', 'normal', 'high']),
            ],
        ], [
            'subject.required' => 'Bitte gib einen Betreff ein.',
            'subject.string' => 'Der Betreff muss ein Text sein.',
            'subject.max' => 'Der Betreff darf höchstens 255 Zeichen lang sein.',

            'description.required' => 'Bitte beschreibe dein Anliegen.',
            'description.string' => 'Die Beschreibung muss ein Text sein.',
            'description.max' => 'Die Beschreibung darf höchstens 10.000 Zeichen lang sein.',

            'category.string' => 'Bitte gib eine gültige Kategorie an.',
            'category.max' => 'Die Kategorie darf höchstens 100 Zeichen lang sein.',

            'priority.required' => 'Bitte wähle eine Priorität.',
            'priority.string' => 'Bitte wähle eine gültige Priorität.',
            'priority.in' => 'Bitte wähle eine gültige Priorität.',
        ]);

        $ticket = new Ticket();

        // Der Besitzer wird ausschließlich serverseitig festgelegt.
        $ticket->user_id = $request->user()->getAuthIdentifier();
        $ticket->subject = $data['subject'];
        $ticket->description = $data['description'];
        $ticket->category = $data['category'] ?? null;
        $ticket->priority = $data['priority'];
        $ticket->status = 'open';

        $ticket->save();

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Dein Ticket wurde erfolgreich erstellt.');
    }

    public function edit(Request $request, string $id): View
    {
        $ticket = $this->accessibleTickets($request)
            ->with(['user', 'assignee', 'messages.user'])
            ->findOrFail($id);

        return view('edit-ticket', [
            'ticket' => $ticket,
            'customer' => $ticket->user,
            'viewer' => $request->user(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        // Die Berechtigung wird beim Speichern erneut geprüft.
        $ticket = $this->accessibleTickets($request)
            ->findOrFail($id);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => [
                'required',
                'string',
                Rule::in(['low', 'normal', 'high']),
            ],
        ], [
            'required' => 'Bitte fülle das Feld „:attribute“ aus.',
            'string' => 'Das Feld „:attribute“ muss einen Text enthalten.',
            'max' => 'Das Feld „:attribute“ darf höchstens :max Zeichen enthalten.',
            'in' => 'Bitte wähle eine gültige Option für „:attribute“.',
        ], [
            'subject' => 'Betreff',
            'description' => 'Beschreibung',
            'category' => 'Kategorie',
            'priority' => 'Priorität',
        ]);

        $ticket->subject = $data['subject'];
        $ticket->description = $data['description'];
        $ticket->priority = $data['priority'];

        if (array_key_exists('category', $data)) {
            $ticket->category = $data['category'];
        }

        $ticket->save();

        return redirect()
            ->route(
                $request->user()->can('access-support')
                    ? 'support'
                    : 'tickets.index'
            )
            ->with('success', 'Das Ticket wurde erfolgreich aktualisiert.');
    }

    public function show(Request $request, string $id): View
    {
        $ticket = $this->accessibleTickets($request)
            ->with(['user', 'assignee', 'messages.user'])
            ->findOrFail($id);

        $view = match (true) {
            $ticket->status === 'resolved' => 'done-ticket',
            $ticket->reopened_at !== null => 'ticket-reopened',
            default => 'open-ticket',
        };

        return view($view, [
            'ticket' => $ticket,
            'customer' => $ticket->user,
            'viewer' => $request->user(),

            'statusLabel' => match ($ticket->status) {
                'open' => 'Offen',
                'in_progress' => 'In Bearbeitung',
                'resolved' => 'Erledigt',
                default => 'Unbekannt',
            },

            'priorityLabel' => match ($ticket->priority) {
                'low' => 'Niedrig',
                'normal' => 'Normal',
                'high' => 'Hoch',
                default => 'Unbekannt',
            },

            'categoryLabel' => match ($ticket->category) {
                'general' => 'Allgemeine Anfrage',
                'technical' => 'Technisches Problem',
                'billing' => 'Rechnung und Zahlung',
                'account' => 'Konto und Anmeldung',
                default => 'Sonstiges',
            },
        ]);
    }

    public function reply(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validateWithBag('reply', [
            'message' => ['bail', 'required', 'string', 'max:5000'],
            'resolve_after_reply' => ['sometimes', 'boolean'],
        ], [
            'message.required' => 'Bitte schreibe eine Antwort.',
            'message.string' => 'Die Antwort muss ein Text sein.',
            'message.max' => 'Die Antwort darf höchstens 5.000 Zeichen enthalten.',
            'resolve_after_reply.boolean' => 'Die Auswahl zum Abschließen ist ungültig.',
        ]);

        DB::transaction(function () use ($request, $id, $validated) {
            $ticket = $this->accessibleTickets($request)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($ticket->status === 'resolved') {
                throw ValidationException::withMessages([
                    'message' => 'Dieses Ticket ist erledigt. Öffne es zuerst wieder.',
                ])->errorBag('reply');
            }

            // Als Autor wird der tatsächlich angemeldete Nutzer gespeichert.
            $ticket->messages()->create([
                'user_id' => $request->user()->getAuthIdentifier(),
                'message' => $validated['message'],
            ]);

            if ($request->boolean('resolve_after_reply')) {
                $ticket->status = 'resolved';
            }

            $ticket->updated_at = now();
            $ticket->save();
        });

        return redirect()
            ->route(
                $request->routeIs('tickets.reply') ? 'tickets.show' : 'edit',
                ['id' => $id]
            )
            ->with('reply_success', 'Deine Antwort wurde gespeichert.');
    }

    public function close(Request $request, string $id): RedirectResponse
    {
        DB::transaction(function () use ($request, $id) {
            $ticket = $this->accessibleTickets($request)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($ticket->status === 'resolved') {
                return;
            }

            $ticket->status = 'resolved';
            $ticket->save();
        });

        return redirect()
            ->route('tickets.show', ['id' => $id])
            ->with('success', 'Das Ticket wurde geschlossen.');
    }

    public function reopen(Request $request, string $id): RedirectResponse
    {
        $reopened = DB::transaction(function () use ($request, $id): bool {
            $ticket = $this->accessibleTickets($request)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($ticket->status !== 'resolved') {
                return false;
            }

            $ticket->status = 'open';
            $ticket->reopened_at = now();
            $ticket->save();

            return true;
        });

        return redirect()
            ->route(
                $reopened ? 'ticket-reopened' : 'tickets.show',
                ['id' => $id]
            )
            ->with(
                'success',
                $reopened
                    ? 'Das Ticket wurde wieder geöffnet. Du kannst jetzt antworten.'
                    : 'Das Ticket ist bereits geöffnet.'
            );
    }

    public function support(Request $request): View
    {
        Gate::authorize('access-support');

        $statusLabels = [
            'open' => 'Offen',
            'in_progress' => 'In Bearbeitung',
            'resolved' => 'Erledigt',
        ];

        $priorityLabels = [
            'high' => 'Hoch',
            'normal' => 'Normal',
            'low' => 'Niedrig',
        ];

        $categoryLabels = [
            'general' => 'Allgemeine Anfrage',
            'technical' => 'Technisches Problem',
            'billing' => 'Rechnung und Zahlung',
            'account' => 'Konto und Anmeldung',
        ];

        $agents = User::query()
            ->where('is_support', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],

            'status' => [
                'nullable',
                Rule::in(array_keys($statusLabels)),
            ],

            'priority' => [
                'nullable',
                Rule::in(array_keys($priorityLabels)),
            ],

            'assignee' => [
                'nullable',
                Rule::in(array_merge(
                    ['unassigned'],
                    $agents->modelKeys()
                )),
            ],

            'page' => ['nullable', 'integer', 'min:1'],
        ], [
            'q.max' => 'Die Suche darf höchstens 200 Zeichen enthalten.',
            'status.in' => 'Bitte wähle einen gültigen Status.',
            'priority.in' => 'Bitte wähle eine gültige Priorität.',
            'assignee.in' => 'Bitte wähle einen gültigen Supportmitarbeiter.',
        ]);

        $search = trim($filters['q'] ?? '');

        $query = Ticket::query()
            ->with([
                'user:id,name',
                'assignee:id,name',
            ]);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $pattern = '%' . $search . '%';

                $query
                    ->where('subject', 'like', $pattern)
                    ->orWhereHas('user', function (Builder $userQuery) use ($pattern) {
                        $userQuery->where('name', 'like', $pattern);
                    });

                $ticketId = ltrim($search, '#');

                if (ctype_digit($ticketId)) {
                    $query->orWhere('id', $ticketId);
                }
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (($filters['assignee'] ?? '') === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif (! empty($filters['assignee'])) {
            $query->where('assigned_to', $filters['assignee']);
        }

        $tickets = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Statistik über alle Tickets, unabhängig von den Filtern.
        $counts = Ticket::query()
            ->select('status')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $counts->sum(),
            'open' => (int) $counts->get('open', 0),
            'in_progress' => (int) $counts->get('in_progress', 0),
            'resolved' => (int) $counts->get('resolved', 0),
        ];

        return view('support', [
            'tickets' => $tickets,
            'stats' => $stats,
            'filters' => $filters,
            'agents' => $agents,
            'statusLabels' => $statusLabels,
            'priorityLabels' => $priorityLabels,
            'categoryLabels' => $categoryLabels,
            'viewer' => $request->user(),
        ]);
    }

    public function assign(Request $request, string $id): RedirectResponse
    {
        Gate::authorize('access-support');

        $validated = $request->validateWithBag('assignment', [
            'assigned_to' => [
                'present',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('is_support', true),
            ],
        ], [
            'assigned_to.present' => 'Bitte wähle eine Zuständigkeit.',
            'assigned_to.integer' => 'Die Auswahl ist ungültig.',
            'assigned_to.exists' => 'Die ausgewählte Person ist kein Supportmitarbeiter.',
        ]);

        $ticket = $this->accessibleTickets($request)
            ->findOrFail($id);

        $ticket->assigned_to = $validated['assigned_to'];
        $ticket->save();

        return back()->with(
            'success',
            'Die Zuständigkeit wurde gespeichert.'
        );
    }

    private function accessibleTickets(Request $request): Builder
    {
        $query = Ticket::query();

        if (! $request->user()->can('access-support')) {
            $query->where(
                'user_id',
                $request->user()->getAuthIdentifier()
            );
        }

        return $query;
    }
}
