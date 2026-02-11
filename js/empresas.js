function agregarEmpresa(){

  let nombre = document.getElementById("nombre").value.trim();
  let rfc = document.getElementById("rfc").value.trim();
  let fecha = document.getElementById("fecha").value;

  if(nombre === "" || rfc === "" || fecha === ""){
      alert("Todos los campos son obligatorios.");
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
