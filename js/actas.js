function agregarActa(){

    let formData = new FormData();
    formData.append("id_empresa", $("#empresa").val());
    formData.append("tipo", $("#tipo").val());
    formData.append("ubicacion", $("#ubicacion").val());

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