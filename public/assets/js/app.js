document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.sidebar-link').forEach((enlace) => {
        if (!enlace.title) enlace.title = enlace.textContent.trim();
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js').catch(() => { });
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
        'planilla-regla-guardar': { titulo: '¿Deseas guardar esta regla?', texto: 'Se aplicará a los períodos comprendidos en su vigencia.', boton: 'Sí, guardar', color: '#7c3aed' },
        'planilla-regla-desactivar': { titulo: '¿Deseas desactivar esta regla?', texto: 'No se aplicará en nuevos cálculos mientras permanezca inactiva.', boton: 'Sí, desactivar', color: '#e11d48' },
        'planilla-regla-activar': { titulo: '¿Deseas activar esta regla?', texto: 'Volverá a incluirse en las vistas previas y cálculos vigentes.', boton: 'Sí, activar', color: '#059669' },
        'planilla-calcular': { titulo: '¿Deseas generar el borrador?', texto: 'Se guardará el cálculo automático del período para su revisión.', boton: 'Sí, generar', color: '#2563eb' },
        'planilla-ajuste-guardar': { titulo: '¿Deseas agregar este ajuste?', texto: 'El importe modificará el neto del trabajador y quedará registrado.', boton: 'Sí, agregar', color: '#7c3aed' },
        'planilla-ajuste-anular': { titulo: '¿Deseas anular este ajuste?', texto: 'El importe se revertirá conservando el historial.', boton: 'Sí, anular', color: '#e11d48' },
        'planilla-aprobar': { titulo: '¿Deseas aprobar la revisión?', texto: 'La planilla quedará protegida y ya no admitirá ajustes ni recálculos.', boton: 'Sí, aprobar', color: '#059669' },
        'planilla-cerrar': { titulo: '¿Deseas cerrar el cálculo?', texto: 'Los importes quedarán protegidos y se habilitará el registro de pagos. Cerrar el cálculo no significa que todos fueron pagados.', boton: 'Sí, cerrar cálculo', color: '#1e293b' },
        'planilla-pago-registrar': { titulo: '¿Deseas registrar este pago?', texto: 'El importe se aplicará al saldo del trabajador.', boton: 'Sí, registrar', color: '#059669' },
        'planilla-pago-anular': { titulo: '¿Deseas anular este pago?', texto: 'El saldo del trabajador se restaurará y la anulación quedará registrada.', boton: 'Sí, anular', color: '#e11d48' },
        'calendario-guardar': { titulo: '¿Deseas registrar esta fecha?', texto: 'Se excluirá de los días exigibles mientras permanezca activa.', boton: 'Sí, registrar', color: '#2563eb' },
        'calendario-editar': { titulo: '¿Deseas guardar los cambios?', texto: 'El cambio se aplicará a procesos abiertos y futuros.', boton: 'Sí, guardar cambios', color: '#f59e0b' },
        'calendario-desactivar': { titulo: '¿Deseas desactivar esta fecha?', texto: 'Volverá a considerarse como día laborable en nuevos cálculos.', boton: 'Sí, desactivar', color: '#e11d48' },
        'calendario-activar': { titulo: '¿Deseas activar esta fecha?', texto: 'Se excluirá de la asistencia y la planilla en nuevos cálculos.', boton: 'Sí, activar', color: '#059669' },
        'institucion-guardar': { titulo: '¿Deseas actualizar los datos institucionales?', texto: 'El nombre, contacto y logotipo se mostrarán en el sistema y en los nuevos documentos.', boton: 'Sí, actualizar', color: '#2563eb' },
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
        'asistencia-cerrar': { titulo: '¿Deseas cerrar la fecha seleccionada?', texto: 'Los alumnos activos sin registro serán marcados como falta. Después, las correcciones se realizarán mediante incidencias.', boton: 'Sí, cerrar fecha', color: '#d97706' },
        'asistencia-cerrar-personal': { titulo: '¿Deseas cerrar la fecha seleccionada?', texto: 'Se registrarán faltas de jornadas y sesiones sin marcación. No podrá cerrarse si la planilla del período está cerrada.', boton: 'Sí, cerrar personal', color: '#7c3aed' }
        , 'config-jornada-guardar': { titulo: '¿Deseas guardar esta jornada?', texto: 'Quedará disponible para las asignaciones y marcaciones.', boton: 'Sí, guardar', color: '#2563eb' }
        , 'config-jornada-editar': { titulo: '¿Deseas actualizar esta jornada?', texto: 'Los cambios se aplicarán a procesos abiertos y marcaciones posteriores.', boton: 'Sí, actualizar', color: '#f59e0b' }
        , 'config-jornada-desactivar': { titulo: '¿Deseas desactivar esta jornada?', texto: 'Dejará de estar disponible para nuevas marcaciones y asignaciones.', boton: 'Sí, desactivar', color: '#e11d48' }
        , 'config-jornada-activar': { titulo: '¿Deseas activar esta jornada?', texto: 'Volverá a estar disponible para su uso.', boton: 'Sí, activar', color: '#059669' }
        , 'config-dispositivo-guardar': { titulo: '¿Deseas autorizar este dispositivo?', texto: 'El equipo aparecerá como disponible en la pantalla de escaneo.', boton: 'Sí, autorizar', color: '#0891b2' }
        , 'config-dispositivo-editar': { titulo: '¿Deseas actualizar este dispositivo?', texto: 'Se guardarán el nombre, código permitido y punto de control.', boton: 'Sí, actualizar', color: '#f59e0b' }
        , 'config-dispositivo-desactivar': { titulo: '¿Deseas desactivar este dispositivo?', texto: 'Dejará de aparecer en la pantalla de escaneo, conservando sus marcaciones.', boton: 'Sí, desactivar', color: '#e11d48' }
        , 'config-dispositivo-activar': { titulo: '¿Deseas activar este dispositivo?', texto: 'Volverá a estar autorizado para registrar marcaciones.', boton: 'Sí, activar', color: '#059669' }
        , 'horario-guardar': { titulo: '¿Deseas guardar este horario?', texto: 'La jornada o sesión quedará disponible para el control de asistencia.', boton: 'Sí, guardar', color: '#2563eb' },
        'horario-editar': { titulo: '¿Deseas guardar los cambios?', texto: 'Se actualizará la configuración del horario.', boton: 'Sí, guardar cambios', color: '#d97706' },
        'horario-desactivar': { titulo: '¿Deseas desactivar esta jornada?', texto: 'Dejará de permitir nuevas marcaciones.', boton: 'Sí, desactivar', color: '#dc2626' },
        'horario-eliminar': { titulo: '¿Deseas desactivar esta sesión?', texto: 'La sesión quedará inactiva y no permitirá nuevas marcaciones.', boton: 'Sí, desactivar', color: '#dc2626' },
        'horario-activar': { titulo: '¿Deseas activar este horario?', texto: 'Volverá a estar disponible para registrar asistencia.', boton: 'Sí, activar', color: '#059669' }
        , 'transicion-laboral': { titulo: '¿Deseas guardar este vínculo laboral?', texto: 'Si la persona tiene un vínculo activo, se finalizará y se conservará su historial.', boton: 'Sí, guardar transición', color: '#7c3aed' }
        , 'vinculo-versionar': { titulo: '¿Deseas aplicar este cambio laboral?', texto: 'La versión anterior se conservará y los nuevos valores regirán desde la fecha indicada.', boton: 'Sí, crear versión', color: '#f59e0b' }
        , 'generar-cobros': { titulo: '¿Generar las cuentas de cobro?', texto: 'Se crearán solo las obligaciones que aún no existan para el período seleccionado.', boton: 'Sí, generar', color: '#2563eb' }
        , 'cobrar': { titulo: '¿Confirmar el pago?', texto: 'Se emitirá un recibo y se actualizarán los saldos seleccionados.', boton: 'Sí, registrar pago', color: '#059669' }
        , 'tarifa-desactivar': { titulo: '¿Desactivar esta tarifa?', texto: 'No se aplicará en nuevas generaciones de cobro.', boton: 'Sí, desactivar', color: '#d97706' }
        , 'tarifa-activar': { titulo: '¿Activar esta tarifa?', texto: 'Volverá a estar disponible para generar cobros.', boton: 'Sí, activar', color: '#059669' }
        , 'concepto-desactivar': { titulo: '¿Desactivar este concepto?', texto: 'No podrá seleccionarse en nuevas generaciones de cobro.', boton: 'Sí, desactivar', color: '#d97706' }
        , 'concepto-activar': { titulo: '¿Activar este concepto?', texto: 'Volverá a estar disponible en Cobros.', boton: 'Sí, activar', color: '#059669' }
        , 'pago-anular': { titulo: '¿Anular este recibo?', texto: 'Los saldos de las deudas se restaurarán y el recibo quedará en historial.', boton: 'Sí, anular recibo', color: '#dc2626' }
        , 'ajuste-cobro': { titulo: '¿Registrar este ajuste?', texto: 'Se actualizará el saldo y quedará registrado en el historial financiero.', boton: 'Sí, registrar ajuste', color: '#d97706' }
        , 'cierre-caja': { titulo: '¿Cerrar esta caja?', texto: 'El cierre quedará registrado y la caja de hoy no aceptará nuevos pagos.', boton: 'Sí, cerrar caja', color: '#7c3aed' }
        , 'caja-rectificar': { titulo: '¿Registrar esta revisión del cierre?', texto: 'El cierre no se reabrirá y los valores anteriores permanecerán en el historial.', boton: 'Sí, registrar revisión', color: '#7c3aed' }
        , 'apertura-caja': { titulo: '¿Abrir esta caja?', texto: 'Podrás registrar cobros hasta que se realice el cierre.', boton: 'Sí, abrir caja', color: '#059669' }
        , 'movimiento-financiero-guardar': { titulo: '¿Registrar este movimiento?', texto: 'Se incorporará al flujo financiero y quedará identificado el responsable.', boton: 'Sí, registrar', color: '#2563eb' }
        , 'movimiento-financiero-anular': { titulo: '¿Anular este movimiento?', texto: 'No se eliminará: permanecerá visible en el historial financiero.', boton: 'Sí, anular', color: '#dc2626' }
        , 'categoria-financiera-guardar': { titulo: '¿Crear esta categoría?', texto: 'Quedará disponible para nuevos ingresos o egresos.', boton: 'Sí, crear', color: '#2563eb' }
        , 'categoria-financiera-desactivar': { titulo: '¿Desactivar esta categoría?', texto: 'No se podrá usar en nuevos movimientos, pero conservará su historial.', boton: 'Sí, desactivar', color: '#d97706' }
        , 'categoria-financiera-activar': { titulo: '¿Activar esta categoría?', texto: 'Volverá a estar disponible para nuevos movimientos.', boton: 'Sí, activar', color: '#059669' }
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
    document.querySelectorAll('form[action*="planilla_regla_guardar"]').forEach((formulario) => { formulario.dataset.confirmarAccion = 'planilla-regla-guardar'; });
    document.querySelectorAll('form[action*="planilla_regla_estado"]').forEach((formulario) => { formulario.dataset.confirmarAccion = formulario.querySelector('[name="estado"]')?.value === 'ACTIVO' ? 'planilla-regla-activar' : 'planilla-regla-desactivar'; });
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
