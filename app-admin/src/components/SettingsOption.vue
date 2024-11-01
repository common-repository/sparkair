<script setup lang="ts">
import ASwitch from "./common/ASwitch.vue";
import ATextField from "./common/ATextField.vue";
import { computed, ref, watch } from "vue";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import ACard from "./common/ACard.vue";
import ACardContent from "./common/ACardContent.vue";
import ACardActions from "./common/ACardActions.vue";
import AButton from "./common/AButton.vue";
import useOptionsStore from "@/stores/options";
import { storeToRefs } from "pinia";
import { SparkPlugin } from "@/stores/registry";
import { ArrowRightIcon } from "@heroicons/vue/20/solid";
import { ArrowDownIcon } from "@heroicons/vue/24/outline";
import { useRoute } from "vue-router";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
  type: {
    validator(value: string) {
      return ["boolean", "string", "button"].includes(value);
    },
    default: "string",
  },
  label: {
    type: String,
    required: true,
  },
  option: {
    type: String,
  },
  plugin: {
    type: SparkPlugin,
    required: true,
  },
  loading: {
    type: Boolean,
  },
  href: {
    type: String,
  },
  done: {
    type: Boolean,
  },
});

const emit = defineEmits(["saved"]);

const optionsStore = useOptionsStore();
const { options, fetching, updating } = storeToRefs(optionsStore);

const pluginOption = computed(() =>
  props.option ? props.plugin.options[props.option] : null
);

const value = computed(() => {
  if (!pluginOption.value) return null;
  return options.value[props.plugin.meta.slug]?.[pluginOption.value.name];
});
const magic = ref(value.value);

const innerDone = ref(false);
let innerDoneTimeout: number | undefined;

const empty = () => {
  if (props.type === "boolean") {
    magic.value = false;
  } else if (props.type === "string") {
    magic.value = "";
  }
};

const reset = (callback = () => ({})) => {
  magic.value = value.value;
  callback();
};

const submit = async (callback = () => ({})) => {
  clearTimeout(innerDoneTimeout);
  if (props.href) {
    window.open(props.href, "_blank");
    return;
  }
  if (pluginOption.value) {
    await optionsStore.update(props.plugin, {
      [pluginOption.value.name]: magic.value,
    });
  }
  innerDone.value = true;
  innerDoneTimeout = setTimeout(() => {
    innerDone.value = false;
  }, 5000);
  emit("saved");
  callback();
};

const saveBoolean = (e: boolean) => {
  magic.value = e;
  submit();
};

watch(
  () => value.value,
  (v) => {
    magic.value = v;
  }
);

defineExpose({ value });

const route = useRoute();

const scrollToHighlight = () => {
  const className = String(route.query.highlight);
  if (className === "") return;
  const element = document.getElementsByClassName(className);
  if (!element.length) return;
  element[0].scrollIntoView({
    behavior: "smooth",
    block: "center",
  });
};
setTimeout(scrollToHighlight, 500);
</script>
<template>
  <div v-if="!props.option || pluginOption" class="flex gap-x-6 pt-3 lg:pt-6">
    <div class="grow lg:flex" :class="props.option">
      <dt class="font-medium text-gray-900 lg:w-64 lg:flex-none">
        <span v-if="type !== 'button'" class="relative">
          {{ label }}
          <span
            class="absolute -left-8 top-1/2 hidden -translate-y-1/2 md:inline-block"
            v-if="option === String($route.query.highlight)"
          >
            <ArrowRightIcon
              class="h-8 w-8 animate-bounce-x text-aprimary-600"
            ></ArrowRightIcon>
          </span>
          <span
            class="absolute -top-8 left-1/2 -translate-x-1/2 md:hidden"
            v-if="option === String($route.query.highlight)"
          >
            <ArrowDownIcon
              class="h-8 w-8 animate-bounce text-aprimary-600"
            ></ArrowDownIcon>
          </span>
        </span>
      </dt>
      <dd class="mt-1 grow">
        <div v-if="type === 'string'" class="line-clamp-1 text-gray-500">
          <template v-if="value">
            {{ value }}
          </template>
          <em v-else class="text-gray-400">Empty</em>
        </div>
        <div v-if="$slots['value-hint']" class="text-xs">
          <slot name="value-hint"> </slot>
        </div>
      </dd>
    </div>
    <div class="flex items-center">
      <template v-if="type === 'boolean'">
        <div class="grow"></div>
        <ASwitch
          :model-value="Boolean(magic)"
          @update:model-value="saveBoolean"
        ></ASwitch>
      </template>
      <Popover
        v-else-if="type === 'string'"
        v-slot="{ open, close }"
        class="relative"
      >
        <PopoverButton
          :class="open ? '' : 'text-opacity-90'"
          class="font-semibold text-aprimary-600 hover:text-aprimary-500"
        >
          {{ !magic ? "Enter" : "Update" }}
        </PopoverButton>
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="translate-y-1 opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="translate-y-0 opacity-100"
          leave-to-class="translate-y-1 opacity-0"
        >
          <PopoverPanel
            class="absolute left-1/2 z-10 mt-3 w-96 -translate-x-80 transform sm:px-0"
          >
            <ACard class="shadow-xl ring-1 ring-black ring-opacity-5">
              <ACardContent
                ><ATextField
                  :model-value="String(magic)"
                  @update:model-value="(v) => (magic = v)"
                  :label="label"
                  @close="empty"
                  class="w-full"
                  closeable
                ></ATextField
              ></ACardContent>
              <ACardActions class="flex justify-end">
                <AButton outlined @click="reset(close)"> Cancel </AButton>
                <AButton
                  :loading="loading || fetching || updating"
                  @click="submit(close)"
                >
                  Save
                </AButton>
              </ACardActions>
            </ACard>
          </PopoverPanel>
        </transition>
      </Popover>
      <template v-else-if="type === 'button'">
        <div class="grow"></div>
        <div class="flex shrink flex-col items-end">
          <AButton
            :loading="loading || fetching || updating"
            @click="submit()"
            >{{ label }}</AButton
          >
          <div v-if="done && innerDone" class="mt-2 flex items-center">
            <CheckCircleIcon
              class="mr-1 h-4 w-4 text-green-600"
            ></CheckCircleIcon>
            <span class="text-xs">Done!</span>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
