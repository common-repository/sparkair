<script setup lang="ts">
import ACard from "@/components/common/ACard.vue";
import ATable, { type ATableHeader } from "@/components/common/ATable.vue";
import useProductRecommendationsStore, {
  type IProductRecommendation,
} from "@/stores/product-recommendations";
import TheHeader from "@/components/TheHeader.vue";
import { storeToRefs } from "pinia";
import {
  CheckIcon,
  CodeBracketIcon,
  PencilIcon,
  PlusIcon,
  TrashIcon,
  ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";
import AButton from "@/components/common/AButton.vue";
import ABadge from "@/components/common/ABadge.vue";
import { createShortcode } from "@/services/shortcode";
import { useClipboard } from "@vueuse/core";
import ATooltip from "@/components/common/ATooltip.vue";
import { ref } from "vue";
import AConfirmDialog from "@/components/common/AConfirmDialog.vue";
import AContainerWrapper from "@/components/common/AContainerWrapper.vue";
import TheFooter from "@/components/TheFooter.vue";
import useRegistryStore from "@/stores/registry";
import ASwitch from "@/components/common/ASwitch.vue";

const registryStore = useRegistryStore();

const { copy, copied } = useClipboard();

const copiedId = ref(0);

const copiedItem = (item: IProductRecommendation) => {
  return copied && copiedId.value === item?.id;
};

const copyShortcode = (item: IProductRecommendation) => {
  const shortcode = registryStore.getPluginByProductsManagerSlug(
    item.productsManager
  )?.meta.extra.productsManager?.shortcode;
  if (!shortcode || !item.id) return;
  copy(createShortcode(shortcode, { id: item.id }));
  copiedId.value = item.id;
};

const getPluginMeta = (item: IProductRecommendation) => {
  return registryStore.getPluginByProductsManagerSlug(item.productsManager)
    ?.meta;
};

const productRecommendationsStore = useProductRecommendationsStore();
productRecommendationsStore.fetch();

const { all, isFetching, isDeletingSingle } = storeToRefs(
  productRecommendationsStore
);

const headers = [
  { text: "Product recommendation", value: "name" },
  { text: "Enabled", value: "status" },
  { text: "Displayed on", value: "pageHooks" },
  { text: "Design style", value: "designStyle.custom" },
  { text: "", value: "actions", align: "right" },
] as ATableHeader[];

const deleteConfirmOpen = ref(false);

const itemToDelete = ref<IProductRecommendation | null>(null);
const deleteItem = (item: IProductRecommendation) => {
  deleteConfirmOpen.value = true;
  itemToDelete.value = item;
};
const confirmDelete = async () => {
  if (itemToDelete.value?.id) {
    await productRecommendationsStore.deleteSingle(itemToDelete.value.id);
  }
  deleteConfirmOpen.value = false;
};
</script>

<template>
  <AContainerWrapper>
    <TheHeader
      title="Product recommendations"
      subtitle="Easily setup a section to promote products anywhere"
    >
      <template #actions>
        <AButton
          class="hidden sm:inline-flex"
          :to="{ name: 'wizard' }"
          :icon="PlusIcon"
        >
          Create new
        </AButton>
      </template>
    </TheHeader>
    <ACard>
      <ATable
        :items="all"
        :loading="isFetching"
        :headers="headers"
        @click:row="
          (item: any) => {
            if (!registryStore.getPluginByProductsManagerSlug(
              item.productsManager
            )) return;
            $router.push({
              name: 'wizard',
              params: { productRecommendationId: item.id },
            })
          }
        "
        pointer
      >
        <template #[`item.name`]="{ item }">
          <div>
            <div>
              <span v-if="item.name.length">
                {{ item.name }}
              </span>
              <span v-else>No name given</span>
              <span
                class="font-light"
                v-if="
                  item.designSettings.titleAboveProducts !==
                  registryStore.getPluginByProductsManagerSlug(
                    item.productsManager
                  )?.meta.extra.productsManager?.title
                "
              >
                - {{ item.designSettings.titleAboveProducts }}
              </span>
            </div>
            <div class="text-xs font-light">
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
              <div class="flex items-center" v-else>
                <ExclamationCircleIcon
                  class="inline h-4 w-4 text-red-600"
                ></ExclamationCircleIcon>
                <em> This plugin is not installed anymore </em>
              </div>
            </div>
          </div>
        </template>
        <template #[`item.status`]="{ item }">
          <ASwitch
            @click.stop
            :model-value="item.status === 'publish'"
            @update:model-value="
              (item.status = item.status === 'publish' ? 'draft' : 'publish'),
                productRecommendationsStore.updateSingle(item.id, {
                  ...item,
                })
            "
          ></ASwitch>
        </template>
        <template #[`item.pageHooks`]="{ item }">
          <ABadge
            v-if="
              item.pageHooks.length > 0 &&
              registryStore.getPluginByProductsManagerSlug(item.productsManager)
            "
            color="indigo"
          >
            {{
              registryStore
                .getPluginByProductsManagerSlug(item.productsManager)
                ?.getPlacementHookByKey(item.pageHooks[0])?.name ??
              item.pageHooks[0]
            }}
          </ABadge>
          <span
            v-else-if="
              !registryStore.getPluginByProductsManagerSlug(
                item.productsManager
              )
            "
            class="text-sm"
          >
            -
          </span>
          <span v-else class="text-sm"> Not displayed using presets </span>
          <em v-if="item.pageHooks.length > 1" class="ml-2 text-xs">
            + {{ item.pageHooks.length - 1 }} other locations
          </em>
        </template>
        <template #[`item.designStyle.custom`]="{ item }">
          <span v-if="item.designStyle.custom">Custom</span>
          <span v-else>Default theme</span>
        </template>
        <template #[`item.actions`]="{ item }">
          <div class="space-x-4">
            <template
              v-if="
                registryStore.getPluginByProductsManagerSlug(
                  item.productsManager
                )
              "
            >
              <ATooltip
                v-if="true || getPluginMeta(item)?.isPro"
                :text="copiedItem(item) ? 'Copied!' : 'Copy shortcode'"
              >
                <AButton
                  :icon="copiedItem(item) ? CheckIcon : CodeBracketIcon"
                  icon-only
                  small
                  @click.stop="copyShortcode(item)"
                ></AButton>
              </ATooltip>
              <ATooltip v-else text="Shortcode available in PRO">
                <a
                  class="block px-0.5"
                  @click.stop
                  :href="
                    getPluginMeta(item)?.websiteUrl ??
                    `https://www.sparkplugins.com/${item.productsManager}`
                  "
                  target="_blank"
                >
                  <CodeBracketIcon class="h-5 w-5 px-0.5 text-red-700" />
                </a>
              </ATooltip>
              <ATooltip text="Edit">
                <AButton
                  :to="{
                    name: 'wizard',
                    params: { productRecommendationId: item.id },
                  }"
                  :icon="PencilIcon"
                  icon-only
                  small
                ></AButton>
              </ATooltip>
            </template>
            <ATooltip text="Delete">
              <AButton
                @click.stop="deleteItem(item)"
                :icon="TrashIcon"
                icon-only
                small
                :loading="isDeletingSingle(item.id)"
              ></AButton>
            </ATooltip>
          </div>
        </template>
      </ATable>
      <button
        type="button"
        class="fixed bottom-3 right-3 z-10 rounded-full bg-aprimary-600 p-2 text-white shadow-lg hover:bg-aprimary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-aprimary-600 sm:hidden"
        @click="$router.push({ name: 'wizard' })"
      >
        <PlusIcon class="h-8 w-8" aria-hidden="true" />
      </button>
    </ACard>
    <AConfirmDialog
      v-model="deleteConfirmOpen"
      title="Are you sure?"
      confirm-text="Yes"
      @confirm="confirmDelete"
    >
      Are you sure you want to delete this product recommendation?
    </AConfirmDialog>
    <TheFooter></TheFooter>
  </AContainerWrapper>
</template>
