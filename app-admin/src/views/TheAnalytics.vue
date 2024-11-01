<script setup lang="ts">
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import TheHeader from "@/components/TheHeader.vue";
import ACard from "@/components/common/ACard.vue";
import ACardHeader from "@/components/common/ACardHeader.vue";
import ASubheader from "@/components/common/ASubheader.vue";
import ACheckbox from "@/components/common/ACheckbox.vue";
import ATable from "@/components/common/ATable.vue";
import AButton from "@/components/common/AButton.vue";
import {
  ArrowDownIcon,
  ArrowTopRightOnSquareIcon,
  ArrowUpIcon,
  ChevronDownIcon,
} from "@heroicons/vue/20/solid";
import { computed, ref, watch, type Ref } from "vue";
import ACardActions from "@/components/common/ACardActions.vue";
import ACardContent from "@/components/common/ACardContent.vue";
import ASparkLine from "@/components/common/charts/ASparkLine.vue";
import useAnalyticsStore, {
  AnalyticsGraphDataType,
  AnalyticsTopProductsType,
  type IAnalyticsSparklineDataPoint,
} from "@/stores/analytics";
import { useRoute, useRouter } from "vue-router";
import useRegistryStore, { SparkPlugin } from "@/stores/registry";
import { storeToRefs } from "pinia";
import ABadge from "@/components/common/ABadge.vue";
import ALoadingOverlay from "@/components/common/ALoadingOverlay.vue";
import moment, { type Moment } from "moment";
import AAlert from "@/components/common/AAlert.vue";
import TheFooter from "@/components/TheFooter.vue";
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import { CalendarDaysIcon, CheckIcon } from "@heroicons/vue/24/outline";
import type { Dayjs } from "dayjs";

const props = defineProps<{
  pluginSlug?: string;
}>();

const analyticsStore = useAnalyticsStore();
const {
  graphData,
  fetching,
  topProductsData,
  fetchingTopProducts,
  topRecommendationsShown,
  fetchingTopRecommendationsShown,
  totalRevenuePlugins,
  fetchingTotalRevenuePlugins,
  totalRevenue,
} = storeToRefs(analyticsStore);

const router = useRouter();
const route = useRoute();

const registryStore = useRegistryStore();
const { plugins, currencySymbol, imagePrefix } = storeToRefs(registryStore);

const productsManagers = registryStore.getProductsManagers();

const updateRoute = (plugin: SparkPlugin) => {
  router.replace({
    ...route,
    params: { ...route.params, pluginSlug: plugin.meta.slug },
  });
};

const selectedPlugin = computed(() =>
  props.pluginSlug ? plugins.value[props.pluginSlug] : null
);

// Renders
const productsRendered = computed(
  () => graphData.value[AnalyticsGraphDataType.Render]?.total ?? 0
);

const productsRenderedCompare = computed(
  () => graphData.value[AnalyticsGraphDataType.Render]?.totalCompare ?? 0
);

const productsRenderedPercentage = computed(
  () =>
    (productsRendered.value - productsRenderedCompare.value) /
    productsRenderedCompare.value
);
const productsRenderedSeries = computed(() => ({
  name: "Renders",
  data:
    graphData.value[AnalyticsGraphDataType.Render]?.timeline.map(
      (value: IAnalyticsSparklineDataPoint) => ({
        x: value.date,
        y: value.value,
      })
    ) ?? [],
}));

// Clicks
const productsClicked = computed(
  () => graphData.value[AnalyticsGraphDataType.Click]?.total ?? 0
);

const productsClickedCompare = computed(
  () => graphData.value[AnalyticsGraphDataType.Click]?.totalCompare ?? 0
);

const productsClickedPercentage = computed(
  () =>
    (productsClicked.value - productsClickedCompare.value) /
    productsClickedCompare.value
);

const productsClickedSeries = computed(() => ({
  name: "Clicks",
  data:
    graphData.value[AnalyticsGraphDataType.Click]?.timeline.map(
      (value: IAnalyticsSparklineDataPoint) => ({
        x: value.date,
        y: value.value,
      })
    ) ?? [],
}));

// Conversions
const productsConverted = computed(
  () => graphData.value[AnalyticsGraphDataType.Conversion]?.total ?? 0
);

const productsConvertedCompare = computed(
  () => graphData.value[AnalyticsGraphDataType.Conversion]?.totalCompare ?? 0
);

const productsConvertedPercentage = computed(
  () =>
    (productsConverted.value - productsConvertedCompare.value) /
    productsConvertedCompare.value
);

const productsConvertedSeries = computed(() => ({
  name: "Conversions",
  data:
    graphData.value[AnalyticsGraphDataType.Conversion]?.timeline.map(
      (value: IAnalyticsSparklineDataPoint) => ({
        x: value.date,
        y: value.value,
      })
    ) ?? [],
}));

// Click rate
const productsClickRate = computed(
  () => graphData.value[AnalyticsGraphDataType.ClickRate]?.total ?? 0
);

const productsClickRateCompare = computed(
  () => graphData.value[AnalyticsGraphDataType.ClickRate]?.totalCompare ?? 0
);

const productsClickRatePercentage = computed(
  () =>
    (productsClickRate.value - productsClickRateCompare.value) /
    productsClickRateCompare.value
);

const productsClickRateSeries = computed(() => ({
  name: "Click rate",
  data:
    graphData.value[AnalyticsGraphDataType.ClickRate]?.timeline.map(
      (value: IAnalyticsSparklineDataPoint) => ({
        x: value.date,
        y: Math.round(value.value * 10000) / 100,
      })
    ) ?? [],
}));

// Conversion rate
const productsConversionRate = computed(
  () => graphData.value[AnalyticsGraphDataType.ConversionRate]?.total ?? 0
);

const productsConversionRateCompare = computed(
  () =>
    graphData.value[AnalyticsGraphDataType.ConversionRate]?.totalCompare ?? 0
);

const productsConversionRatePercentage = computed(
  () =>
    (productsConversionRate.value - productsConversionRateCompare.value) /
    productsConversionRateCompare.value
);

const productsConversionRateSeries = computed(() => ({
  name: "Conversion rate",
  data:
    graphData.value[AnalyticsGraphDataType.ConversionRate]?.timeline.map(
      (value: IAnalyticsSparklineDataPoint) => ({
        x: value.date,
        y: Math.round(value.value * 10000) / 100,
      })
    ) ?? [],
}));

// Revenue
const productsRevenue = computed(
  () => graphData.value[AnalyticsGraphDataType.Revenue]?.total ?? 0
);

const productsRevenueCompare = computed(
  () => graphData.value[AnalyticsGraphDataType.Revenue]?.totalCompare ?? 0
);

const productsRevenuePercentage = computed(
  () =>
    (productsRevenue.value - productsRevenueCompare.value) /
    productsRevenueCompare.value
);

const productsRevenueSeries = computed(() => ({
  name: "Revenue",
  data:
    graphData.value[AnalyticsGraphDataType.Revenue]?.timeline.map(
      (value: IAnalyticsSparklineDataPoint) => ({
        x: value.date,
        y: value.value,
      })
    ) ?? [],
}));

const stats = computed(() => [
  {
    name: "Total product renders",
    stat: productsRendered.value,
    compareStat: productsRenderedCompare.value,
    percentage: productsRenderedPercentage.value,
    series: productsRenderedSeries.value,
    loading: fetching.value[AnalyticsGraphDataType.Render] ?? false,
    graphDataType: AnalyticsGraphDataType.Render,
    decimals: 0,
  },
  {
    name: "Total clicks",
    stat: productsClicked.value,
    compareStat: productsClickedCompare.value,
    percentage: productsClickedPercentage.value,
    series: productsClickedSeries.value,
    loading: fetching.value[AnalyticsGraphDataType.Click] ?? false,
    graphDataType: AnalyticsGraphDataType.Click,
    decimals: 0,
  },
  {
    name: "Total conversions",
    stat: productsConverted.value,
    compareStat: productsConvertedCompare.value,
    percentage: productsConvertedPercentage.value,
    series: productsConvertedSeries.value,
    loading: fetching.value[AnalyticsGraphDataType.Conversion] ?? false,
    graphDataType: AnalyticsGraphDataType.Conversion,
    decimals: 0,
  },
  {
    name: "Click rate",
    stat: `${(productsClickRate.value * 100).toFixed(2)}%`,
    compareStat: `${(productsClickRateCompare.value * 100).toFixed(2)}%`,
    percentage: productsClickRatePercentage.value,
    series: productsClickRateSeries.value,
    loading: fetching.value[AnalyticsGraphDataType.ClickRate] ?? false,
    graphDataType: AnalyticsGraphDataType.ClickRate,
    decimals: 2,
  },
  {
    name: "Conversion rate",
    stat: `${(productsConversionRate.value * 100).toFixed(2)}%`,
    compareStat: `${(productsConversionRateCompare.value * 100).toFixed(2)}%`,
    percentage: productsConversionRatePercentage.value,
    series: productsConversionRateSeries.value,
    loading: fetching.value[AnalyticsGraphDataType.ConversionRate] ?? false,
    graphDataType: AnalyticsGraphDataType.ConversionRate,
    decimals: 2,
  },
  {
    name: "Revenue",
    stat: `${currencySymbol.value} ${productsRevenue.value.toFixed(2)}`,
    compareStat: `${
      currencySymbol.value
    } ${productsRevenueCompare.value.toFixed(2)}`,
    percentage: productsRevenuePercentage.value,
    series: productsRevenueSeries.value,
    loading: fetching.value[AnalyticsGraphDataType.ConversionRate] ?? false,
    graphDataType: AnalyticsGraphDataType.Revenue,
    decimals: 2,
  },
]);

const topProductsLists = computed(() => [
  {
    name: "Top products clicked",
    stat: productsRendered.value,
    data: topProductsData.value[AnalyticsTopProductsType.Clicked] ?? [],
    loading: fetchingTopProducts.value[AnalyticsTopProductsType.Clicked],
    topProductsType: AnalyticsTopProductsType.Clicked,
  },
  {
    name: "Top products converted",
    stat: productsClicked.value,
    data: topProductsData.value[AnalyticsTopProductsType.Converted] ?? [],
    loading: fetchingTopProducts.value[AnalyticsTopProductsType.Converted],
    topProductsType: AnalyticsTopProductsType.Converted,
  },
]);

const selectedProductManagerSlugs: Ref<string[]> = ref([]);

const dateBaseDefault = [moment.utc().subtract(6, "days"), moment.utc()];
const dateCompareDefault = [
  moment.utc().subtract(13, "days"),
  moment.utc().subtract(7, "days"),
];

const dateBase = ref(dateBaseDefault);
const dateCompare = ref(dateCompareDefault);
const utcOffset = moment().format("Z");

const resetFilters = () => {
  selectedProductManagerSlugs.value = [];
};

const setFetchedFilters = () => {
  fetchedFilters.value = currentFilters.value;
};
const fetchedFilters = ref<string | null>(null);
const currentFilters = computed(() => {
  return JSON.stringify({
    productManagers: selectedProductManagerSlugs.value,
  });
});
const dataDiffersFromFilters = computed(
  () => fetchedFilters.value !== currentFilters.value
);

const getUrlParams = computed(() => {
  return {
    productManagers: selectedProductManagerSlugs.value.join(","),
    dateBase: [dateBase.value[0].startOf("day"), dateBase.value[1].endOf("day")]
      .map((d) => d.toISOString())
      .join(","),
    dateCompare: [
      dateCompare.value[0].startOf("day"),
      dateCompare.value[1].endOf("day"),
    ]
      .map((d) => d.toISOString())
      .join(","),
    utcOffset: utcOffset,
  };
});

const fetchGraphs = () => {
  if (!selectedPlugin.value) return;

  const plugin = selectedPlugin.value;
  stats.value.forEach((stat) =>
    analyticsStore.fetch(plugin, {
      graphDataType: stat.graphDataType,
      params: getUrlParams.value,
    })
  );
  topProductsLists.value.forEach((stat) =>
    analyticsStore.fetchTopProducts(plugin, {
      topProductsType: stat.topProductsType,
      params: getUrlParams.value,
    })
  );

  analyticsStore.fetchTopRecommendationsShown(plugin, {
    params: getUrlParams.value,
  });
  setFetchedFilters();
  analyticsStore.fetchTotalRevenuePlugins(plugin);
};

const firstFreePlugin = computed(() =>
  Object.values(plugins.value).find((p) => !p.meta.isPro)
);

const noProPluginInstalled = ref(false);
const proAndFreeInstalled = computed(() => {
  const hasFree = firstFreePlugin.value;
  const hasPro = Object.values(plugins.value).find((p) => p.meta.isPro);
  return hasFree && hasPro;
});
if (!props.pluginSlug) {
  const plugin = Object.values(plugins.value).find((p) => p.meta.isPro);
  if (plugin) {
    updateRoute(plugin);
  } else {
    noProPluginInstalled.value = true;
  }
} else {
  fetchGraphs();
}

const installedProPlugins = computed(() => {
  return Object.values(plugins.value).filter(
    (p) => p.meta.isPro && p.meta.extra.productsManager
  );
});

watch(
  () => props.pluginSlug,
  () => {
    fetchGraphs();
  }
);

const getSparkLineOptionsForItem = (item: any) => {
  return {
    tooltip: {
      y: {
        formatter: function (value: number, series: any) {
          return value.toFixed(item.decimals ?? 0);
        },
      },
    },
  };
};

const currentDatePickerTabIdx = ref(0);
const datePickerFormatter = ref({
  date: "YYYY-MM-DD",
  month: "MMM",
});

type IDatePickerObject = {
  start: string;
  end: string;
};

const getDatePickerObject = (date: Moment[]): IDatePickerObject => {
  return {
    start: date[0].format(datePickerFormatter.value.date),
    end: date[1].format(datePickerFormatter.value.date),
  };
};

const dateBasePicker = ref<IDatePickerObject>(
  getDatePickerObject(dateBaseDefault)
);
const dateComparePicker = ref<IDatePickerObject>(
  getDatePickerObject(dateCompareDefault)
);

const resetDates = (callback: () => void = () => ({})) => {
  dateBasePicker.value = getDatePickerObject(dateBase.value);
  dateComparePicker.value = getDatePickerObject(dateCompare.value);
  currentDatePickerTabIdx.value = 0;
  callback();
};
resetDates();

const setDateFilters = (callback: () => void) => {
  dateBase.value = [
    moment(dateBasePicker.value.start),
    moment(dateBasePicker.value.end),
  ];
  dateCompare.value = [
    moment(dateComparePicker.value.start),
    moment(dateComparePicker.value.end),
  ];
  fetchGraphs();
  resetDates(callback);
  currentDatePickerTabIdx.value = 0;
};

const datePickerTabs = computed(() => [
  { id: "base", name: "Date range", dates: dateBasePicker.value },
  { id: "compare", name: "Compare with", dates: dateComparePicker.value },
]);

const autoFillCompareToDate = (newValue: any) => {
  const baseMoments = [moment(newValue?.start), moment(newValue?.end)];
  const dateDiffBase =
    Math.abs(baseMoments[0].diff(baseMoments[1], "days")) + 1;
  dateComparePicker.value = getDatePickerObject([
    baseMoments[0].subtract(dateDiffBase, "days"),
    baseMoments[1].subtract(dateDiffBase, "days"),
  ]);
  currentDatePickerTabIdx.value = 1;
};

const isSelectedShortcut = (shortcut: any) => {
  const base = shortcut.base.map((d: Moment) =>
    d.format(datePickerFormatter.value.date)
  );
  const compare = shortcut.compare.map((d: Moment) =>
    d.format(datePickerFormatter.value.date)
  );

  return (
    dateBasePicker.value.start === base[0] &&
    dateBasePicker.value.end === base[1] &&
    dateComparePicker.value.start === compare[0] &&
    dateComparePicker.value.end === compare[1]
  );
};

const shortcuts = computed(() => [
  {
    label: "Last 7 Days",
    base: [moment.utc().subtract(6, "days"), moment.utc()],
    compare: [
      moment.utc().subtract(13, "days"),
      moment.utc().subtract(7, "days"),
    ],
  },
  {
    label: "Last 14 Days",
    base: [moment.utc().subtract(13, "days"), moment.utc()],
    compare: [
      moment.utc().subtract(27, "days"),
      moment.utc().subtract(14, "days"),
    ],
  },
  {
    label: "Last 30 Days",
    base: [moment.utc().subtract(29, "days"), moment.utc()],
    compare: [
      moment.utc().subtract(59, "days"),
      moment.utc().subtract(30, "days"),
    ],
  },
  {
    label: "Last 90 Days",
    base: [moment.utc().subtract(89, "days"), moment.utc()],
    compare: [
      moment.utc().subtract(179, "days"),
      moment.utc().subtract(90, "days"),
    ],
  },
  {
    label: "This Month vs. Last Month",
    base: [moment.utc().startOf("month"), moment.utc()],
    compare: [
      moment.utc().subtract(1, "months"),
      moment.utc().subtract(1, "months").startOf("month"),
    ],
  },
  {
    label: "This Month vs. This Month Last Year",
    base: [moment.utc().startOf("month"), moment.utc()],
    compare: [
      moment.utc().subtract(1, "years"),
      moment.utc().subtract(1, "years").startOf("month"),
    ],
  },
]);
</script>

<template>
  <div class="relative">
    <div
      v-if="noProPluginInstalled"
      class="absolute -inset-x-3 -inset-y-1 z-50 flex items-baseline justify-center bg-gray-100/5 backdrop-blur-sm"
    >
      <div class="mt-10 w-full md:max-w-md">
        <ACard>
          <ACardHeader>
            Ready to witness this plugin's performance?
            <template #subheader
              >Elevate with the PRO version!</template
            ></ACardHeader
          >
          <ACardContent class="-mt-6">
            <ul class="mb-6 ml-4 list-disc">
              <li>
                Gain <strong>deep insights</strong> into your recommendation
                performance
              </li>
              <li>
                Unlock true <strong>conversion and revenue</strong> analytics
              </li>
              <li>
                Supercharge your targeting with essential KPIs for
                <strong>upsells and cross-sells</strong>
              </li>
              <li>
                Plus, a <strong>treasure trove of options</strong> to fine-tune
                recommendations for maximum impact
              </li>
            </ul>
            <div class="mb-6 text-sm">
              Don't fret, we've got it all covered, even with the free version.
              Once you GO PRO, your insights start
              <strong>from the day the free version was activated</strong>!
            </div>
            <AButton
              v-if="firstFreePlugin"
              :append-icon="ArrowTopRightOnSquareIcon"
              block
              :href="firstFreePlugin.meta.websiteUrl"
              target="_blank"
              >GO PRO NOW</AButton
            >
            <div class="mt-5">
              This is how analytics shines in the PRO version:
            </div>
            <a
              :href="`${imagePrefix}/sparkwoo-analytics-preview.png`"
              target="_blank"
              class="cursor-zoom-in"
            >
              <img
                class="w-full"
                :src="`${imagePrefix}/sparkwoo-analytics-preview.png`"
                alt="SparkWoo Analytics Preview in PRO version"
              />
            </a>
          </ACardContent>
        </ACard>
      </div>
    </div>
    <TheHeader
      title="Analytics"
      subtitle="How your Spark Plugins perform over time"
    >
      <template #actions>
        <div class="relative flex items-center space-x-4">
          <Popover v-slot="{ open, close }">
            <PopoverButton
              :class="open ? '' : 'text-opacity-90'"
              class="group inline-flex h-9 items-center rounded-md border border-gray-300 pl-4 pr-2 text-xs transition-opacity hover:opacity-80"
            >
              <div class="flex flex-col">
                <div class="text-right">
                  <span class="font-medium"
                    >{{ dateBase[0].format("ll") }} -
                    {{ dateBase[1].format("ll") }}</span
                  >
                </div>
                <div class="text-right">
                  <span class="font-medium">vs. </span>
                  <span class="font-light"
                    >{{ dateCompare[0].format("ll") }} -
                    {{ dateCompare[1].format("ll") }}</span
                  >
                </div>
              </div>
              <ChevronDownIcon
                :class="open ? '' : 'text-opacity-70'"
                class="ml-2 h-5 w-5 text-gray-400 transition duration-150 ease-in-out group-hover:text-opacity-80"
                aria-hidden="true"
              />
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
                class="fixed -inset-1 z-40 transform px-4 md:absolute md:inset-auto md:right-0 md:mt-3 md:px-0"
              >
                <ACard>
                  <div class="border-b px-4 md:p-4">
                    <ol
                      role="list"
                      class="divide-y divide-gray-300 rounded-md border-gray-300 bg-white md:flex md:divide-y-0 md:border"
                    >
                      <li
                        v-for="(
                          datePickerTab, datePickerTabIdx
                        ) in datePickerTabs"
                        :key="datePickerTab.name"
                        class="relative mb-0 cursor-pointer md:flex md:flex-1"
                        @click="currentDatePickerTabIdx = datePickerTabIdx"
                      >
                        <a class="group flex w-full items-center">
                          <span class="flex items-center py-2 text-sm md:px-4">
                            <span
                              class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full"
                              :class="{
                                'bg-aprimary-600 group-hover:bg-aprimary-800':
                                  datePickerTabIdx === currentDatePickerTabIdx,
                                'bg-gray-100 group-hover:bg-gray-300':
                                  datePickerTabIdx !== currentDatePickerTabIdx,
                              }"
                            >
                              <component
                                :is="
                                  currentDatePickerTabIdx <= datePickerTabIdx
                                    ? CalendarDaysIcon
                                    : CheckIcon
                                "
                                class="h-6 w-6"
                                :class="{
                                  'text-white':
                                    currentDatePickerTabIdx ===
                                    datePickerTabIdx,
                                  'text-gray-900':
                                    currentDatePickerTabIdx !==
                                    datePickerTabIdx,
                                }"
                                aria-hidden="true"
                              />
                            </span>
                            <div
                              class="ml-4 flex flex-col text-sm text-gray-900"
                            >
                              <span class="font-medium">
                                {{ datePickerTab.name }}
                              </span>
                              <span>
                                {{
                                  moment(datePickerTab.dates.start).format("ll")
                                }}
                                -
                                {{
                                  moment(datePickerTab.dates.end).format("ll")
                                }}
                              </span>
                            </div>
                          </span>
                        </a>
                        <template
                          v-if="datePickerTabIdx !== datePickerTabs.length - 1"
                        >
                          <!-- Arrow separator for lg screens and up -->
                          <div
                            class="absolute right-0 top-0 hidden h-full w-5 md:block"
                            aria-hidden="true"
                          >
                            <svg
                              class="h-full w-full text-gray-300"
                              viewBox="0 0 22 80"
                              fill="none"
                              preserveAspectRatio="none"
                            >
                              <path
                                d="M0 -2L20 40L0 82"
                                vector-effect="non-scaling-stroke"
                                stroke="currentcolor"
                                stroke-linejoin="round"
                              />
                            </svg>
                          </div>
                        </template>
                      </li>
                    </ol>
                  </div>
                  <div
                    class="flex overflow-x-auto border-b px-4 py-4 md:block md:space-y-2 md:pb-2 md:pt-0"
                  >
                    <ABadge
                      class="mr-1 flex grow cursor-pointer whitespace-nowrap opacity-80 transition-opacity hover:opacity-100"
                      :color="isSelectedShortcut(shortcut) ? 'indigo' : 'gray'"
                      v-for="(shortcut, idx) in shortcuts"
                      :key="idx"
                      @click="
                        () => {
                          dateBasePicker = getDatePickerObject(shortcut.base);
                          dateComparePicker = getDatePickerObject(
                            shortcut.compare
                          );
                        }
                      "
                    >
                      {{ shortcut.label }}
                    </ABadge>
                  </div>
                  <div class="max-h-96 overflow-y-auto">
                    <div class="overflow-hidden">
                      <VueTailwindDatepicker
                        v-if="currentDatePickerTabIdx === 0"
                        v-model="dateBasePicker"
                        :formatter="datePickerFormatter"
                        no-input
                        class="-m-3 sm:-m-5"
                        :shortcuts="false"
                        @update:model-value="autoFillCompareToDate"
                      />
                      <VueTailwindDatepicker
                        v-if="currentDatePickerTabIdx === 1"
                        v-model="dateComparePicker"
                        :formatter="datePickerFormatter"
                        no-input
                        class="-m-3 sm:-m-5"
                        :shortcuts="false"
                      />
                    </div>
                  </div>
                  <ACardActions>
                    <AButton @click="close" outlined>Close</AButton>
                    <div class="grow"></div>
                    <AButton @click="() => resetDates()" outlined
                      >Reset dates</AButton
                    >
                    <AButton @click="setDateFilters(close)">Filter</AButton>
                  </ACardActions>
                </ACard>
              </PopoverPanel>
            </transition>
          </Popover>
          <Popover v-slot="{ open, close }">
            <PopoverButton
              :class="open ? '' : 'text-opacity-90'"
              class="group inline-flex h-9 items-center rounded-md bg-aprimary-600 pl-4 pr-2 text-sm font-medium leading-5 text-white hover:text-opacity-100"
            >
              <span>Filter</span>
              <ChevronDownIcon
                :class="open ? '' : 'text-opacity-70'"
                class="ml-2 h-5 w-5 text-aprimary-300 transition duration-150 ease-in-out group-hover:text-opacity-80"
                aria-hidden="true"
              />
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
                class="absolute right-0 z-40 mt-3 w-screen max-w-sm transform px-4 sm:px-0"
              >
                <ACard>
                  <ACardContent>
                    <ASubheader class="-mt-4 mb-2">Plugin</ASubheader>
                    <div class="space-y-2">
                      <div>Filter data based on plugin.</div>
                      <ACheckbox
                        v-model="selectedProductManagerSlugs"
                        v-for="p in installedProPlugins"
                        :key="p.meta.slug"
                        :label="p.meta.name"
                        :value="p.meta.extra.productsManager?.slug"
                      ></ACheckbox>
                    </div>
                  </ACardContent>
                  <ACardActions>
                    <div class="grow"></div>
                    <AButton @click="resetFilters" outlined
                      >Reset filters</AButton
                    >
                    <AButton
                      :disabled="!dataDiffersFromFilters"
                      @click="fetchGraphs(), close()"
                      >Filter</AButton
                    >
                  </ACardActions>
                </ACard>
              </PopoverPanel>
            </transition>
          </Popover>
        </div>
      </template>
    </TheHeader>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
      <ACard v-if="proAndFreeInstalled" class="col-span-full">
        <AAlert
          type="spark"
          :title="`🚀 Ready to supercharge your ${firstFreePlugin?.meta.name} experience?`"
        >
          The free version's data isn't making waves in the stats below. Elevate
          your game with <strong>{{ firstFreePlugin?.meta.name }} PRO</strong>!
          Unleash a data-driven transformation from the moment the free version
          was activated. Go PRO and watch your insights soar!
          <template #actions>
            <AButton
              v-if="firstFreePlugin"
              :append-icon="ArrowTopRightOnSquareIcon"
              :href="firstFreePlugin.meta.websiteUrl"
              target="_blank"
              >{{ firstFreePlugin?.meta.name }} PRO</AButton
            >
          </template>
        </AAlert>
      </ACard>
      <ACard class="col-span-full" inverted>
        <ACardContent class="!pt-2">
          <div class="md:flex">
            <div class="mr-10 space-y-2">
              <dt>
                <ASubheader inverted>Total Revenue SparkPlugins</ASubheader>
              </dt>
              <ALoadingOverlay
                :model-value="fetchingTotalRevenuePlugins"
              ></ALoadingOverlay>
              <dd class="whitespace-nowrap text-4xl font-semibold text-white">
                {{ registryStore.formatCurrency(totalRevenue, 0) }}
              </dd>
            </div>
            <div class="flex grow flex-wrap">
              <div
                v-for="p in productsManagers"
                :key="p.slug"
                class="mr-8 flex flex-col space-y-2"
              >
                <dt>
                  <ASubheader inverted>{{ p.slug }}</ASubheader>
                </dt>
                <dd
                  class="flex grow items-center whitespace-nowrap text-2xl font-semibold text-white/80"
                >
                  <span v-if="p.installed && p.isPro">
                    {{
                      registryStore.formatCurrency(
                        totalRevenuePlugins.find(
                          (r) => r.productsManager === p.slug
                        )?.revenue ?? 0,
                        0
                      )
                    }}
                  </span>
                  <ABadge
                    v-else-if="!p.installed && p.soon"
                    color="indigo"
                    class="opacity-60"
                  >
                    Soon!
                  </ABadge>
                  <AButton
                    v-else
                    :append-icon="ArrowTopRightOnSquareIcon"
                    :href="
                      registryStore.getPluginByProductsManagerSlug(p.slug)?.meta
                        .websiteUrl ?? `https://www.sparkplugins.com/${p.slug}`
                    "
                    target="_blank"
                    small
                    class="!hover:bg-white !bg-white !text-aprimary-700"
                  >
                    {{ p.installed ? "Get PRO" : "Download" }}
                  </AButton>
                </dd>
              </div>
            </div>
          </div>
        </ACardContent>
      </ACard>
      <dl class="col-span-full grid grid-cols-1 gap-5 md:grid-cols-3">
        <ACard
          v-for="item in stats"
          :key="item.name"
          class="relative flex h-36 flex-col justify-between"
        >
          <div class="px-4 pb-2 sm:px-6 sm:py-2">
            <dt>
              <ASubheader>{{ item.name }}</ASubheader>
            </dt>
            <dd
              v-if="!item.loading"
              class="mt-1 flex flex-wrap items-baseline justify-between"
            >
              <div
                class="flex items-baseline text-2xl font-semibold text-aprimary-600"
              >
                {{ item.stat }}
                <span class="ml-2 text-sm font-medium text-gray-500"
                  >vs. {{ item.compareStat }}</span
                >
              </div>

              <div
                v-if="Number.isFinite(item.percentage)"
                :class="[
                  item.percentage > 0
                    ? 'bg-green-100 text-green-800'
                    : item.percentage < 0
                    ? 'bg-red-100 text-red-800'
                    : 'bg-blue-100 text-blue-800',
                  'mt-1 inline-flex items-baseline rounded-full px-2.5 py-0.5 text-sm font-medium',
                ]"
              >
                <component
                  v-if="item.percentage !== 0"
                  :is="item.percentage > 0 ? ArrowUpIcon : ArrowDownIcon"
                  class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center text-green-500"
                  :class="{
                    'text-green-500': item.percentage > 0,
                    'text-red-500': item.percentage < 0,
                  }"
                  aria-hidden="true"
                ></component>
                <span class="sr-only">
                  {{ item.percentage > 0 ? "Increased" : "Decreased" }}
                  by
                </span>
                {{ Math.abs(item.percentage * 100).toFixed(2) }}%
              </div>
            </dd>
          </div>
          <ASparkLine
            v-if="!item.loading"
            :series="[item.series]"
            :options="getSparkLineOptionsForItem(item)"
          >
          </ASparkLine>
          <ALoadingOverlay :model-value="item.loading"></ALoadingOverlay>
        </ACard>
      </dl>
      <ACard v-for="(list, i) in topProductsLists" :key="i">
        <ACardHeader small> {{ list.name }} </ACardHeader>
        <ATable
          :headers="[
            { text: 'Product', value: 'title' },
            { text: 'Count', value: 'value', align: 'right' },
          ]"
          :items="list.data"
          :loading="list.loading"
          dense
        >
          <template #[`item.title`]="{ item }">
            <a
              :href="item.url"
              target="_blank"
              class="text-aprimary-900 hover:underline"
              >{{ item.title }}</a
            >
          </template>
        </ATable>
      </ACard>
      <ACard>
        <ACardHeader small> Top shown recommendations </ACardHeader>
        <ATable
          :headers="[
            { text: 'Recommendation', value: 'productsManager' },
            { text: 'Count', value: 'value', align: 'right' },
          ]"
          :items="topRecommendationsShown"
          :loading="fetchingTopRecommendationsShown"
          dense
        >
          <template #[`item.productsManager`]="{ item }">
            <div class="flex flex-col">
              <template
                v-if="
                  registryStore.getPluginByProductsManagerSlug(
                    item.productsManager
                  )
                "
              >
                {{
                  registryStore.getPluginByProductsManagerSlug(
                    item.productsManager
                  )?.meta.extra.productsManager?.title
                }}
              </template>
              <em v-else> Not installed</em>
              <span
                v-if="
                  registryStore.getPluginByProductsManagerSlug(
                    item.productsManager
                  )
                "
                class="text-xs font-normal text-gray-500"
              >
                {{
                  registryStore
                    .getPluginByProductsManagerSlug(item.productsManager)
                    ?.getPlacementHookByKey(item.placementHook)?.name ??
                  $filters.capitalize(item.placementHook)
                }}
              </span>
            </div>
          </template>
        </ATable>
      </ACard>
      <!-- <ACard>
        <ACardHeader small> Best converting </ACardHeader>
      </ACard> -->
    </div>
  </div>
  <TheFooter></TheFooter>
</template>
