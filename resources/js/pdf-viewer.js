/**
 * Dynamic loader helper for PDF.js viewer.
 * Only loads PDF.js module when the user actually opens a PDF document,
 * keeping the main app bundle lightweight.
 */
let pdfViewerClassPromise = null;

// Keep active viewers outside Alpine proxies to avoid JavaScript Private Member access errors
const viewerInstances = new Map();

async function getPdfViewerClass() {
    if (!pdfViewerClassPromise) {
        pdfViewerClassPromise = (async () => {
            const [pdfjsLib, { default: pdfWorker }] = await Promise.all([
                import('pdfjs-dist/build/pdf'),
                import('pdfjs-dist/build/pdf.worker.min.js?url')
            ]);

            pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;

            return class PdfViewer {
                constructor(canvasContainer) {
                    this.container = canvasContainer;
                    this.pdfDoc = null;
                    this.pageNum = 1;
                    this.pageRendering = false;
                    this.pageNumPending = null;
                    this.currentRenderTask = null;
                    this.scale = 1.0;
                    this.loadingTask = null;
                }

                async load(url, onLoaded = null) {
                    this.destroy();
                    try {
                        this.loadingTask = pdfjsLib.getDocument(url);
                        this.pdfDoc = await this.loadingTask.promise;
                        this.pageNum = 1;
                        this.scale = 1.0;
                        if (onLoaded) {
                            onLoaded({
                                numPages: this.pdfDoc.numPages,
                                currentPage: this.pageNum
                            });
                        }
                        await this.renderPage(this.pageNum);
                    } catch (error) {
                        console.error('Failed to load PDF document:', error);
                        throw error;
                    }
                }

                async renderPage(num) {
                    if (!this.pdfDoc) return;

                    // If a render is already in progress, cancel it cleanly
                    if (this.currentRenderTask) {
                        try {
                            this.currentRenderTask.cancel();
                        } catch (_) {}
                        this.currentRenderTask = null;
                    }

                    this.pageRendering = true;
                    try {
                        const page = await this.pdfDoc.getPage(num);

                        // Find the scrollable viewport (parent with overflow-auto or modal container)
                        const scrollContainer = this.container.closest('.overflow-auto') || this.container.parentElement || this.container;
                        const availableWidth = Math.max(280, (scrollContainer.clientWidth || window.innerWidth) - 32);
                        const unscaledViewport = page.getViewport({ scale: 1 });
                        const autoFitScale = Math.max(0.1, availableWidth / unscaledViewport.width);
                        const finalScale = +(autoFitScale * this.scale).toFixed(3);

                        const viewport = page.getViewport({ scale: finalScale });

                        let canvas = this.container.querySelector('canvas');
                        if (!canvas) {
                            canvas = document.createElement('canvas');
                            canvas.className = 'm-auto rounded-lg shadow-2xl block select-none';
                            this.container.appendChild(canvas);
                        }

                        // Remove any CSS max-width constraint that stops zoom expansion
                        canvas.style.maxWidth = 'none';
                        canvas.style.width = `${Math.round(viewport.width)}px`;
                        canvas.style.height = `${Math.round(viewport.height)}px`;

                        const ctx = canvas.getContext('2d');
                        canvas.height = Math.round(viewport.height);
                        canvas.width = Math.round(viewport.width);

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };

                        this.currentRenderTask = page.render(renderContext);
                        await this.currentRenderTask.promise;
                        this.currentRenderTask = null;
                        this.pageRendering = false;

                        if (this.pageNumPending !== null) {
                            const pending = this.pageNumPending;
                            this.pageNumPending = null;
                            await this.renderPage(pending);
                        }
                    } catch (error) {
                        this.pageRendering = false;
                        if (error?.name === 'RenderingCancelledException') {
                            // Ignored: cancelled in favor of newer zoom/page render
                            return;
                        }
                        console.error('Error rendering PDF page:', error);
                    }
                }

                queueRenderPage(num) {
                    if (this.pageRendering) {
                        this.pageNumPending = num;
                        if (this.currentRenderTask) {
                            try {
                                this.currentRenderTask.cancel();
                            } catch (_) {}
                        }
                    } else {
                        this.renderPage(num);
                    }
                }

                prevPage() {
                    if (this.pageNum <= 1) return this.pageNum;
                    this.pageNum--;
                    this.queueRenderPage(this.pageNum);
                    return this.pageNum;
                }

                nextPage() {
                    if (!this.pdfDoc || this.pageNum >= this.pdfDoc.numPages) return this.pageNum;
                    this.pageNum++;
                    this.queueRenderPage(this.pageNum);
                    return this.pageNum;
                }

                zoom(delta) {
                    this.scale = Math.min(3.5, Math.max(0.3, +(this.scale + delta).toFixed(2)));
                    this.queueRenderPage(this.pageNum);
                    return this.scale;
                }

                resetZoom() {
                    this.scale = 1.0;
                    this.queueRenderPage(this.pageNum);
                    return this.scale;
                }

                destroy() {
                    if (this.currentRenderTask) {
                        try {
                            this.currentRenderTask.cancel();
                        } catch (_) {}
                        this.currentRenderTask = null;
                    }
                    if (this.loadingTask) {
                        this.loadingTask.destroy();
                        this.loadingTask = null;
                    }
                    this.pdfDoc = null;
                    this.container.innerHTML = '';
                    this.scale = 1.0;
                }
            };
        })();
    }
    return pdfViewerClassPromise;
}

window.pdfViewerManager = {
    async getOrCreate(id, container) {
        if (!viewerInstances.has(id)) {
            const PdfViewer = await getPdfViewerClass();
            viewerInstances.set(id, new PdfViewer(container));
        }
        return viewerInstances.get(id);
    },
    get(id) {
        return viewerInstances.get(id);
    },
    destroy(id) {
        const viewer = viewerInstances.get(id);
        if (viewer) {
            viewer.destroy();
            viewerInstances.delete(id);
        }
    }
};
