<script setup lang="ts">
import {
  ArrowTopRightOnSquareIcon,
  CheckCircleIcon,
} from "@heroicons/vue/20/solid";

defineProps<{
  pro?: boolean;
  free?: boolean;
  check?: boolean;
  go?: boolean;
  link?: string;
}>();
</script>

<template>
  <a
    :href="link"
    :target="!!link ? '_blank' : undefined"
    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
    :class="{
      'cursor-pointer': !!link,
      'bg-green-100 text-green-700 hover:bg-green-200 hover:text-green-800':
        pro && check,
      'bg-red-100 text-red-700 hover:bg-red-200 hover:text-red-800':
        pro && !check,
      'bg-aprimary-100 text-aprimary-700': free,
    }"
  >
    <template v-if="pro"> {{ go ? "GO " : "" }}PRO </template>
    <template v-else-if="free"> FREE </template>

    <CheckCircleIcon
      v-if="check"
      class="ml-1 h-5 w-5"
      :class="{
        'text-green-700': pro && check,
        'text-red-700': pro && !check,
        'bg-aprimary-100 text-aprimary-700': free,
      }"
    ></CheckCircleIcon>
    <ArrowTopRightOnSquareIcon
      v-if="link"
      class="ml-1 h-5 w-5"
      :class="{
        'text-green-700': pro && check,
        'text-red-700': pro && !check,
        'bg-aprimary-100 text-aprimary-700': free,
      }"
    ></ArrowTopRightOnSquareIcon>
  </a>
</template>
