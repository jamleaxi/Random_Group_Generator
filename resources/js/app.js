const toastStyles = {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    error: 'border-red-200 bg-red-50 text-red-800',
    warning: 'border-amber-300 bg-amber-50 text-amber-900',
};

function toastContainer() {
    let container = document.getElementById('toast-container');

    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2 px-4 sm:px-0';
        document.body.appendChild(container);
    }

    return container;
}

function showToast(message, type = 'success', duration = 5000) {
    const container = toastContainer();
    const toast = document.createElement('div');

    toast.className = `pointer-events-auto cursor-pointer rounded-md border px-4 py-3 text-sm shadow-lg transition-all duration-300 opacity-0 translate-y-2 ${toastStyles[type] ?? toastStyles.success}`;
    toast.textContent = message;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', 'translate-y-2');
    });

    const remove = () => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    };

    toast.addEventListener('click', remove);
    setTimeout(remove, duration);
}

function confirmToast(message) {
    return new Promise((resolve) => {
        const container = toastContainer();
        const toast = document.createElement('div');

        toast.className = 'pointer-events-auto rounded-md border border-amber-300 bg-white px-4 py-3 text-sm shadow-lg opacity-0 translate-y-2 transition-all duration-300';
        toast.innerHTML = `
            <p class="mb-3 text-gray-800">${message}</p>
            <div class="flex justify-end gap-2">
                <button type="button" data-action="cancel" class="rounded-md px-3 py-1 text-sm font-medium text-gray-600 hover:bg-gray-100">Cancel</button>
                <button type="button" data-action="confirm" class="rounded-md bg-red-600 px-3 py-1 text-sm font-medium text-white hover:bg-red-700">Delete</button>
            </div>
        `;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
        });

        toast.addEventListener('click', (event) => {
            const action = event.target.dataset.action;

            if (!action) {
                return;
            }

            toast.remove();
            resolve(action === 'confirm');
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const flash = window.flashData;

    if (flash) {
        if (flash.status) {
            showToast(flash.status, 'success');
        }

        if (flash.error) {
            showToast(flash.error, 'error');
        }

        if (flash.duplicates && flash.duplicates.length) {
            showToast(`Already in this batch, skipped: ${flash.duplicates.join(', ')}`, 'warning', 7000);
        }
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();

            confirmToast(form.dataset.confirm).then((confirmed) => {
                if (confirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });
    });
});
