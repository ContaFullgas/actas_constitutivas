function agregarEmpresa(){

  let nombre = document.getElementById("nombre").value.trim();
  let rfc = document.getElementById("rfc").value.trim();
  let fecha = document.getElementById("fecha").value;

  if(nombre === "" || rfc === "" || fecha === ""){
      alert("Todos los campos son obligatorios.");
      return;
  }

  if(rfc.length !== 12 && rfc.length !== 13){
      alert("El RFC debe tener 12 o 13 caracteres.");
      return;
  }

  $.post("../ajax/empresa_add.php", {
    nombre, rfc, fecha
  }, function(resp){
    alert(resp);
    location.reload();
  });
}


function eliminarEmpresa(id){
  if(confirm("¿Eliminar empresa?")){
    $.post("../ajax/empresa_delete.php", { id }, function(resp){
      alert(resp);
      location.reload();
    });
  }
}


function abrirEditarEmpresa(id, nombre, rfc, fecha){

    $("#edit_id").val(id);
    $("#edit_nombre").val(nombre);
    $("#edit_rfc").val(rfc);
    $("#edit_fecha").val(fecha);

    let modal = new bootstrap.Modal(
        document.getElementById("modalEditarEmpresa")
    );
    modal.show();
}

function guardarEdicionEmpresa(){

    let id = $("#edit_id").val();
    let nombre = $("#edit_nombre").val().trim();
    let rfc = $("#edit_rfc").val().trim();
    let fecha = $("#edit_fecha").val();

    if(nombre === "" || rfc === "" || fecha === ""){
        alert("Todos los campos son obligatorios.");
        return;
    }

    if(rfc.length !== 12 && rfc.length !== 13){
        alert("El RFC debe tener 12 o 13 caracteres.");
        return;
    }

    $.post("../ajax/empresa_update.php", {
        id, nombre, rfc, fecha
    }, function(resp){
        alert(resp);
        location.reload();
    });
}
