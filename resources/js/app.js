import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const initBarcodeScanner = () => {
    const startButton = document.getElementById('start-barcode-scanner');
    const stopButton = document.getElementById('stop-barcode-scanner');
    const panel = document.getElementById('barcode-scanner-panel');
    const status = document.getElementById('barcode-scanner-status');
    const video = document.getElementById('barcode-scanner-video');
    const barcodeInput = document.getElementById('barcode');

    if (!startButton || !stopButton || !panel || !status || !video || !barcodeInput) {
        return;
    }

    let activeScanToken = 0;
    let controls = null;
    let scannerModule = null;
    let nativeDetector = null;
    let nativeScanTimer = null;
    let enhancedScanTimer = null;
    let lastRejectedCodeAt = 0;
    const enhancedScanCanvas = document.createElement('canvas');

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.className = `mt-1 text-sm ${isError ? 'text-red-600' : 'text-gray-500'}`;
    };

    const stopNativeScanner = () => {
        if (nativeScanTimer) {
            window.clearTimeout(nativeScanTimer);
            nativeScanTimer = null;
        }
    };

    const stopEnhancedScanner = () => {
        if (enhancedScanTimer) {
            window.clearTimeout(enhancedScanTimer);
            enhancedScanTimer = null;
        }
    };

    const stopScanner = () => {
        activeScanToken += 1;
        stopNativeScanner();
        stopEnhancedScanner();

        if (controls) {
            controls.stop();
            controls = null;
        }

        video.srcObject = null;
        panel.classList.add('hidden');
        startButton.disabled = false;
    };

    const finishScan = (value, activeControls) => {
        activeScanToken += 1;
        stopNativeScanner();
        stopEnhancedScanner();

        barcodeInput.value = String(value).trim();
        barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
        setStatus('Code scanned successfully.');

        if (activeControls) {
            activeControls.stop();
        }

        controls = null;
        video.srcObject = null;
        panel.classList.add('hidden');
        startButton.disabled = false;
        barcodeInput.focus();
    };

    const gtinCheckDigitIsValid = (value) => {
        if (!/^\d{8}$|^\d{12}$|^\d{13}$|^\d{14}$/.test(value)) {
            return false;
        }

        const digits = value.split('').map(Number);
        const checkDigit = digits.pop();
        let sum = 0;
        let shouldTriple = true;

        for (let index = digits.length - 1; index >= 0; index -= 1) {
            sum += digits[index] * (shouldTriple ? 3 : 1);
            shouldTriple = !shouldTriple;
        }

        return ((10 - (sum % 10)) % 10) === checkDigit;
    };

    const formatNameFromZxing = (BarcodeFormat, format) => Object
        .entries(BarcodeFormat)
        .find(([name, value]) => Number.isNaN(Number(name)) && value === format)?.[0] ?? '';

    const normalizeScannedCode = (value, format = '') => {
        const code = String(value ?? '').trim();

        if (code === '') {
            return null;
        }

        const formatName = String(format).toUpperCase().replaceAll('-', '_');
        const digits = code.replace(/\D/g, '');
        const isOnlyDigits = digits === code;
        const isKnownProductFormat = ['EAN_13', 'EAN_8', 'UPC_A'].includes(formatName);

        if ((isOnlyDigits || isKnownProductFormat) && /^\d{8}$|^\d{12}$|^\d{13}$|^\d{14}$/.test(digits)) {
            return gtinCheckDigitIsValid(digits) ? digits : null;
        }

        if (formatName === 'UPC_E' && /^\d{6,8}$/.test(digits)) {
            return digits;
        }

        return code;
    };

    const rejectScannedCode = () => {
        const now = Date.now();

        if (now - lastRejectedCodeAt < 1500) {
            return;
        }

        lastRejectedCodeAt = now;
        setStatus('May nabasang number pero hindi valid ang barcode check digit. Hold steady and refocus.', true);
    };

    const tryFinishScan = (value, format, activeControls, markScanned) => {
        const code = normalizeScannedCode(value, format);

        if (!code) {
            rejectScannedCode();
            return false;
        }

        if (!markScanned()) {
            return false;
        }

        finishScan(code, activeControls);

        return true;
    };

    const loadScannerModule = async () => {
        scannerModule ??= Promise.all([
            import('@zxing/browser'),
            import('@zxing/library'),
        ]).then(([browserModule, libraryModule]) => ({
            ...browserModule,
            DecodeHintType: libraryModule.DecodeHintType,
        }));

        return scannerModule;
    };

    const zxingFormats = (BarcodeFormat) => [
        BarcodeFormat.EAN_13,
        BarcodeFormat.EAN_8,
        BarcodeFormat.UPC_A,
        BarcodeFormat.UPC_E,
        BarcodeFormat.CODE_128,
        BarcodeFormat.CODE_39,
        BarcodeFormat.CODE_93,
        BarcodeFormat.ITF,
        BarcodeFormat.CODABAR,
        BarcodeFormat.QR_CODE,
    ].filter((format) => format !== undefined);

    const nativeBarcodeFormats = [
        'ean_13',
        'ean_8',
        'upc_a',
        'upc_e',
        'code_128',
        'code_39',
        'code_93',
        'itf',
        'codabar',
        'qr_code',
    ];

    const createNativeDetector = async () => {
        if (!('BarcodeDetector' in window)) {
            return null;
        }

        if (nativeDetector) {
            return nativeDetector;
        }

        try {
            const supportedFormats = typeof window.BarcodeDetector.getSupportedFormats === 'function'
                ? await window.BarcodeDetector.getSupportedFormats()
                : nativeBarcodeFormats;
            const formats = nativeBarcodeFormats.filter((format) => supportedFormats.includes(format));

            if (formats.length === 0) {
                return null;
            }

            nativeDetector = new window.BarcodeDetector({ formats });

            return nativeDetector;
        } catch (error) {
            return null;
        }
    };

    const startNativeScanner = (scanToken, detector, getActiveControls, markScanned) => {
        if (!detector) {
            return;
        }

        const scanFrame = async () => {
            nativeScanTimer = null;

            if (scanToken !== activeScanToken) {
                return;
            }

            try {
                if (video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA) {
                    const [result] = await detector.detect(video);

                    if (result?.rawValue && tryFinishScan(result.rawValue, result.format, getActiveControls(), markScanned)) {
                        return;
                    }
                }
            } catch (error) {
                // Native detection is only a fallback; ZXing keeps scanning below.
            }

            if (scanToken === activeScanToken) {
                nativeScanTimer = window.setTimeout(scanFrame, 150);
            }
        };

        nativeScanTimer = window.setTimeout(scanFrame, 150);
    };

    const enhancedScanRegions = [
        { x: 0, y: 0, width: 1, height: 1 },
        { x: 0.04, y: 0.08, width: 0.92, height: 0.78 },
        { x: 0, y: 0.14, width: 1, height: 0.68 },
        { x: 0.1, y: 0.18, width: 0.8, height: 0.58 },
    ];

    const enhancedScanVariants = ['plain', 'contrast', 'threshold'];

    const enhanceFrame = (context, width, height, variant) => {
        if (variant === 'plain') {
            return;
        }

        const image = context.getImageData(0, 0, width, height);
        const data = image.data;
        const grays = new Uint8ClampedArray(width * height);
        let min = 255;
        let max = 0;
        let total = 0;

        for (let index = 0, pixel = 0; index < data.length; index += 4, pixel += 1) {
            const gray = Math.round((data[index] * 0.299) + (data[index + 1] * 0.587) + (data[index + 2] * 0.114));
            grays[pixel] = gray;
            min = Math.min(min, gray);
            max = Math.max(max, gray);
            total += gray;
        }

        const range = Math.max(1, max - min);
        const threshold = total / grays.length;

        for (let index = 0, pixel = 0; index < data.length; index += 4, pixel += 1) {
            const normalized = ((grays[pixel] - min) / range) * 255;
            const value = variant === 'threshold'
                ? (normalized < threshold ? 0 : 255)
                : Math.max(0, Math.min(255, ((normalized - 128) * 1.9) + 128));

            data[index] = value;
            data[index + 1] = value;
            data[index + 2] = value;
        }

        context.putImageData(image, 0, 0);
    };

    const prepareEnhancedCanvas = (region, variant) => {
        const sourceWidth = video.videoWidth;
        const sourceHeight = video.videoHeight;

        if (!sourceWidth || !sourceHeight) {
            return null;
        }

        const sourceX = Math.max(0, Math.round(sourceWidth * region.x));
        const sourceY = Math.max(0, Math.round(sourceHeight * region.y));
        const sourceRegionWidth = Math.min(sourceWidth - sourceX, Math.round(sourceWidth * region.width));
        const sourceRegionHeight = Math.min(sourceHeight - sourceY, Math.round(sourceHeight * region.height));

        if (sourceRegionWidth <= 0 || sourceRegionHeight <= 0) {
            return null;
        }

        const targetWidth = Math.min(1800, Math.max(900, Math.round(sourceRegionWidth * 1.75)));
        const targetHeight = Math.min(1200, Math.max(260, Math.round((targetWidth / sourceRegionWidth) * sourceRegionHeight)));

        enhancedScanCanvas.width = targetWidth;
        enhancedScanCanvas.height = targetHeight;

        const context = enhancedScanCanvas.getContext('2d', { willReadFrequently: true });

        if (!context) {
            return null;
        }

        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, targetWidth, targetHeight);
        context.imageSmoothingEnabled = true;
        context.imageSmoothingQuality = 'high';
        context.drawImage(
            video,
            sourceX,
            sourceY,
            sourceRegionWidth,
            sourceRegionHeight,
            0,
            0,
            targetWidth,
            targetHeight,
        );
        enhanceFrame(context, targetWidth, targetHeight, variant);

        return enhancedScanCanvas;
    };

    const decodeEnhancedFrame = (scanner, BarcodeFormat) => {
        for (const region of enhancedScanRegions) {
            for (const variant of enhancedScanVariants) {
                const canvas = prepareEnhancedCanvas(region, variant);

                if (!canvas) {
                    continue;
                }

                try {
                    const result = scanner.decodeFromCanvas(canvas);

                    if (result?.getText()) {
                        return {
                            format: formatNameFromZxing(BarcodeFormat, result.getBarcodeFormat?.()),
                            text: result.getText(),
                        };
                    }
                } catch (error) {
                    // Keep trying the next crop/contrast variant.
                }
            }
        }

        return null;
    };

    const startEnhancedFrameScanner = (scanToken, scanner, BarcodeFormat, getActiveControls, markScanned) => {
        const scanFrame = () => {
            enhancedScanTimer = null;

            if (scanToken !== activeScanToken || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
                if (scanToken === activeScanToken) {
                    enhancedScanTimer = window.setTimeout(scanFrame, 250);
                }

                return;
            }

            const result = decodeEnhancedFrame(scanner, BarcodeFormat);

            if (result && tryFinishScan(result.text, result.format, getActiveControls(), markScanned)) {
                return;
            }

            if (scanToken === activeScanToken) {
                enhancedScanTimer = window.setTimeout(scanFrame, 250);
            }
        };

        enhancedScanTimer = window.setTimeout(scanFrame, 250);
    };

    const applyBestCameraFocus = async (scannerControls) => {
        if (!scannerControls?.streamVideoCapabilitiesGet || !scannerControls?.streamVideoConstraintsApply) {
            return;
        }

        try {
            const liveTrack = (track) => track.readyState === 'live';
            const capabilities = scannerControls.streamVideoCapabilitiesGet(liveTrack);
            const advanced = [];

            if (capabilities?.focusMode?.includes('continuous')) {
                advanced.push({ focusMode: 'continuous' });
            }

            if (capabilities?.exposureMode?.includes('continuous')) {
                advanced.push({ exposureMode: 'continuous' });
            }

            if (advanced.length > 0) {
                await scannerControls.streamVideoConstraintsApply({ advanced }, liveTrack);
            }
        } catch (error) {
            // Some laptop cameras do not expose focus controls.
        }
    };

    startButton.addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            panel.classList.remove('hidden');
            setStatus('Camera scanning requires Chrome or Edge on HTTPS or localhost. You can type the code manually.', true);
            return;
        }

        const scanToken = activeScanToken + 1;
        activeScanToken = scanToken;

        try {
            startButton.disabled = true;
            panel.classList.remove('hidden');
            setStatus('Opening camera...');

            const [{ BarcodeFormat, BrowserMultiFormatReader, DecodeHintType }, fallbackDetector] = await Promise.all([
                loadScannerModule(),
                createNativeDetector(),
            ]);
            const hints = new Map();
            hints.set(DecodeHintType.POSSIBLE_FORMATS, zxingFormats(BarcodeFormat));
            hints.set(DecodeHintType.TRY_HARDER, true);

            const scanner = new BrowserMultiFormatReader(hints, {
                delayBetweenScanAttempts: 120,
                delayBetweenScanSuccess: 250,
            });
            let codeWasScanned = false;
            const markScanned = () => {
                if (codeWasScanned) {
                    return false;
                }

                codeWasScanned = true;
                return true;
            };

            const scannerControls = await scanner.decodeFromConstraints(
                {
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                    audio: false,
                },
                video,
                (result, error, activeControls) => {
                    if (!result || scanToken !== activeScanToken) {
                        return;
                    }

                    tryFinishScan(
                        result.getText(),
                        formatNameFromZxing(BarcodeFormat, result.getBarcodeFormat?.()),
                        activeControls,
                        markScanned,
                    );
                },
            );

            if (scanToken !== activeScanToken || codeWasScanned) {
                scannerControls.stop();
                return;
            }

            controls = scannerControls;
            await applyBestCameraFocus(scannerControls);
            startNativeScanner(scanToken, fallbackDetector, () => scannerControls, markScanned);
            startEnhancedFrameScanner(scanToken, scanner, BarcodeFormat, () => scannerControls, markScanned);
            setStatus('Hold the full barcode steady inside the frame. Move closer or farther if it stays blurry. Only valid EAN/UPC numbers will be copied.');
        } catch (error) {
            if (scanToken !== activeScanToken) {
                return;
            }

            stopScanner();
            panel.classList.remove('hidden');

            const permissionWasDenied = error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError';
            const message = permissionWasDenied
                ? 'Camera access was blocked. Please allow camera access in Chrome and try again.'
                : 'Camera could not be opened. Please use HTTPS or localhost, then try again.';

            setStatus(message, true);
        }
    });

    stopButton.addEventListener('click', stopScanner);

    window.addEventListener('beforeunload', stopScanner);
};

const initCashieringScanner = () => {
    const startButton = document.getElementById('cashier-start-scanner');
    const stopButton = document.getElementById('cashier-stop-scanner');
    const panel = document.getElementById('cashier-scanner-panel');
    const status = document.getElementById('cashier-scanner-status');
    const video = document.getElementById('cashier-scanner-video');
    const barcodeInput = document.getElementById('cashier-barcode');

    if (!startButton || !stopButton || !panel || !status || !video || !barcodeInput) {
        return;
    }

    let controls = null;
    let scannerModule = null;
    let scanToken = 0;

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.className = `mt-1 text-sm ${isError ? 'text-red-600' : 'text-gray-500'}`;
    };

    const stopScanner = () => {
        scanToken += 1;

        if (controls) {
            controls.stop();
            controls = null;
        }

        video.srcObject = null;
        panel.classList.add('hidden');
        startButton.disabled = false;
    };

    const loadScannerModule = async () => {
        scannerModule ??= Promise.all([
            import('@zxing/browser'),
            import('@zxing/library'),
        ]).then(([browserModule, libraryModule]) => ({
            ...browserModule,
            DecodeHintType: libraryModule.DecodeHintType,
        }));

        return scannerModule;
    };

    const cashierFormats = (BarcodeFormat) => [
        BarcodeFormat.EAN_13,
        BarcodeFormat.EAN_8,
        BarcodeFormat.UPC_A,
        BarcodeFormat.UPC_E,
        BarcodeFormat.CODE_128,
        BarcodeFormat.CODE_39,
        BarcodeFormat.CODE_93,
        BarcodeFormat.ITF,
        BarcodeFormat.CODABAR,
        BarcodeFormat.QR_CODE,
    ].filter((format) => format !== undefined);

    const finishScan = (value, activeControls) => {
        const code = String(value ?? '').trim();

        if (code === '') {
            return;
        }

        scanToken += 1;
        barcodeInput.value = code;
        barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
        window.dispatchEvent(new CustomEvent('cashiering:barcode-scanned', {
            detail: { code },
        }));

        if (activeControls) {
            activeControls.stop();
        }

        controls = null;
        video.srcObject = null;
        panel.classList.add('hidden');
        startButton.disabled = false;
    };

    startButton.addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            panel.classList.remove('hidden');
            setStatus('Camera scanning requires Chrome or Edge on HTTPS or localhost. You can type the code manually.', true);
            return;
        }

        const activeToken = scanToken + 1;
        scanToken = activeToken;

        try {
            startButton.disabled = true;
            panel.classList.remove('hidden');
            setStatus('Opening camera...');

            const { BarcodeFormat, BrowserMultiFormatReader, DecodeHintType } = await loadScannerModule();
            const hints = new Map();
            hints.set(DecodeHintType.POSSIBLE_FORMATS, cashierFormats(BarcodeFormat));
            hints.set(DecodeHintType.TRY_HARDER, true);

            const scanner = new BrowserMultiFormatReader(hints, {
                delayBetweenScanAttempts: 150,
                delayBetweenScanSuccess: 250,
            });
            let codeWasScanned = false;
            const scannerControls = await scanner.decodeFromConstraints(
                {
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                    audio: false,
                },
                video,
                (result, error, activeControls) => {
                    if (!result || activeToken !== scanToken || codeWasScanned) {
                        return;
                    }

                    codeWasScanned = true;
                    finishScan(result.getText(), activeControls);
                },
            );

            if (activeToken !== scanToken || codeWasScanned) {
                scannerControls.stop();
                return;
            }

            controls = scannerControls;
            setStatus('Hold the barcode inside the guide. It will be added to cart automatically.');
        } catch (error) {
            if (activeToken !== scanToken) {
                return;
            }

            stopScanner();
            panel.classList.remove('hidden');

            const permissionWasDenied = error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError';
            const message = permissionWasDenied
                ? 'Camera access was blocked. Please allow camera access and try again.'
                : 'Camera could not be opened. You can type the barcode manually.';

            setStatus(message, true);
        }
    });

    stopButton.addEventListener('click', stopScanner);
    window.addEventListener('beforeunload', stopScanner);
};

const initStockOutScanner = () => {
    const startButton = document.getElementById('stockout-start-scanner');
    const stopButton  = document.getElementById('stockout-stop-scanner');
    const panel       = document.getElementById('stockout-scanner-panel');
    const status      = document.getElementById('stockout-scanner-status');
    const video       = document.getElementById('stockout-scanner-video');

    if (!startButton || !stopButton || !panel || !status || !video) {
        return;
    }

    let controls     = null;
    let scannerModule = null;
    let scanToken    = 0;

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.className = `mt-1 text-sm ${isError ? 'text-red-600' : 'text-gray-500'}`;
    };

    const stopScanner = () => {
        scanToken += 1;

        if (controls) {
            controls.stop();
            controls = null;
        }

        video.srcObject = null;
        panel.classList.add('hidden');
        startButton.disabled = false;
    };

    const loadScannerModule = async () => {
        scannerModule ??= Promise.all([
            import('@zxing/browser'),
            import('@zxing/library'),
        ]).then(([browserModule, libraryModule]) => ({
            ...browserModule,
            DecodeHintType: libraryModule.DecodeHintType,
        }));

        return scannerModule;
    };

    const supportedFormats = (BarcodeFormat) => [
        BarcodeFormat.EAN_13,
        BarcodeFormat.EAN_8,
        BarcodeFormat.UPC_A,
        BarcodeFormat.UPC_E,
        BarcodeFormat.CODE_128,
        BarcodeFormat.CODE_39,
        BarcodeFormat.CODE_93,
        BarcodeFormat.ITF,
        BarcodeFormat.CODABAR,
        BarcodeFormat.QR_CODE,
    ].filter((f) => f !== undefined);

    const finishScan = (value, activeControls) => {
        const code = String(value ?? '').trim();

        if (code === '') {
            return;
        }

        scanToken += 1;

        // Dispatch event so the page JS can match the product
        window.dispatchEvent(new CustomEvent('stockout:barcode-scanned', {
            detail: { code },
        }));

        setStatus(`Scanned: ${code}`);

        if (activeControls) {
            activeControls.stop();
        }

        controls = null;
        video.srcObject = null;
        panel.classList.add('hidden');
        startButton.disabled = false;
    };

    startButton.addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            panel.classList.remove('hidden');
            setStatus('Camera scanning requires Chrome or Edge on HTTPS or localhost. You can type the code manually.', true);
            return;
        }

        const activeToken = scanToken + 1;
        scanToken = activeToken;

        try {
            startButton.disabled = true;
            panel.classList.remove('hidden');
            setStatus('Opening camera...');

            const { BarcodeFormat, BrowserMultiFormatReader, DecodeHintType } = await loadScannerModule();
            const hints = new Map();
            hints.set(DecodeHintType.POSSIBLE_FORMATS, supportedFormats(BarcodeFormat));
            hints.set(DecodeHintType.TRY_HARDER, true);

            const scanner = new BrowserMultiFormatReader(hints, {
                delayBetweenScanAttempts: 150,
                delayBetweenScanSuccess: 250,
            });
            let codeWasScanned = false;
            const scannerControls = await scanner.decodeFromConstraints(
                {
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                    audio: false,
                },
                video,
                (result, error, activeControls) => {
                    if (!result || activeToken !== scanToken || codeWasScanned) {
                        return;
                    }

                    codeWasScanned = true;
                    finishScan(result.getText(), activeControls);
                },
            );

            if (activeToken !== scanToken || codeWasScanned) {
                scannerControls.stop();
                return;
            }

            controls = scannerControls;
            setStatus('Hold the barcode inside the guide. The product will be selected automatically.');
        } catch (error) {
            if (activeToken !== scanToken) {
                return;
            }

            stopScanner();
            panel.classList.remove('hidden');

            const permissionWasDenied = error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError';
            const message = permissionWasDenied
                ? 'Camera access was blocked. Please allow camera access and try again.'
                : 'Camera could not be opened. You can type the barcode manually.';

            setStatus(message, true);
        }
    });

    stopButton.addEventListener('click', stopScanner);
    window.addEventListener('beforeunload', stopScanner);
};

const initStockInScanner = () => {
    const cameraBtn    = document.getElementById('scan-camera-btn-in');
    const stopBtn      = document.getElementById('stop-camera-btn-in');
    const container    = document.getElementById('camera-container-in');
    const video        = document.getElementById('camera-preview-in');
    const statusEl     = document.getElementById('scanner-status-in');
    const barcodeInput = document.getElementById('barcode-input-in');

    // The match logic lives in the view's inline <script> so both camera and
    // keyboard input share the same behaviour.
    const doMatch = (code) => {
        if (typeof window.stockInMatchBarcode === 'function') {
            window.stockInMatchBarcode(code);
        }
    };

    if (!cameraBtn || !video) return;

    let controls = null;
    let scannerModule = null;
    let scanToken = 0;

    const setStatus = (msg, isError = false) => {
        if (statusEl) {
            statusEl.textContent = msg;
            statusEl.className = `mt-2 text-xs ${isError ? 'text-red-600' : 'text-gray-400'}`;
        }
    };

    const stopScanner = () => {
        scanToken += 1;
        if (controls) { controls.stop(); controls = null; }
        if (video.srcObject) video.srcObject = null;
        if (container) container.classList.add('hidden');
    };

    const loadScannerModule = async () => {
        scannerModule ??= Promise.all([
            import('@zxing/browser'),
            import('@zxing/library'),
        ]).then(([browserModule, libraryModule]) => ({
            ...browserModule,
            DecodeHintType: libraryModule.DecodeHintType,
        }));
        return scannerModule;
    };

    const supportedFormats = (BarcodeFormat) => [
        BarcodeFormat.EAN_13, BarcodeFormat.EAN_8, BarcodeFormat.UPC_A,
        BarcodeFormat.UPC_E, BarcodeFormat.CODE_128, BarcodeFormat.CODE_39,
        BarcodeFormat.QR_CODE,
    ].filter(f => f !== undefined);

    cameraBtn.addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            setStatus('Camera requires HTTPS or localhost.', true);
            return;
        }

        const activeToken = ++scanToken;
        if (container) container.classList.remove('hidden');
        setStatus('Opening camera…');

        try {
            const { BarcodeFormat, BrowserMultiFormatReader, DecodeHintType } = await loadScannerModule();
            const hints = new Map();
            hints.set(DecodeHintType.POSSIBLE_FORMATS, supportedFormats(BarcodeFormat));
            hints.set(DecodeHintType.TRY_HARDER, true);

            const scanner = new BrowserMultiFormatReader(hints, { delayBetweenScanAttempts: 150 });
            let scanned = false;

            const scannerControls = await scanner.decodeFromConstraints(
                { video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false },
                video,
                (result, error, activeControls) => {
                    if (!result || activeToken !== scanToken || scanned) return;
                    scanned = true;
                    const code = result.getText();
                    if (barcodeInput) barcodeInput.value = code;
                    doMatch(code);
                    activeControls.stop();
                    controls = null;
                    if (video.srcObject) video.srcObject = null;
                    if (container) container.classList.add('hidden');
                },
            );

            if (activeToken !== scanToken || scanned) { scannerControls.stop(); return; }
            controls = scannerControls;
            setStatus('Point camera at barcode. Detection is automatic.');
        } catch (err) {
            if (activeToken !== scanToken) return;
            stopScanner();
            const denied = err?.name === 'NotAllowedError' || err?.name === 'PermissionDeniedError';
            setStatus(denied ? 'Camera blocked. Allow camera access.' : 'Could not open camera. Type barcode manually.', true);
        }
    });

    if (stopBtn) stopBtn.addEventListener('click', stopScanner);
    window.addEventListener('beforeunload', stopScanner);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initBarcodeScanner();
        initCashieringScanner();
        initStockOutScanner();
        initStockInScanner();
    });
} else {
    initBarcodeScanner();
    initCashieringScanner();
    initStockOutScanner();
    initStockInScanner();
}
