function agregarActa(){

    let id_empresa = $("#empresa").val();
    let tipo = $("#tipo").val().trim();
    let ubicacion = $("#ubicacion").val().trim();

    if(id_empresa === "" || tipo === "" || ubicacion === ""){
        alert("Todos los campos son obligatorios (excepto la foto).");
        return;
    }

    let formData = new FormData();
    formData.append("id_empresa", id_empresa);
    formData.append("tipo", tipo);
    formData.append("ubicacion", ubicacion);

    let foto = document.getElementById("foto").files[0];
    if(foto){
        formData.append("foto", foto);
    }

    $.ajax({
        url: "../ajax/acta_add.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(resp){
            alert(resp);
            location.reload();
        }
    });
}



function eliminarActa(id){
  if(confirm("¿Eliminar acta?")){
    $.post("../ajax/acta_delete.php", { id }, function(resp){
      alert(resp);
      location.reload();
    });
  }
}

function verImagen(src){
    document.getElementById("imgGrande").src = src;
    let modal = new bootstrap.Modal(document.getElementById('modalImagen'));
    modal.show();
}

function abrirEditarActa(id, id_empresa, tipo, ubicacion){

    $("#edit_id_acta").val(id);
    $("#edit_empresa").val(id_empresa);
    $("#edit_tipo").val(tipo);
    $("#edit_ubicacion").val(ubicacion);

    let modal = new bootstrap.Modal(
        document.getElementById("modalEditarActa")
    );
    modal.show();
}

function guardarEdicionActa(){

    let id = $("#edit_id_acta").val();
    let id_empresa = $("#edit_empresa").val();
    let tipo = $("#edit_tipo").val().trim();
    let ubicacion = $("#edit_ubicacion").val().trim();

    if(id_empresa === "" || tipo === "" || ubicacion === ""){
        alert("Todos los campos son obligatorios.");
        return;
    }

    let formData = new FormData();
    formData.append("id", id);
    formData.append("id_empresa", id_empresa);
    formData.append("tipo", tipo);
    formData.append("ubicacion", ubicacion);

    let foto = document.getElementById("edit_foto").files[0];
    if(foto){
        formData.append("foto", foto);
    }

    $.ajax({
        url: "../ajax/acta_update.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(resp){
            alert(resp);
            location.reload();
        }
    });
}
