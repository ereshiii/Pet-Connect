<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Chart, type ChartType, type ChartConfiguration } from 'chart.js/auto';

interface Props {
    type: ChartType;
    data: any;
    options?: any;
    height?: string;
    width?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'bar',
    options: () => ({}),
    height: '300px',
    width: '100%',
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
const chartInstance = ref<Chart | null>(null);

const defaultOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top' as const,
            labels: {
                usePointStyle: true,
                color: 'rgb(107, 114, 128)', // text-gray-500
            },
        },
    },
    scales: props.type === 'pie' || props.type === 'doughnut' ? {} : {
        x: {
            grid: {
                color: 'rgb(243, 244, 246)', // gray-100
            },
            ticks: {
                color: 'rgb(107, 114, 128)', // text-gray-500
            },
        },
        y: {
            grid: {
                color: 'rgb(243, 244, 246)', // gray-100
            },
            ticks: {
                color: 'rgb(107, 114, 128)', // text-gray-500
            },
        },
    },
    ...props.options,
}));

const createChart = () => {
    if (!canvasRef.value) return;

    if (chartInstance.value) {
        chartInstance.value.destroy();
    }

    const config: ChartConfiguration = {
        type: props.type,
        data: props.data,
        options: defaultOptions.value,
    };

    chartInstance.value = new Chart(canvasRef.value, config);
};

const updateChart = () => {
    if (chartInstance.value) {
        chartInstance.value.data = props.data;
        chartInstance.value.options = defaultOptions.value;
        chartInstance.value.update();
    }
};

onMounted(() => {
    createChart();
});

watch(() => props.data, updateChart, { deep: true });
watch(() => props.options, updateChart, { deep: true });
watch(() => props.type, createChart);

// Cleanup on unmount
const cleanup = () => {
    if (chartInstance.value) {
        chartInstance.value.destroy();
        chartInstance.value = null;
    }
};

// Dark mode support
const updateTheme = (isDark: boolean) => {
    const textColor = isDark ? 'rgb(209, 213, 219)' : 'rgb(107, 114, 128)';
    const gridColor = isDark ? 'rgb(55, 65, 81)' : 'rgb(243, 244, 246)';
    
    if (chartInstance.value) {
        if (chartInstance.value.options.plugins?.legend?.labels) {
            chartInstance.value.options.plugins.legend.labels.color = textColor;
        }
        
        if (chartInstance.value.options.scales) {
            Object.values(chartInstance.value.options.scales).forEach((scale: any) => {
                if (scale.grid) scale.grid.color = gridColor;
                if (scale.ticks) scale.ticks.color = textColor;
            });
        }
        
        chartInstance.value.update();
    }
};

// Watch for dark mode changes
if (typeof window !== 'undefined') {
    const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    darkModeQuery.addEventListener('change', (e) => updateTheme(e.matches));
    
    // Check for manual dark mode class
    const observer = new MutationObserver(() => {
        const isDark = document.documentElement.classList.contains('dark');
        updateTheme(isDark);
    });
    
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
}

defineExpose({
    chart: chartInstance,
    updateChart,
    cleanup,
});
</script>

<template>
    <div class="w-full" :style="{ height, width }">
        <canvas ref="canvasRef" class="w-full h-full"></canvas>
    </div>
</template>