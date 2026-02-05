function solicitarDevolucion(id){
    if(!confirm("¿Deseas solicitar la devolución de esta acta?")){
        return;
    }

    $.post("../ajax/solicitar_devolucion.php",
        { id_prestamo: id },
        function(resp){
            $("#msg").html(`
                <div class="alert alert-success">
                    ${resp}
                </div>
            `);
            setTimeout(() => location.reload(), 800);
        }
    );
}
