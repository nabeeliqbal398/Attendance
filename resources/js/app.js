import "./bootstrap";
import TomSelect from "tom-select";

const syncDarkMode = () => {
    const isDark = localStorage.getItem("isDark") === "true";
    const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

    if (localStorage.getItem("isDark")) {
        if (isDark) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    } else {
        if (systemDark) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    }
};

const setAppLoading = (isLoading) => {
    document.documentElement.classList.toggle("is-app-loading", isLoading);
    document.body?.classList.toggle("is-app-loading", isLoading);
};

let hasCompletedInitialNavigation = false;

document.addEventListener("livewire:navigating", () => {
    setAppLoading(true);
});

document.addEventListener("livewire:navigated", () => {
    syncDarkMode();
    setAppLoading(false);
    if (hasCompletedInitialNavigation) {
        document.getElementById("main-content")?.focus?.({ preventScroll: true });
    }
    hasCompletedInitialNavigation = true;
});

window.addEventListener("pageshow", () => setAppLoading(false));
document.addEventListener("DOMContentLoaded", syncDarkMode);

let map;

window.initializeMap = ({ onUpdate, location }) => {
    let defaultLocation = location ?? [31.482914, 74.396186];
    map = L.map("map").setView(defaultLocation, 13);

    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 21,
    }).addTo(map);

    let marker = L.marker(defaultLocation, {
        draggable: true,
    }).addTo(map);

    marker.on("dragend", function (event) {
        let position = marker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });

    map.on("move", function () {
        let center = map.getCenter();
        marker.setLatLng(center);
        updateCoordinates(center.lat, center.lng);
    });

    updateCoordinates(defaultLocation[0], defaultLocation[1]);

    function updateCoordinates(lat, lng) {
        onUpdate(lat, lng);
    }
};

window.setMapLocation = ({ location }) => {
    if (location == null) return;

    map.setView(location, 13);
};

import { startNativeBarcodeScanner, stopNativeBarcodeScanner, switchNativeCamera } from './services/native/barcode';
import { checkMockLocation } from './services/native/mock-location';

window.startNativeBarcodeScanner = startNativeBarcodeScanner;
window.stopNativeBarcodeScanner = stopNativeBarcodeScanner;
window.switchNativeCamera = switchNativeCamera;
window.checkMockLocation = checkMockLocation;

window.isNativeApp = () =>
    !!(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());

window.openMap = async (lat, lng) => {
    const url = `https://www.google.com/maps?q=${lat},${lng}`;
    
    if (window.isNativeApp()) {
        try {
            // Try using Capacitor Browser plugin (opens in external browser/app)
            if (window.Capacitor?.Plugins?.Browser) {
                await window.Capacitor.Plugins.Browser.open({ url: url });
                return;
            }
            // Fallback: Try App Launcher for geo intent (opens Google Maps app directly)
            if (window.Capacitor?.Plugins?.App) {
                await window.Capacitor.Plugins.App.openUrl({ url: `geo:${lat},${lng}?q=${lat},${lng}` });
                return;
            }
        } catch (e) {
            console.warn('Capacitor open failed, using fallback', e);
        }
        // Fallback to _system target
        window.open(url, '_system');
    } else {
        window.open(url, '_blank');
    }
};

document.addEventListener('pagehide', () => {
    if (window.stopNativeBarcodeScanner) {
        window.stopNativeBarcodeScanner();
    }
});

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden' && window.stopNativeBarcodeScanner) {
        window.stopNativeBarcodeScanner();
    }
});

// Livewire Navigation (SPA-style links)
document.addEventListener('livewire:navigating', () => {
    if (window.stopNativeBarcodeScanner) {
        window.stopNativeBarcodeScanner();
    }
});

// Capacitor Back Button
if (window.Capacitor?.Plugins?.App) {
    window.Capacitor.Plugins.App.addListener('backButton', () => {
        if (window.stopNativeBarcodeScanner) {
            window.stopNativeBarcodeScanner();
        }
    });
}
