/**
 * Dialog modal global pengganti alert() dan confirm() bawaan peramban.
 *
 * - window.appAlert(pesan, opsi)   : modal informasi dengan satu tombol (menggantikan alert)
 * - window.appConfirm(pesan, opsi) : modal konfirmasi, mengembalikan Promise<boolean>
 * - window.alert                   : dialihkan ke appAlert sehingga seluruh pemanggilan lama otomatis memakai modal
 * - wire:confirm / data-confirm    : klik atau submit elemen bertanda ini ditahan sampai pengguna menyetujui di modal
 */

const DESTRUCTIVE = /hapus|batalkan|lepas|peringatan|tidak dapat dibatalkan|ikut terhapus/i;

const ICONS = {
    danger: '<svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>',
    warning: '<svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>',
    info: '<svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>',
};

const TONES = {
    danger: { badge: 'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400', ok: 'bg-rose-600 hover:bg-rose-700 focus-visible:ring-rose-500' },
    warning: { badge: 'bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400', ok: 'bg-amber-600 hover:bg-amber-700 focus-visible:ring-amber-500' },
    info: { badge: 'bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400', ok: 'bg-zinc-900 hover:bg-zinc-800 focus-visible:ring-zinc-500 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white' },
};

const BUTTON = 'inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 cursor-pointer';

/** Antrean agar beberapa dialog berurutan tidak saling menimpa */
let queue = Promise.resolve();

function open({ message, title, tone, okText, cancelText }) {
    return new Promise((resolve) => {
        const style = TONES[tone];
        const dialog = document.createElement('dialog');
        dialog.className = 'm-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-zinc-200 bg-white p-0 text-zinc-900 shadow-2xl backdrop:bg-zinc-950/60 backdrop:backdrop-blur-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';

        const body = document.createElement('div');
        body.className = 'flex gap-4 p-6';
        body.innerHTML = `
            <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl ${style.badge}">${ICONS[tone]}</div>
            <div class="min-w-0 flex-1">
                <h3 data-role="title" class="text-base font-bold"></h3>
                <p data-role="message" class="mt-1.5 whitespace-pre-line break-words text-sm leading-relaxed text-zinc-600 dark:text-zinc-400"></p>
            </div>`;
        body.querySelector('[data-role="title"]').textContent = title;
        body.querySelector('[data-role="message"]').textContent = message;

        const footer = document.createElement('div');
        footer.className = 'flex flex-col-reverse gap-2 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60 sm:flex-row sm:justify-end';

        let cancel = null;
        if (cancelText) {
            cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.textContent = cancelText;
            cancel.className = `${BUTTON} border border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-100 focus-visible:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700`;
            footer.append(cancel);
        }

        const ok = document.createElement('button');
        ok.type = 'button';
        ok.textContent = okText;
        ok.className = `${BUTTON} text-white ${style.ok}`;
        footer.append(ok);

        dialog.append(body, footer);
        document.body.append(dialog);

        // Selesaikan sekali saja: tombol, klik latar, atau Esc (event close) tidak boleh menyelesaikan dua kali
        let settled = false;
        const finish = (value) => {
            if (settled) return;
            settled = true;
            if (dialog.open) dialog.close();
            dialog.remove();
            resolve(value);
        };

        ok.addEventListener('click', () => finish(true));
        cancel?.addEventListener('click', () => finish(false));
        // Klik di area gelap dan tombol Esc sama dengan membatalkan
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) finish(false);
        });
        dialog.addEventListener('close', () => finish(false));
        dialog.addEventListener('cancel', () => finish(false));

        dialog.showModal();
        // Fokus awal pada tombol aman: Batal untuk konfirmasi, OK untuk pemberitahuan
        (cancel ?? ok).focus();
    });
}

function enqueue(options) {
    const run = () => open(options);
    queue = queue.then(run, run);

    return queue;
}

window.appAlert = (message, options = {}) => {
    const text = String(message ?? '');
    const tone = options.tone ?? (/gagal|kesalahan|ditolak|tidak (?:dapat|ada|didukung)|belum siap/i.test(text) ? 'warning' : 'info');

    return enqueue({
        message: text,
        title: options.title ?? (tone === 'warning' ? 'Perhatian' : 'Informasi'),
        tone,
        okText: options.okText ?? 'Mengerti',
        cancelText: null,
    });
};

window.appConfirm = (message, options = {}) => {
    const text = String(message ?? '');
    const tone = options.tone ?? (DESTRUCTIVE.test(text) ? 'danger' : 'warning');

    return enqueue({
        message: text,
        title: options.title ?? (tone === 'danger' ? 'Konfirmasi Tindakan' : 'Konfirmasi'),
        tone,
        okText: options.okText ?? (tone === 'danger' ? 'Ya, Lanjutkan' : 'Ya, Lanjut'),
        cancelText: options.cancelText ?? 'Batal',
    });
};

// Seluruh alert() lama otomatis tampil sebagai modal
window.alert = (message) => {
    window.appAlert(message);
};

/**
 * Tahan klik/submit pada elemen ber-wire:confirm atau data-confirm, tampilkan modal,
 * lalu ulangi aksinya setelah disetujui. confirm() bawaan dinetralkan hanya selama pengulangan
 * agar wire:confirm milik Livewire tidak memunculkan dialog peramban lagi.
 */
function interceptConfirm(event) {
    const el = event.target.closest?.('[wire\\:confirm], [data-confirm]');

    if (!el || el.dataset.confirmApproved === '1' || el.disabled) return;

    const attribute = el.getAttribute('wire:confirm');
    const message = (attribute !== null ? attribute || 'Apakah Anda yakin?' : el.dataset.confirm).replaceAll('\\n', '\n');
    if (!message) return;

    event.preventDefault();
    event.stopImmediatePropagation();

    window.appConfirm(message, {
        title: el.dataset.confirmTitle,
        okText: el.dataset.confirmOk,
        tone: el.dataset.confirmTone,
    }).then((approved) => {
        if (!approved) return;

        const nativeConfirm = window.confirm;
        window.confirm = () => true;
        el.dataset.confirmApproved = '1';

        try {
            event.type === 'submit' && el.requestSubmit ? el.requestSubmit() : el.click();
        } finally {
            delete el.dataset.confirmApproved;
            window.confirm = nativeConfirm;
        }
    });
}

document.addEventListener('click', interceptConfirm, true);
document.addEventListener('submit', interceptConfirm, true);
