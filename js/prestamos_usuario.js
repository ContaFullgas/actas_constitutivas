function solicitarPrestamo(id_acta) {
  showConfirm(
    "¿Solicitar préstamo?",
    "Se enviará una solicitud al administrador para esta acta.",
    function () {
      $.post("../ajax/solicitar_prestamo.php", { id_acta }, function (resp) {
        let esError = resp.includes("no está disponible");

        showToast(resp, esError ? "error" : "success");

        if (!esError) {
          const badge = `<span class="badge-status badge-pendiente">Solicitud pendiente</span>`;
          $("#accion_" + id_acta).html(badge);
          $("#accion_mob_" + id_acta).html(badge);
        }
      });
    }
  );
}

function verImagen(src) {
  document.getElementById("imgGrande").src = src;
  let modal = new bootstrap.Modal(document.getElementById("modalImagen"));
  modal.show();
}