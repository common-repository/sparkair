<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-40" @close="open = false">
      <div class="fixed inset-0 overflow-hidden">
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="transition-opacity ease-linear duration-300"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div
            class="absolute inset-0 overflow-hidden bg-gray-700/30 backdrop-blur"
          >
            <div
              class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full"
            >
              <slot name="loading-overlay">
                <ALoadingOverlay :model-value="loading"> </ALoadingOverlay>
              </slot>
              <TransitionChild
                as="template"
                enter="transform transition ease-in-out duration-300 sm:duration-300"
                enter-from="translate-x-full"
                enter-to="translate-x-0"
                leave="transform transition ease-in-out duration-300 sm:duration-300"
                leave-from="translate-x-0"
                leave-to="translate-x-full"
              >
                <DialogPanel
                  class="pointer-events-auto w-screen max-w-2xl"
                  :class="{
                    'max-w-2xl': !wide,
                    'max-w-[80vw]': wide,
                  }"
                >
                  <div class="flex h-full flex-col shadow-xl">
                    <div
                      class="sticky top-0 border-b bg-gray-50 px-4 py-4 sm:px-6"
                    >
                      <div class="flex items-start justify-between">
                        <DialogTitle
                          class="text-lg font-medium text-gray-900"
                          :class="{ 'blur-sm': demo }"
                        >
                          {{ title }}
                        </DialogTitle>
                        <div class="ml-3 flex h-7 items-center">
                          <AButtonIcon :icon="XMarkIcon" @click="open = false">
                          </AButtonIcon>
                        </div>
                      </div>
                    </div>
                    <div
                      class="flex-1 overflow-y-scroll bg-white"
                      :class="{ 'blur-sm': demo }"
                    >
                      <slot name="default"></slot>
                    </div>
                    <div
                      v-if="$slots.footer"
                      class="sticky bottom-0 border-t bg-gray-50 px-4 py-2 sm:px-6"
                    >
                      <div class="flex justify-items-end space-x-2">
                        <slot name="footer"> </slot>
                      </div>
                    </div>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";
import { useVModel } from "@vueuse/core";
import AButtonIcon from "@/components/common/AButtonIcon.vue";
import ALoadingOverlay from "./ALoadingOverlay.vue";

const props = withDefaults(
  defineProps<{
    title?: string;
    modelValue: boolean;
    demo?: boolean;
    loading?: boolean;
    wide?: boolean;
  }>(),
  {
    loading: false,
  }
);
const emit = defineEmits(["update:modelValue"]);

const open = useVModel(props, "modelValue", emit);
</script>
