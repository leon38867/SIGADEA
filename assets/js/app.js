document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('show'));
    }

    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            if (!confirm(element.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-role-select]').forEach((select) => {
        const target = document.querySelector(select.dataset.roleSelect);
        const update = () => {
            if (!target) return;
            target.classList.toggle('d-none', select.value.toLowerCase() === 'administrador');
        };
        select.addEventListener('change', update);
        update();
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.querySelector(button.dataset.passwordToggle);
        if (!input) return;
        button.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.textContent = showing ? 'Ver' : 'Ocultar';
            button.setAttribute('aria-label', showing ? 'Ver contrasena' : 'Ocultar contrasena');
        });
    });

    const previewModal = document.getElementById('documentPreviewModal');
    if (previewModal) {
        const frame = previewModal.querySelector('[data-document-frame]');
        const title = previewModal.querySelector('#documentPreviewTitle');
        const download = previewModal.querySelector('[data-document-download]');
        const open = previewModal.querySelector('[data-document-open]');
        const zoomLabel = previewModal.querySelector('.document-zoom-label');
        const modal = new bootstrap.Modal(previewModal);
        let zoom = 1;

        const setZoom = (value) => {
            zoom = Math.min(1.75, Math.max(.75, value));
            frame.style.transform = `scale(${zoom})`;
            frame.style.width = `${100 / zoom}%`;
            frame.style.height = `${100 / zoom}%`;
            zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
        };

        document.querySelectorAll('[data-document-preview]').forEach((button) => {
            button.addEventListener('click', () => {
                const url = button.dataset.documentUrl;
                title.textContent = button.dataset.documentTitle || 'Vista previa';
                frame.src = url;
                download.href = url;
                open.href = url;
                setZoom(1);
                modal.show();
            });
        });

        previewModal.querySelectorAll('[data-document-zoom]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.dataset.documentZoom === 'in') setZoom(zoom + .25);
                if (button.dataset.documentZoom === 'out') setZoom(zoom - .25);
                if (button.dataset.documentZoom === 'reset') setZoom(1);
            });
        });

        previewModal.addEventListener('hidden.bs.modal', () => {
            frame.removeAttribute('src');
        });
    }
});
