<script setup lang="ts">
import {
  Chart as ChartJS,
  RadialLinearScale,
  ArcElement,
  Tooltip,
  Legend,
  ChartOptions,
} from 'chart.js';
import { PolarArea } from 'vue-chartjs';
import { computed } from 'vue';

ChartJS.register(RadialLinearScale, ArcElement, Tooltip, Legend);

interface Props {
  data: {
    labels: string[];
    datasets: Array<{
      data: number[];
      backgroundColor: string[];
      borderColor?: string[];
      borderWidth?: number;
    }>;
  };
  title?: string;
  height?: number;
  showLegend?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Polar Area Chart',
  height: 350,
  showLegend: true,
});

const chartOptions = computed<ChartOptions<'polarArea'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: props.showLegend,
      position: 'right' as const,
      labels: {
        padding: 20,
        usePointStyle: true,
      },
    },
    title: {
      display: !!props.title,
      text: props.title,
      font: {
        size: 16,
        weight: 'bold',
      },
      padding: {
        bottom: 20,
      },
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const label = context.label || '';
          const value = context.parsed.r;
          const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
          const percentage = ((value / total) * 100).toFixed(1);
          return `${label}: ${value.toLocaleString()} (${percentage}%)`;
        },
      },
    },
  },
  scales: {
    r: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.1)',
      },
      pointLabels: {
        display: false,
      },
      ticks: {
        display: false,
      },
    },
  },
}));
</script>

<template>
  <div :style="{ height: `${height}px` }">
    <PolarArea :data="data" :options="chartOptions" />
  </div>
</template>