function solicitarDevolucion(id) {
    showConfirm(
        "¿Solicitar devolución?",
        "Se enviará la solicitud de devolución al administrador.",
        function () {
            $.post("../ajax/solicitar_devolucion.php",
                { id_prestamo: id },
                function (resp) {
                    showToast(resp, "success");

                    const badge = `<span class="badge-status badge-devolucion">Devolución pendiente</span>`;
                    $("#accion_" + id).html(badge);
                    $("#accion_mob_" + id).html(badge);

                    setTimeout(() => location.reload(), 1200);
                }
            );
        }
    );
}


