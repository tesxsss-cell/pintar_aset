document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const preview = document.querySelector(input.dataset.imagePreview);
            const file = input.files?.[0];
            if (!preview || !file) return;
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('#sidebar');
    const openButton = document.querySelector('[data-sidebar-toggle]');
    const closeButton = document.querySelector('[data-sidebar-close]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    const setSidebarOpen = (isOpen) => {
        if (!sidebar || !openButton || !backdrop) {
            return;
        }

        sidebar.classList.toggle('-translate-x-full', !isOpen);
        backdrop.classList.toggle('hidden', !isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
        openButton.setAttribute('aria-expanded', String(isOpen));
    };

    openButton?.addEventListener('click', () => setSidebarOpen(true));
    closeButton?.addEventListener('click', () => setSidebarOpen(false));
    backdrop?.addEventListener('click', () => setSidebarOpen(false));

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            setSidebarOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSidebarOpen(false);
        }
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-confirm]');

        if (button && !window.confirm(button.dataset.confirm)) {
            event.preventDefault();
        }
    });
});
