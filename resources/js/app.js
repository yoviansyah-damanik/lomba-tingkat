import html2canvas from 'html2canvas-pro';
import JSZip from 'jszip';
import './digital-card-export';

window.html2canvas = html2canvas;
window.JSZip = JSZip;

// Listener global untuk event 'alert' Livewire agar otomatis memicu Flux Toast component
window.addEventListener('alert', (event) => {
    const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
    if (!data) return;
    const type = (data.type === 'error' || data.type === 'danger') ? 'danger' : (data.type || 'info');
    const heading = data.title || (type === 'success' ? 'Berhasil' : (type === 'danger' ? 'Perhatian' : (type === 'warning' ? 'Peringatan' : 'Informasi')));
    const text = data.message || '';

    window.dispatchEvent(new CustomEvent('toast-show', {
        detail: {
            duration: 5000,
            slots: { text, heading },
            dataset: { variant: type }
        }
    }));
});
