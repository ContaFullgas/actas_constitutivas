<!DOCTYPE html>
<html lang="es" data-theme='light'>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── TEMA CLARO ─────────────────────────────────── */
        :root, [data-theme="light"] {
            --bg:        #F4F5F7;
            --surface:   #FFFFFF;
            --border:    #E2E5EA;
            --text-main: #1A1D23;
            --text-soft: #6B7280;
            --input-bg:  #FFFFFF;
            --dot-color: rgba(0,0,0,0.06);
        }

        /* ── TEMA OSCURO ─────────────────────────────────── */
        [data-theme="dark"] {
            --bg:        #0F1117;
            --surface:   #1A1D27;
            --border:    #2A2D3A;
            --text-main: #F0F2F8;
            --text-soft: #7A8090;
            --input-bg:  #1F2235;
            --dot-color: rgba(255,255,255,0.04);
        }

        :root { --accent: #2563EB; }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            margin: 0;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ── BOTÓN DARK MODE ─────────────────────────────── */
        .btn-theme {
            position: fixed;
            top: 18px; right: 20px;
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--surface);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease;
            color: var(--text-soft);
            z-index: 100;
        }
        .btn-theme:hover { color: var(--text-main); }
        .btn-theme svg {
            width: 15px; height: 15px;
            fill: none; stroke: currentColor;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }
        .icon-sun  { display: none; }
        .icon-moon { display: block; }
        [data-theme="dark"] .icon-sun  { display: block; }
        [data-theme="dark"] .icon-moon { display: none; }

        /* ════════════════════════════════════════════════════
           SPLIT LAYOUT
        ════════════════════════════════════════════════════ */
        .login-split {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ── PANEL IZQUIERDO ─────────────────────────────── */
        .panel-left {
            flex: 0 0 50%;
            width: 50%;
            background: #0d1b2e;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 52px 48px;
            text-align: center;
        }

        /* Círculo grande superior izquierdo */
        .panel-left .circle-tl {
            position: absolute;
            top: -130px;
            left: -130px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            border: 72px solid rgba(30, 90, 180, 0.22);
            pointer-events: none;
        }

        /* Círculo mediano centro-derecha */
        .panel-left .circle-cr {
            position: absolute;
            top: 50%;
            right: -100px;
            transform: translateY(-60%);
            width: 320px;
            height: 320px;
            border-radius: 50%;
            border: 55px solid rgba(30, 90, 180, 0.13);
            pointer-events: none;
        }

        /* Pills decorativas estilo MaterialM */
        .deco-pills {
            position: absolute;
            bottom: 52px;
            right: 44px;
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .deco-pill {
            width: 52px;
            border-radius: 40px;
            background: rgba(37, 99, 235, 0.5);
        }
        .deco-pill:nth-child(1) { height: 110px; }
        .deco-pill:nth-child(2) { height: 145px; background: rgba(37, 99, 235, 0.35); }
        .deco-pill:nth-child(3) { height: 85px;  background: rgba(37, 99, 235, 0.22); }

        /* Texto del panel izquierdo — centrado */
        .panel-left-text {
            position: relative;
            z-index: 2;
            max-width: 320px;
        }

        .panel-left-text h2 {
            font-size: 52px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
            letter-spacing: -0.6px;
            margin-bottom: 16px;
        }

        .panel-left-text p {
            font-size: 16px;
            color: rgba(255,255,255,0.48);
            line-height: 1.7;
        }

        /* ── PANEL DERECHO ───────────────────────────────── */
        .panel-right {
            flex: 0 0 50%;
            width: 50%;
            background: var(--surface);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            transition: background 0.3s ease;
        }

        /* Card con ancho máximo controlado */
        .login-wrapper {
            width: 100%;
            max-width: 380px;
            animation: fadeUp 0.45s ease both;
        }

        .login-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 36px 32px 32px;
            transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
            border: 1.5px solid #C0C5CF;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
        }

        [data-theme="dark"] .login-card {
            border: 1px solid var(--border);
            box-shadow: none;
        }

        /* ── ENCABEZADO ──────────────────────────────────── */
        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-logo-img img {
            width: 220px;
            height: 120px;
            object-fit: contain;
            margin: 0 auto 14px;
            display: block;
        }

        .login-title {
            font-size: 18px; font-weight: 600; letter-spacing: -0.4px;
            color: var(--text-main); margin-bottom: 4px;
        }

        .login-subtitle { font-size: 13px; color: var(--text-soft); }

        /* ── CAMPOS ──────────────────────────────────────── */
        .form-group { margin-bottom: 14px; }

        .form-group label {
            display: block; font-size: 12px; font-weight: 500;
            color: var(--text-soft); margin-bottom: 5px;
        }

        .form-control {
            width: 100%; padding: 9px 12px; font-size: 13.5px;
            font-family: 'DM Sans', sans-serif;
            background: var(--input-bg); border: 1px solid var(--border);
            border-radius: 8px; color: var(--text-main); outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent);
        }

        .form-control::placeholder { color: var(--text-soft); opacity: 0.6; }

        input[name="password"] { font-family: 'DM Mono', monospace; letter-spacing: 1px; }
        input[name="password"]::placeholder { font-family: 'DM Sans', sans-serif; letter-spacing: normal; }

        /* ── BOTÓN ENTRAR ────────────────────────────────── */
        .btn-submit {
            width: 100%; padding: 10px; font-size: 14px; font-weight: 600;
            font-family: 'DM Sans', sans-serif; background: var(--accent);
            color: #fff; border: none; border-radius: 8px; cursor: pointer;
            margin-top: 6px; transition: opacity 0.15s ease, transform 0.1s ease;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .btn-submit:hover  { opacity: 0.88; }
        .btn-submit:active { transform: scale(0.99); }

        .btn-submit svg {
            width: 15px; height: 15px; stroke: #fff; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        .btn-submit.loading { opacity: 0.7; pointer-events: none; }

        /* ── MENSAJE ERROR ───────────────────────────────── */
        #msg { margin-top: 14px; min-height: 20px; }

        #msg .alert-danger {
            font-size: 13px; text-align: center; padding: 9px 14px;
            border-radius: 8px;
            border: 1px solid color-mix(in srgb, #DC2626 20%, transparent);
            background: color-mix(in srgb, #DC2626 8%, transparent);
            color: #DC2626;
        }

        /* ── PIE ─────────────────────────────────────────── */
        .login-footer {
            text-align: center; margin-top: 20px;
            font-size: 12px; color: var(--text-soft);
        }

        /* ── ANIMACIÓN ───────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ════════════════════════════════════════════════════
           RESPONSIVE
        ════════════════════════════════════════════════════ */

        /* Tablet landscape (1024px o menos) */
        @media (max-width: 1024px) {
            .panel-left { flex: 0 0 45%; width: 45%; padding: 40px 32px; }
            .panel-right { flex: 0 0 55%; width: 55%; }
        }

        /* Tablet portrait (768px o menos) */
        @media (max-width: 768px) {
            .login-split { flex-direction: column; }

            .panel-left {
                flex: none;
                width: 100%;
                min-height: 220px;
                padding: 40px 32px;
            }

            /* Ocultar pills en tablet para más limpieza */
            .deco-pills { display: none; }

            .panel-left-text h2 { font-size: 24px; }

            .panel-right {
                flex: none;
                width: 100%;
                background: var(--bg);
                background-image: radial-gradient(var(--dot-color) 1px, transparent 1px);
                background-size: 24px 24px;
                padding: 40px 24px;
            }

            .login-wrapper { max-width: 100%; }
        }

        /* Móvil (480px o menos) */
        @media (max-width: 480px) {
            .panel-left { min-height: 180px; padding: 32px 24px; }
            .panel-left-text h2 { font-size: 20px; }
            .panel-left-text p  { font-size: 13px; }

            .panel-right { padding: 28px 16px; }

            .login-card { padding: 28px 20px 24px; }

            .login-logo-img img { width: 160px; height: 90px; }
        }
    </style>
</head>

<body>

<!-- Botón dark mode -->
<button class="btn-theme" id="themeToggle" title="Cambiar tema">
    <svg class="icon-moon" viewBox="0 0 24 24">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
    <svg class="icon-sun" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="5"/>
        <line x1="12" y1="1"  x2="12" y2="3"/>
        <line x1="12" y1="21" x2="12" y2="23"/>
        <line x1="4.22"  y1="4.22"  x2="5.64"  y2="5.64"/>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
        <line x1="1"  y1="12" x2="3"  y2="12"/>
        <line x1="21" y1="12" x2="23" y2="12"/>
        <line x1="4.22"  y1="19.78" x2="5.64"  y2="18.36"/>
        <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>
    </svg>
</button>

<div class="login-split">

    <!-- ══ PANEL IZQUIERDO ══ -->
    <div class="panel-left">
        <div class="circle-tl"></div>
        <div class="circle-cr"></div>

        <div class="panel-left-text">
            <h2>Bienvenido(a)
                <br>Control de Actas</h2>
            <p>Plataforma de gestión documental para el control y seguimiento de actas administrativas.</p>
        </div>

        <div class="deco-pills">
            <div class="deco-pill"></div>
            <div class="deco-pill"></div>
            <div class="deco-pill"></div>
        </div>
    </div>

    <!-- ══ PANEL DERECHO ══ -->
    <div class="panel-right">
        <div class="login-wrapper">
            <div class="login-card">

                <div class="login-header">
                    <div class="login-logo-img">
                        <img src="Fullgas_Gasolineras.png" alt="logo">
                    </div>
                    <div class="login-title">Control de Actas</div>
                    <br>
                    <div class="login-subtitle">Ingrese usuario y contraseña para iniciar sesión</div>
                </div>

                <form id="formLogin">

                    <div class="form-group">
                        <label>Usuario</label>
                        <input type="text" name="usuario" class="form-control"
                               placeholder="Tu nombre de usuario" required>
                    </div>

                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Tu contraseña" required>
                    </div>

                    <button type="submit" class="btn-submit" id="btnEntrar">
                        <svg viewBox="0 0 24 24">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Ingresar
                    </button>

                </form>

                <div id="msg"></div>

            </div>

            <div class="login-footer">
                Control de Actas &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── Dark mode ────────────────────────────────────────
const toggle = document.getElementById('themeToggle');
const html   = document.documentElement;

if (localStorage.getItem('theme') === 'dark') {
    html.setAttribute('data-theme', 'dark');
}

toggle.addEventListener('click', () => {
    const isDark = html.getAttribute('data-theme') === 'dark';
    html.setAttribute('data-theme', isDark ? 'light' : 'dark');
    localStorage.setItem('theme',   isDark ? 'light' : 'dark');
});

// ── Login (sin cambios funcionales) ──────────────────
$("#formLogin").submit(function(e){
    e.preventDefault();

    const btn = $("#btnEntrar");
    btn.addClass("loading").text("Verificando...");

    $.ajax({
        url: "auth/login.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(resp){
            if(resp === "admin"){
                window.location.href = "admin/dashboard.php";
            }else if(resp === "usuario"){
                window.location.href = "usuario/empresas.php";
            }else{
                $("#msg").html(`
                    <div class="alert-danger">
                        Usuario o contraseña incorrectos
                    </div>
                `);
                btn.removeClass("loading").html(`
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none"
                         stroke="#fff" stroke-width="2" stroke-linecap="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Entrar
                `);
            }
        }
    });
});
</script>

</body>
</html>


