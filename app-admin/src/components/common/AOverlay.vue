<script setup lang="ts">
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { useVModel } from "@vueuse/core";

const props = defineProps<{
  modelValue: boolean;
  persistent?: boolean;
  absolute?: boolean;
}>();

const emit = defineEmits(["update:modelValue"]);

const open = useVModel(props, "modelValue", emit);

const closeModal = () => {
  if (!props.persistent) {
    open.value = false;
  }
};
</script>

<template>
  <TransitionRoot appear :show="open" as="template">
    <div
      @close="closeModal"
      :class="{
        'absolute inset-0 z-30': absolute,
        'relative z-50': !absolute,
      }"
    >
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="inset-0 z-30 backdrop-blur-sm"
          :class="{
            'fixed bg-slate-500/5': !absolute,
            'absolute bg-white/5': absolute,
          }"
        ></div>
      </TransitionChild>

      <div
        class="inset-0 z-40 overflow-y-auto"
        :class="{
          fixed: !absolute,
          absolute: absolute,
        }"
      >
        <div class="flex min-h-full items-center justify-center p-2">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <div>
              <slot name="default"></slot>
            </div>
          </TransitionChild>
        </div>
      </div>
    </div>
  </TransitionRoot>
</template>
