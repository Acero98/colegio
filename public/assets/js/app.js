document.addEventListener('DOMContentLoaded', () => {
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
        const guardar = formulario.querySelector('button[type="submit"], button:not([type])');
        if (!guardar || yaTieneCancelar) return;

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
});
