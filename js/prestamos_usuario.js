function solicitarPrestamo(id_acta){
    if(!confirm("¿Deseas solicitar el préstamo de esta acta?")){
        return;
    }

    $.post("../ajax/solicitar_prestamo.php",
        { id_acta },
        function(resp){

            let tipo = resp.includes("Ya tienes")
                ? "warning"
                : "success";

            $("#msg").html(`
                <div class="alert alert-${tipo}">
                    ${resp}
                </div>
            `);
        }
    );
}
