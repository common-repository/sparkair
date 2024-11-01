<script setup lang="ts">
import { computed } from "vue";
import {
  RadioGroup,
  RadioGroupDescription,
  RadioGroupLabel,
  RadioGroupOption,
} from "@headlessui/vue";
import { CheckCircleIcon } from "@heroicons/vue/20/solid";
import ABadge from "@/components/common/ABadge.vue";
import BadgeFreePro from "@/components/BadgeFreePro.vue";
import ACardContent from "../common/ACardContent.vue";
import { useVModel } from "@vueuse/core";
import useProductRecommendationsStore, {
  type IProductRecommendation,
} from "@/stores/product-recommendations";
import useRegistryStore, {
  type IProductsManager,
  type IProductsManagerExtended,
} from "@/stores/registry";
import { storeToRefs } from "pinia";
import AAlert from "../common/AAlert.vue";
import ACard from "../common/ACard.vue";
import {
  ArrowTopRightOnSquareIcon,
  SparklesIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps<{
  modelValue: IProductRecommendation;
}>();
const emit = defineEmits(["update:modelValue"]);

const registryStore = useRegistryStore();
const { allSparkPlugins } = storeToRefs(registryStore);

const productRecommendationPost = useVModel(props, "modelValue", emit);

const productRecommendationsStore = useProductRecommendationsStore();
productRecommendationsStore.fetch();
const { getBy } = storeToRefs(productRecommendationsStore);

const productsManagers = registryStore.getProductsManagers();

const maxReached = computed(
  () => (productsManagerExtended: IProductsManagerExtended) => {
    if (productsManagerExtended.isPro) return false;
    if (
      productRecommendationPost.value.id &&
      productRecommendationPost.value.productsManager ===
        productsManagerExtended.slug
    )
      return false;
    return (
      getBy.value("productsManager", productsManagerExtended.slug).length > 0
    );
  }
);

// Set first available as the product manager
const initialize = () => {
  if (
    !productRecommendationPost.value.productsManager ||
    maxReached.value(
      productsManagers[productRecommendationPost.value.productsManager]
    )
  ) {
    for (const pluginMeta of allSparkPlugins.value) {
      const slug = pluginMeta.extra.productsManager?.slug;
      if (!slug) continue;
      if (
        pluginMeta.extra.productsManager &&
        pluginMeta.installed &&
        !maxReached.value(productsManagers[slug])
      ) {
        productRecommendationPost.value.productsManager = slug;
        break;
      }
    }
  }
};

defineExpose({ initialize });

const productManagersOrdered = computed(() => {
  return Object.values(productsManagers).sort(
    (a: IProductsManagerExtended, b: IProductsManagerExtended) => {
      if (a.slug === "sparkair") return -1;
      if (b.slug === "sparkair") return 1;
      return 0;
    }
  );
});
</script>

<template>
  <ACardContent class="-mb-6">
    Select the type of recommended products to show
  </ACardContent>
  <ACardContent class="space-y-6">
    <RadioGroup v-model="productRecommendationPost.productsManager">
      <div
        class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4 lg:grid-cols-3"
      >
        <div
          v-for="productsManager in productManagersOrdered"
          :key="productsManager.slug"
          class="relative"
        >
          <div
            v-if="productsManager.installed && maxReached(productsManager)"
            class="absolute inset-0 z-10 flex flex-col items-center justify-center p-10 text-center"
          >
            <div
              class="mb-3 rounded-lg border border-red-700 bg-red-100 p-2 text-red-700 opacity-90"
            >
              Only one product recommendation per type is available in the free
              version, want more? Go pro!
            </div>
            <BadgeFreePro
              go
              pro
              :link="
                registryStore.getPluginByProductsManagerSlug(
                  productsManager.slug
                )?.meta.websiteUrl
              "
            ></BadgeFreePro>
          </div>
          <div
            class="absolute bottom-4 right-4 z-10"
            v-if="!productsManager.installed || !maxReached(productsManager)"
          >
            <BadgeFreePro
              v-if="productsManager.installed && productsManager.isPro"
              pro
              check
            ></BadgeFreePro>
            <BadgeFreePro
              v-else-if="productsManager.installed && !productsManager.isPro"
              pro
              :link="
                registryStore.getPluginMetaByProductsManagerSlug(
                  productsManager.slug
                )?.websiteUrl
              "
              go
            ></BadgeFreePro>
            <ABadge v-else-if="productsManager.soon" color="indigo" large>
              Soon!
            </ABadge>
            <a
              v-else
              :href="
                registryStore.getPluginMetaByProductsManagerSlug(
                  productsManager.slug
                )?.websiteUrl
              "
              target="_blank"
              class="inline-block rounded-full ring-0"
            >
              <ABadge
                large
                color="green"
                :append-icon="ArrowTopRightOnSquareIcon"
                class="cursor-pointer"
              >
                Get
                {{
                  registryStore
                    .getPluginMetaByProductsManagerSlug(productsManager.slug)
                    ?.name.split(" ")[0]
                }}
              </ABadge>
            </a>
          </div>
          <RadioGroupOption
            as="template"
            :value="productsManager.slug"
            :disabled="
              !productsManager.installed || maxReached(productsManager)
            "
            v-slot="{ checked, active, disabled }"
          >
            <div
              :class="{
                'border-1 border-white ring-2 ring-aprimary-600': checked,
                'border-gray-300': !checked,
                'cursor-not-allowed opacity-40': disabled,
                'cursor-pointer': !disabled,
                'bg-white': productsManager.slug !== 'sparkair',
                'dark bg-aprimary-700': productsManager.slug === 'sparkair',
              }"
              class="flex h-full rounded-lg border p-4 shadow-sm focus:outline-none"
            >
              <span class="flex flex-1">
                <span class="flex flex-col">
                  <RadioGroupLabel
                    as="span"
                    class="block text-sm font-medium"
                    :class="{
                      'text-white': productsManager.slug === 'sparkair',
                      'text-gray-900': productsManager.slug !== 'sparkair',
                    }"
                    >{{ productsManager.title }}</RadioGroupLabel
                  >
                  <RadioGroupDescription
                    as="span"
                    class="mb-10 mt-1 flex items-center text-sm"
                    :class="{
                      'text-aprimary-100': productsManager.slug === 'sparkair',
                      'text-gray-500': productsManager.slug !== 'sparkair',
                    }"
                    >{{ productsManager.description }}</RadioGroupDescription
                  >
                </span>
              </span>
              <CheckCircleIcon
                :class="{
                  invisible: !checked,
                  'text-white': productsManager.slug === 'sparkair',
                  'text-aprimary-600': productsManager.slug !== 'sparkair',
                }"
                class="h-6 w-6"
                aria-hidden="true"
              ></CheckCircleIcon>
              <span
                class="pointer-events-none absolute -inset-px rounded-lg border-transparent"
                aria-hidden="true"
              >
                <SparklesIcon
                  class="absolute bottom-1 left-1 h-32 w-32 text-white/10"
                ></SparklesIcon>
              </span>
            </div>
          </RadioGroupOption>
        </div>
      </div>
    </RadioGroup>
    <ACard>
      <AAlert
        type="spark"
        :image="`${registryStore.imagePrefix}/sparkplugins-icon.svg`"
      >
        <h3 class="text-sm font-medium text-aprimary-800">
          More Choices Coming Soon!
        </h3>
        <div class="text-sm text-aprimary-700">
          <div>
            The SparkPlugins team is developing more options for you to choose
            from! Have a product recommendation suggestion? Contact us through
            support!
          </div>
        </div>
      </AAlert>
    </ACard>
  </ACardContent>
</template>
