import qz from 'qz-tray';

// --------------------------------------------------------------------------
// QZ Tray Security & Certificate Signing Setup
// --------------------------------------------------------------------------
qz.security.setSignatureAlgorithm('SHA512');

qz.security.setCertificatePromise(function(resolve, reject) {
    fetch('/qz/certificate', { cache: 'no-store' })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Certificate endpoint returned HTTP ' + response.status);
            }
            return response.text();
        })
        .then(resolve)
        .catch(function(error) {
            console.error('[QZ Tray Error] Failed to load digital certificate:', error);
            reject(error);
        });
});

qz.security.setSignaturePromise(function(toSign) {
    return function(resolve, reject) {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';

        fetch('/qz/sign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/plain'
            },
            body: JSON.stringify({ request: toSign })
        })
        .then(function(response) {
            if (!response.ok) {
                return response.text().then(function(errText) {
                    throw new Error('Signing endpoint error (HTTP ' + response.status + '): ' + errText);
                });
            }
            return response.text();
        })
        .then(resolve)
        .catch(function(error) {
            console.error('[QZ Tray Error] Failed to generate RSA-SHA512 signature:', error);
            reject(error);
        });
    };
});

const QZTray = {
    async connect() {
        if (qz.websocket.isActive()) {
            return true;
        }

        try {
            await qz.websocket.connect();
            console.log('[QZ Tray] Connected securely');
            return true;
        } catch (error) {
            console.error('[QZ Tray] Connection failed:', error);
            return false;
        }
    },

    async disconnect() {
        if (qz.websocket.isActive()) {
            await qz.websocket.disconnect();
            console.log('[QZ Tray] Disconnected');
        }
    },

    isConnected() {
        return qz.websocket.isActive();
    },

    async getPrinters() {
        try {
            await this.connect();

            const printers = await qz.printers.find();

            console.log('[QZ Tray] Printers:', printers);

            return printers;
        } catch (error) {
            console.error('[QZ Tray] Printer detection failed:', error);
            return [];
        }
    },

    async getPrinter(preferredPrinter = null) {
        await this.connect();
        if (preferredPrinter) {
            return preferredPrinter;
        }
        try {
            const defaultPrinter = await qz.printers.getDefault();
            if (defaultPrinter) {
                console.log('[QZ Tray] Using default printer:', defaultPrinter);
                return defaultPrinter;
            }
        } catch (e) {
            console.warn('[QZ Tray] Could not fetch default printer, searching all printers...', e);
        }

        const printers = await qz.printers.find();
        if (printers && printers.length > 0) {
            console.log('[QZ Tray] Using first detected printer:', printers[0]);
            return printers[0];
        }
        throw new Error('No printers detected by QZ Tray.');
    },

    async printHTML(html, printerName = null, configOptions = {}) {
        try {
            const connected = await this.connect();
            if (!connected) {
                throw new Error('QZ Tray is not connected. Please ensure QZ Tray desktop app is running.');
            }

            const printer = await this.getPrinter(printerName);
            console.log('[QZ Tray] Sending print job to printer:', printer);

            const options = Object.assign({ margins: 0 }, configOptions);
            const config = qz.configs.create(printer, options);

            const data = [{
                type: 'pixel',
                format: 'html',
                flavor: 'plain',
                data: html
            }];

            await qz.print(config, data);
            console.log('[QZ Tray] Print job sent successfully');
            return { success: true, printer };
        } catch (error) {
            console.error('[QZ Tray] Printing failed:', error);
            throw error;
        }
    },
};

window.QZTray = QZTray;

export default QZTray;