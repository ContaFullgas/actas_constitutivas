function autorizarPrestamo(id){
    showConfirm(
        "¿Autorizar préstamo?",
        "El usuario podrá retirar el acta solicitada.",
        function(){
            $.post("../ajax/autorizar_prestamo.php", { id_prestamo: id }, function(resp){
                showToast(resp, "success");
                setTimeout(() => location.reload(), 1200);
            });
        }
    );
}

function rechazarPrestamo(id){
    const obsWrap  = document.getElementById('confirmObsWrap');
    const obsInput = document.getElementById('confirmObsInput');
    obsWrap.style.display = 'block';
    obsInput.value = '';

    showConfirm(
        "¿Rechazar solicitud?",
        "La solicitud será cancelada.",
        function(){
            const observaciones = document.getElementById('confirmObsInput').value.trim();
            $.post("../ajax/rechazar_prestamo.php", { id_prestamo: id, observaciones: observaciones }, function(resp){
                showToast(resp, "success");
                setTimeout(() => location.reload(), 1200);
            });
        }
    );
}

function autorizarDevolucion(id){
    // Paso 1: usar showConfirm normal
    showConfirm(
        "¿Autorizar devolución?",
        "El acta volverá a estar disponible.",
        function(){
            // Paso 2: mostrar selector de condición SIN llamar showConfirm
            // El overlay sigue abierto; solo reemplazamos el contenido del box
            _mostrarSelectorCondicion(id);
        }
    );
}

function _mostrarSelectorCondicion(id){
    const overlay = document.getElementById('confirmOverlay');
    const box     = document.getElementById('confirmBox');

    // Aseguramos que el overlay siga visible
    overlay.classList.add('show');

    // Animación de salida
    box.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
    box.style.opacity    = '0';
    box.style.transform  = 'scale(0.95)';

    setTimeout(function(){
        // Reemplazar contenido completo del box
        box.innerHTML = `
            <div class="confirm-icon" style="background:color-mix(in srgb,#2563EB 10%,transparent);color:#2563EB;">🗂️</div>
            <div class="confirm-title">¿En qué estado se encuentra?</div>
            <div class="confirm-desc">Indica la condición en que fue devuelta el acta.</div>
            <div class="confirm-condicion-actions">
                <button class="btn-condicion-bien" id="btnBienEstado">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Buen estado
                </button>
                <button class="btn-condicion-mal" id="btnMalEstado">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Mal estado
                </button>
            </div>
        `;

        // Animación de entrada
        box.style.opacity   = '1';
        box.style.transform = 'scale(1)';

        // Vincular botones
        document.getElementById('btnBienEstado').onclick = function(){
            overlay.classList.remove('show');
            _enviarDevolucion(id, 'bueno');
        };

        document.getElementById('btnMalEstado').onclick = function(){
            overlay.classList.remove('show');
            _enviarDevolucion(id, 'malo');
        };

        // Clic fuera del box cierra sin enviar
        overlay.onclick = function(e){
            if(e.target === overlay){
                overlay.classList.remove('show');
                overlay.onclick = null;
            }
        };

    }, 160);
}

function _enviarDevolucion(id, condicion){
    $.post("../ajax/autorizar_devolucion.php", { id_prestamo: id, condicion: condicion }, function(resp){
        showToast(resp, "success");
        setTimeout(() => location.reload(), 1200);
    });
}


