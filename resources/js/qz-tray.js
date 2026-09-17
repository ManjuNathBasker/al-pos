import qz from 'qz-tray';

// --------------------------------------------------------------------------
// QZ Tray Security & Certificate Signing Setup
// --------------------------------------------------------------------------
qz.security.setSignatureAlgorithm('SHA512');

let cachedCertificate = null;
let lastCertTime = 0;
let lastSignTime = 0;

qz.security.setCertificatePromise(function(resolve, reject) {
    if (cachedCertificate) {
        lastCertTime = 0;
        resolve(cachedCertificate);
        return;
    }
    const certStart = performance.now();
    fetch('/qz/certificate', { cache: 'no-store' })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Certificate endpoint returned HTTP ' + response.status);
            }
            return response.text();
        })
        .then(function(cert) {
            cachedCertificate = cert;
            lastCertTime = performance.now() - certStart;
            resolve(cert);
        })
        .catch(function(error) {
            console.error('[QZ Tray Error] Failed to load digital certificate:', error);
            reject(error);
        });
});

qz.security.setSignaturePromise(function(toSign) {
    return function(resolve, reject) {
        const signStart = performance.now();
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
        .then(function(signature) {
            lastSignTime = performance.now() - signStart;
            resolve(signature);
        })
        .catch(function(error) {
            console.error('[QZ Tray Error] Failed to generate RSA-SHA512 signature:', error);
            reject(error);
        });
    };
});

let cachedPrinter = null;
let cachedConfig = null;
let cachedConfigPrinter = null;
let cachedConfigOptionsStr = null;
let connectingPromise = null;

function getConfig(printer, options) {
    const optionsStr = JSON.stringify(options || {});
    if (cachedConfig && cachedConfigPrinter === printer && cachedConfigOptionsStr === optionsStr) {
        return cachedConfig;
    }
    const config = qz.configs.create(printer, options);
    cachedConfig = config;
    cachedConfigPrinter = printer;
    cachedConfigOptionsStr = optionsStr;
    return config;
}

const QZTray = {
    async connect() {
        if (qz.websocket.isActive()) {
            return { connected: true, duration: 0, reused: true };
        }

        if (connectingPromise) {
            return connectingPromise;
        }

        const start = performance.now();
        connectingPromise = (async () => {
            try {
                if (!qz.websocket.isActive()) {
                    await qz.websocket.connect();
                }
                const duration = performance.now() - start;
                console.log(`[QZ Tray] Connected securely in ${duration.toFixed(2)} ms`);
                return { connected: true, duration, reused: false };
            } catch (error) {
                console.error('[QZ Tray] Connection failed:', error);
                return { connected: false, duration: performance.now() - start, reused: false, error };
            } finally {
                connectingPromise = null;
            }
        })();

        return connectingPromise;
    },

    async disconnect() {
        if (qz.websocket.isActive()) {
            await qz.websocket.disconnect();
            this.resetCache();
            console.log('[QZ Tray] Disconnected');
        }
    },

    isConnected() {
        return qz.websocket.isActive();
    },

    resetCache() {
        cachedPrinter = null;
        cachedConfig = null;
        cachedConfigPrinter = null;
        cachedConfigOptionsStr = null;
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
        if (preferredPrinter && preferredPrinter === cachedPrinter) {
            return { printer: cachedPrinter, duration: 0, cached: true };
        }
        if (!preferredPrinter && cachedPrinter) {
            return { printer: cachedPrinter, duration: 0, cached: true };
        }

        const start = performance.now();
        let printer = null;

        if (preferredPrinter) {
            printer = preferredPrinter;
        } else {
            try {
                const defaultPrinter = await qz.printers.getDefault();
                if (defaultPrinter) {
                    console.log('[QZ Tray] Using default printer:', defaultPrinter);
                    printer = defaultPrinter;
                }
            } catch (e) {
                console.warn('[QZ Tray] Could not fetch default printer, searching all printers...', e);
            }

            if (!printer) {
                const printers = await qz.printers.find();
                if (printers && printers.length > 0) {
                    console.log('[QZ Tray] Using first detected printer:', printers[0]);
                    printer = printers[0];
                }
            }
        }

        if (!printer) {
            throw new Error('No printers detected by QZ Tray.');
        }

        cachedPrinter = printer;
        const duration = performance.now() - start;
        return { printer, duration, cached: false };
    },

    async init(preferredPrinter = null) {
        try {
            console.log('[QZ Tray] Initializing background connection and printer cache...');
            const connRes = await this.connect();
            if (connRes.connected) {
                try {
                    await this.getPrinter(preferredPrinter);
                    console.log('[QZ Tray] Background init complete. Cached printer:', cachedPrinter);
                } catch (pErr) {
                    console.warn('[QZ Tray] Background printer lookup warning (will retry on print):', pErr.message);
                }
            }
        } catch (err) {
            console.warn('[QZ Tray] Background connection warning (will retry on print):', err.message);
        }
    },

    async printHTML(html, printerName = null, configOptions = {}) {
        lastCertTime = 0;
        lastSignTime = 0;
        const totalStart = performance.now();

        try {
            const connRes = await this.connect();
            if (!connRes.connected) {
                throw new Error('QZ Tray is not connected. Please ensure QZ Tray desktop app is running.');
            }
            const connectTime = connRes.duration;

            const printerRes = await this.getPrinter(printerName);
            const printerTime = printerRes.duration;
            const printer = printerRes.printer;

            const options = Object.assign({ margins: 0 }, configOptions);
            const config = getConfig(printer, options);

            const data = [{
                type: 'pixel',
                format: 'html',
                flavor: 'plain',
                data: html
            }];

            const printStart = performance.now();
            try {
                await qz.print(config, data);
            } catch (printErr) {
                if (!qz.websocket.isActive()) {
                    console.warn('[QZ Tray] Connection lost during print, attempting reconnect...');
                    this.resetCache();
                    const reConn = await this.connect();
                    if (reConn.connected) {
                        const rePrinter = await this.getPrinter(printerName);
                        const reConfig = getConfig(rePrinter.printer, options);
                        await qz.print(reConfig, data);
                    } else {
                        throw printErr;
                    }
                } else {
                    throw printErr;
                }
            }
            const printTime = performance.now() - printStart;
            const totalTime = performance.now() - totalStart;

            console.log(`[QZ PERF] certificate: ${lastCertTime.toFixed(2)} ms`);
            console.log(`[QZ PERF] connect: ${connectTime.toFixed(2)} ms`);
            console.log(`[QZ PERF] printer lookup: ${printerTime.toFixed(2)} ms`);
            console.log(`[QZ PERF] sign: ${lastSignTime.toFixed(2)} ms`);
            console.log(`[QZ PERF] print: ${printTime.toFixed(2)} ms`);
            console.log(`[QZ PERF] total: ${totalTime.toFixed(2)} ms`);

            return { success: true, printer };
        } catch (error) {
            console.error('[QZ Tray] Printing failed:', error);
            throw error;
        }
    },
};

window.QZTray = QZTray;

export default QZTray;