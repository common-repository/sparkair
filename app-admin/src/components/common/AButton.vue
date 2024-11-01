<script setup lang="ts">
import { computed } from "vue";
import { useRouter, type RouteLocationRaw } from "vue-router";
import ALoadingIcon from "./ALoadingIcon.vue";

const props = defineProps<{
  icon?: any;
  appendIcon?: any;
  iconOnly?: boolean;
  disabled?: boolean;
  loading?: boolean;
  small?: boolean;
  large?: boolean;
  outlined?: boolean;
  text?: boolean;
  block?: boolean;
  href?: string;
  target?: string;
  to?: RouteLocationRaw;
}>();

const router = useRouter();

const loadingOrDisabled = computed(() => {
  return props.disabled || props.loading;
});

const sizeClasses = computed(() => {
  let c = `${props.iconOnly ? "px-2" : "px-4"} py-2 text-sm leading-5`;
  if (props.small) {
    c = `${props.iconOnly ? "px-0.5 py-0.5" : "px-2.5 py-1.5"}  text-xs `;
  } else if (props.large) {
    c = `${
      props.iconOnly ? "px-0.5 py-0.5" : "px-3.5 py-2.5"
    }  text-base leading-6`;
  }
  return c;
});

const calculateColorClasses = computed(() => {
  let disabledClasses = loadingOrDisabled.value
    ? "opacity-80 pointer-events-none cursor-not-allowed"
    : "";

  let colors = disabledClasses;
  if (props.text) {
    colors = `${colors} text-white`;
  } else if (props.outlined) {
    colors = `${colors} text-gray-500 hover:bg-gray-50 hover:text-gray-600 border border-gray-300 bg-white`;
  } else if (props.iconOnly) {
    colors = `${colors} text-aprimary-500 hover:bg-aprimary-50 hover:text-aprimary-600 bg-white`;
  } else {
    colors = `${colors} bg-aprimary-600 hover:bg-aprimary-700 border-transparent text-white shadow-sm`;
  }
  return colors;
});

const buttonClick = () => {
  if (props.disabled) return;
  if (props.to) {
    router.push(props.to);
    return;
  }
  if (props.href) {
    if (props.target === "_blank") {
      window.open(props.href, "_blank");
    } else {
      window.location.href = props.href;
    }
  }
};
</script>

<template>
  <button
    type="button"
    class="relative inline-flex items-center justify-center gap-x-2 rounded-md font-medium"
    :class="{
      'w-full': block,
      [`${sizeClasses} ${calculateColorClasses}`]: true,
    }"
    @click="buttonClick"
    :disabled="loadingOrDisabled"
  >
    <component
      v-if="icon"
      :is="icon"
      :class="[iconOnly ? 'px-0.5' : '-ml-0.5', loading ? 'invisible' : '']"
      class="h-5 w-5"
      aria-hidden="true"
    />
    <span
      class="whitespace-nowrap"
      v-if="$slots.default"
      :class="{ invisible: loading }"
    >
      <slot></slot>
    </span>
    <div
      v-if="loading"
      class="absolute inset-2 flex items-center justify-center"
    >
      <ALoadingIcon
        :color="outlined ? 'text-aprimary-700' : 'text-white'"
        size="h-5 w-5"
      ></ALoadingIcon>
    </div>

    <component
      v-if="appendIcon"
      :is="appendIcon"
      class="-mr-0.5 h-5 w-5"
      aria-hidden="true"
      :class="{ invisible: loading }"
    />
  </button>
</template>
