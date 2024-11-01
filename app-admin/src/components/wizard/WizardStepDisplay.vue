<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { ChevronUpIcon } from "@heroicons/vue/20/solid";
import ACardContent from "@/components/common/ACardContent.vue";
import WizardStepDisplaySelectableLayout from "./WizardStepDisplaySelectableLayout.vue";
import { useClipboard, useVModel } from "@vueuse/core";
import { type IProductRecommendation } from "@/stores/product-recommendations";
import { computed, watch } from "vue";
import BadgeFreePro from "@/components/BadgeFreePro.vue";
import {
  HomeIcon,
  PhotoIcon,
  CheckCircleIcon,
  ShoppingCartIcon as ShoppingCartIconSolid,
  UserCircleIcon,
} from "@heroicons/vue/24/solid";
import {
  ClipboardDocumentListIcon,
  ClipboardDocumentCheckIcon,
} from "@heroicons/vue/24/outline";
import { CreditCardIcon, ShoppingCartIcon } from "@heroicons/vue/24/outline";
import AAlert from "@/components/common/AAlert.vue";
import ATextField from "@/components/common/ATextField.vue";
import { createShortcode } from "@/services/shortcode";
import useRegistryStore, { type IPlacementHook } from "@/stores/registry";
import ACard from "../common/ACard.vue";
import AOverlay from "../common/AOverlay.vue";
import AButton from "../common/AButton.vue";

const props = defineProps<{
  modelValue: IProductRecommendation;
}>();
const emit = defineEmits(["update:modelValue"]);

const productRecommendationPost = useVModel(props, "modelValue", emit);

const registryStore = useRegistryStore();

const getPluginMeta = () => {
  return registryStore.getPluginByProductsManagerSlug(
    productRecommendationPost.value.productsManager
  )?.meta;
};

const isPro = computed(
  () =>
    registryStore.getProductsManagers()[
      productRecommendationPost.value.productsManager
    ].isPro
);

const selectedHook = computed(
  () => productRecommendationPost.value.pageHooks[0]
);

const maxReached = computed(() => (hook: any) => {
  if (isPro.value) return false;
  if (hook.key === selectedHook.value) return false;
  return productRecommendationPost.value.pageHooks.length > 0;
});

watch(productRecommendationPost, () => {
  if (!isPro.value && productRecommendationPost.value.pageHooks.length > 1) {
    productRecommendationPost.value.pageHooks =
      productRecommendationPost.value.pageHooks.slice(0, 1);
  }
});

const isChecked = (hook: IPlacementHook) =>
  productRecommendationPost.value.pageHooks.includes(hook.key);

const mayAdd = (hook: IPlacementHook) =>
  !(
    !isPro.value &&
    !isChecked(hook) &&
    productRecommendationPost.value.pageHooks.length > 0
  );

const addHook = (hook: IPlacementHook) => {
  if (!mayAdd(hook)) return;

  const newPageHooks = productRecommendationPost.value.pageHooks.filter(
    (i) => i !== hook.key
  );
  if (!isChecked(hook)) {
    newPageHooks.push(hook.key);
  }
  productRecommendationPost.value.pageHooks = newPageHooks;
};

const shortcode = computed(() => {
  const sc =
    selectedRecommendationsPlugin.value?.meta.extra.productsManager?.shortcode;
  if (!sc || !productRecommendationPost.value.id) return "";
  return createShortcode(sc, { id: productRecommendationPost.value.id });
});
const { copy, copied } = useClipboard();

const selectedRecommendationsPlugin = computed(() => {
  return registryStore.getPluginByProductsManagerSlug(
    productRecommendationPost.value.productsManager
  );
});

productRecommendationPost.value.pageHooks =
  productRecommendationPost.value.pageHooks.filter((h) =>
    selectedRecommendationsPlugin.value?.productRecommendations?.placementHooks
      .map((ph) => ph.key)
      .includes(h)
  );

const displayVariants = [
  {
    title:
      "Display the recommendations the modern way using <strong>blocks</strong>",
    key: "blocks",
    alert:
      "Make sure your theme supports blocks, otherwise you will not be able to use this feature.",
    pro: true,
  },
  {
    title: "Display the recommendations using a <strong>shortcode<strong>",
    key: "shortcode",
    pro: false,
  },
  {
    title:
      "Directly inject the recommendations using classic <strong>hooks</strong>",
    key: "hooks",
    alert:
      "If the pages you are targeting are using blocks, this hooks may not work. Use the block or shortcode instead.",
    pro: false,
  },
];
</script>

<template>
  <ACardContent>
    <div class="space-y-4">
      <Disclosure
        v-slot="{ open }"
        v-for="variant in displayVariants"
        :key="variant.key"
        :defaultOpen="
          (variant.key === 'blocks' && isPro) ||
          (variant.key === 'shortcode' && !isPro)
        "
        class="relative"
        as="div"
      >
        <div
          class="rounded-lg border"
          :class="{
            'border-transparent ring-2 ring-indigo-500': open,
          }"
        >
          <DisclosureButton
            class="flex w-full cursor-pointer items-center justify-between px-4 py-4 text-left text-sm font-medium"
            :data-cy="`variant-${variant.key}`"
          >
            <span v-html="variant.title"></span>
            <div class="flex items-center">
              <div class="font-bold" v-if="variant.key === 'hooks'">
                {{ productRecommendationPost.pageHooks.length }} hook{{
                  productRecommendationPost.pageHooks.length !== 1 ? "s" : ""
                }}
                selected
              </div>
              <BadgeFreePro
                v-else-if="!isPro && variant.pro"
                :link="
                  getPluginMeta()?.websiteUrl ??
                  `https://www.sparkplugins.com/${productRecommendationPost.productsManager}`
                "
                go
                pro
              ></BadgeFreePro>
              <ChevronUpIcon
                :class="open ? 'rotate-180 transform' : ''"
                class="ml-4 inline-block h-5 w-5 text-indigo-500 transition-all"
              />
            </div>
          </DisclosureButton>
          <DisclosurePanel class="px-4 pb-2 pt-2 text-sm text-gray-500">
            <ACard v-if="!isPro && variant.pro" class="mb-4">
              <AAlert type="error" title="Upgrade to PRO">
                Displaying products using
                <strong>{{
                  variant.key === "blocks" ? "blocks" : "a shortcode"
                }}</strong>
                is only available if you update to <strong>PRO</strong>.
                <template #actions>
                  <AButton
                    target="_blank"
                    :href="
                      getPluginMeta()?.websiteUrl ??
                      `https://www.sparkplugins.com/${productRecommendationPost.productsManager}`
                    "
                    class="bg-red-600 hover:bg-red-700"
                  >
                    <div>Get PRO now</div>
                  </AButton>
                </template>
              </AAlert>
            </ACard>
            <div
              :class="{
                'opacity-60': !isPro && variant.pro,
              }"
            >
              <ACard v-if="variant.alert" class="mb-4">
                <AAlert type="warning">{{ variant.alert }}</AAlert>
              </ACard>
              <div v-if="variant.key === 'blocks'">
                Use this wizard to create a refined block of recommended
                products which you can insert into any page like any other block
                using your preferred page builder.

                <div
                  class="my-6 flex justify-center rounded-lg bg-gray-100 p-2 md:p-6"
                >
                  <img
                    :src="`${registryStore.imagePrefix}/demo-blocks.gif`"
                    alt="Demo of how blocks work"
                    class="w-full max-w-2xl"
                  />
                </div>
              </div>
              <div v-else-if="variant.key === 'shortcode'">
                <div v-if="productRecommendationPost.id">
                  You have the option to employ the following shortcode for
                  seamless presentation of the recommended products.
                  <div class="mt-2">
                    <ATextField
                      :model-value="shortcode"
                      readonly
                      show-button
                      @click="copy(shortcode)"
                    >
                      <template #append>
                        <button
                          @click="copy(shortcode)"
                          class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2.5 text-sm font-semibold hover:text-indigo-600"
                          :class="{
                            'text-green-800': copied,
                            'text-gray-800': !copied,
                          }"
                        >
                          <component
                            :is="
                              copied
                                ? ClipboardDocumentCheckIcon
                                : ClipboardDocumentListIcon
                            "
                            class="-ml-0.5 h-5 w-5"
                            :class="{
                              'text-green-500': copied,
                              'text-gray-400': !copied,
                            }"
                            aria-hidden="true"
                          ></component>
                          <template v-if="!copied"> Copy </template>
                          <template v-else> Copied </template>
                        </button>
                      </template>
                    </ATextField>
                  </div>
                </div>
                <div v-else>
                  A convenient shortcode will be at your disposal upon
                  completing this wizard. Use this shortcode as you used to.
                </div>
              </div>
              <div
                v-else-if="variant.key === 'hooks'"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3"
              >
                <div
                  v-for="hook in selectedRecommendationsPlugin
                    ?.productRecommendations?.placementHooks"
                  :key="hook.key"
                  :class="[
                    isChecked(hook) ? 'border-aprimary-600' : 'border-gray-300',
                    'relative block cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none sm:flex sm:justify-between',
                  ]"
                  @click="addHook(hook)"
                  :data-cy="hook.key"
                >
                  <div
                    v-if="maxReached(hook)"
                    class="absolute inset-0 z-10 flex flex-col items-center justify-center p-10 text-center"
                  >
                    <p
                      class="mb-3 rounded-lg border border-red-700 bg-red-100 p-2 text-red-700 opacity-90"
                    >
                      Only one location available in free version, want more? Go
                      pro!
                    </p>
                    <BadgeFreePro
                      go
                      pro
                      :link="selectedRecommendationsPlugin?.meta.websiteUrl"
                    ></BadgeFreePro>
                  </div>
                  <div
                    :class="{
                      'cursor-not-allowed opacity-40': maxReached(hook),
                    }"
                    class="w-full content-between"
                  >
                    <div class="flex">
                      <div class="flex flex-1">
                        <div class="flex flex-col">
                          <span
                            class="text-sm font-medium leading-6 text-gray-900"
                          >
                            {{ hook.name }}
                          </span>
                          <p class="text-sm text-gray-500">
                            {{ hook.description }}
                          </p>
                        </div>
                      </div>
                      <CheckCircleIcon
                        :class="[
                          !isChecked(hook) ? 'invisible' : '',
                          'h-5 w-5 text-aprimary-600',
                        ]"
                        aria-hidden="true"
                      ></CheckCircleIcon>
                    </div>
                    <div
                      class="relative mt-4 flex items-center rounded-t-md border-2 border-b-0 border-slate-200 p-1"
                    >
                      <span
                        class="ml-1 inline-block h-2 w-2 rounded-full bg-red-500"
                      >
                      </span>
                      <span
                        class="ml-1 inline-block h-2 w-2 rounded-full bg-yellow-500"
                      >
                      </span>
                      <span
                        class="ml-1 inline-block h-2 w-2 rounded-full bg-green-500"
                      >
                      </span>
                      <span
                        class="absolute inset-0 inline-flex items-center justify-center"
                      >
                        <span class="flex h-2 w-24 bg-slate-200"></span>
                      </span>
                    </div>
                    <div
                      class="grid-rows-7 grid grid-cols-3 gap-1 rounded-b-md border-2 border-slate-200 px-16 py-4"
                    >
                      <div class="col-span-3 h-8 rounded-sm bg-gray-100"></div>
                      <template v-if="hook.key === 'home-before-footer'">
                        <div
                          class="col-span-3 row-span-3 grid place-content-center rounded-sm bg-gray-100 py-5"
                        >
                          <HomeIcon
                            class="inline-block h-16 w-16 text-gray-300"
                          ></HomeIcon>
                        </div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <template v-else-if="hook.key === 'shop-after-products'">
                        <div
                          v-for="i in Array.from(
                            { length: 9 },
                            (v, k) => k + 1
                          )"
                          :key="i"
                          class="relative grid h-8 place-content-center rounded-sm bg-gray-100"
                        >
                          <PhotoIcon class="h-4 w-4 text-gray-300"></PhotoIcon>
                        </div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <template
                        v-else-if="hook.key === 'single-product-after-product'"
                      >
                        <div
                          class="col-span-2 row-span-3 grid place-content-center rounded-sm bg-gray-100"
                        >
                          <div>
                            <PhotoIcon
                              class="h-16 w-16 text-gray-300"
                            ></PhotoIcon>
                          </div>
                        </div>
                        <div
                          class="col-span-1 h-6 rounded-sm bg-gray-100"
                        ></div>
                        <div
                          class="col-span-1 h-12 rounded-sm bg-gray-100"
                        ></div>
                        <div
                          class="col-span-1 h-6 rounded-sm bg-gray-100"
                        ></div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <template v-else-if="hook.key === 'no-products-found'">
                        <div
                          class="col-span-3 row-span-3 grid place-content-center rounded-sm bg-gray-100 py-11"
                        >
                          <div class="h-4 text-gray-300">no products</div>
                        </div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <template v-else-if="hook.key === 'cart-after-cart'">
                        <div
                          class="col-span-3 row-span-3 grid place-content-center rounded-sm bg-gray-100 py-5"
                        >
                          <ShoppingCartIconSolid
                            class="inline-block h-16 w-16 text-gray-300"
                          ></ShoppingCartIconSolid>
                        </div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <template v-else-if="hook.key === 'cart-empty'">
                        <div
                          class="col-span-3 row-span-3 grid place-content-center rounded-sm bg-gray-100 py-5"
                        >
                          <ShoppingCartIcon
                            class="inline-block h-16 w-16 text-gray-300"
                          ></ShoppingCartIcon>
                        </div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <template v-else-if="hook.key === 'checkout-before-form'">
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                        <div
                          class="col-span-3 row-span-3 grid place-content-center rounded-sm bg-gray-100 py-5"
                        >
                          <CreditCardIcon
                            class="inline-block h-16 w-16 text-gray-300"
                          ></CreditCardIcon>
                        </div>
                      </template>
                      <template v-else-if="hook.key === 'checkout-after-form'">
                        <div
                          class="col-span-3 row-span-3 grid place-content-center rounded-sm bg-gray-100 py-5"
                        >
                          <CreditCardIcon
                            class="inline-block h-16 w-16 text-gray-300"
                          ></CreditCardIcon>
                        </div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <template v-else-if="hook.key === 'account-content'">
                        <div
                          class="col-span-3 row-span-3 grid place-content-center rounded-sm bg-gray-100 py-5"
                        >
                          <UserCircleIcon
                            class="inline-block h-16 w-16 text-gray-300"
                          ></UserCircleIcon>
                        </div>
                        <div class="col-span-3 h-8 rounded-sm bg-gray-100">
                          <WizardStepDisplaySelectableLayout
                            v-model="productRecommendationPost.pageHooks"
                            :hook="hook.key"
                          ></WizardStepDisplaySelectableLayout>
                        </div>
                      </template>
                      <div
                        class="col-span-3 row-span-2 h-16 rounded-sm bg-gray-100"
                      ></div>
                    </div>
                  </div>
                  <span
                    :class="[
                      isChecked(hook)
                        ? 'border-2 border-aprimary-600'
                        : 'border-transparent',
                      'pointer-events-none absolute -inset-px rounded-lg',
                    ]"
                    aria-hidden="true"
                  ></span>
                </div>
              </div>
            </div>
          </DisclosurePanel>
        </div>
      </Disclosure>
    </div>
  </ACardContent>
</template>
