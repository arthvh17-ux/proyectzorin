const API_URL = 'http://localhost:8000';

document.addEventListener('DOMContentLoaded', () => {
    cargarActivos();
    cargarBitacora();

    const formActivo = document.getElementById('formActivo');
    if (formActivo) {
        formActivo.addEventListener('submit', function(e) {
            e.preventDefault();
            validarYRegistrarActivo();
        });
    }
});

// 1. Petición asíncrona para consultar y listar Activos + Dashboard
async function cargarActivos() {
    try {
        const response = await fetch(`${API_URL}/activos.php`);
        const activos = await response.json();
        
        const tbody = document.getElementById('tablaActivos');
        tbody.innerHTML = '';

        if(activos.message) {
            tbody.innerHTML = `<tr><td colspan="5">${activos.message}</td></tr>`;
            return;
        }

        // Variables para indicadores estadísticos (Métricas)
        let total = activos.length;
        let prestados = 0;
        let disponibles = 0;

        activos.forEach(activo => {
            let badgeEstado = '';
            let botonAccion = '';

            if (activo.estado === 'disponible') {
                disponibles++;
                badgeEstado = '<span class="badge-disponible">Disponible</span>';
                botonAccion = `<button onclick="prestarActivo(${activo.id})">Prestar</button>`;
            } else if (activo.estado === 'prestado') {
                prestados++;
                badgeEstado = '<span class="badge-prestado">Prestado</span>';
                botonAccion = `<button style="background-color:#27ae60;" onclick="devolverActivo(${activo.id})">Devolver</button>`;
            } else {
                badgeEstado = '<span class="badge-mantenimiento">Mantenimiento</span>';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${activo.id}</td>
                <td>${activo.nombre_activo}</td>
                <td>${activo.tipo}</td>
                <td>${badgeEstado}</td>
                <td>${botonAccion}</td>
            `;
            tbody.appendChild(tr);
        });

        // Actualizar métricas en el dashboard
        document.getElementById('totalActivos').innerText = total;
        document.getElementById('activosPrestados').innerText = prestados;
        document.getElementById('activosDisponibles').innerText = disponibles;

    } catch (error) {
        console.error("Error cargando inventario:", error);
    }
}

// 2. Validación en el frontend antes de enviar a la API
function validarYRegistrarActivo() {
    const nombre = document.getElementById('nombreActivo').value.trim();
    const tipo = document.getElementById('tipoActivo').value.trim();
    const descripcion = document.getElementById('descripcionActivo').value.trim();

    // Validación rigurosa de longitud y caracteres
    if (nombre.length < 3 || tipo.length < 3) {
        alert("¡Validación frontal! El nombre y el tipo deben tener al menos 3 caracteres.");
        return;
    }

    // Si pasa validación, ejecuta el registro
    ejecutarRegistroActivo(nombre, tipo, descripcion);
}

// Envío asíncrono a API
async function executarRegistroActivo(nombre, tipo, descripcion) {
    const nuevoActivo = {
        nombre_activo: nombre,
        tipo: tipo,
        descripcion: descripcion,
        estado: 'disponible'
    };

    try {
        const response = await fetch(`${API_URL}/activos.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(nuevoActivo)
        });
        const result = await response.json();
        
        if(result.status === 'success') {
            alert(result.message);
            document.getElementById('formActivo').reset();
            cargarActivos();
            cargarBitacora();
        } else {
            alert("Error del servidor: " + result.message);
        }
    } catch (error) {
        console.error("Error de conexión al registrar:", error);
    }
}

// 3. Petición asíncrona para Préstamo
async function prestarActivo(activoId) {
    const fechaLimite = new Date();
    fechaLimite.setDate(fechaLimite.getDate() + 7); // Préstamo automático por 7 días
    const fechaLimiteStr = fechaLimite.toISOString().slice(0, 19).replace('T', ' ');

    const payload = {
        usuario_id: 1, 
        activo_id: activoId,
        fecha_limite: fechaLimiteStr
    };

    try {
        const response = await fetch(`${API_URL}/prestamos.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        alert(result.message);
        cargarActivos();
        cargarBitacora();
    } catch (error) {
        console.error("Error al prestar:", error);
    }
}

// 4. Petición asíncrona para Devolución
async function devolverActivo(activoId) {
    const payload = {
        prestamo_id: 1,
        activo_id: activoId
    };

    try {
        const response = await fetch(`${API_URL}/prestamos.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        alert(result.message);
        cargarActivos();
        cargarBitacora();
    } catch (error) {
        console.error("Error al devolver:", error);
    }
}

// 5. Cargar bitácora de auditoría asíncronamente
async function cargarBitacora() {
    try {
        const response = await fetch(`${API_URL}/auditoria.php`);
        const eventos = await response.json();
        
        const tbody = document.getElementById('tablaBitacora');
        tbody.innerHTML = '';

        eventos.forEach(evento => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${evento.id}</td>
                <td>${evento.usuario_accion_id} (${evento.usuario_nombre})</td>
                <td style="font-weight:bold; color:#c0392b;">${evento.accion}</td>
                <td>${evento.descripcion}</td>
                <td>${evento.fecha_evento}</td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error("Error cargando bitácora:", error);
    }
}