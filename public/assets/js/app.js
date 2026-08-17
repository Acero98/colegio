document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.sidebar-link').forEach((enlace) => {
        if (!enlace.title) enlace.title = enlace.textContent.trim();
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js').catch(() => {});
    }
    const flash = document.getElementById('flash-message');
    if (flash && window.Swal) {
        Swal.fire({
            icon: flash.dataset.tipo || 'success',
            title: flash.dataset.texto || '',
            timer: 2400,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    const plantillaEstadoPersona = document.getElementById('plantilla-estado-persona');
    if (plantillaEstadoPersona) {
        document.querySelectorAll('.btn-editar[data-id]').forEach((botonEditar) => {
            const formularioEstado = plantillaEstadoPersona.content.firstElementChild.cloneNode(true);
            const siguienteEstado = botonEditar.dataset.estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
            const botonEstado = formularioEstado.querySelector('.btn-estado');
            formularioEstado.querySelector('[name="id_persona"]').value = botonEditar.dataset.id;
            formularioEstado.querySelector('[name="estado"]').value = siguienteEstado;
            botonEstado.classList.add(siguienteEstado === 'ACTIVO' ? 'btn-activar' : 'btn-desactivar');
            botonEstado.title = siguienteEstado === 'ACTIVO' ? 'Activar persona' : 'Desactivar persona';
            botonEstado.setAttribute('aria-label', botonEstado.title);
            botonEstado.innerHTML = siguienteEstado === 'ACTIVO'
                ? '<i class="bi bi-person-check-fill"></i>'
                : '<i class="bi bi-person-x-fill"></i>';
            botonEditar.after(formularioEstado);
        });
    }

    const confirmaciones = {
        guardar: { titulo: '¿Deseas guardar esta persona?', texto: 'Se registrará en la base institucional.', boton: 'Sí, guardar', color: '#2563eb' },
        editar: { titulo: '¿Deseas guardar los cambios?', texto: 'Se actualizará la información de la persona.', boton: 'Sí, guardar cambios', color: '#fef3c7', claseBoton: 'swal-editar-confirmar' },
        ACTIVAR: { titulo: '¿Deseas activar a esta persona?', texto: 'Volverá a estar disponible en el sistema.', boton: 'Sí, activar', color: '#059669' },
        INACTIVO: { titulo: '¿Deseas desactivar a esta persona?', texto: 'Dejará de estar disponible para nuevos procesos.', boton: 'Sí, desactivar', color: '#dc2626' },
        'academico-guardar': { titulo: '¿Deseas agregar este registro académico?', texto: 'Se incorporará a la estructura académica.', boton: 'Sí, agregar', color: '#2563eb' },
        'academico-editar': { titulo: '¿Deseas guardar los cambios?', texto: 'Se actualizará la estructura académica.', boton: 'Sí, guardar cambios', color: '#d97706' },
        'academico-activar': { titulo: '¿Deseas activar este registro?', texto: 'Volverá a estar disponible para su uso.', boton: 'Sí, activar', color: '#059669' },
        'academico-desactivar': { titulo: '¿Deseas desactivar este registro?', texto: 'No estará disponible para nuevos procesos.', boton: 'Sí, desactivar', color: '#dc2626' },
        'matricula-guardar': { titulo: '¿Deseas registrar esta matrícula?', texto: 'Se validará el cupo disponible de la sección.', boton: 'Sí, matricular', color: '#2563eb' },
        'matricula-editar': { titulo: '¿Deseas guardar los cambios?', texto: 'Se actualizará la matrícula y se validará el cupo si corresponde.', boton: 'Sí, guardar cambios', color: '#d97706' },
        'promocion-procesar': { titulo: '¿Deseas procesar la promoción?', texto: 'Se crearán las nuevas matrículas y se aplicarán promociones, repitencias, retiros o egresos.', boton: 'Sí, procesar', color: '#2563eb' },
        'asistencia-manual': { titulo: '¿Deseas guardar la asistencia manual?', texto: 'Se actualizará el registro de todos los alumnos de la sección.', boton: 'Sí, guardar', color: '#2563eb' },
        'asistencia-cerrar': { titulo: '¿Deseas cerrar la asistencia de hoy?', texto: 'Los alumnos activos sin registro serán marcados como falta.', boton: 'Sí, cerrar día', color: '#d97706' },
        'asistencia-cerrar-personal': { titulo: '¿Deseas cerrar la asistencia del personal?', texto: 'Se registrarán faltas de jornadas y sesiones que no tengan marcación.', boton: 'Sí, cerrar personal', color: '#7c3aed' }
        ,'horario-guardar': { titulo: '¿Deseas guardar este horario?', texto: 'La jornada o sesión quedará disponible para el control de asistencia.', boton: 'Sí, guardar', color: '#2563eb' },
        'horario-editar': { titulo: '¿Deseas guardar los cambios?', texto: 'Se actualizará la configuración del horario.', boton: 'Sí, guardar cambios', color: '#d97706' },
        'horario-desactivar': { titulo: '¿Deseas desactivar esta jornada?', texto: 'Dejará de permitir nuevas marcaciones.', boton: 'Sí, desactivar', color: '#dc2626' },
        'horario-eliminar': { titulo: '¿Deseas desactivar esta sesión?', texto: 'La sesión quedará inactiva y no permitirá nuevas marcaciones.', boton: 'Sí, desactivar', color: '#dc2626' },
        'horario-activar': { titulo: '¿Deseas activar este horario?', texto: 'Volverá a estar disponible para registrar asistencia.', boton: 'Sí, activar', color: '#059669' }
        ,'transicion-laboral': { titulo: '¿Deseas guardar este vínculo laboral?', texto: 'Si la persona tiene un vínculo activo, se finalizará y se conservará su historial.', boton: 'Sí, guardar transición', color: '#7c3aed' }
    };
    document.querySelectorAll('form[action*="academico_guardar"]').forEach((formulario) => { formulario.dataset.confirmarAccion = 'academico-guardar'; });
    document.querySelectorAll('form[action*="academico_actualizar"]').forEach((formulario) => { formulario.dataset.confirmarAccion = 'academico-editar'; });
    document.querySelectorAll('form[action*="academico_cambiar_estado"]').forEach((formulario) => {
        formulario.dataset.confirmarAccion = formulario.querySelector('[name="estado"]')?.value === 'ACTIVO' ? 'academico-activar' : 'academico-desactivar';
    });
    document.querySelectorAll('form[action*="matriculas_guardar"]').forEach((formulario) => { formulario.dataset.confirmarAccion = 'matricula-guardar'; });
    document.querySelectorAll('form[action*="matriculas_actualizar"]').forEach((formulario) => { formulario.dataset.confirmarAccion = 'matricula-editar'; });
    document.querySelectorAll('form[action*="horarios_guardar"]').forEach((formulario) => {
        const entidad = formulario.querySelector('[name="entidad"]')?.value;
        formulario.dataset.confirmarAccion = entidad === 'jornada_desactivar' ? 'horario-desactivar' : (entidad === 'sesion_desactivar' ? 'horario-eliminar' : (entidad === 'jornada_activar' || entidad === 'sesion_activar' ? 'horario-activar' : (entidad?.includes('actualizar') ? 'horario-editar' : 'horario-guardar')));
    });
    document.querySelectorAll('form[data-promocion-anual]').forEach((formulario) => { formulario.dataset.confirmarAccion = 'promocion-procesar'; });
    document.querySelectorAll('form[data-confirmar-accion]').forEach((formulario) => {
        formulario.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            const accion = formulario.dataset.confirmarAccion || formulario.querySelector('[name="estado"]')?.value;
            const configuracion = confirmaciones[accion] || confirmaciones.editar;
            const confirmado = window.Swal
                ? (await Swal.fire({ icon: 'question', title: configuracion.titulo, text: configuracion.texto, showCancelButton: true, confirmButtonText: configuracion.boton, cancelButtonText: 'Cancelar', confirmButtonColor: configuracion.color, customClass: configuracion.claseBoton ? { confirmButton: configuracion.claseBoton } : {} })).isConfirmed
                : window.confirm(configuracion.titulo);
            if (confirmado) HTMLFormElement.prototype.submit.call(formulario);
        });
    });

    document.querySelectorAll('[id^="modal-"] form').forEach((formulario) => {
        const yaTieneCancelar = Array.from(formulario.querySelectorAll('button')).some((boton) =>
            boton.textContent.trim().toLowerCase() === 'cancelar'
        );
        let guardar = formulario.querySelector('button[type="submit"], button:not([type])');
        if (yaTieneCancelar) return;
        if (!guardar) {
            guardar = document.createElement('button');
            guardar.type = 'submit';
            guardar.textContent = formulario.action.includes('actualizar') ? 'Guardar cambios' : 'Guardar';
            guardar.className = 'rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white';
            formulario.append(guardar);
        }

        const cancelar = document.createElement('button');
        cancelar.type = 'button';
        cancelar.textContent = 'Cancelar';
        cancelar.className = 'modal-cancelar';
        cancelar.addEventListener('click', () => {
            const modal = formulario.closest('[id^="modal-"]');
            if (modal) modal.classList.add('hidden');
        });
        const acciones = document.createElement('div');
        acciones.className = 'modal-acciones';
        guardar.parentNode.insertBefore(acciones, guardar);
        acciones.append(cancelar, guardar);
    });

    document.querySelectorAll('[id^="modal-"].fixed').forEach((modal) => {
        const caja = modal.firstElementChild;
        if (!caja || caja.querySelector('[data-modal-close], .bi-x-lg')) return;
        caja.classList.add('relative');
        const cerrar = document.createElement('button');
        cerrar.type = 'button';
        cerrar.dataset.modalClose = 'true';
        cerrar.className = 'modal-x';
        cerrar.innerHTML = '<i class="bi bi-x-lg"></i>';
        cerrar.addEventListener('click', () => modal.classList.add('hidden'));
        caja.prepend(cerrar);
    });

    if (window.DataTable) {
        const tablaAlumnos = document.querySelector('#contenedor-tabla-alumnos table');
        if (tablaAlumnos) {
            tablaAlumnos.id = 'tabla-alumnos';
            tablaAlumnos.dataset.datatable = '';
            tablaAlumnos.dataset.pageOptions = '5,10,25,50';

            const filaVacia = tablaAlumnos.querySelector('tbody tr td[colspan]')?.closest('tr');
            filaVacia?.remove();

            const encabezadoAlumnos = tablaAlumnos.querySelector('thead tr');
            if (encabezadoAlumnos && !encabezadoAlumnos.querySelector('[data-columna-numero]')) {
                const columnaNumero = document.createElement('th');
                columnaNumero.dataset.columnaNumero = 'true';
                columnaNumero.className = 'no-sort px-5 py-4';
                columnaNumero.textContent = 'N°';
                encabezadoAlumnos.prepend(columnaNumero);
                tablaAlumnos.querySelectorAll('tbody tr').forEach((fila, indice) => {
                    const numero = document.createElement('td');
                    numero.className = 'px-5 py-4 text-slate-500';
                    numero.textContent = String(indice + 1);
                    fila.prepend(numero);
                    fila.classList.add('transition', 'hover:bg-slate-50');
                });
            }
        }

        const tablaApoderados = document.querySelector('#contenedor-tabla-apoderados table');
        if (tablaApoderados) {
            tablaApoderados.id = 'tabla-apoderados';
            tablaApoderados.dataset.datatable = '';
            tablaApoderados.dataset.pageOptions = '5,10,25,50';

            const filaVacia = tablaApoderados.querySelector('tbody tr td[colspan]')?.closest('tr');
            filaVacia?.remove();

            const encabezadoApoderados = tablaApoderados.querySelector('thead tr');
            if (encabezadoApoderados && !encabezadoApoderados.querySelector('[data-columna-numero]')) {
                const columnaNumero = document.createElement('th');
                columnaNumero.dataset.columnaNumero = 'true';
                columnaNumero.className = 'no-sort px-5 py-4';
                columnaNumero.textContent = 'N°';
                encabezadoApoderados.prepend(columnaNumero);
                tablaApoderados.querySelectorAll('tbody tr').forEach((fila, indice) => {
                    const numero = document.createElement('td');
                    numero.className = 'px-5 py-4 text-slate-500';
                    numero.textContent = String(indice + 1);
                    fila.prepend(numero);
                    fila.classList.add('transition', 'hover:bg-slate-50');
                });
            }
        }

        const tablas = Array.from(document.querySelectorAll('table[data-datatable]'));
        const cuerpoRoles = document.getElementById('lista-roles');
        if (cuerpoRoles) {
            const encabezadoRoles = cuerpoRoles.closest('table')?.querySelector('thead tr');
            if (encabezadoRoles && !encabezadoRoles.querySelector('[data-columna-numero]')) {
                const columnaNumero = document.createElement('th');
                columnaNumero.dataset.columnaNumero = 'true';
                columnaNumero.className = 'px-5 py-4';
                columnaNumero.textContent = 'N°';
                encabezadoRoles.prepend(columnaNumero);
                cuerpoRoles.querySelectorAll('tr').forEach((fila, indice) => {
                    const numero = document.createElement('td');
                    numero.className = 'px-5 py-4 text-slate-500';
                    numero.textContent = String(indice + 1);
                    fila.prepend(numero);
                    fila.classList.add('transition', 'hover:bg-slate-50');

                    const celdaEstado = fila.children[4];
                    const estado = celdaEstado?.textContent.trim() || '';
                    if (celdaEstado && estado) {
                        const activo = estado === 'ACTIVO';
                        celdaEstado.innerHTML = `<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${activo ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}">${estado}</span>`;
                    }
                });
            }
        }
        tablas.forEach((tabla) => {
            const filaVacia = tabla.querySelector('tbody tr td[colspan]')?.closest('tr');
            filaVacia?.remove();

            const ultimaColumna = tabla.querySelector('thead th:last-child');
            if (ultimaColumna && ['', 'acciones'].includes(ultimaColumna.textContent.trim().toLowerCase())) {
                ultimaColumna.classList.add('no-sort');
            }
            const opcionesTamano = (tabla.dataset.pageOptions || '10,25,50,100')
                .split(',')
                .map(Number)
                .filter(Number.isFinite);
            const orden = (tabla.dataset.order || '')
                .split(',')
                .map((parte) => parte.trim().split(':'))
                .filter((parte) => parte.length === 2 && Number.isFinite(Number(parte[0])))
                .map((parte) => [Number(parte[0]), parte[1] === 'desc' ? 'desc' : 'asc']);
            new DataTable(tabla, {
                pageLength: 10,
                lengthMenu: opcionesTamano,
                order: orden,
                autoWidth: false,
                layout: {
                    topStart: 'pageLength',
                    topEnd: 'search',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                language: {
                    search: '',
                    searchPlaceholder: 'Buscar...',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros)',
                    zeroRecords: 'No se encontraron registros',
                    emptyTable: 'No hay registros disponibles',
                    paginate: {
                        first: 'Primero',
                        last: 'Último',
                        next: 'Siguiente',
                        previous: 'Anterior'
                    }
                },
                columnDefs: [
                    { targets: 'no-sort', orderable: false, searchable: false }
                ]
            });
        });
    }
});
