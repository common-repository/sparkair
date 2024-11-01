<script setup lang="ts">
import { computed, ref } from "vue";
import { CheckCircleIcon, CheckIcon } from "@heroicons/vue/24/solid";
import AButton from "@/components/common/AButton.vue";
import ACardActions from "@/components/common/ACardActions.vue";
import { useVModel } from "@vueuse/core";
import { useRouter, useRoute } from "vue-router";
import ALoadingOverlay from "../common/ALoadingOverlay.vue";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/vue/24/outline";

export interface IWizardStep {
  key: string;
  name: string;
  description: string;
  nextDisabled?: boolean;
}

const props = defineProps<{
  modelValue: any;
  steps: IWizardStep[];
  create: Function;
  update: Function;
  loading: boolean;
}>();

const router = useRouter();
const route = useRoute();

const emit = defineEmits(["update:modelValue"]);

const magic = useVModel(props, "modelValue", emit);

const currentStepNumber = ref(0);

const currentStep = computed(() => props.steps[currentStepNumber.value]);

const lastStep = computed(
  () => currentStepNumber.value === props.steps.length - 1
);

const save = async () => {
  if (magic?.value.id) {
    await props.update(magic.value.id, magic.value);
  } else {
    const object = await props.create(magic.value);
    router.push({
      name: String(route.name),
      params: { objectId: object.id },
    });
    magic.value.id = object.id;
  }
};

const scrollToTop = () => {
  const element = document.getElementById("nav");
  if (!element) return;
  element.scrollIntoView({
    behavior: "smooth",
    block: "start",
  });
};

const previous = async () => {
  scrollToTop();
  currentStepNumber.value = currentStepNumber.value - 1;
};

const next = async () => {
  scrollToTop();
  if (currentStepNumber.value + 1 === props.steps.length) {
    await save();
  }
  currentStepNumber.value = currentStepNumber.value + 1;
};
</script>

<template>
  <div class="relative">
    <ALoadingOverlay :model-value="loading"></ALoadingOverlay>
    <div class="lg:border-b lg:border-gray-200">
      <nav id="nav" class="mx-auto max-w-7xl" aria-label="Progress">
        <ol
          role="list"
          class="overflow-hidden rounded-md lg:flex lg:rounded-none lg:border-gray-200"
        >
          <li
            v-for="(step, stepIdx) in steps"
            :key="step.key"
            class="relative mb-0 overflow-hidden lg:flex-1"
          >
            <div
              :class="[
                stepIdx === 0 ? 'rounded-t-md border-b-0' : '',
                stepIdx === steps.length - 1 ? 'rounded-b-md border-t-0' : '',
                'overflow-hidden border border-gray-200 lg:border-0',
              ]"
            >
              <span class="group">
                <span
                  :class="{
                    'bg-transparent  lg:bottom-0 lg:top-auto lg:h-1 lg:w-full':
                      currentStepNumber !== stepIdx,
                    'bg-aprimary-600 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full':
                      currentStepNumber === stepIdx,
                    'group-hover:bg-gray-200': currentStepNumber > stepIdx,
                  }"
                  class="absolute left-0 top-0 h-full w-1"
                  aria-hidden="true"
                ></span>
                <span
                  :class="[
                    stepIdx !== 0 ? 'lg:pl-9' : '',
                    currentStepNumber > stepIdx ? 'cursor-pointer' : '',
                    'flex items-start px-6 py-5 text-sm font-medium',
                  ]"
                  @click="
                    () => {
                      if (currentStepNumber > stepIdx) {
                        currentStepNumber = stepIdx;
                      }
                    }
                  "
                >
                  <span class="flex-shrink-0">
                    <span
                      class="flex h-10 w-10 items-center justify-center rounded-full"
                      :class="{
                        'bg-aprimary-600': currentStepNumber > stepIdx,
                        'border-2 border-aprimary-600':
                          currentStepNumber === stepIdx,
                        'border-2 border-gray-300': currentStepNumber < stepIdx,
                      }"
                    >
                      <CheckIcon
                        v-if="currentStepNumber > stepIdx"
                        class="h-6 w-6 text-white"
                        aria-hidden="true"
                      />
                      <span
                        v-else
                        :class="{
                          'text-aprimary-600': currentStepNumber === stepIdx,
                          'text-gray-500': currentStepNumber < stepIdx,
                        }"
                        >{{ String(stepIdx + 1).padStart(2, "0") }}</span
                      >
                    </span>
                  </span>
                  <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                    <span
                      class="text-sm font-medium"
                      :class="{
                        'text-aprimary-600': currentStepNumber === stepIdx,
                        'text-gray-500': currentStepNumber < stepIdx,
                      }"
                      >{{ step.name }}</span
                    >
                    <span class="text-sm font-medium text-gray-500">{{
                      step.description
                    }}</span>
                  </span>
                </span>
              </span>
              <template v-if="stepIdx !== 0">
                <!-- Separator -->
                <div
                  class="absolute inset-0 left-0 top-0 hidden w-3 lg:block"
                  aria-hidden="true"
                >
                  <svg
                    class="h-full w-full text-gray-300"
                    viewBox="0 0 12 82"
                    fill="none"
                    preserveAspectRatio="none"
                  >
                    <path
                      d="M0.5 0V31L10.5 41L0.5 51V82"
                      stroke="currentcolor"
                      vector-effect="non-scaling-stroke"
                    />
                  </svg>
                </div>
              </template>
            </div>
          </li>
        </ol>
      </nav>
    </div>
    <div v-if="currentStepNumber < steps.length">
      <div v-for="(step, stepIdx) in steps" :key="step.key">
        <slot v-if="currentStepNumber === stepIdx" :name="`step.${step.key}`">
        </slot>
      </div>
    </div>
    <div v-else class="relative px-6 py-32 sm:px-12 sm:py-40 lg:px-16">
      <div
        class="relative mx-auto flex max-w-3xl flex-col items-center text-center"
      >
        <CheckCircleIcon class="h-48 text-green-600"></CheckCircleIcon>
        <div class="mb-8 mt-3 text-xl">
          You have finished setting up this product recommendation, great job!
        </div>
        <div class="mt-5">
          <AButton :to="{ name: 'product-recommendations' }" large>
            View all product recommendations
          </AButton>
        </div>
      </div>
    </div>
  </div>
  <ACardActions class="sticky bottom-0 z-10">
    <AButton
      v-if="currentStepNumber > 0"
      @click="previous"
      outlined
      :icon="ChevronLeftIcon"
    >
      Previous
    </AButton>
    <div class="grow"></div>
    <AButton
      v-if="currentStepNumber < steps.length"
      data-cy="next"
      @click="next"
      :disabled="steps[currentStepNumber]?.nextDisabled"
      :append-icon="lastStep ? CheckIcon : ChevronRightIcon"
    >
      {{ lastStep ? "Finish" : "Next" }}
    </AButton>
    <div
      class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 transform items-center space-x-2 md:hidden"
    >
      <div v-for="(step, stepIdx) in steps" :key="step.key">
        <div
          class="h-2 w-2 rounded-full"
          :class="{
            'bg-aprimary-300': currentStepNumber > stepIdx,
            'bg-aprimary-600': currentStepNumber === stepIdx,
            'bg-gray-300': currentStepNumber < stepIdx,
          }"
        ></div>
      </div>
    </div>
  </ACardActions>
</template>
