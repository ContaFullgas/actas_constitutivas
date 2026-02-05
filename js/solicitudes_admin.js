function autorizarPrestamo(id){
    if(!confirm("¿Autorizar este préstamo?")) return;

    $.post("../ajax/autorizar_prestamo.php",
        { id_prestamo: id },
        function(resp){
            alert(resp);
            location.reload();
        }
    );
}

function rechazarPrestamo(id){
    if(!confirm("¿Rechazar esta solicitud?")) return;

    $.post("../ajax/rechazar_prestamo.php",
        { id_prestamo: id },
        function(resp){
            alert(resp);
            location.reload();
        }
    );
}

function autorizarDevolucion(id){
    if(!confirm("¿Autorizar devolución del acta?")) return;

    $.post("../ajax/autorizar_devolucion.php",
        { id_prestamo: id },
        function(resp){
            alert(resp);
            location.reload();
        }
    );
}
