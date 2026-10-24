{{-- Lingkup Alpine pratinjau berkas (zoom foto / PDF) bersama halaman detail peserta dan bindamping --}}
<div class="flex h-full w-full flex-1 flex-col gap-6"
     x-data="{
         // State Document Preview Modal
         docModalOpen: false,
         docModalSrc: '',
         docModalTitle: '',
         docModalType: '', // 'image' or 'pdf' or 'other'
         imageZoomScale: 1,

         openDocModal(src, title, ext) {
             this.docModalSrc = src;
             this.docModalTitle = title || 'Pratinjau Berkas';
             const extension = (ext || '').toLowerCase();
             if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(extension)) {
                 this.docModalType = 'image';
             } else if (extension === 'pdf') {
                 this.docModalType = 'pdf';
             } else {
                 this.docModalType = 'other';
             }
             this.imageZoomScale = 1;
             this.docModalOpen = true;
         },
         closeDocModal() {
             this.docModalOpen = false;
             this.docModalSrc = '';
             this.imageZoomScale = 1;
         },
         zoomIn() {
             if (this.imageZoomScale < 3.5) {
                 this.imageZoomScale = +(this.imageZoomScale + 0.25).toFixed(2);
             }
         },
         zoomOut() {
             if (this.imageZoomScale > 0.5) {
                 this.imageZoomScale = +(this.imageZoomScale - 0.25).toFixed(2);
             }
         },
         resetZoom() {
             this.imageZoomScale = 1;
         },
         toggleZoom() {
             this.imageZoomScale = this.imageZoomScale === 1 ? 2 : 1;
         }
     }"
     @keydown.escape.window="if (docModalOpen) closeDocModal()">
    {{ $slot }}
</div>
