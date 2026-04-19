


document.addEventListener('DOMContentLoaded', () => {
    // Browser handles PDF rendering natively - no manipulation needed
    // Just ensure page loaded correctly
    const pdfContainer = document.getElementById('pdf-container');
    if (pdfContainer) {
        const iframe = pdfContainer.querySelector('.pdf-viewer');
        if (iframe) {
            iframe.onload = () => {
                console.log('PDF loaded successfully');
            };
            iframe.onerror = () => {
                console.error('PDF failed to load');
            };
        }
    }
});

