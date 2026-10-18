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
            if (document.querySelector('#confirmation-dialog[open]')) {
                return;
            }
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

    const confirmationDialog = document.querySelector('#confirmation-dialog');
    const confirmationTitle = document.querySelector('#confirmation-title');
    const confirmationDescription = document.querySelector('#confirmation-description');
    const acceptButton = confirmationDialog?.querySelector('[data-confirm-accept]');
    const cancelButtons = confirmationDialog?.querySelectorAll('[data-confirm-cancel]');
    const approvedForms = new WeakSet();
    const submittingForms = new WeakSet();
    let pendingConfirmation = null;

    if (confirmationDialog && confirmationTitle && confirmationDescription && acceptButton) {
        const cancelConfirmation = () => {
            pendingConfirmation = null;
            confirmationDialog.close();
            document.documentElement.classList.remove('has-confirmation-modal');
        };

        cancelButtons.forEach((button) => button.addEventListener('click', cancelConfirmation));
        confirmationDialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            cancelConfirmation();
        });
        confirmationDialog.addEventListener('keydown', (event) => {
            if (event.key !== 'Tab') {
                return;
            }
            const buttons = [...confirmationDialog.querySelectorAll('button:not([disabled])')];
            const first = buttons[0];
            const last = buttons[buttons.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        });
        confirmationDialog.addEventListener('close', () => {
            if (!confirmationDialog.open) {
                document.documentElement.classList.remove('has-confirmation-modal');
                pendingConfirmation = null;
            }
        });
        confirmationDialog.addEventListener('click', (event) => {
            const rect = confirmationDialog.getBoundingClientRect();
            if (event.target === confirmationDialog && (
                event.clientX < rect.left || event.clientX > rect.right ||
                event.clientY < rect.top || event.clientY > rect.bottom
            )) {
                cancelConfirmation();
            }
        });

        // Event submit mencakup klik tombol maupun Enter dan baru berjalan
        // setelah validasi HTML (misalnya alasan penolakan wajib diisi) lolos.
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
                return;
            }
            const submitter = event.submitter;
            const config = submitter?.hasAttribute('data-confirm') ? submitter :
                (form.hasAttribute('data-confirm') ? form : null);
            if (!config) {
                return;
            }
            if (submittingForms.has(form)) {
                event.preventDefault();
                return;
            }
            if (approvedForms.has(form)) {
                approvedForms.delete(form);
                submittingForms.add(form);
                window.setTimeout(() => {
                    if (event.defaultPrevented) {
                        submittingForms.delete(form);
                        return;
                    }
                    form.setAttribute('aria-busy', 'true');
                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                        button.disabled = true;
                    });
                }, 0);
                return;
            }

            event.preventDefault();
            if (confirmationDialog.open) {
                return;
            }
            pendingConfirmation = { form, submitter };
            confirmationTitle.textContent = config.dataset.confirmTitle || 'Lanjutkan tindakan ini?';
            confirmationDescription.textContent = config.dataset.confirm || 'Pastikan data sudah benar sebelum melanjutkan.';
            acceptButton.textContent = config.dataset.confirmLabel || 'Ya, lanjutkan';
            confirmationDialog.dataset.tone = config.dataset.confirmTone === 'success' ? 'success' : 'danger';
            document.documentElement.classList.add('has-confirmation-modal');
            confirmationDialog.showModal();
            confirmationDialog.querySelector('.confirmation-button-cancel')?.focus();
        });

        acceptButton.addEventListener('click', () => {
            if (!pendingConfirmation) {
                return;
            }
            const { form, submitter } = pendingConfirmation;
            pendingConfirmation = null;
            confirmationDialog.close();
            document.documentElement.classList.remove('has-confirmation-modal');
            if (!form.isConnected || !form.reportValidity()) {
                return;
            }
            approvedForms.add(form);
            // requestSubmit tetap mempertahankan validasi, CSRF, _method,
            // dan nilai tombol submit; tidak menggunakan form.submit().
            try {
                if (submitter && submitter.form === form && !submitter.disabled) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            } finally {
                approvedForms.delete(form);
            }
        });

        window.addEventListener('pageshow', () => {
            document.querySelectorAll('form[aria-busy="true"]').forEach((form) => {
                submittingForms.delete(form);
                form.removeAttribute('aria-busy');
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
                    button.disabled = false;
                });
            });
        });
    }
});
