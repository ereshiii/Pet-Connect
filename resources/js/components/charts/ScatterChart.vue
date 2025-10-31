<script setup lang="ts">
import {
  Chart as ChartJS,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  ChartOptions,
} from 'chart.js';
import { Scatter } from 'vue-chartjs';
import { computed } from 'vue';

ChartJS.register(LinearScale, PointElement, LineElement, Tooltip, Legend);

interface Props {
  data: {
    datasets: Array<{
      label: string;
      data: Array<{
        x: number;
        y: number;
      }>;
      backgroundColor: string;
      borderColor: string;
      borderWidth?: number;
      pointRadius?: number;
      pointHoverRadius?: number;
    }>;
  };
  title?: string;
  height?: number;
  xAxisLabel?: string;
  yAxisLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Scatter Chart',
  height: 400,
  xAxisLabel: 'X Axis',
  yAxisLabel: 'Y Axis',
});

const chartOptions = computed<ChartOptions<'scatter'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
    },
    title: {
      display: !!props.title,
      text: props.title,
      font: {
        size: 16,
        weight: 'bold',
      },
    },
    tooltip: {
      mode: 'nearest' as const,
      intersect: false,
      callbacks: {
        label: function(context) {
          return `${context.dataset.label}: (${context.parsed.x}, ${context.parsed.y})`;
        },
      },
    },
  },
  scales: {
    x: {
      type: 'linear' as const,
      position: 'bottom' as const,
      title: {
        display: true,
        text: props.xAxisLabel,
      },
      grid: {
        color: 'rgba(0, 0, 0, 0.1)',
      },
    },
    y: {
      title: {
        display: true,
        text: props.yAxisLabel,
      },
      grid: {
        color: 'rgba(0, 0, 0, 0.1)',
      },
    },
  },
  interaction: {
    intersect: false,
  },
}));
</script>

<template>
  <div :style="{ height: `${height}px` }">
    <Scatter :data="data" :options="chartOptions" />
  </div>
</template>