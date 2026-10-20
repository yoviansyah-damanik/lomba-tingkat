/**
 * Modul Utilitas Ekspor Kartu Digital ke Format .PNG & .ZIP (Kumulatif)
 * Mendukung penyimpanan kartu tunggal, kumulatif dalam arsip ZIP, maupun lembar A4 utuh.
 */

export async function saveCardAsPng(filename = 'KARTU_DIGITAL') {
    const card = document.querySelector('[data-digital-card]') || document.querySelector('.w-\\[79mm\\]');
    if (!card) {
        alert('Elemen kartu digital tidak ditemukan.');
        return;
    }

    const btn = document.getElementById('btn-save-png');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Menyiapkan PNG...';
    }

    try {
        const canvas = await window.html2canvas(card, {
            scale: 3, // Skala 3x menghasilkan resolusi tajam 300+ DPI
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
        });

        const dataUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = `${filename}.png`;
        link.href = dataUrl;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (err) {
        console.error('Gagal mengekspor kartu ke PNG:', err);
        alert('Terjadi kesalahan saat memproses gambar .PNG.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
}

export async function saveCumulativeCardsAsZip(cardSelector = '[data-digital-card]', zipFilename = 'KARTU_DIGITAL_KUMULATIF') {
    const cards = document.querySelectorAll(cardSelector);
    if (!cards || cards.length === 0) {
        alert('Tidak ada kartu digital yang ditemukan untuk diekspor.');
        return;
    }

    if (!window.JSZip) {
        alert('Modul kompresi ZIP belum siap. Silakan coba sesaat lagi.');
        return;
    }

    const zip = new window.JSZip();
    const folder = zip.folder(zipFilename);

    // Modal Progress Penyiapan Berkas
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
                    <h3 class="text-base font-bold text-slate-100">Menyimpan Kartu Kumulatif</h3>
                    <p id="png-progress-text" class="text-xs text-slate-400 mt-1">Memulai pemrosesan...</p>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                    <div id="png-progress-bar" class="bg-amber-500 h-2.5 rounded-full transition-all duration-200" style="width: 0%"></div>
                </div>
                <p class="text-[11px] text-slate-500 italic">Format resolusi tinggi .PNG untuk tiap peserta</p>
            </div>
        `;
        document.body.appendChild(overlay);
    } else {
        overlay.classList.remove('hidden');
    }

    const progressText = document.getElementById('png-progress-text');
    const progressBar = document.getElementById('png-progress-bar');

    try {
        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const name = card.getAttribute('data-card-name') || `KARTU_${i + 1}`;
            const idNumber = card.getAttribute('data-card-id') || '';
            const safeName = `${name}${idNumber ? '_' + idNumber : ''}`
                .trim()
                .replace(/[^a-zA-Z0-9_-]/g, '_')
                .replace(/_+/g, '_')
                .substring(0, 50);

            if (progressText) {
                progressText.innerText = `Memproses kartu ${i + 1} dari ${cards.length} (${name})...`;
            }
            if (progressBar) {
                progressBar.style.width = `${Math.round(((i + 1) / cards.length) * 90)}%`;
            }

            const canvas = await window.html2canvas(card, {
                scale: 3,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
            });

            const dataUrl = canvas.toDataURL('image/png');
            const base64Data = dataUrl.split(',')[1];
            folder.file(`${String(i + 1).padStart(2, '0')}_${safeName}.png`, base64Data, { base64: true });
        }

        if (progressText) {
            progressText.innerText = 'Mengompresi seluruh berkas ke ZIP...';
        }
        if (progressBar) {
            progressBar.style.width = '96%';
        }

        const zipBlob = await zip.generateAsync({
            type: 'blob',
            compression: 'DEFLATE',
            compressionOptions: { level: 6 }
        });

        if (progressBar) {
            progressBar.style.width = '100%';
        }

        const link = document.createElement('a');
        link.href = URL.createObjectURL(zipBlob);
        link.download = `${zipFilename}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    } catch (err) {
        console.error('Gagal mengekspor kartu kumulatif ke PNG/ZIP:', err);
        alert('Terjadi kesalahan saat memproses penyimpanan kartu kumulatif.');
    } finally {
        if (overlay) {
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 400);
        }
    }
}

export async function saveSheetAsPng(sheetIndex = 0, filename = 'LEMBAR_A4_KARTU_DIGITAL') {
    const sheets = document.querySelectorAll('[data-sheet-page]');
    const target = sheets[sheetIndex] || sheets[0];
    if (!target) {
        alert('Lembar cetak tidak ditemukan.');
        return;
    }

    try {
        const canvas = await window.html2canvas(target, {
            scale: 2.5,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
        });

        const dataUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = `${filename}.png`;
        link.href = dataUrl;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (err) {
        console.error('Gagal mengekspor lembar ke PNG:', err);
        alert('Terjadi kesalahan saat memproses lembar .PNG.');
    }
}

// Daftarkan fungsi ke window untuk akses langsung dari inline onclick Blade
if (typeof window !== 'undefined') {
    window.saveCardAsPng = saveCardAsPng;
    window.saveCumulativeCardsAsZip = saveCumulativeCardsAsZip;
    window.saveSheetAsPng = saveSheetAsPng;
}
