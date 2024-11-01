<script setup lang="ts">
import { ref } from "vue";
import { DialogTitle } from "@headlessui/vue";
import AButton from "./AButton.vue";
import AOverlay from "./AOverlay.vue";
import ACardActions from "./ACardActions.vue";
import ACard from "./ACard.vue";
import ACardHeader from "./ACardHeader.vue";
import ACardContent from "./ACardContent.vue";
import { useVModel } from "@vueuse/core";

const props = withDefaults(
  defineProps<{
    modelValue: boolean;
    title: string;
    confirmText?: string;
    cancelText?: string;
    loading?: boolean;
  }>(),
  { confirmText: "OK", cancelText: "Cancel", loading: false }
);

const emit = defineEmits(["update:modelValue", "confirm", "cancel"]);
const isOpen = useVModel(props, "modelValue", emit);

const closeModal = () => {
  isOpen.value = false;
  emit("cancel");
};
const openModal = () => {
  isOpen.value = true;
};

const confirm = () => {
  emit("confirm");
  isOpen.value = false;
};
</script>

<template>
  <slot name="activator" :on="{ click: openModal }"></slot>
  <AOverlay v-model="isOpen">
    <ACard class="w-full max-w-md transform">
      <ACardHeader>
        <slot name="title">{{ title }}</slot>
      </ACardHeader>
      <ACardContent class="-mt-6">
        <div class="text-sm text-gray-500">
          <slot></slot>
        </div>
      </ACardContent>
      <ACardActions>
        <div class="grow"></div>
        <AButton @click="closeModal" outlined :loading="loading">
          {{ cancelText }}
        </AButton>
        <AButton @click="confirm" :loading="loading">
          {{ confirmText }}
        </AButton>
      </ACardActions>
    </ACard>
  </AOverlay>
</template>
