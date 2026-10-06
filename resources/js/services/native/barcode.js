import {
    CapacitorBarcodeScanner,
    CapacitorBarcodeScannerAndroidScanningLibrary,
    CapacitorBarcodeScannerCameraDirection,
    CapacitorBarcodeScannerScanOrientation,
    CapacitorBarcodeScannerTypeHint,
} from "@capacitor/barcode-scanner";

let isScanning = false;
let currentFacingMode = 'environment';
let isSwitching = false;

function setNativeScanUi(isActive) {
    document.body.classList.toggle('is-native-scanning', isActive);
    document.documentElement.classList.toggle('is-native-scanning', isActive);
    if (window.setShowOverlay) window.setShowOverlay(isActive);
}

export async function startNativeBarcodeScanner(onScanSuccess, facingMode = null) {
    if (isScanning) return;
    isScanning = true;
    
    if (facingMode) currentFacingMode = facingMode;

    try {
        setNativeScanUi(true);

        const result = await CapacitorBarcodeScanner.scanBarcode({
            hint: CapacitorBarcodeScannerTypeHint.ALL,
            scanInstructions: 'Align the QR code inside the frame',
            cameraDirection: currentFacingMode === 'user'
                ? CapacitorBarcodeScannerCameraDirection.FRONT
                : CapacitorBarcodeScannerCameraDirection.BACK,
            scanOrientation: CapacitorBarcodeScannerScanOrientation.ADAPTIVE,
            android: {
                scanningLibrary: CapacitorBarcodeScannerAndroidScanningLibrary.MLKIT,
            },
            web: {
                showCameraSelection: true,
                scannerFPS: 30,
            },
        });

        if (result?.ScanResult) {
            await onScanSuccess(result.ScanResult);
        }
    } catch (e) {
        if (!isSwitching) {
            console.error("Scanner failed", e);
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Scanner Error',
                    text: e.message || String(e),
                    confirmButtonColor: '#6366f1'
                });
            }
        }
    } finally {
        if (!isSwitching) {
            setNativeScanUi(false);
        }
        isScanning = false;
    }
}

export async function stopNativeBarcodeScanner() {
    setNativeScanUi(false);
    isScanning = false;
}

export async function switchNativeCamera(onScanSuccess) {
    if (!isScanning) return; // Can't switch if not scanning

    // Set flag to suppress error alerts and cleanup during the stop
    isSwitching = true;
    console.log('[NATIVE CAM] Switching camera direction. Current:', currentFacingMode);
    
    try {
        setNativeScanUi(false);
        
        // Toggle camera direction
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        console.log('[NATIVE CAM] New target direction:', currentFacingMode);
        
        // CRITICAL: Wait for camera hardware release on Android
        await new Promise(resolve => setTimeout(resolve, 800));
        
        // Clear the switching flag before starting so the new scan manages UI properly
        isSwitching = false;
        isScanning = false; // Reset scanning state so startNativeBarcodeScanner allows entry
        
        // Start the new scanner with the switched camera
        await startNativeBarcodeScanner(onScanSuccess, currentFacingMode);
        console.log('[NATIVE CAM] Switch complete');
    } catch(e) {
        console.error('[NATIVE CAM] Switch native camera failed:', e);
        isSwitching = false;
        
        if (window.Swal) {
            window.Swal.fire({
                icon: 'error',
                title: 'Switch Failed',
                text: 'Could not switch native camera: ' + (e.message || String(e)),
                confirmButtonColor: '#6366f1'
            });
        }
    }
}
