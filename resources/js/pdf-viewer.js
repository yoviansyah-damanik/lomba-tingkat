/**
 * Dynamic loader helper for PDF.js viewer.
 * Only loads PDF.js module when the user actually opens a PDF document,
 * keeping the main app bundle lightweight.
 */
let pdfViewerClassPromise = null;

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
                    this.scale = 1.0;
                    this.loadingTask = null;
                }

                async load(url, onLoaded = null) {
                    this.destroy();
                    try {
                        this.loadingTask = pdfjsLib.getDocument(url);
                        this.pdfDoc = await this.loadingTask.promise;
                        this.pageNum = 1;
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
                    this.pageRendering = true;
                    const page = await this.pdfDoc.getPage(num);

                    const containerWidth = this.container.clientWidth || window.innerWidth;
                    const unscaledViewport = page.getViewport({ scale: 1 });
                    const autoFitScale = (containerWidth - 32) / unscaledViewport.width;
                    const finalScale = Math.max(0.6, autoFitScale * this.scale);

                    const viewport = page.getViewport({ scale: finalScale });

                    let canvas = this.container.querySelector('canvas');
                    if (!canvas) {
                        canvas = document.createElement('canvas');
                        canvas.className = 'mx-auto rounded-lg shadow-lg block max-w-full';
                        this.container.appendChild(canvas);
                    }

                    const ctx = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };

                    const renderTask = page.render(renderContext);
                    await renderTask.promise;
                    this.pageRendering = false;

                    if (this.pageNumPending !== null) {
                        this.renderPage(this.pageNumPending);
                        this.pageNumPending = null;
                    }
                }

                queueRenderPage(num) {
                    if (this.pageRendering) {
                        this.pageNumPending = num;
                    } else {
                        this.renderPage(num);
                    }
                }

                prevPage() {
                    if (this.pageNum <= 1) return;
                    this.pageNum--;
                    this.queueRenderPage(this.pageNum);
                }

                nextPage() {
                    if (!this.pdfDoc || this.pageNum >= this.pdfDoc.numPages) return;
                    this.pageNum++;
                    this.queueRenderPage(this.pageNum);
                }

                zoom(delta) {
                    this.scale = Math.min(3.0, Math.max(0.5, +(this.scale + delta).toFixed(2)));
                    this.queueRenderPage(this.pageNum);
                }

                resetZoom() {
                    this.scale = 1.0;
                    this.queueRenderPage(this.pageNum);
                }

                destroy() {
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

window.loadPdfViewer = async function(container) {
    const PdfViewer = await getPdfViewerClass();
    return new PdfViewer(container);
};
