<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TicketController;

// Anmeldung
Route::get('/', function () {
    return view('sign-in');
})->name('sign-in');

Route::post('/sign-in', [LoginController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('sign-in.store');

// Registrierung
Route::view('/register', 'register')
    ->name('register');

Route::post('/register', [RegisterController::class, 'store'])
    ->middleware(['guest', 'throttle:10,1'])
    ->name('register.store');

// Abmelden
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Meine Tickets
Route::get('/ticket', [TicketController::class, 'index'])
    ->middleware('auth')
    ->name('tickets.index');

// Neues Ticket anzeigen
Route::get('/create-ticket', [TicketController::class, 'create'])
    ->middleware('auth')
    ->name('create-ticket');

// Neues Ticket speichern
Route::post('/create-ticket', [TicketController::class, 'store'])
    ->middleware('auth')
    ->name('create-ticket.store');

// Ticket-Detailseite öffnen
Route::get('/open-ticket/{id}', [TicketController::class, 'show'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('tickets.show');

// Ticket bearbeiten
Route::get('/edit/{id}', [TicketController::class, 'edit'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('edit');

// Ticketänderungen speichern
Route::put('/edit/{id}', [TicketController::class, 'update'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('edit.update');

// Supportbereich
Route::middleware(['auth', 'can:access-support'])->group(function () {
    Route::get('/support', [TicketController::class, 'support'])
        ->name('support');

    Route::patch('/support/tickets/{id}/assign', [TicketController::class, 'assign'])
        ->whereNumber('id')
        ->name('support.assign');
});

// Erledigtes Ticket
Route::get('/done/{id}', [TicketController::class, 'show'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('done');

Route::patch('/done/{id}/reopen', [TicketController::class, 'reopen'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('tickets.reopen');

// Wieder geöffnetes Ticket
Route::get('/ticket-reopened/{id}', [TicketController::class, 'show'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('ticket-reopened');

Route::patch('/done/{id}/reopen', [TicketController::class, 'reopen'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('tickets.reopen');


Route::post('/edit/{id}/reply', [TicketController::class, 'reply'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('edit.reply');


Route::post('/open-ticket/{id}/reply', [TicketController::class, 'reply'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('tickets.reply');

Route::patch('/open-ticket/{id}/close', [TicketController::class, 'close'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('tickets.close');
