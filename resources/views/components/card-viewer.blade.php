{{-- Pratinjau ID card layar penuh (<dialog> modal), dua mode:
     1. iframe  : setiap tautan ke halaman kartu digital (/digital-cards/... dan /admin/scout-groups/.../id-cards) dibuka di modal, bukan tab baru.
                  Tautan verifikasi QR publik (/verify/) tidak diubah.
     2. clone   : kartu yang sudah tampil inline di halaman (mis. Kartu Digital saya) diperbesar lewat event window
                  "open-card-preview" ({ target: '<selector kartu>', filename, title }). --}}
<div x-data="{
        mode: 'iframe',
        url: '',
        title: 'Pratinjau ID Card',
        target: '',
        filename: 'KARTU_DIGITAL',
        dialog() {
            return document.getElementById('card-viewer-dialog');
        },
        open() {
            const dialog = this.dialog();
            if (dialog && ! dialog.open) dialog.showModal();
        },
        intercept(event) {
            const link = event.target.closest('a[href*=\'/digital-cards/\'], a[href*=\'/id-cards\']');
            if (! link || link.href.includes('/digital-cards/verify/') || event.ctrlKey || event.metaKey || event.shiftKey) return;

            event.preventDefault();
            event.stopPropagation();
            this.mode = 'iframe';
            this.url = link.href;
            this.title = link.getAttribute('title') || 'Pratinjau ID Card';
            this.open();
        },
        preview(detail) {
            const source = document.querySelector(detail.target);
            if (! source) return;

            this.mode = 'clone';
            this.target = detail.target;
            this.filename = detail.filename || 'KARTU_DIGITAL';
            this.title = detail.title || 'Pratinjau ID Card';
            this.open();

            this.$nextTick(() => {
                const stage = this.$refs.stage;
                stage.replaceChildren(source.cloneNode(true));
                const card = stage.firstElementChild;
                const available = stage.clientHeight - 32;
                // Kartu B2 diperbesar mengikuti tinggi layar, dibatasi agar tetap tajam
                const scale = Math.min(2.6, Math.max(1, available / card.offsetHeight));
                card.style.zoom = scale;
            });
        },
        download() {
            window.saveCardAsPng(this.filename, document.querySelector(this.target));
        },
        close() {
            const dialog = this.dialog();
            if (dialog && dialog.open) dialog.close();
        },
        closed() {
            this.url = '';
            this.$refs.stage?.replaceChildren();
        },
    }"
    x-on:click.document.capture="intercept($event)"
    x-on:open-card-preview.window="preview($event.detail)">
    <template x-teleport="body">
        <dialog id="card-viewer-dialog"
            x-on:close="closed()"
            class="m-0 h-dvh max-h-none w-full max-w-none overflow-hidden bg-zinc-950 p-0 text-white backdrop:bg-black/80"
            aria-label="Pratinjau ID Card">
            <div class="flex h-full flex-col">
                <div class="relative flex shrink-0 items-center justify-between gap-3 bg-gradient-to-r from-zinc-900 via-slate-900 to-zinc-900 px-5 py-3 sm:px-8">
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-amber-400/60 to-transparent"></div>
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-400/15 ring-1 ring-amber-400/30">
                            <flux:icon name="qr-code" class="size-5 text-amber-400" />
                        </div>
                        <div class="min-w-0 leading-tight">
                            <span class="block text-[10px] font-bold uppercase tracking-[0.18em] text-amber-400">Lomba Tingkat III</span>
                            <span class="block truncate text-sm font-bold text-zinc-50" x-text="title"></span>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <template x-if="mode === 'clone'">
                            <flux:button size="sm" variant="primary" icon="arrow-down-tray" x-on:click="download()">
                                Unduh .PNG
                            </flux:button>
                        </template>
                        <template x-if="mode === 'iframe'">
                            <flux:button size="sm" icon="arrow-top-right-on-square" x-bind:href="url" target="_blank" rel="noopener">
                                Buka Tab Baru
                            </flux:button>
                        </template>
                        <flux:button size="sm" variant="subtle" icon="x-mark" class="hover:!bg-red-500/15 hover:!text-red-400" x-on:click="close()">
                            Tutup
                        </flux:button>
                    </div>
                </div>

                <template x-if="mode === 'iframe' && url">
                    <iframe :src="url" title="Pratinjau ID Card" class="min-h-0 w-full flex-1 border-0 bg-[#0f172a]"></iframe>
                </template>

                <div x-ref="stage" x-show="mode === 'clone'" class="flex min-h-0 flex-1 items-center justify-center overflow-auto bg-[#0f172a] p-4" x-on:click.self="close()"></div>
            </div>
        </dialog>
    </template>
</div>
