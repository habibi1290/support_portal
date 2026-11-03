<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket-system</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex justify-content-center align-items-center min-vh-100">
<div class="container  px-3 mx-auto">
    <div class="row">
        <div class="col support-left py-4 px-3 test">

            <div class="d-flex align-items-center gap-3 mb-5">
                <i class="bi bi-ticket-perforated support-logo-icon"
                   aria-hidden="true"></i>
                <span class="fs-3 fw-bold">Supportportal</span>
            </div>

            <h2 class="fw-bold mb-4">Dein Kundenservice</h2>

            <div class="d-flex align-items-start gap-3 mb-4">
        <span class="support-icon">
            <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
        </span>

                <div>
                    <h3 class="h5 fw-semibold mb-2">Anfragen stellen</h3>
                    <p class="support-description mb-0">
                        Beschreibe dein Anliegen einfach und schnell.
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3 mb-4">
        <span class="support-icon">
            <i class="bi bi-chat" aria-hidden="true"></i>
        </span>

                <div>
                    <h3 class="h5 fw-semibold mb-2">Antworten verfolgen</h3>
                    <p class="support-description mb-0">
                        Behalte den Überblick über den aktuellen Stand deiner Tickets.
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3">
        <span class="support-icon">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
        </span>

                <div>
                    <h3 class="h5 fw-semibold mb-2">Probleme lösen</h3>
                    <p class="support-description mb-0">
                        Erhalte kompetente Unterstützung und finde eine Lösung.
                    </p>
                </div>
            </div>
        </div>

        <div class="col py-5 ps-5">
            <h1 class="h1-sign-in-page">Konto erstellen</h1>
            <p>Registriere dich und behalte deine Anfragen im Blick.</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('register.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="register-name" class="form-label">
                        Vorname und Nachname
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="register-name"
                        name="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="register-email" class="form-label">
                        E-Mail-Adresse
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="register-email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="maxmustermann@beispiel.com"
                        autocomplete="email"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="register-password" class="form-label">
                        Passwort
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="register-password"
                        name="password"
                        autocomplete="new-password"
                        minlength="12"
                        aria-describedby="register-password-help"
                        required
                    >

                    <div id="register-password-help" class="form-text">
                        Dein Passwort muss mindestens 12 Zeichen lang sein und mindestens
                        einen Großbuchstaben, einen Kleinbuchstaben, eine Ziffer und
                        ein Sonderzeichen enthalten.
                    </div>
                </div>

                <div class="mt-4">
                    <label for="register-password-confirmation" class="form-label">
                        Passwort bestätigen
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="register-password-confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        minlength="12"
                        required
                    >
                </div>

                <div class="text-center mt-5">
                    <button
                        type="submit"
                        class="btn btn-primary btn-register-page"
                    >
                        Registrieren
                    </button>
                </div>
            </form>

            <div class="line mt-4"></div>

            <div class="text-center mt-4">
                <p>
                    Bereits ein Konto?
                    <a href="{{ route('sign-in') }}" class="register-btn-sign-in-page">
                        Anmelden
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
