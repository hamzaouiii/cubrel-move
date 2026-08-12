import { ref, watch, onUnmounted } from "vue";
import { CHART_PALETTE } from "@/Pages/Components/Dashbaord/dashboardUi.js";

let themeVersion = null;
let observer = null;
let subscriberCount = 0;

function ensureObserver() {
    if (typeof window === "undefined") return;
    if (!themeVersion) themeVersion = ref(0);
    if (!observer) {
        observer = new MutationObserver(() => {
            themeVersion.value++;
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["data-theme"],
        });
    }
}

function readVar(name, fallback) {
    if (typeof window === "undefined") return fallback;
    const value = getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim();
    return value || fallback;
}

export function useChartTheme() {
    ensureObserver();
    subscriberCount++;

    onUnmounted(() => {
        subscriberCount--;
        if (subscriberCount <= 0 && observer) {
            observer.disconnect();
            observer = null;
            themeVersion = null;
            subscriberCount = 0;
        }
    });

    function onThemeChange(callback) {
        return watch(themeVersion, callback);
    }

    function chartColors(count = CHART_PALETTE.length) {
        return Array.from({ length: count }, (_, i) =>
            readVar(
                `--chart-color-${i + 1}`,
                CHART_PALETTE[i % CHART_PALETTE.length],
            ),
        );
    }

    const axisTextColor = () => readVar("--color-text-muted", "#64748b");
    const gridColor = () => readVar("--color-border", "rgba(0, 0, 0, 0.08)");
    const surfaceColor = () => readVar("--color-bg-surface", "#ffffff");

    return {
        onThemeChange,
        chartColors,
        axisTextColor,
        gridColor,
        surfaceColor,
    };
}
