function agregarEmpresa(){
    let nombre = document.getElementById("nombre").value.trim();
    let rfc    = document.getElementById("rfc").value.trim();
    let fecha  = document.getElementById("fecha").value;

    if(nombre === "" || rfc === "" || fecha === ""){
        showToast("Todos los campos son obligatorios.", "error");
        return;
    }
    if(rfc.length !== 12 && rfc.length !== 13){
        showToast("El RFC debe tener 12 o 13 caracteres.", "error");
        return;
    }
    $.post("../ajax/empresa_add.php", { nombre, rfc, fecha }, function(resp){
        showToast(resp, "success");
        setTimeout(() => location.reload(), 1200);
    });
}

function eliminarEmpresa(id){
    showConfirm("¿Eliminar empresa?", "Esta acción no se puede deshacer.", function(){
        $.post("../ajax/empresa_delete.php", { id }, function(resp){
            if(resp.trim() === "en_uso"){
                // Mostrar modal de empresa en uso
                document.getElementById('enUsoOverlay').classList.add('show');
            } else {
                showToast(resp, "success");
                setTimeout(() => location.reload(), 1200);
            }
        });
    });
}

function cerrarEnUso(){
    document.getElementById('enUsoOverlay').classList.remove('show');
}

function abrirEditarEmpresa(id, nombre, rfc, fecha){
    $("#edit_id").val(id);
    $("#edit_nombre").val(nombre);
    $("#edit_rfc").val(rfc);
    $("#edit_fecha").val(fecha);
    let modal = new bootstrap.Modal(document.getElementById("modalEditarEmpresa"));
    modal.show();
}

function guardarEdicionEmpresa(){
    let id     = $("#edit_id").val();
    let nombre = $("#edit_nombre").val().trim();
    let rfc    = $("#edit_rfc").val().trim();
    let fecha  = $("#edit_fecha").val();

    if(nombre === "" || rfc === "" || fecha === ""){
        showToast("Todos los campos son obligatorios.", "error");
        return;
    }
    if(rfc.length !== 12 && rfc.length !== 13){
        showToast("El RFC debe tener 12 o 13 caracteres.", "error");
        return;
    }
    $.post("../ajax/empresa_update.php", { id, nombre, rfc, fecha }, function(resp){
        showToast(resp, "success");
        setTimeout(() => location.reload(), 1200);
    });
}


