<!DOCTYPE html>
<html lang="es" data-theme='light'>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Control de Actas</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="css/index.css" rel="stylesheet">

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


