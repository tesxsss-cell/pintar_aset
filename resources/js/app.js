const formatFileSize = (bytes) => {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const compressImage = async (file) => {
    if (!file.type.startsWith('image/')) {
        return file;
    }

    const bitmap = await createImageBitmap(file);
    const maxDimension = 1600;
    const scale = Math.min(1, maxDimension / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(bitmap.width * scale));
    canvas.height = Math.max(1, Math.round(bitmap.height * scale));

    const context = canvas.getContext('2d');
    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close();

    const blob = await new Promise((resolve, reject) => {
        canvas.toBlob(
            (result) => result ? resolve(result) : reject(new Error('Gagal mengompres gambar.')),
            'image/webp',
            0.78,
        );
    });

    if (blob.size >= file.size) {
        return file;
    }

    const baseName = file.name.replace(/\.[^/.]+$/, '');

    return new File([blob], `${baseName}.webp`, {
        type: 'image/webp',
        lastModified: Date.now(),
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', async () => {
            const preview = document.querySelector(input.dataset.imagePreview);
            const status = input.closest('label')?.querySelector('[data-image-status]');
            const originalFile = input.files?.[0];

            if (!originalFile) {
                preview?.classList.add('hidden');
                return;
            }

            const maxSize = Number(input.dataset.maxSize || 0);

            if (maxSize && originalFile.size > maxSize) {
                input.value = '';
                preview?.classList.add('hidden');
                if (status) {
                    status.textContent = 'Ukuran foto melebihi batas maksimal 5 MB.';
                    status.classList.add('text-red-600');
                }
                return;
            }

            let selectedFile = originalFile;

            if (input.hasAttribute('data-compress-image') && 'createImageBitmap' in window) {
                try {
                    if (status) {
                        status.textContent = 'Mengompres foto…';
                        status.classList.remove('text-red-600');
                    }

                    selectedFile = await compressImage(originalFile);
                    const transfer = new DataTransfer();
                    transfer.items.add(selectedFile);
                    input.files = transfer.files;
                } catch {
                    selectedFile = originalFile;
                }
            }

            if (preview) {
                preview.src = URL.createObjectURL(selectedFile);
                preview.classList.remove('hidden');
            }

            if (status) {
                status.textContent = `Siap diunggah (${formatFileSize(selectedFile.size)}). Maksimal 5 MB dan akan dikompres kembali oleh sistem.`;
                status.classList.remove('text-red-600');
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const layout = document.querySelector('[data-admin-layout]');
    const sidebar = document.querySelector('#sidebar');
    const toggleButton = document.querySelector('[data-sidebar-toggle]');
    const closeButton = document.querySelector('[data-sidebar-close]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (layout && sidebar && toggleButton && backdrop) {
        // Harus sama dengan breakpoint lg Tailwind (64rem).
        const desktopMedia = window.matchMedia('(min-width: 64rem)');
        const storageKey = 'pintar-aset.admin.sidebar.desktop';
        let desktopOpen = true;
        let mobileOpen = false;
        let previousBodyOverflowHidden = false;
        let mobileScrollLocked = false;

        try {
            desktopOpen = localStorage.getItem(storageKey) !== 'closed';
        } catch {
            // Sidebar tetap berfungsi ketika penyimpanan browser diblokir.
        }

        const renderSidebar = () => {
            const isDesktop = desktopMedia.matches;
            const isOpen = isDesktop ? desktopOpen : mobileOpen;
            const lockScroll = !isDesktop && mobileOpen;

            layout.dataset.sidebarDesktop = desktopOpen ? 'open' : 'closed';
            layout.dataset.sidebarMobile = mobileOpen ? 'open' : 'closed';
            sidebar.inert = !isOpen;
            sidebar.setAttribute('aria-hidden', String(!isOpen));
            backdrop.classList.toggle('hidden', !lockScroll);
            toggleButton.setAttribute('aria-expanded', String(isOpen));
            toggleButton.setAttribute('aria-label', isOpen ? 'Tutup navigasi' : 'Buka navigasi');
            toggleButton.setAttribute('title', isOpen ? 'Tutup navigasi' : 'Buka navigasi');

            if (lockScroll && !mobileScrollLocked) {
                previousBodyOverflowHidden = document.body.classList.contains('overflow-hidden');
                document.body.classList.add('overflow-hidden');
                mobileScrollLocked = true;
            } else if (!lockScroll && mobileScrollLocked) {
                document.body.classList.toggle('overflow-hidden', previousBodyOverflowHidden);
                mobileScrollLocked = false;
            }
        };

        const setSidebarOpen = (isOpen) => {
            if (desktopMedia.matches) {
                desktopOpen = isOpen;
                try {
                    localStorage.setItem(storageKey, isOpen ? 'open' : 'closed');
                } catch {
                    // Preferensi hanya berlaku selama halaman ini dibuka.
                }
            } else {
                mobileOpen = isOpen;
            }

            // Jangan biarkan fokus berada pada elemen yang akan disembunyikan.
            if (!isOpen && sidebar.contains(document.activeElement)) {
                toggleButton.focus();
            }
            renderSidebar();

            if (isOpen && !desktopMedia.matches) {
                closeButton?.focus();
            }
        };

        toggleButton.addEventListener('click', () => {
            setSidebarOpen(!(desktopMedia.matches ? desktopOpen : mobileOpen));
        });
        closeButton?.addEventListener('click', () => setSidebarOpen(false));
        backdrop.addEventListener('click', () => setSidebarOpen(false));

        desktopMedia.addEventListener('change', () => {
            // Drawer mobile selalu direset, tanpa mengubah preferensi desktop.
            mobileOpen = false;
            if (sidebar.contains(document.activeElement) && !(desktopMedia.matches && desktopOpen)) {
                toggleButton.focus();
            }
            renderSidebar();
        });

        document.addEventListener('keydown', (event) => {
            const isOpen = desktopMedia.matches ? desktopOpen : mobileOpen;
            if (event.key === 'Escape' && isOpen) {
                setSidebarOpen(false);
                toggleButton.focus();
            }

            // Fokus keyboard tetap berada di drawer selama menu mobile terbuka.
            if (event.key === 'Tab' && !desktopMedia.matches && mobileOpen) {
                const focusable = [...sidebar.querySelectorAll(
                    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex="0"]',
                )].filter((element) => element.getClientRects().length > 0);
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last?.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first?.focus();
                }
            }
        });

        renderSidebar();
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-confirm]');

        if (button && !window.confirm(button.dataset.confirm)) {
            event.preventDefault();
        }
    });
});
