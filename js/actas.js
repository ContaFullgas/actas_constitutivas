function agregarActa(){
  let id_empresa = document.getElementById("empresa").value;
  let tipo = document.getElementById("tipo").value;
  let ubicacion = document.getElementById("ubicacion").value;

  $.post("../ajax/acta_add.php", {
    id_empresa, tipo, ubicacion
  }, function(resp){
    alert(resp);
    location.reload();
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
