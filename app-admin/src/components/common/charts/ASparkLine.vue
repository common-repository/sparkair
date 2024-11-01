<script setup lang="ts">
import { computed } from "vue";
import ABaseChart from "./ABaseChart.vue";
import type { ApexOptions } from "apexcharts";
import { mergeDeep } from "@/services/utils";

const props = defineProps<{
  series: ApexAxisChartSeries;
  options?: ApexOptions;
}>();

const chartOptions: ApexOptions = {
  chart: {
    type: "area",
    sparkline: {
      enabled: true,
    },
  },
};
const mergedOptions = computed(() => mergeDeep(chartOptions, props.options));
</script>

<template>
  <ABaseChart :series="series" :options="mergedOptions"> </ABaseChart>
</template>
