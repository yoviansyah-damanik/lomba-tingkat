/**
 * Modul ekspor kartu digital ke .PNG (satuan) dan .ZIP (kumulatif, berisi PNG seluruh kartu).
 * Memakai html2canvas-pro agar warna modern Tailwind v4 (oklch) dapat dirender.
 */

const RENDER_SCALE = 4;

/**
 * Pastikan font dan seluruh gambar kartu sudah termuat sebelum ditangkap
 */
async function waitForAssets(root = document) {
    if (document.fonts?.ready) {
        await document.fonts.ready;
    }

    const pending = [...root.querySelectorAll('img')]
        .filter((img) => !img.complete)
        .map((img) => new Promise((resolve) => {
            img.addEventListener('load', resolve, { once: true });
            img.addEventListener('error', resolve, { once: true });
        }));

    await Promise.all(pending);
}

/**
 * Render elemen kartu menjadi canvas transparan pada sudut membulat
 */
async function renderCard(card) {
    return window.html2canvas(card, {
        scale: RENDER_SCALE,
        useCORS: true,
        backgroundColor: null,
        logging: false,
        scrollX: 0,
        scrollY: -window.scrollY,
    });
}

function canvasToBlob(canvas) {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('Gagal membuat PNG'))), 'image/png');
    });
}

function download(blob, filename) {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
}

function safeName(value, fallback = 'KARTU') {
    const cleaned = String(value || fallback)
        .trim()
        .replace(/[^a-zA-Z0-9_-]+/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '')
        .substring(0, 60);

    return cleaned || fallback;
}

/**
 * Simpan satu kartu sebagai berkas .PNG
 */
export async function saveCardAsPng(filename = 'KARTU_DIGITAL', cardElement = null) {
    const card = cardElement || document.querySelector('[data-digital-card]');
    if (!card) {
        alert('Elemen kartu digital tidak ditemukan.');
        return;
    }

    const trigger = document.getElementById('btn-save-png');
    trigger?.setAttribute('disabled', 'disabled');

    try {
        await waitForAssets(card);
        const blob = await canvasToBlob(await renderCard(card));
        download(blob, `${safeName(filename)}.png`);
    } catch (err) {
        console.error('Gagal mengekspor kartu ke PNG:', err);
        alert('Terjadi kesalahan saat memproses gambar .PNG.');
    } finally {
        trigger?.removeAttribute('disabled');
    }
}

function progressOverlay() {
    let overlay = document.getElementById('png-export-progress-modal');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'png-export-progress-modal';
        overlay.className = 'fixed inset-0 bg-black/80 backdrop-blur-xs z-50 flex flex-col items-center justify-center p-4 text-white';
        overlay.innerHTML = `
            <div class="bg-slate-900 border border-slate-700 rounded-2xl p-6 max-w-sm w-full text-center shadow-2xl space-y-4">
                <div class="size-14 rounded-2xl bg-amber-500/20 text-amber-400 mx-auto flex items-center justify-center">
                    <svg class="size-7 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-100">Menyiapkan Berkas ZIP</h3>
                    <p id="png-progress-text" class="text-xs text-slate-400 mt-1">Memulai pemrosesan...</p>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                    <div id="png-progress-bar" class="bg-amber-500 h-2.5 rounded-full transition-all duration-200 w-0"></div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    overlay.classList.remove('hidden');

    return {
        overlay,
        text: overlay.querySelector('#png-progress-text'),
        bar: overlay.querySelector('#png-progress-bar'),
    };
}

/**
 * Simpan seluruh kartu pada halaman sebagai .ZIP berisi satu .PNG per kartu.
 * Atribut data-card-group pada kartu dipakai sebagai nama subfolder di dalam ZIP.
 */
export async function saveCardsAsZip(zipFilename = 'KARTU_DIGITAL', cardSelector = '[data-digital-card]') {
    const cards = [...document.querySelectorAll(cardSelector)];
    if (cards.length === 0) {
        alert('Tidak ada kartu digital yang ditemukan untuk diekspor.');
        return;
    }

    if (!window.JSZip) {
        alert('Modul kompresi ZIP belum siap. Silakan coba sesaat lagi.');
        return;
    }

    const zip = new window.JSZip();
    const root = zip.folder(safeName(zipFilename, 'KARTU_DIGITAL'));
    const { overlay, text, bar } = progressOverlay();
    const counters = {};

    try {
        await waitForAssets();

        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const name = card.getAttribute('data-card-name') || `KARTU_${i + 1}`;
            const idNumber = card.getAttribute('data-card-id');
            const group = card.getAttribute('data-card-group');
            const folder = group ? root.folder(safeName(group, 'KARTU')) : root;

            counters[group || ''] = (counters[group || ''] || 0) + 1;
            const order = String(counters[group || '']).padStart(2, '0');

            text.innerText = `Memproses kartu ${i + 1} dari ${cards.length} (${name})...`;
            bar.style.width = `${Math.round((i / cards.length) * 92)}%`;

            const blob = await canvasToBlob(await renderCard(card));
            folder.file(`${order}_${safeName(`${name}${idNumber ? '_' + idNumber : ''}`)}.png`, blob);
        }

        text.innerText = 'Mengompresi seluruh berkas ke ZIP...';
        bar.style.width = '96%';

        const zipBlob = await zip.generateAsync({ type: 'blob', compression: 'DEFLATE', compressionOptions: { level: 6 } });

        bar.style.width = '100%';
        download(zipBlob, `${safeName(zipFilename, 'KARTU_DIGITAL')}.zip`);
    } catch (err) {
        console.error('Gagal mengekspor kartu ke ZIP:', err);
        alert('Terjadi kesalahan saat memproses penyimpanan kartu.');
    } finally {
        setTimeout(() => overlay.classList.add('hidden'), 400);
    }
}

if (typeof window !== 'undefined') {
    window.saveCardAsPng = saveCardAsPng;
    window.saveCardsAsZip = saveCardsAsZip;
}
