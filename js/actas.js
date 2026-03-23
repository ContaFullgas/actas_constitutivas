function agregarActa(){

    let id_empresa = $("#empresa").val();
    let id_tipo    = $("#tipo").val();
    let ubicacion  = $("#ubicacion").val().trim();

    if(id_empresa === "" || id_tipo === "" || ubicacion === ""){
        showToast("Todos los campos son obligatorios.", "error");
        return;
    }

    let formData = new FormData();
    formData.append("id_empresa", id_empresa);
    formData.append("id_tipo", id_tipo);
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
            showToast(resp, "success");
            setTimeout(() => location.reload(), 1200);
        }
    });
}

function eliminarActa(id){
    showConfirm(
        "¿Eliminar acta?",
        "Esta acción no se puede deshacer.",
        function(){
            $.post("../ajax/acta_delete.php", { id }, function(resp){
                showToast(resp, "success");
                setTimeout(() => location.reload(), 1200);
            });
        }
    );
}

function verImagen(src){
    document.getElementById("imgGrande").src = src;
    let modal = new bootstrap.Modal(document.getElementById('modalImagen'));
    modal.show();
}

// ── CAMBIO: se agrega el parámetro fotoActual para mostrar la imagen existente
function abrirEditarActa(id, id_empresa, id_tipo, ubicacion, fotoActual){
    $("#edit_id_acta").val(id);
    $("#edit_ubicacion").val(ubicacion);
    $("#edit_empresa").val(id_empresa).trigger('change');
    $("#edit_tipo").val(id_tipo);

    const zone     = document.getElementById('dropZoneEdit');
    const preview  = document.getElementById('previewEdit');
    const filename = document.getElementById('filenameEdit');

    // Si existe imagen actual, mostrarla en el drop zone
    if(fotoActual && fotoActual !== ''){
        preview.src = fotoActual;
        zone.classList.add('has-file');
        filename.textContent = 'Imagen actual';
    } else {
        // Sin imagen previa: limpiar la zona
        resetDropZone('dropZoneEdit', 'edit_foto', 'previewEdit', 'filenameEdit');
    }

    let modal = new bootstrap.Modal(document.getElementById("modalEditarActa"));
    modal.show();
}

function guardarEdicionActa(){

    let id         = $("#edit_id_acta").val();
    let id_empresa = $("#edit_empresa").val();
    let id_tipo    = $("#edit_tipo").val();
    let ubicacion  = $("#edit_ubicacion").val().trim();

    if(id_empresa === "" || id_tipo === "" || ubicacion === ""){
        showToast("Todos los campos son obligatorios.", "error");
        return;
    }

    let formData = new FormData();
    formData.append("id", id);
    formData.append("id_empresa", id_empresa);
    formData.append("id_tipo", id_tipo);
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
            showToast(resp, "success");
            setTimeout(() => location.reload(), 1200);
        }
    });
}

// Select2 — formulario principal + modal editar
$(document).ready(function(){

    $('#empresa').select2({
        theme: 'bootstrap-5',
        placeholder: "Seleccionar empresa...",
        allowClear: true,
        width: '100%'
    });

    $('#edit_empresa').select2({
        theme: 'bootstrap-5',
        placeholder: "Seleccionar empresa...",
        allowClear: true,
        dropdownParent: $('#modalEditarActa'),
        width: '100%'
    });

});


