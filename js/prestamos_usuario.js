function solicitarPrestamo(id_acta) {
  if (!confirm("¿Deseas solicitar el préstamo de esta acta?")) {
    return;
  }

  $.post("../ajax/solicitar_prestamo.php", { id_acta }, function (resp) {
    let esError = resp.includes("no está disponible");

    $("#msg").html(`
                <div class="alert alert-${esError ? "warning" : "success"}">
                    ${resp}
                </div>
            `);

    // 🔥 Si fue exitoso, actualizar botón dinámicamente
    if (!esError) {
      $("#accion_" + id_acta).html(`
                    <span class="badge bg-warning text-dark">
                        Solicitud pendiente
                    </span>
                `);
    }
  });
}

function verImagen(src) {
  document.getElementById("imgGrande").src = src;
  let modal = new bootstrap.Modal(document.getElementById("modalImagen"));
  modal.show();
}
