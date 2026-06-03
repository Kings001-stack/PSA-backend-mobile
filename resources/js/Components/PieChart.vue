<template>
    <div class="chart-container">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from "vue";
import { Chart, ArcElement, Tooltip, Legend } from "chart.js";

Chart.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const chartCanvas = ref(null);
let chartInstance = null;

const createChart = () => {
    console.log("📈 Creating chart with data:", props.data);

    if (chartInstance) {
        chartInstance.destroy();
    }

    if (!chartCanvas.value) {
        console.error("❌ Canvas element not found!");
        return;
    }

    const ctx = chartCanvas.value.getContext("2d");
    chartInstance = new Chart(ctx, {
        type: "pie",
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        padding: 15,
                        font: {
                            size: 11,
                            weight: "600",
                        },
                        usePointStyle: true,
                        pointStyle: "circle",
                    },
                },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.8)",
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        size: 13,
                        weight: "bold",
                    },
                    bodyFont: {
                        size: 12,
                    },
                },
            },
            ...props.options,
        },
    });

    console.log("✅ Chart created successfully");
};

onMounted(() => {
    createChart();
});

watch(
    () => props.data,
    () => {
        createChart();
    },
    { deep: true },
);

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>
