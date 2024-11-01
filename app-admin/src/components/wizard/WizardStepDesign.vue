<script setup lang="ts">
import { ref, computed, watch } from "vue";
import {
  RadioGroup,
  RadioGroupDescription,
  RadioGroupLabel,
  RadioGroupOption,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
} from "@headlessui/vue";
import {
  PaintBrushIcon,
  ChevronDownIcon,
  PencilSquareIcon,
} from "@heroicons/vue/20/solid";

import ACard from "../common/ACard.vue";
import ATabs from "../common/ATabs.vue";
import ACardContent from "../common/ACardContent.vue";
import ASwitch from "../common/ASwitch.vue";
import ATextField from "../common/ATextField.vue";
import AHint from "../common/AHint.vue";
import AColorPicker from "../common/AColorPicker.vue";
import { useClipboard, useVModel } from "@vueuse/core";
import { ArrowsPointingOutIcon, Cog8ToothIcon } from "@heroicons/vue/24/solid";
import { type IProductRecommendation } from "@/stores/product-recommendations";
import {
  CheckIcon,
  ChevronRightIcon,
  ChevronLeftIcon,
} from "@heroicons/vue/24/outline";
import useRegistryStore, { SparkPlugin } from "@/stores/registry";
import BadgeFreePro from "../BadgeFreePro.vue";
import ALoadingOverlay from "../common/ALoadingOverlay.vue";
import AButtonGroup from "../common/AButtonGroup.vue";
import AButton from "../common/AButton.vue";
import ATooltip from "../common/ATooltip.vue";
import AAlert from "../common/AAlert.vue";
import useOptionsStore from "@/stores/options";
import { storeToRefs } from "pinia";
import ASubheader from "../common/ASubheader.vue";
import CssOffset from "../CssOffset.vue";

const props = defineProps<{
  modelValue: IProductRecommendation;
  plugin?: SparkPlugin;
}>();

const emit = defineEmits(["update:modelValue", "update:designSettings"]);

const productRecommendationPost = useVModel(props, "modelValue", emit);

const registryStore = useRegistryStore();

const isPro = computed(
  () =>
    registryStore.getProductsManagers()[
      productRecommendationPost.value.productsManager
    ].isPro
);

if (!isPro.value) {
  productRecommendationPost.value.designSettings.sliderEnabled = false;
}

const selectedRecommendationsPlugin = computed(() => {
  return registryStore.getPluginByProductsManagerSlug(
    productRecommendationPost.value.productsManager
  );
});

enum DesignTabs {
  Settings = "settings",
  Style = "style",
  CSSClasses = "css-classes",
}

const productsManagerMeta = computed(() =>
  registryStore.getPluginByProductsManagerSlug(
    productRecommendationPost.value.productsManager
  )
);

if (!productRecommendationPost.value.designSettings.titleAboveProducts) {
  productRecommendationPost.value.designSettings.titleAboveProducts =
    productsManagerMeta.value?.meta.extra.productsManager?.title;
}

const tabs = computed(() => [
  {
    id: DesignTabs.Settings,
    name: "Settings",
    icon: Cog8ToothIcon,
    slugs: [],
  },
  { id: DesignTabs.Style, name: "Style", icon: PaintBrushIcon, slugs: [] },
  {
    id: DesignTabs.CSSClasses,
    name: "CSS Classes",
    icon: PencilSquareIcon,
    slugs: [],
  },
]);

const selectedTabIdx = ref(0);
const selectedTab = computed(() => tabs.value[selectedTabIdx.value]);

const previewUrl = computed(() => {
  const postJsonString = JSON.stringify(productRecommendationPost.value);
  const productRecommendation = window.btoa(encodeURIComponent(postJsonString));
  const url = new URL(window.location.origin);
  url.searchParams.append(
    "sparkwooProductRecommendationPostModelData",
    productRecommendation
  );
  if (props.plugin) {
    url.searchParams.append("sparkwooPluginSlug", props.plugin.meta.slug);
  }
  return url.toString();
});

watch(previewUrl, () => {
  loadingPreview.value = true;
});

const loadingPreview = ref(true);
const iframeLoaded = (event: any) => {
  loadingPreview.value = false;
};

const headings = [
  { name: "Heading 1", class: "text-2xl", tag: "h1" },
  { name: "Heading 2", class: "text-xl", tag: "h2" },
  { name: "Heading 3", class: "text-lg", tag: "h3" },
  { name: "Heading 4", class: "text-base", tag: "h4" },
];

const { copy: copyText } = useClipboard();

const copied = ref(false);
const copiedText = ref("");
const copy = (t: string) => {
  copyText(t);
  copied.value = true;
  copiedText.value = t;
};

const copyInner = (e: Event) => {
  const target = e.target as HTMLElement;
  const text = target.textContent;
  if (text) {
    copy(text);
  }
};

const classes = {
  title: {
    class: ".sparkwoo-pr-title",
    name: "Title",
  },
  prev: {
    class: ".sp-carousel-button.sp-carousel-button-prev",
    name: "Previous button",
  },
  next: {
    class: ".sp-carousel-button.sp-carousel-button-next",
    name: "Next button",
  },
  indicatorActive: {
    class: ".sp-carousel-indicator-dot-active",
    name: "Active indicator",
  },
  indicator: {
    class: ".sp-carousel-indicator-dot",
    name: "Indicator",
  },
  item: {
    class: ".sparkwoo-pr-item",
    name: "Product item",
  },
};

const optionsStore = useOptionsStore();
const { options, getOption } = storeToRefs(optionsStore);

const fetchOptions = () => {
  if (!props.plugin) return;
  optionsStore.fetch(props.plugin);
};

fetchOptions();

const isMultiLanguage = computed(() => {
  if (!props.plugin) return false;
  return !!getOption.value(props.plugin, "multi_language_enabled");
});
</script>

<template>
  <ACardContent class="-mb-6">
    Design the way the products are displayed
  </ACardContent>
  <div class="grid grid-cols-1 lg:grid-cols-2">
    <ACardContent>
      <ACard>
        <ATabs v-model="selectedTabIdx" :tabs="tabs"></ATabs>
        <ACardContent
          class="space-y-4 px-4 py-6"
          v-if="selectedTab.id === DesignTabs.Settings"
        >
          <ATextField
            v-if="isMultiLanguage"
            model-value="Default title..."
            label="Title to show above the products"
            readonly
            hint="This value can be changed using a translation or localization plugin"
          ></ATextField>
          <ATextField
            v-else
            v-model="
              productRecommendationPost.designSettings.titleAboveProducts
            "
            label="Title to show above the products"
            hint="Having a multi language site? Enable 'multi language' in settings to translate this value for each language using a translation or localization plugin"
          ></ATextField>
          <div class="grid grid-cols-2 gap-6">
            <ATextField
              v-model.number="
                productRecommendationPost.designSettings.numberToShow
              "
              label="Number of products to show"
              type="number"
              hide-details
            ></ATextField>
          </div>
          <ASubheader>Columns</ASubheader>
          <ASwitch
            v-model="
              productRecommendationPost.designSettings.useThemeColumnsSetting
            "
            label="Use theme column rendering"
            @update:model-value="
              (v) => {
                if (v) {
                  productRecommendationPost.designSettings.useThemeNumberOfColumns = true;
                }
              }
            "
          ></ASwitch>
          <ASwitch
            v-if="
              productRecommendationPost.designSettings.useThemeColumnsSetting
            "
            v-model="
              productRecommendationPost.designSettings.useThemeNumberOfColumns
            "
            label="Theme's default amount of columns"
          ></ASwitch>
          <div>
            <div class="grid grid-cols-1 gap-x-6 xl:grid-cols-2">
              <AButtonGroup
                v-model.number="
                  productRecommendationPost.designSettings.numberPerRow
                "
                label="Columns on larger screens"
                :buttons="[
                  { text: '2', value: 2 },
                  { text: '3', value: 3 },
                  { text: '4', value: 4 },
                  { text: '5', value: 5 },
                  { text: '6', value: 6 },
                  { text: '7', value: 7 },
                  { text: '8', value: 8 },
                ]"
                v-if="
                  !productRecommendationPost.designSettings
                    .useThemeNumberOfColumns ||
                  !productRecommendationPost.designSettings
                    .useThemeColumnsSetting
                "
              ></AButtonGroup>

              <AButtonGroup
                label="Columns on smaller screens"
                v-model="
                  productRecommendationPost.designSettings.numberPerRowSm
                "
                :buttons="[
                  { text: '1 column', value: 1 },
                  { text: '2 columns', value: 2 },
                ]"
                v-if="
                  !productRecommendationPost.designSettings
                    .useThemeColumnsSetting
                "
              ></AButtonGroup>
              <CssOffset
                v-if="
                  !productRecommendationPost.designSettings
                    .useThemeColumnsSetting
                "
                v-model:offset="
                  productRecommendationPost.designSettings.columnMargin
                "
                v-model:offsetUnit="
                  productRecommendationPost.designSettings.columnMarginUnit
                "
                label="Margin between columns"
              ></CssOffset>
            </div>
            <AHint
              >We recommend to use the theme way to display the columns. If this
              does not work properly, you can try SparkPlugins' 'one size fits
              all' by disable the
              <strong>theme column rendering</strong>-switch, make custom CSS
              using our classes, or
              <a href="https://www.sparkplugins.com/support" target="_blank">
                don't hesitate to contact us to help you out</a
              >.
            </AHint>
          </div>
          <ASubheader> What to show </ASubheader>
          <ASwitch
            v-model="
              productRecommendationPost.designSettings.showAddToCartButton
            "
            label="Show 'add to cart'-button"
          ></ASwitch>
          <ASwitch
            v-model="productRecommendationPost.designSettings.showPrice"
            label="Show price"
          ></ASwitch>
          <ASwitch
            v-model="productRecommendationPost.designSettings.hideNoProducts"
            label="Hide when no products are available"
            hint="When there are no products to show, a message will be displayed. This message can be changed using a translation or localization plugin."
          ></ASwitch>
          <ASwitch
            v-model="
              productRecommendationPost.designSettings.showOutOfStockProducts
            "
            label="Show out of stock products"
          ></ASwitch>
          <ASwitch
            v-if="productRecommendationPost.productsManager === 'sparkair'"
            v-model="
              productRecommendationPost.designSettings.showLoginSuggestion
            "
            label="For users not logged in, show a login suggestion"
            hint="When a user is not logged in, a message will be displayed. This message can be changed using a translation or localization plugin."
          ></ASwitch>
          <ASubheader>
            Carousel
            <BadgeFreePro
              v-if="!isPro"
              go
              pro
              :link="productsManagerMeta?.meta.websiteUrl"
            ></BadgeFreePro>
          </ASubheader>
          <div>
            Allow the products to slide in a carousel, saving space and
            displaying all products in a single row.
          </div>
          <ASwitch
            v-model="productRecommendationPost.designSettings.sliderEnabled"
            label="Enable carousel"
            :disabled="!isPro"
            :hint="
              isPro
                ? 'You may want to disable \'Use theme column rendering\' when using the carousel in order to render products correctly.'
                : undefined
            "
          ></ASwitch>
          <template
            v-if="productRecommendationPost.designSettings.sliderEnabled"
          >
            <ASwitch
              v-model="
                productRecommendationPost.designSettings.sliderShowArrows
              "
              label="Show previous and next arrows"
              :disabled="
                !productRecommendationPost.designSettings.sliderEnabled
              "
            ></ASwitch>
            <template
              v-if="productRecommendationPost.designSettings.sliderShowArrows"
            >
              <RadioGroup
                v-model="
                  productRecommendationPost.designSettings.sliderArrowsVariant
                "
              >
                <RadioGroupLabel
                  class="mb-1 block truncate text-sm font-medium text-gray-700"
                >
                  Arrow variant
                </RadioGroupLabel>
                <div class="grid grid-cols-3 gap-x-4 gap-y-6">
                  <RadioGroupOption
                    as="template"
                    v-for="variant in [
                      { value: 'plain', text: 'Plain' },
                      { value: 'square', text: 'Square' },
                      { value: 'circle', text: 'Circle' },
                    ]"
                    :key="variant.value"
                    :value="variant.value"
                    v-slot="{ active, checked }"
                  >
                    <div
                      :class="[
                        active || checked
                          ? 'border-aprimary-600 ring-1 ring-aprimary-600'
                          : 'border-gray-300',
                        'relative flex cursor-pointer items-center justify-center rounded-lg border bg-white p-4 shadow-sm focus:outline-none',
                      ]"
                    >
                      <RadioGroupLabel
                        as="span"
                        class="sr-only block text-sm font-medium text-gray-900"
                        >{{ variant.text }}</RadioGroupLabel
                      >
                      <RadioGroupDescription
                        as="span"
                        class="text-gray-500"
                        :class="{
                          'border-2 border-gray-500': [
                            'square',
                            'circle',
                          ].includes(variant.value),
                          'rounded-full': variant.value === 'circle',
                        }"
                      >
                        <ChevronLeftIcon class="h-8 w-8"></ChevronLeftIcon>
                      </RadioGroupDescription>
                    </div>
                  </RadioGroupOption>
                </div>
              </RadioGroup>
              <div>
                <span
                  class="mb-1 block truncate text-sm font-medium text-gray-700"
                >
                  Arrow position on larger screens
                </span>
                <AButtonGroup
                  v-model="
                    productRecommendationPost.designSettings.sliderArrowInside
                  "
                  :buttons="[
                    { text: 'Outside', value: false },
                    { text: 'Inside', value: true },
                  ]"
                ></AButtonGroup>
                <AHint>
                  On small screens the arrows are always inside the carousel.
                </AHint>
              </div>
            </template>
            <ASwitch
              v-model="
                productRecommendationPost.designSettings.sliderShowIndicator
              "
              label="Show indicator bullets below slider"
            ></ASwitch>
            <template
              v-if="
                productRecommendationPost.designSettings.sliderShowIndicator
              "
            >
              <RadioGroup
                v-model="
                  productRecommendationPost.designSettings
                    .sliderIndicatorVariant
                "
              >
                <RadioGroupLabel
                  class="mb-1 block truncate text-sm font-medium text-gray-700"
                >
                  Indicator variant
                </RadioGroupLabel>
                <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                  <RadioGroupOption
                    as="template"
                    v-for="variant in [
                      { value: 'dots', text: 'Dots' },
                      { value: 'window', text: 'Sliding window' },
                    ]"
                    :key="variant.value"
                    :value="variant.value"
                    v-slot="{ active, checked }"
                  >
                    <div
                      :class="[
                        active || checked
                          ? 'border-aprimary-600 ring-1 ring-aprimary-600'
                          : 'border-gray-300',
                        'relative flex cursor-pointer items-center justify-center rounded-lg border bg-white p-4 shadow-sm focus:outline-none',
                      ]"
                    >
                      <RadioGroupLabel
                        as="span"
                        class="sr-only block text-sm font-medium text-gray-900"
                        >{{ variant.text }}</RadioGroupLabel
                      >
                      <RadioGroupDescription as="div">
                        <div
                          v-if="variant.value === 'dots'"
                          class="flex space-x-2.5"
                        >
                          <div
                            v-for="x in [...Array(8).keys()]"
                            :key="x"
                            class="h-2.5 w-2.5 rounded-full border-2 border-gray-300"
                            :class="{
                              'border-gray-700 bg-gray-700': x === 5,
                            }"
                          ></div>
                        </div>
                        <div
                          class="relative flex space-x-2.5"
                          v-if="variant.value === 'window'"
                        >
                          <div
                            v-for="x in [...Array(8).keys()]"
                            :key="x"
                            class="z-30 h-2 w-2 rounded-full bg-gray-700/50"
                          ></div>
                          <div
                            class="absolute z-20 -my-0.5 h-3 w-16 rounded-full bg-gray-300 px-1 ring-2 ring-gray-500/75"
                          ></div>
                        </div>
                      </RadioGroupDescription>
                    </div>
                  </RadioGroupOption>
                </div>
              </RadioGroup>
            </template>
            <ASwitch
              v-model="productRecommendationPost.designSettings.sliderAuto"
              label="Automatically slide every 5 seconds"
            ></ASwitch>
          </template>
          <template
            v-if="productRecommendationPost.productsManager === 'sparkfbt'"
          >
            <ASubheader>
              Shop the Combination
              <BadgeFreePro
                v-if="!isPro"
                go
                pro
                :link="productsManagerMeta?.meta.websiteUrl"
              ></BadgeFreePro>
            </ASubheader>
            <ASwitch
              v-model="
                productRecommendationPost.designSettings.showAddAllToCart
              "
              label="Show 'shop the combination'"
              hint="Enable the option for customers to add all (or a selection of) frequently bought together products to the cart with a single click."
              :disabled="!isPro"
            ></ASwitch>
            <ATextField
              v-if="productRecommendationPost.designSettings.showAddAllToCart"
              v-model="
                productRecommendationPost.designSettings.titleShopTheCombination
              "
              label="Title for 'shop the combination'"
              :readonly="isMultiLanguage || !isPro"
              :hint="
                isMultiLanguage
                  ? 'This value can be changed using a translation or localization plugin'
                  : 'Having a multi language site? Enable \'multi language\' in the settings to translate this value for each language using a translation or localization plugin.'
              "
            ></ATextField>
          </template>
          <template
            v-if="productRecommendationPost.productsManager === 'sparkair'"
          >
            <ASubheader>
              AI Recommendations
              <BadgeFreePro
                v-if="!isPro"
                go
                pro
                :link="productsManagerMeta?.meta.websiteUrl"
              ></BadgeFreePro>
            </ASubheader>
            <ASwitch
              v-model="
                productRecommendationPost.designSettings.showMatchPercentage
              "
              label="Show match percentage"
              hint="Enable the option to show the AI match percentage of the recommended products, like Netflix does with movies."
              :disabled="!isPro"
            ></ASwitch>
          </template>
        </ACardContent>
        <ACardContent v-else-if="selectedTab.id === DesignTabs.Style">
          <ASwitch
            v-model="productRecommendationPost.designStyle.custom"
            label="Use custom styling instead of theme style"
          ></ASwitch>
          <div class="relative space-y-4">
            <ASubheader>Cart button</ASubheader>
            <div class="grid grid-cols-2 gap-6">
              <div>
                <AColorPicker
                  v-model="
                    productRecommendationPost.designStyle.addToCartButtonColor
                  "
                  label="'Add to cart'-button color"
                  :disabled="
                    !productRecommendationPost.designSettings
                      .showAddToCartButton
                  "
                >
                </AColorPicker>
                <em
                  v-if="
                    !productRecommendationPost.designSettings
                      .showAddToCartButton
                  "
                >
                  Only available when 'Add to cart' button is enabled
                </em>
              </div>
              <div>
                <AColorPicker
                  v-model="
                    productRecommendationPost.designStyle
                      .addToCartButtonTextColor
                  "
                  label="'Add to cart'-button text color"
                  :disabled="
                    !productRecommendationPost.designSettings
                      .showAddToCartButton
                  "
                >
                </AColorPicker>
                <em
                  v-if="
                    !productRecommendationPost.designSettings
                      .showAddToCartButton
                  "
                >
                  Only available when 'Add to cart' button is enabled
                </em>
              </div>
            </div>
            <ASubheader>Title</ASubheader>
            <div class="grid grid-cols-2 items-end gap-6">
              <AColorPicker
                v-model="productRecommendationPost.designStyle.titleColor"
                label="Title color"
              >
              </AColorPicker>
              <div>
                <span class="inline-flex h-10 rounded-md shadow-sm">
                  <button
                    type="button"
                    class="relative inline-flex items-center rounded-l-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10"
                    :class="{
                      'border-aprimary-700 bg-aprimary-50 text-aprimary-700':
                        productRecommendationPost.designStyle.titleBold,
                      'bg-white text-gray-900 hover:bg-gray-50 ':
                        !productRecommendationPost.designStyle.titleBold,
                    }"
                    @click="
                      productRecommendationPost.designStyle.titleBold =
                        !productRecommendationPost.designStyle.titleBold
                    "
                  >
                    <strong>B</strong>
                  </button>
                  <button
                    type="button"
                    class="relative -ml-px inline-flex items-center px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10"
                    :class="{
                      'border-aprimary-700 bg-aprimary-50 text-aprimary-700':
                        productRecommendationPost.designStyle.titleItalic,
                      'bg-white text-gray-900 hover:bg-gray-50 ':
                        !productRecommendationPost.designStyle.titleItalic,
                    }"
                    @click="
                      productRecommendationPost.designStyle.titleItalic =
                        !productRecommendationPost.designStyle.titleItalic
                    "
                  >
                    <em>I</em>
                  </button>
                  <button
                    type="button"
                    class="relative -ml-px inline-flex items-center px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10"
                    :class="{
                      'border-aprimary-700 bg-aprimary-50 text-aprimary-700':
                        productRecommendationPost.designStyle.titleUnderlined,
                      'bg-white text-gray-900 hover:bg-gray-50 ':
                        !productRecommendationPost.designStyle.titleUnderlined,
                    }"
                    @click="
                      productRecommendationPost.designStyle.titleUnderlined =
                        !productRecommendationPost.designStyle.titleUnderlined
                    "
                  >
                    <u>U</u>
                  </button>
                  <Menu as="div" class="relative -ml-px block">
                    <MenuButton
                      class="relative inline-flex h-10 items-center rounded-r-md bg-white px-2 py-2 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-10"
                    >
                      <span>{{
                        headings.find(
                          (h) =>
                            h.tag ===
                            productRecommendationPost.designStyle.titleTag
                        )?.name ?? ""
                      }}</span>
                      <ChevronDownIcon class="h-5 w-5" aria-hidden="true" />
                    </MenuButton>
                    <transition
                      enter-active-class="transition ease-out duration-100"
                      enter-from-class="transform opacity-0 scale-95"
                      enter-to-class="transform opacity-100 scale-100"
                      leave-active-class="transition ease-in duration-75"
                      leave-from-class="transform opacity-100 scale-100"
                      leave-to-class="transform opacity-0 scale-95"
                    >
                      <MenuItems
                        class="absolute right-0 z-10 -mr-1 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                      >
                        <div class="py-1">
                          <MenuItem
                            v-for="item in headings"
                            :key="item.name"
                            v-slot="{ active }"
                            @click="
                              productRecommendationPost.designStyle.titleTag =
                                item.tag
                            "
                          >
                            <span
                              :class="[
                                active
                                  ? 'bg-gray-100 text-gray-900'
                                  : 'text-gray-700',
                                'flex cursor-pointer items-center justify-between px-4 py-2',
                                item.class,
                              ]"
                            >
                              {{ item.name }}
                              <CheckIcon
                                v-if="
                                  productRecommendationPost.designStyle
                                    .titleTag === item.tag
                                "
                                class="w5 h-5 text-aprimary-600"
                              ></CheckIcon>
                            </span>
                          </MenuItem>
                        </div>
                      </MenuItems>
                    </transition>
                  </Menu>
                </span>
              </div>
              <CssOffset
                v-model:offset="
                  productRecommendationPost.designStyle.titleMarginBottom
                "
                v-model:offsetUnit="
                  productRecommendationPost.designStyle.titleMarginBottomUnit
                "
                label="Margin below title"
              ></CssOffset>
            </div>
            <ASubheader>Section</ASubheader>
            <div>
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <CssOffset
                    v-model:offset="
                      productRecommendationPost.designStyle.paddingX
                    "
                    v-model:offsetUnit="
                      productRecommendationPost.designStyle.paddingXUnit
                    "
                    label="Padding left and right"
                  ></CssOffset>
                </div>
                <div>
                  <CssOffset
                    v-model:offset="
                      productRecommendationPost.designStyle.paddingY
                    "
                    v-model:offsetUnit="
                      productRecommendationPost.designStyle.paddingYUnit
                    "
                    label="Padding top and bottom"
                  ></CssOffset>
                </div>
              </div>
              <AHint>
                Padding is used to add space between the border of the section
                and the products.
              </AHint>
            </div>
            <AColorPicker
              v-model="productRecommendationPost.designStyle.backgroundColor"
              label="Background color"
            >
            </AColorPicker>
            <div
              class="absolute inset-0 z-10 bg-white opacity-40"
              v-if="!productRecommendationPost.designStyle.custom"
            ></div>
          </div>
        </ACardContent>
        <ACardContent v-else-if="selectedTab.id === DesignTabs.CSSClasses">
          <div class="prose">
            <div>
              You can use our pre-defined CSS classes to fully customize the
              look and feel of the way products are displayed.
              <strong>Hover</strong> and <strong>click to copy</strong> the
              designated items below to get their classes.
            </div>
            <div>
              The product recommendations are wrapped in class
              <span @click="copyInner" class="code cursor-pointer"
                >.sparkwoo-pr</span
              >
            </div>
          </div>
          <ACard class="mt-4 grid grid-cols-6 items-stretch gap-2 p-4">
            <div class="col-span-5 col-start-2">
              <ATooltip
                :text="classes.title.name"
                @click="copy(classes.title.class)"
              >
                <div
                  class="h-4 w-32 cursor-pointer bg-gray-300 transition-colors hover:bg-gray-400"
                ></div>
              </ATooltip>
            </div>
            <ATooltip
              :text="classes.prev.name"
              @click="copy(classes.prev.class)"
            >
              <div class="flex h-24 items-center justify-center">
                <ChevronLeftIcon
                  v-if="isPro"
                  class="h-8 w-8 cursor-pointer text-gray-300 transition-colors hover:text-gray-400"
                ></ChevronLeftIcon>
              </div>
            </ATooltip>
            <ATooltip
              v-for="i in [1, 2, 3, 4]"
              :key="i"
              :text="classes.item.name"
              @click="copy(classes.item.class)"
            >
              <div
                class="h-24 cursor-pointer bg-gray-300 transition-colors hover:bg-gray-400"
              ></div>
            </ATooltip>
            <ATooltip
              :text="classes.next.name"
              @click="copy(classes.next.class)"
            >
              <div class="flex h-24 items-center justify-center">
                <ChevronRightIcon
                  v-if="isPro"
                  class="h-8 w-8 cursor-pointer text-gray-300 transition-colors hover:text-gray-400"
                ></ChevronRightIcon>
              </div>
            </ATooltip>
            <div
              v-if="isPro"
              class="col-span-full flex justify-center space-x-2"
            >
              <ATooltip
                class="inline-block p-1"
                :text="classes.indicatorActive.name"
                @click="copy(classes.indicatorActive.class)"
              >
                <div
                  class="h-3 w-3 cursor-pointer rounded-full bg-gray-300 transition-colors hover:bg-gray-400"
                ></div>
              </ATooltip>
              <ATooltip
                class="inline-block p-1"
                :text="classes.indicator.name"
                @click="copy(classes.indicator.class)"
              >
                <div
                  class="h-3 w-3 cursor-pointer rounded-full border-2 border-gray-300 transition-colors hover:border-gray-400"
                ></div>
              </ATooltip>
            </div>
          </ACard>
          <template v-if="plugin?.meta.slug === 'sparkfbt-pro'">
            <ASubheader>Shop the Combination classes</ASubheader>
            <div class="prose prose-sm mt-4">
              <div>
                <span class="code cursor-pointer"
                  >.sparkwoo-add-to-cart-wrapper</span
                >
                wraps the <strong>shop the combination</strong> area. This area
                contains the following classes:
              </div>

              <ul class="text-xs leading-6">
                <li>
                  <span @click="copyInner" class="code cursor-pointer"
                    >.sparkwoo-add-to-cart-title</span
                  >
                  contains the title.
                </li>
                <li>
                  <span @click="copyInner" class="code cursor-pointer"
                    >.sparkwoo-add-to-cart-product-variation-message</span
                  >
                  is the class of the message shown when variations are
                  available.
                </li>
                <li>
                  <span @click="copyInner" class="code cursor-pointer"
                    >.sparkwoo-add-to-cart-product-variation-wrapper</span
                  >
                  wraps a single product variation including a
                  <span class="code">label</span>
                  and a
                  <span class="code">select</span>.
                </li>
                <li>
                  <span @click="copyInner" class="code cursor-pointer"
                    >.sparkwoo-add-to-cart-total-wrapper</span
                  >
                  wraps the totals area.
                </li>
                <li>
                  <span @click="copyInner" class="code cursor-pointer"
                    >.sparkwoo-add-to-cart-total-description</span
                  >
                  contains the description of the totals.
                </li>
                <li>
                  <span @click="copyInner" class="code cursor-pointer"
                    >.sparkwoo-add-to-cart-total-price</span
                  >
                  contains the price of selected products.
                </li>
                <li>
                  <span @click="copyInner" class="code cursor-pointer"
                    >.sparkwoo-add-selected-to-cart</span
                  >
                  is the class of the button to add the selected products to the
                  cart.
                </li>
              </ul>
            </div>
          </template>
          <template v-if="plugin?.meta.slug === 'sparkair-pro'">
            <ASubheader>AI Match classes</ASubheader>
            <div class="mt-4">
              The AI match elements are wrapped in class
              <span @click="copyInner" class="code cursor-pointer"
                >.sparkair-match</span
              >
            </div>
            <ACard class="mt-4 flex items-center justify-center p-4">
              <div
                class="flex h-20 w-20 cursor-pointer flex-col items-center justify-center rounded-full bg-gray-100 transition-colors"
                @click.stop="copy('.sparkair-match')"
              >
                <div>
                  <ATooltip
                    text="Match value"
                    @click.stop="copy('.sparkair-match-value')"
                  >
                    <div
                      class="mr-1 flex w-8 cursor-pointer items-center justify-center bg-gray-300 p-1 transition-colors hover:bg-gray-400"
                    >
                      <span>#</span>
                    </div>
                  </ATooltip>
                  <ATooltip
                    text="Match percentage sign"
                    @click.stop="
                      copy('.sparkair-match-value .sparkair-match-percentage')
                    "
                  >
                    <div
                      class="flex w-4 cursor-pointer items-center justify-center bg-gray-300 p-1 transition-colors hover:bg-gray-400"
                    >
                      <span>%</span>
                    </div>
                  </ATooltip>
                </div>
                <ATooltip
                  text="Match label"
                  @click.stop="copy('.sparkair-match-label')"
                >
                  <div
                    class="mt-1 flex h-4 cursor-pointer items-center justify-center bg-gray-300 p-1 text-xs transition-colors hover:bg-gray-400"
                  >
                    <span>match</span>
                  </div>
                </ATooltip>
              </div>
            </ACard>
          </template>
          <AAlert v-if="copied" class="mt-4">
            <span class="code">{{ copiedText }}</span>
            has been copied to your clipboard.
          </AAlert>
        </ACardContent>
      </ACard>
    </ACardContent>
    <ACardContent>
      <ACard class="h-full overflow-hidden">
        <ACardContent>
          <div class="gray mb-2 flex">
            <div class="grow font-normal italic text-gray-400">
              * This preview shows random products and is just an indication
            </div>
            <ATooltip class="shrink-0" text="Preview full screen in a new tab">
              <AButton
                :href="previewUrl"
                target="_blank"
                outlined
                :icon="ArrowsPointingOutIcon"
                icon-only
              ></AButton>
            </ATooltip>
          </div>
        </ACardContent>
        <div class="relative flex h-full min-h-[600px] justify-center px-1">
          <iframe
            v-if="previewUrl"
            @load="iframeLoaded"
            :src="previewUrl"
            class="pointer-events-none h-full min-h-[600px] w-full"
          >
          </iframe>
          <ALoadingOverlay :model-value="loadingPreview"></ALoadingOverlay>
        </div>
      </ACard>
    </ACardContent>
  </div>
</template>
