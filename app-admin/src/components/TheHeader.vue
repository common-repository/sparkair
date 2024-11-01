<script setup lang="ts">
import { ChevronLeftIcon } from "@heroicons/vue/24/outline";
import useRegistryStore from "@/stores/registry";

const registryStore = useRegistryStore();

const props = defineProps<{
  title: string;
  subtitle: string;
  backTo?: any;
  backText?: string;
}>();

const sizeHeader = () => {
  const backLink = document.getElementById("back-link");
  const header = document.getElementById("header-wrap");
  if (backLink?.style) {
    backLink.style.fontSize = 16 - Math.min(window.scrollY, 30) * 0.1 + "px";
    if (props.backText) {
      backLink.style.marginBottom = -Math.min(window.scrollY, 30) * 0.2 + "px";
    }
  }

  if (header) {
    if (window.scrollY > 40) {
      if (!header.classList.contains("border-b")) {
        header.classList.add("border-b");
      }
    } else {
      header.classList.remove("border-b");
    }
  }
};
sizeHeader();

window.removeEventListener("scroll", sizeHeader);
window.addEventListener("scroll", sizeHeader);
</script>

<template>
  <div
    class="sticky top-0 z-40 -mx-3 pt-2 backdrop-blur-sm sm:top-6 sm:px-3"
    :class="{
      // 'md:py-0': !backText,
      // 'pt-2': backText,
    }"
    style="background-color: rgba(240, 240, 241, 0.8)"
  >
    <div id="header-wrap" class="mb-0 px-3 pb-2 sm:px-0 md:mb-4">
      <div class="flex items-end md:h-5">
        <RouterLink
          id="back-link"
          class="flex items-center space-x-2 text-base text-aprimary-600"
          v-if="backTo"
          :to="backTo"
        >
          <ChevronLeftIcon class="h-4 w-4"></ChevronLeftIcon>
          <span>{{ backText }}</span>
        </RouterLink>
      </div>
      <div
        class="mx-auto flex flex-col md:flex-row md:items-center md:justify-between md:space-x-5"
      >
        <div class="flex space-x-4">
          <div class="inline-flex flex-shrink-0 items-center">
            <a href="https://sparkplugins.com" target="_blank" rel="noreferrer">
              <img
                class="h-10"
                :src="`${registryStore.imagePrefix}/sparkplugins-icon.svg`"
                alt="SparkPlugins.com"
              />
            </a>
          </div>
          <div class="flex items-center space-x-5 truncate">
            <div>
              <span class="text-lg font-bold text-gray-900 sm:text-2xl">
                {{ title }}
              </span>
              <div class="text-sm font-medium text-gray-500">
                {{ subtitle }}
              </div>
            </div>
          </div>
        </div>
        <div class="mt-2 md:mt-0 md:flex-shrink-0">
          <slot name="actions"></slot>
        </div>
      </div>
    </div>
  </div>
</template>
