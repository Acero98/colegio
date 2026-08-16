document.addEventListener('DOMContentLoaded', () => {
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
            tablaAlumnos.dataset.lengthMenu = '10,25,50';

            const filaVacia = tablaAlumnos.querySelector('tbody tr td[colspan]')?.closest('tr');
            filaVacia?.remove();

            const encabezadoAlumnos = tablaAlumnos.querySelector('thead tr');
            if (encabezadoAlumnos && !encabezadoAlumnos.querySelector('[data-columna-numero]')) {
                const columnaNumero = document.createElement('th');
                columnaNumero.dataset.columnaNumero = 'true';
                columnaNumero.className = 'px-5 py-4';
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
        const cuerpoPersonas = document.getElementById('lista-personas');
        if (cuerpoPersonas) {
            const tablaPersonas = cuerpoPersonas.closest('table');
            if (tablaPersonas && !tablas.includes(tablaPersonas)) tablas.push(tablaPersonas);
            cuerpoPersonas.querySelectorAll('tr').forEach((fila) => { fila.style.display = ''; });
            document.getElementById('buscar-personas')?.closest('.border-b')?.remove();
            document.getElementById('resumen-personas')?.remove();
        }
        tablas.forEach((tabla) => {
            const ultimaColumna = tabla.querySelector('thead th:last-child');
            if (ultimaColumna && ['', 'acciones'].includes(ultimaColumna.textContent.trim().toLowerCase())) {
                ultimaColumna.classList.add('no-sort');
            }
            const opcionesTamano = (tabla.dataset.lengthMenu || '10,25,50,100')
                .split(',')
                .map(Number)
                .filter(Number.isFinite);
            new DataTable(tabla, {
                pageLength: 10,
                lengthMenu: opcionesTamano,
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
