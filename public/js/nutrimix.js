const firebaseConfig = {
    apiKey: "AIzaSyAQe__wZ1_xjW6dLjWTCgKDamN5EnT5mjc",
    databaseURL: "https://smart-incubator-d53d4-default-rtdb.asia-southeast1.firebasedatabase.app/",
};

if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}

const db = firebase.database();
const DEVICE_PATH = "/device/nutrimix_1";
const COMMAND_PATH = `${DEVICE_PATH}/command`;

const activeStatusPath = {};
const cached = {
    weight: 0,
    target: null,
};

let isFirebaseConnected = false;
let lastSeenTimestamp = 0;

const STATUS_PATHS = {
    weight: [
        `${DEVICE_PATH}/status/weight_g`,
        `${DEVICE_PATH}/status/weight`,
        `${DEVICE_PATH}/weight`,
    ],
    target: [
        `${DEVICE_PATH}/status/target_g`,
        `${DEVICE_PATH}/status/target`,
        `${DEVICE_PATH}/target`,
    ],
    state: [
        `${DEVICE_PATH}/status/state`,
        `${DEVICE_PATH}/status/process_state`,
        `${DEVICE_PATH}/state`,
    ],
    mode: [
        `${DEVICE_PATH}/status/mode`,
        `${DEVICE_PATH}/mode`,
    ],
    screw: [
        `${DEVICE_PATH}/status/screw`,
        `${DEVICE_PATH}/status/relay/screw`,
        `${DEVICE_PATH}/relay/screw`,
        `${DEVICE_PATH}/relay/relay1`,
    ],
    trimmer: [
        `${DEVICE_PATH}/status/trimmer`,
        `${DEVICE_PATH}/status/relay/trimmer`,
        `${DEVICE_PATH}/relay/trimmer`,
        `${DEVICE_PATH}/relay/relay2`,
    ],
    servo: [
        `${DEVICE_PATH}/status/servo`,
        `${DEVICE_PATH}/servo`,
    ],
    hx: [
        `${DEVICE_PATH}/status/hx711_ready`,
        `${DEVICE_PATH}/status/hx_ready`,
        `${DEVICE_PATH}/hx_ready`,
    ],
    error: [
        `${DEVICE_PATH}/status/error`,
        `${DEVICE_PATH}/error`,
    ],
    trimmerDuration: [
        `${DEVICE_PATH}/status/trimmer_duration_s`,
        `${DEVICE_PATH}/status/trimmer_duration`,
    ],
    eta: [
        `${DEVICE_PATH}/status/eta`,
        `${DEVICE_PATH}/status/eta_ts`,
    ],
};

document.addEventListener("DOMContentLoaded", () => {
    initConnectionListener();
    initHeartbeatListener();
    initStatusListeners();
    initControls();
    setInterval(refreshDeviceStatus, 5000);
    refreshDeviceStatus();
});

function initConnectionListener() {
    db.ref(".info/connected").on("value", (snapshot) => {
        isFirebaseConnected = snapshot.val() === true;
        updateFirebaseStatus();
        refreshDeviceStatus();
    });
}

function initHeartbeatListener() {
    db.ref(`${DEVICE_PATH}/last_seen`).on("value", (snapshot) => {
        const ts = snapshot.val();
        if (typeof ts === "number") {
            lastSeenTimestamp = ts;
        }
        updateLastSeenUI();
        refreshDeviceStatus();
    });
}

function initStatusListeners() {
    bindStatus("weight", STATUS_PATHS.weight, (value) => {
        cached.weight = Number(value) || 0;
        updateWeightUI();
    });

    bindStatus("target", STATUS_PATHS.target, (value) => {
        cached.target = value === null || value === undefined ? null : Number(value);
        updateTargetUI();
    });

    bindStatus("state", STATUS_PATHS.state, (value) => {
        updateProcessState(value);
    });

    bindStatus("mode", STATUS_PATHS.mode, (value) => {
        updateModeUI(value);
    });

    bindStatus("screw", STATUS_PATHS.screw, (value) => {
        const enabled = normalizeBool(value);
        updateToggleUI("screw", enabled);
    });

    bindStatus("trimmer", STATUS_PATHS.trimmer, (value) => {
        const enabled = normalizeBool(value);
        updateToggleUI("trimmer", enabled);
    });

    bindStatus("servo", STATUS_PATHS.servo, (value) => {
        updateServoUI(value);
    });

    bindStatus("hx", STATUS_PATHS.hx, (value) => {
        const hxStatus = document.getElementById("hxStatus");
        if (!hxStatus) return;
        const isReady = normalizeBool(value);
        hxStatus.textContent = isReady ? "READY" : "NOT READY";
        hxStatus.style.color = isReady ? "#2e7d32" : "#dc2626";
    });

    bindStatus("error", STATUS_PATHS.error, (value) => {
        const errorEl = document.getElementById("errorStatus");
        if (!errorEl) return;
        errorEl.textContent = value ? String(value) : "-";
    });

    bindStatus("trimmerDuration", STATUS_PATHS.trimmerDuration, (value) => {
        const el = document.getElementById("trimmerDuration");
        if (!el) return;
        if (value === null || value === undefined || value === "") {
            el.textContent = "--";
            return;
        }
        el.textContent = `${value} s`;
    });

    bindStatus("eta", STATUS_PATHS.eta, (value) => {
        const el = document.getElementById("etaValue");
        if (!el) return;
        if (!value) {
            el.textContent = "--";
            return;
        }
        if (typeof value === "number") {
            const date = new Date(value);
            el.textContent = date.toLocaleTimeString("id-ID");
            return;
        }
        el.textContent = String(value);
    });
}

function bindStatus(key, candidates, handler) {
    candidates.forEach((path) => {
        db.ref(path).on("value", (snapshot) => {
            const value = snapshot.val();
            if (value === null || value === undefined) {
                return;
            }
            activeStatusPath[key] = path;
            handler(value);
        });
    });
}

function initControls() {
    const startBtn = document.getElementById("startAuto");
    const stopBtn = document.getElementById("stopAuto");
    const targetInput = document.getElementById("targetWeight");

    if (startBtn) {
        startBtn.addEventListener("click", () => {
            const targetValue = Number(targetInput?.value || 0);
            if (!targetValue || targetValue <= 0) {
                showToast("Masukkan target berat yang valid", "warning");
                return;
            }
            setCommand("target_g", targetValue);
            setCommand("mode", "auto");
            setCommand("start", Date.now());
            showToast("Perintah auto dikirim", "success");
        });
    }

    if (stopBtn) {
        stopBtn.addEventListener("click", () => {
            setCommand("stop", Date.now());
            showToast("Perintah stop dikirim", "warning");
        });
    }

    const screwToggle = document.getElementById("screwToggle");
    if (screwToggle) {
        screwToggle.addEventListener("change", () => {
            setCommand("mode", "manual");
            setCommand("manual/screw", screwToggle.checked);
        });
    }

    const trimmerToggle = document.getElementById("trimmerToggle");
    if (trimmerToggle) {
        trimmerToggle.addEventListener("change", () => {
            setCommand("mode", "manual");
            setCommand("manual/trimmer", trimmerToggle.checked);
        });
    }

    const servoOpen = document.getElementById("servoOpen");
    if (servoOpen) {
        servoOpen.addEventListener("click", () => {
            setCommand("mode", "manual");
            setCommand("manual/servo", "open");
            showToast("Perintah servo buka dikirim", "success");
        });
    }

    const servoClose = document.getElementById("servoClose");
    if (servoClose) {
        servoClose.addEventListener("click", () => {
            setCommand("mode", "manual");
            setCommand("manual/servo", "close");
            showToast("Perintah servo tutup dikirim", "success");
        });
    }
}

function setCommand(path, value) {
    db.ref(`${COMMAND_PATH}/${path}`).set(value).catch(() => {
        showToast("Gagal mengirim perintah", "error");
    });
}

function normalizeBool(value) {
    if (typeof value === "boolean") {
        return value;
    }

    if (typeof value === "number") {
        return value === 1;
    }

    if (typeof value === "string") {
        const lower = value.toLowerCase();
        return lower === "1" || lower === "on" || lower === "true" || lower === "open";
    }

    return false;
}

function updateWeightUI() {
    const currentWeight = document.getElementById("currentWeight");
    const weightValue = document.getElementById("weightValue");

    if (currentWeight) {
        currentWeight.textContent = `${cached.weight.toFixed(1)} g`;
    }

    if (weightValue) {
        weightValue.textContent = `${cached.weight.toFixed(1)} g`;
    }

    updateProgress();
}

function updateTargetUI() {
    const activeTarget = document.getElementById("activeTarget");
    const targetValue = document.getElementById("targetValue");

    if (activeTarget) {
        activeTarget.textContent = cached.target ? `${cached.target} g` : "--";
    }

    if (targetValue) {
        targetValue.textContent = cached.target ? `${cached.target} g` : "--";
    }

    updateProgress();
}

function updateProgress() {
    const progressFill = document.getElementById("progressFill");
    const progressLabel = document.getElementById("progressLabel");

    if (!progressFill || !progressLabel) return;

    if (!cached.target || cached.target <= 0) {
        progressFill.style.width = "0%";
        progressLabel.textContent = "0%";
        return;
    }

    const ratio = Math.min(cached.weight / cached.target, 1);
    const percentage = Math.round(ratio * 100);
    progressFill.style.width = `${percentage}%`;
    progressLabel.textContent = `${percentage}%`;
}

function updateProcessState(value) {
    const stateEl = document.getElementById("processState");
    if (!stateEl) return;
    stateEl.textContent = value ? String(value) : "Idle";
}

function updateModeUI(value) {
    const modeEl = document.getElementById("deviceMode");
    if (!modeEl) return;
    modeEl.textContent = value ? String(value) : "Auto";
}

function updateToggleUI(type, enabled) {
    const statusEl = document.getElementById(`${type}Status`);
    const stateEl = document.getElementById(`${type}State`);
    const toggleEl = document.getElementById(`${type}Toggle`);

    if (statusEl) {
        statusEl.textContent = enabled ? "ON" : "OFF";
        statusEl.style.color = enabled ? "#2e7d32" : "#dc2626";
    }

    if (stateEl) {
        stateEl.textContent = enabled ? "ON" : "OFF";
    }

    if (toggleEl) {
        toggleEl.checked = enabled;
    }
}

function updateServoUI(value) {
    const servoStatus = document.getElementById("servoStatus");
    const servoState = document.getElementById("servoState");

    const normalized = normalizeBool(value);
    const display = typeof value === "string" ? value.toUpperCase() : normalized ? "OPEN" : "CLOSE";

    if (servoStatus) {
        servoStatus.textContent = display;
    }

    if (servoState) {
        servoState.textContent = display;
    }
}

function refreshDeviceStatus() {
    const now = Math.floor(Date.now() / 1000);
    const isDeviceOnline = lastSeenTimestamp > 0 && now - lastSeenTimestamp <= 30;

    updateWifiStatus(isDeviceOnline);
    updateConnectionBadge(isFirebaseConnected, isDeviceOnline);
}

function updateFirebaseStatus() {
    const firebaseStatus = document.getElementById("firebaseStatus");
    if (!firebaseStatus) {
        return;
    }

    firebaseStatus.textContent = isFirebaseConnected ? "Terhubung" : "Terputus";
    firebaseStatus.style.color = isFirebaseConnected ? "#2e7d32" : "#dc2626";
}

function updateWifiStatus(isOnline) {
    const wifiStatus = document.getElementById("wifiStatus");
    if (!wifiStatus) {
        return;
    }

    wifiStatus.textContent = isOnline ? "Online" : "Offline";
    wifiStatus.style.color = isOnline ? "#2e7d32" : "#dc2626";
}

function updateLastSeenUI() {
    const lastSeenEl = document.getElementById("lastSeen");
    if (!lastSeenEl) {
        return;
    }

    if (!lastSeenTimestamp) {
        lastSeenEl.textContent = "--";
        return;
    }

    const localDate = new Date(lastSeenTimestamp * 1000);
    lastSeenEl.textContent = localDate.toLocaleString("id-ID");
}

function updateConnectionBadge(firebaseOnline, deviceOnline) {
    const badge = document.getElementById("connectionBadge");
    if (!badge) {
        return;
    }

    if (!firebaseOnline) {
        badge.textContent = "Firebase Offline";
        badge.style.backgroundColor = "#dc2626";
        return;
    }

    if (!deviceOnline) {
        badge.textContent = "Perangkat Offline";
        badge.style.backgroundColor = "#f59e0b";
        return;
    }

    badge.textContent = "Terhubung";
    badge.style.backgroundColor = "rgba(255, 255, 255, 0.3)";
}

function showToast(message, type = "info") {
    const existing = document.querySelector(".nutrimix-toast");
    if (existing) {
        existing.remove();
    }

    const toast = document.createElement("div");
    toast.className = `nutrimix-toast ${type}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 2500);
}
