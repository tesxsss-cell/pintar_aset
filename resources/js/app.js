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
