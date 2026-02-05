<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | Control de Actas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-4">

            <div class="card shadow">
                <div class="card-body">

                    <h4 class="text-center mb-4">Iniciar sesión</h4>

                    <form id="formLogin">

                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Entrar
                            </button>
                        </div>

                    </form>

                    <div id="msg" class="mt-3"></div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
$("#formLogin").submit(function(e){
    e.preventDefault();

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
                    <div class="alert alert-danger text-center">
                        Usuario o contraseña incorrectos
                    </div>
                `);
            }
        }
    });
});
</script>

</body>
</html>
