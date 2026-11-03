<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>sign-in-page</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex justify-content-center align-items-center min-vh-100">
<div class="container  px-3 mx-auto">
    <div class="row">
        <div class="col support-left py-4 px-3 col-left-sign-in">

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

            <h1 class="h1-sign-in-page">Willkommen</h1>
            <p>Melde dich an, um deine Tickets zu verwalten.</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sign-in.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="login-email" class="form-label">
                        E-Mail-Adresse
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="login-email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="maxmustermann@beispiel.com"
                        autocomplete="email"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="login-password" class="form-label">
                        Passwort
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="login-password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <input type="hidden" name="remember" value="0">

                <div class="form-check mt-5">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="login-remember"
                        name="remember"
                        value="1"
                        @checked(old('remember', true))
                    >

                    <label class="form-check-label" for="login-remember">
                        Angemeldet bleiben
                    </label>
                </div>

                <div class="text-center mt-5">
                    <button
                        type="submit"
                        class="btn btn-primary btn-sign-in-page"
                    >
                        Anmelden
                    </button>
                </div>
            </form>

            <div class="line mt-4"></div>

            <div class="text-center mt-4">
                <p>
                    Noch kein Konto?
                    <a href="{{ route('register') }}" class="register-btn-sign-in-page">
                        Registrieren
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>
</body>
</html>
