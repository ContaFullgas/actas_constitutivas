function abrirNuevo() {
  $("#id_usuario").val("");
  $("#usuario").val("");
  $("#nombre").val("");
  $("#departamento").val("");
  $("#password").val("");
  $("#rol").val("usuario");

  new bootstrap.Modal(document.getElementById("modalUsuario")).show();
}

function abrirEditar(id, usuario, nombre, departamento, rol) {
  $("#id_usuario").val(id);
  $("#usuario").val(usuario);
  $("#nombre").val(nombre);
  $("#departamento").val(departamento);
  $("#password").val("");
  $("#rol").val(rol);

  new bootstrap.Modal(document.getElementById("modalUsuario")).show();
}

function guardarUsuario() {
  let id = $("#id_usuario").val();
  let usuario = $("#usuario").val().trim();
  let nombre = $("#nombre").val().trim();
  let departamento = $("#departamento").val();
  let password = $("#password").val().trim();
  let rol = $("#rol").val();

  if (usuario == "" || nombre == "" || departamento == "") {
    alert("Campos obligatorios.");
    return;
  }

  $.post(
    "../ajax/usuario_save.php",
    {
      id,
      usuario,
      nombre,
      departamento,
      password,
      rol,
    },
    function (resp) {
      alert(resp);
      location.reload();
    },
  );
}

function cambiarEstado(id, estado) {
  $.post("../ajax/usuario_estado.php", { id, estado }, function (resp) {
    alert(resp);
    location.reload();
  });
}
