function agregarEmpresa(){
  let nombre = document.getElementById("nombre").value;
  let rfc = document.getElementById("rfc").value;
  let fecha = document.getElementById("fecha").value;

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
