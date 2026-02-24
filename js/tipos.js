function abrirNuevo(){
    $("#id_tipo").val("");
    $("#nombre_tipo").val("");

    new bootstrap.Modal(document.getElementById("modalTipo")).show();
}

function abrirEditar(id,nombre){
    $("#id_tipo").val(id);
    $("#nombre_tipo").val(nombre);

    new bootstrap.Modal(document.getElementById("modalTipo")).show();
}

function guardarTipo(){

    let id = $("#id_tipo").val();
    let nombre = $("#nombre_tipo").val().trim();

    if(nombre==""){
        alert("El nombre es obligatorio.");
        return;
    }

    $.post("../ajax/tipo_save.php",{id,nombre},function(resp){
        alert(resp);
        location.reload();
    });
}

function cambiarEstado(id,estado){
    $.post("../ajax/tipo_estado.php",{id,estado},function(resp){
        alert(resp);
        location.reload();
    });
}