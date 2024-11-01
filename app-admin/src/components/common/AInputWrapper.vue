<template>
  <div>
    <label
      v-if="label"
      :for="id"
      class="mb-2 block text-sm font-medium text-gray-700"
      :class="labelClass"
    >
      {{ label }}
    </label>
    <div class="relative">
      <slot name="wrapper">
        <div
          class="flex items-center rounded-md border shadow-sm focus-within:ring-1"
          :class="{
            'hover-within:cursor-not-allowed cursor-not-allowed bg-gray-100':
              readonly || loading,
            'bg-white': !readonly && !loading,
            'border-red-300  text-red-900 placeholder:text-red-300 focus-within:border-red-500  focus-within:ring-red-500':
              errorMessage,
            'border-gray-300 focus-within:border-aprimary-500 focus-within:ring-aprimary-500':
              !errorMessage,
          }"
          :disabled="readonly || loading"
        >
          <div v-if="prependIcon" class="pointer-events-none flex pl-3">
            <component
              :is="prependIcon"
              class="h-5 w-5 text-gray-400"
              aria-hidden="true"
            ></component>
          </div>
          <div
            v-if="prefix"
            class="grow-0 select-none items-center text-gray-500 sm:text-sm"
            :class="{
              'pl-2': prependIcon,
              'pl-2.5': !prependIcon,
            }"
          >
            {{ prefix }}
          </div>
          <div class="relative flex grow">
            <div
              v-if="loading"
              class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
            >
              <ALoadingIcon class="text-aprimary-500" aria-hidden="true" />
            </div>
            <slot name="default" :id="id"></slot>
            <div
              v-if="errorMessage"
              class="pointer-events-none absolute inset-y-0 flex items-center"
              :class="{
                'right-0 pr-3': align === 'left',
                'left-0 pl-3': align === 'right',
              }"
            >
              <ExclamationCircleIcon
                class="h-5 w-5 text-red-500"
                aria-hidden="true"
              />
            </div>
          </div>
          <div
            v-if="suffix"
            class="flex shrink select-none items-center text-gray-500 sm:text-sm"
            :class="{
              'pr-3': !$slots.append,
              'pr-1': $slots.append,
            }"
          >
            {{ suffix }}
          </div>
          <div
            v-if="$slots.prependButtons"
            class="flex shrink select-none items-center pr-2 text-gray-500 sm:text-sm"
          >
            <slot name="prependButtons"></slot>
          </div>
          <button
            v-if="closeable"
            @click="emit('close')"
            type="button"
            class="group relative -ml-px inline-flex items-center space-x-2 rounded-r-md border-gray-300 p-2 text-sm font-medium text-gray-700"
          >
            <XMarkIcon
              class="-ml-0.5 h-5 w-5 text-gray-400 transition-opacity group-hover:opacity-60"
              aria-hidden="true"
            />
          </button>
          <ATooltip
            :text="readonly || loading ? undefined : appendButtonTooltip"
          >
            <button
              v-if="appendButtonIcon"
              @click="emit('click:append-button')"
              type="button"
              class="relative -ml-px inline-flex items-center space-x-2 rounded-r-md border-l border-gray-300 p-2 text-sm font-medium text-gray-700"
              :disabled="readonly || loading"
              :class="{
                'cursor-not-allowed bg-gray-100': readonly || loading,
                'hover:bg-gray-100': !(readonly || loading),
              }"
            >
              <component
                :is="appendButtonIcon"
                class="-ml-0.5 h-5 w-5 text-gray-400"
                aria-hidden="true"
              />
            </button>
          </ATooltip>
          <template v-if="$slots.append">
            <slot name="append"></slot>
          </template>
        </div>
      </slot>
      <div v-if="$slots.after">
        <slot name="after"></slot>
      </div>
      <div
        v-if="!hideDetails"
        class="mb-0 mt-1 text-xs"
        :class="{
          'text-red-600': errorMessage,
          'text-gray-500': !errorMessage,
        }"
      >
        <template v-if="errorMessage">
          {{ errorMessage }}
        </template>
        <template v-else-if="hint">
          {{ hint }}
        </template>
        &nbsp;
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { XMarkIcon } from "@heroicons/vue/24/outline";
import { ExclamationCircleIcon } from "@heroicons/vue/24/solid";
import ALoadingIcon from "./ALoadingIcon.vue";
import ATooltip from "./ATooltip.vue";

const props = defineProps({
  label: { type: String },
  closeable: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  prependIcon: { type: [Object], default: null },
  appendButtonIcon: { type: [Object], default: null },
  appendButtonTooltip: { type: String, default: null },
  prefix: { type: String, default: "" },
  suffix: { type: String, default: "" },
  labelClass: { type: [String, Object, Array], default: null },
  errorMessage: { type: String, default: null },
  hint: { type: String, default: null },
  hideDetails: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  align: { type: String, default: "left" },
});

const emit = defineEmits(["close", "click:append-button"]);
const id = (Math.random() + 1).toString(36).substring(7);
</script>
