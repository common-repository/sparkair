<template>
  <div class="flex flex-col overflow-hidden" :class="{ 'rounded-lg': rounded }">
    <div class="overflow-x-auto lg:mx-0">
      <div
        class="relative inline-block min-w-full align-middle"
        :class="{ 'max-h-96 overflow-auto': limitHeight }"
      >
        <div
          v-if="refreshing"
          class="absolute right-4 top-4 z-50 flex h-20 w-20 flex-col items-center justify-center rounded bg-white opacity-90 shadow"
        >
          <span
            class="mdi mdi-spin mdi-loading inline-flex text-4xl text-aprimary-500"
          ></span>
          <span class="mt-2 inline-flex text-xs text-gray-500">
            Loading...
          </span>
        </div>
        <table class="min-w-full border-separate" style="border-spacing: 0">
          <thead class="bg-gray-50">
            <tr class="text-left text-xs uppercase">
              <th
                v-for="header in headers"
                :key="`header-${header.value}`"
                scope="col"
                class="sticky top-0 z-10 whitespace-nowrap border-b border-gray-300 bg-gray-50 bg-opacity-75 font-medium backdrop-blur backdrop-filter"
                :class="{
                  'py-3.5 pl-4 pr-3 sm:pl-6 lg:pl-6': isFirstHeader(
                    header.value
                  ),
                  'px-3 py-3.5 ':
                    !isFirstHeader(header.value) && !isLastHeader(header.value),
                  'py-3.5 pl-3 pr-4 sm:pr-6 lg:pr-6': isLastHeader(
                    header.value
                  ),
                  'text-right': header?.align === 'right',
                  'text-left': !header?.align || header?.align === 'left',
                }"
              >
                <span
                  class="group inline-flex w-full items-center text-gray-500 hover:text-gray-500"
                  :class="{
                    'cursor-pointer': header.sortable,
                    'flex-row-reverse': header?.align === 'right',
                  }"
                  @click="sortColumn(header)"
                >
                  <slot :name="`header.${header.value}`" v-bind:item="header">
                    <span
                      v-if="header.icon"
                      :class="`mdi ${header.icon} mr-1`"
                    ></span>
                    {{ header.text }}
                  </slot>
                  <span
                    v-if="header.sortable"
                    class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible"
                    :class="{
                      'visible bg-gray-200 text-gray-900 group-hover:bg-gray-300':
                        sortedColumn === header.value,
                      invisible: sortedColumn !== header.value,
                    }"
                  >
                    <component
                      :is="
                        sortedColumn === header.value && sortedDesc
                          ? ChevronUpIcon
                          : ChevronDownIcon
                      "
                      class="h-5 w-5"
                      aria-hidden="true"
                    />
                  </span>
                </span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white">
            <template v-if="innerItems.length === 0">
              <tr>
                <td :colspan="headers.length" class="text-center text-sm">
                  <ALoadingBar v-if="loading" :loading="loading">
                    {{ loadingText }}
                  </ALoadingBar>
                  <div v-else class="my-2 text-sm text-gray-400">
                    <em>No items found...</em>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr
                v-for="(item, itemIndex) in innerItems"
                :key="`item-${itemIndex}`"
                @click="emit('click:row', item)"
              >
                <td
                  v-for="header in headers"
                  :key="`item-${header.value}-${itemIndex}`"
                  :class="{
                    'pl-4 pr-3 font-medium text-gray-900 sm:pl-6 lg:pl-6':
                      isFirstHeader(header.value),
                    'px-3':
                      !isFirstHeader(header.value) &&
                      !isLastHeader(header.value),
                    'pl-3 pr-4 sm:pr-6 lg:pr-6': isLastHeader(header.value),
                    'py-3.5': !dense,
                    'py-2': dense,
                    'border-b border-gray-200': itemIndex !== items.length - 1,
                    'text-right':
                      header?.align === 'right' || header.value === 'actions',
                    'text-left': !header?.align || header?.align === 'left',
                    'whitespace-nowrap': !header?.wrap,
                    'text-gray-500': !isFirstHeader(header.value),
                    'cursor-pointer': pointer,
                  }"
                  class="text-sm"
                >
                  <div
                    class="flex items-center"
                    :class="{
                      'justify-end text-right':
                        header?.align === 'right' || header.value === 'actions',
                      'justify-start text-left':
                        !header?.align || header?.align === 'left',
                    }"
                  >
                    <slot
                      :name="`item.${header.value}`"
                      v-bind:item="item"
                      v-bind:header="header"
                    >
                      {{ $filters.propertyByDotNotation(item, header.value) }}
                    </slot>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import ALoadingBar from "@/components/common/ALoadingBar.vue";
import { storeToRefs } from "pinia";
import { ChevronDownIcon, ChevronUpIcon } from "@heroicons/vue/20/solid";

export interface ATableHeader {
  text?: string;
  value: string;
  align?: string;
  icon?: string;
  wrap?: string;
  sortable?: boolean;
}

const props = withDefaults(
  defineProps<{
    headers: ATableHeader[];
    items: any[];
    dense?: boolean;
    rounded?: boolean;
    loading?: boolean;
    refreshing?: boolean;
    loadingText?: string;
    limitHeight?: boolean;
    pointer?: boolean;
  }>(),
  {
    dense: false,
    rounded: true,
    loading: false,
    refreshing: false,
    loadingText: "Items are being loaded",
  }
);

const emit = defineEmits(["click:row"]);

const sortedColumn = ref<string | null>(null);
const sortedDesc = ref(false);

const isFirstHeader = (key: string) => {
  return props.headers.findIndex((h) => h.value === key) === 0;
};

const isLastHeader = (key: string) => {
  return (
    props.headers.findIndex((h) => h.value === key) === props.headers.length - 1
  );
};

const sortColumn = (header: ATableHeader) => {
  if (!header.sortable) return;
  if (sortedColumn.value !== header.value) {
    sortedColumn.value = header.value;
    sortedDesc.value = false;
  } else if (!sortedDesc.value) {
    sortedDesc.value = true;
  } else {
    sortedColumn.value = null;
    sortedDesc.value = false;
  }
};

const innerItems = computed(() => {
  const items = [...props.items];
  items.sort((a: any, b: any) => {
    if (!sortedColumn.value) return 1;
    const valueA = String(a[sortedColumn.value]).toLowerCase();
    const valueB = String(b[sortedColumn.value]).toLowerCase();
    const sortDirection = sortedDesc.value ? 1 : -1;
    return valueA >= valueB ? sortDirection * -1 : sortDirection * 1;
  });
  return items;
});
</script>
