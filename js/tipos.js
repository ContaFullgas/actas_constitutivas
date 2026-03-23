function abrirNuevo(){
    $("#id_tipo").val("");
    $("#nombre_tipo").val("");
    new bootstrap.Modal(document.getElementById("modalTipo")).show();
}

function abrirEditar(id, nombre){
    $("#id_tipo").val(id);
    $("#nombre_tipo").val(nombre);
    new bootstrap.Modal(document.getElementById("modalTipo")).show();
}

function guardarTipo(){
    let id     = $("#id_tipo").val();
    let nombre = $("#nombre_tipo").val().trim();

    if(nombre === ""){
        showToast("El nombre es obligatorio.", "error");
        return;
    }

    $.post("../ajax/tipo_save.php", { id, nombre }, function(resp){
        showToast(resp, "success");
        setTimeout(() => location.reload(), 1200);
    });
}

function cambiarEstado(id, estado){
    const titulo = estado == 0 ? "¿Desactivar tipo?" : "¿Activar tipo?";
    const desc   = estado == 0
        ? "El tipo no aparecerá en nuevas actas."
        : "El tipo volverá a estar disponible.";

    showConfirm(titulo, desc, function(){
        $.post("../ajax/tipo_estado.php", { id, estado }, function(resp){
            showToast(resp, "success");
            setTimeout(() => location.reload(), 1200);
        });
    });
}