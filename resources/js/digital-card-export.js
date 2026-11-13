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

function progressOverlay(headingText = 'Menyiapkan Berkas ZIP') {
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
                    <h3 id="png-progress-heading" class="text-base font-bold text-slate-100">Menyiapkan Berkas ZIP</h3>
                    <p id="png-progress-text" class="text-xs text-slate-400 mt-1">Memulai pemrosesan...</p>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                    <div id="png-progress-bar" class="bg-amber-500 h-2.5 rounded-full transition-all duration-200 w-0"></div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    const heading = overlay.querySelector('#png-progress-heading');
    if (heading) {
        heading.innerText = headingText;
    }

    overlay.classList.remove('hidden');

    return {
        overlay,
        text: overlay.querySelector('#png-progress-text'),
        bar: overlay.querySelector('#png-progress-bar'),
    };
}

function canvasToJpegBytes(canvas, quality = 0.95) {
    return new Promise((resolve, reject) => {
        canvas.toBlob(async (blob) => {
            if (!blob) {
                reject(new Error('Gagal membuat JPEG untuk halaman PDF'));
                return;
            }
            resolve(new Uint8Array(await blob.arrayBuffer()));
        }, 'image/jpeg', quality);
    });
}

/**
 * Susun berkas PDF A4 Portrait (595.28 x 841.89 pt = 210 x 297 mm) dari daftar halaman JPEG
 */
function buildA4PdfBlob(pages) {
    const encoder = new TextEncoder();
    const chunks = [];
    const offsets = [0];
    let currentOffset = 0;

    const pushBytes = (bytes) => {
        chunks.push(bytes);
        currentOffset += bytes.byteLength;
    };
    const pushStr = (str) => pushBytes(encoder.encode(str));

    pushStr('%PDF-1.4\n%\xE2\xE3\xCF\xD3\n');

    const pageCount = pages.length;
    const pageObjIds = pages.map((_, idx) => 3 + idx * 3);

    // 1 0 obj: Catalog
    offsets[1] = currentOffset;
    pushStr('1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n');

    // 2 0 obj: Pages
    offsets[2] = currentOffset;
    pushStr(`2 0 obj\n<< /Type /Pages /Kids [${pageObjIds.map((id) => `${id} 0 R`).join(' ')}] /Count ${pageCount} >>\nendobj\n`);

    const contentStream = 'q 595.28 0 0 841.89 0 0 cm /Im0 Do Q';
    const contentLength = contentStream.length;

    for (let i = 0; i < pageCount; i++) {
        const { width, height, bytes } = pages[i];
        const pageObjId = 3 + i * 3;
        const contentObjId = pageObjId + 1;
        const imgObjId = pageObjId + 2;

        // Page Object (A4: 595.28 x 841.89 pt)
        offsets[pageObjId] = currentOffset;
        pushStr(
            `${pageObjId} 0 obj\n` +
            `<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources << /XObject << /Im0 ${imgObjId} 0 R >> >> /Contents ${contentObjId} 0 R >>\n` +
            `endobj\n`
        );

        // Content Stream Object
        offsets[contentObjId] = currentOffset;
        pushStr(
            `${contentObjId} 0 obj\n` +
            `<< /Length ${contentLength} >>\nstream\n${contentStream}\nendstream\n` +
            `endobj\n`
        );

        // Image XObject (/DCTDecode JPEG)
        offsets[imgObjId] = currentOffset;
        pushStr(
            `${imgObjId} 0 obj\n` +
            `<< /Type /XObject /Subtype /Image /Width ${width} /Height ${height} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${bytes.byteLength} >>\nstream\n`
        );
        pushBytes(bytes);
        pushStr('\nendstream\nendobj\n');
    }

    const totalObjects = 2 + pageCount * 3;
    const xrefOffset = currentOffset;

    pushStr(`xref\n0 ${totalObjects + 1}\n0000000000 65535 f \n`);
    for (let objId = 1; objId <= totalObjects; objId++) {
        pushStr(`${String(offsets[objId]).padStart(10, '0')} 00000 n \n`);
    }

    pushStr(
        `trailer\n<< /Size ${totalObjects + 1} /Root 1 0 R >>\n` +
        `startxref\n${xrefOffset}\n%%EOF`
    );

    return new Blob(chunks, { type: 'application/pdf' });
}

/**
 * Unduh seluruh lembar A4 (atau satu lembar A4 tertentu) sebagai berkas .PDF siap cetak
 */
export async function saveSheetsAsPdf(pdfFilename = null, sheetElement = null) {
    const sheets = sheetElement
        ? [sheetElement]
        : [...document.querySelectorAll('[data-a4-sheet]')];

    if (sheets.length === 0) {
        window.print();
        return;
    }

    const resolvedName = pdfFilename
        || document.querySelector('[data-pdf-filename]')?.getAttribute('data-pdf-filename')
        || 'KARTU_ID_A4';

    const { overlay, text, bar } = progressOverlay('Menyiapkan Berkas PDF (A4)');

    try {
        await waitForAssets();
        const pages = [];

        for (let i = 0; i < sheets.length; i++) {
            text.innerText = `Merender Lembar A4 ${i + 1} dari ${sheets.length}...`;
            bar.style.width = `${Math.round((i / sheets.length) * 90)}%`;

            const canvas = await window.html2canvas(sheets[i], {
                scale: 3,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: -window.scrollY,
            });

            const bytes = await canvasToJpegBytes(canvas, 0.95);
            pages.push({ width: canvas.width, height: canvas.height, bytes });
        }

        text.innerText = 'Menyusun dokumen PDF (A4)...';
        bar.style.width = '96%';

        const pdfBlob = buildA4PdfBlob(pages);

        bar.style.width = '100%';
        download(pdfBlob, `${safeName(resolvedName, 'KARTU_ID_A4')}.pdf`);
    } catch (err) {
        console.error('Gagal mengekspor lembar A4 ke PDF:', err);
        alert('Terjadi kesalahan saat memproses berkas .PDF (A4).');
    } finally {
        setTimeout(() => overlay.classList.add('hidden'), 400);
    }
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
    window.saveSheetsAsPdf = saveSheetsAsPdf;
}
