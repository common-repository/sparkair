<script setup lang="ts">
import { computed, ref } from "vue";
import AConfirmDialog from "../common/AConfirmDialog.vue";
import ATextArea from "../common/ATextArea.vue";
import useRegistryStore, { SparkPlugin } from "@/stores/registry";

const props = defineProps<{
  pluginSlug: string;
  deactivateUrl: string;
}>();

const isOpen = ref(true);

const cancel = () => {
  setTimeout(() => {
    document.dispatchEvent(new Event("sparkwoo:deactivate-cancel"));
  }, 200);
};

const sendingFeedback = ref(false);

const isSkip = computed(() => reason.value?.id === "skip");

const confirm = async () => {
  if (!isSkip.value) {
    sendingFeedback.value = true;
    await plugin.value.api.post("/deactivate/feedback", {
      reason: reason.value?.text,
      elaborate: elaborate.value,
    });
    sendingFeedback.value = false;
  }
  window.location.href = props.deactivateUrl;
};

const registryStore = useRegistryStore();

const plugin = computed<SparkPlugin>(() => {
  return registryStore.plugins[props.pluginSlug];
});

const options = computed(() =>
  [
    { id: "skip", text: "Skip" },
    { id: "alternative", text: "I found a better alternative" },
    { id: "stopped-working", text: "The plugin stopped working" },
    { id: "no-need", text: "I no longer need it" },
    { id: "couldnt-working", text: "I couldn't get the plugin to work" },
    { id: "broke-site", text: "The plugin broke my site" },
    { id: "temporary", text: "I am deactivating temporarily" },
    { id: "pro", text: "I am upgrading to Pro" },
    { id: "other", text: "Other" },
  ].filter(
    (option) =>
      option.id !== "pro" || (option.id === "pro" && !plugin.value.meta.isPro)
  )
);

const reason = ref<{ id: string; text: string }>(options.value[0]);
const elaborate = ref("");
</script>

<template>
  <AConfirmDialog
    v-model="isOpen"
    title="Quick feedback"
    :confirm-text="isSkip ? 'Skip & deactivate' : 'Send & deactivate'"
    @cancel="cancel"
    @confirm="confirm"
    :loading="sendingFeedback"
  >
    Please help us continuously improve by providing feedback on why you are
    deactivating
    <strong>{{ plugin.meta.name }}</strong
    >.

    <div class="mt-6 space-y-2">
      <div v-for="option in options" :key="option.id" class="flex items-center">
        <input
          :id="option.id"
          name="notification-method"
          type="radio"
          v-model="reason"
          class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600"
          :value="option"
        />
        <label
          :for="option.id"
          class="ml-3 block text-sm font-medium leading-6 text-gray-900"
          >{{ option.text }}</label
        >
      </div>
    </div>

    <div class="mt-6">
      <ATextArea
        label="Could you please elaborate your choice?"
        v-model="elaborate"
      ></ATextArea>
    </div>
  </AConfirmDialog>
</template>
