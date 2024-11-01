<script setup lang="ts">
import TheWizard, { type IWizardStep } from "@/components/wizard/TheWizard.vue";
import WizardStepProductType from "@/components/wizard/WizardStepProductType.vue";
import WizardStepDisplay from "@/components/wizard/WizardStepDisplay.vue";
import WizardStepDesign from "@/components/wizard/WizardStepDesign.vue";
import { ref, type Ref, computed, watch, onMounted, nextTick } from "vue";
import useProductRecommendationsStore, {
  type IProductRecommendation,
} from "@/stores/product-recommendations";
import ACard from "@/components/common/ACard.vue";
import AContainerWrapper from "@/components/common/AContainerWrapper.vue";
import TheHeader from "@/components/TheHeader.vue";
import TheFooter from "@/components/TheFooter.vue";
import useRegistryStore from "@/stores/registry";
import AOverlay from "@/components/common/AOverlay.vue";
import ACardContent from "@/components/common/ACardContent.vue";
import ACardHeader from "@/components/common/ACardHeader.vue";
import ATextField from "@/components/common/ATextField.vue";
import ACardActions from "@/components/common/ACardActions.vue";
import AButton from "@/components/common/AButton.vue";
import { PencilSquareIcon } from "@heroicons/vue/20/solid";
import { mergeDeep } from "@/services/utils";

const props = defineProps<{
  id?: number;
}>();

const defaultPost = {
  name: "",
  productsManager: "",
  pageHooks: [],
  designStyle: {
    custom: false,
    addToCartButtonColor: null,
    addToCartButtonTextColor: null,
    backgroundColor: null,
    titleColor: null,
    paddingX: 0,
    paddingXUnit: "em",
    paddingY: 0,
    paddingYUnit: "em",
    titleTag: "h4",
    titleBold: false,
    titleItalic: false,
    titleUnderlined: false,
    titleMarginBottom: 0,
    titleMarginBottomUnit: "em",
  },
  designSettings: {
    titleAboveProducts: null,
    showAddToCartButton: true,
    showPrice: true,
    hideNoProducts: false,
    showOutOfStockProducts: false,
    numberToShow: 4,
    numberPerRow: 4,
    numberPerRowSm: 2,
    columnMargin: 2,
    columnMarginUnit: "em",
    useThemeColumnsSetting: true,
    useThemeNumberOfColumns: true,
    sliderEnabled: false,
    sliderShowArrows: false,
    sliderArrowsVariant: "plain",
    sliderArrowInside: false,
    sliderShowIndicator: false,
    sliderIndicatorVariant: "dots",
    sliderAuto: false,
    showAddAllToCart: false,
    showLoginSuggestion: true,
    titleShopTheCombination: "Shop the Combination",
    showMatchPercentage: false,
  },
};

const productRecommendationPost: Ref<IProductRecommendation> = ref(
  JSON.parse(JSON.stringify(defaultPost))
);

const steps: Ref<IWizardStep[]> = computed(() => [
  {
    key: "type",
    name: "Variant",
    description: "Determine the products to show",
    nextDisabled: productRecommendationPost.value.productsManager.length === 0,
  },
  {
    key: "display",
    name: "Display",
    description: "Where to display the products",
  },
  {
    key: "design",
    name: "Design",
    description: "Give the products a personal touch",
  },
]);

const productRecommendationsStore = useProductRecommendationsStore();
const wizardStepProductType = ref<InstanceType<typeof WizardStepProductType>>();

const fillNameOverlay = ref<boolean>(!props.id);
const newName = ref<string>(productRecommendationPost.value.name);
const newNameField = ref<InstanceType<typeof ATextField>>();
const saveName = () => {
  productRecommendationPost.value.name = newName.value;
  fillNameOverlay.value = false;
};
const openName = async () => {
  fillNameOverlay.value = true;
  newName.value = productRecommendationPost.value.name;
  await nextTick();
  newNameField.value?.focus();
  newNameField.value?.select();
};

onMounted(() => {
  if (props.id) {
    productRecommendationsStore.fetchSingle(props.id).then((item) => {
      productRecommendationPost.value = mergeDeep(
        JSON.parse(JSON.stringify(defaultPost)),
        item
      );
    });
  } else {
    wizardStepProductType.value?.initialize();
  }
});

watch(
  () => productRecommendationsStore.items,
  () => {
    wizardStepProductType.value?.initialize();
  }
);

const registryStore = useRegistryStore();
const selectedPlugin = computed(() =>
  registryStore.getPluginByProductsManagerSlug(
    productRecommendationPost.value.productsManager
  )
);

const loading = computed(() => {
  return (
    productRecommendationsStore.isFetching ||
    (props.id && productRecommendationsStore.isFetchingSingle(props.id)) ||
    (props.id && productRecommendationsStore.isUpdatingSingle(props.id)) ||
    productRecommendationsStore.isCreating
  );
});
</script>

<template>
  <AContainerWrapper>
    <TheHeader
      :title="`${productRecommendationPost.id ? 'Edit' : 'Create'} ${
        productRecommendationPost.name.length
          ? `'${productRecommendationPost.name}'`
          : 'a product recommendation'
      }`"
      subtitle="Easily setup a section to promote products anywhere"
      :backTo="{ name: 'product-recommendations' }"
      backText="All product recommendations"
    >
      <template #actions>
        <AButton @click="openName" :icon="PencilSquareIcon">
          Edit name
        </AButton>
      </template>
    </TheHeader>
    <ACard class="relative">
      <AOverlay
        :persistent="!productRecommendationPost.name"
        v-model="fillNameOverlay"
        absolute
      >
        <ACard class="max-w-sm">
          <ACardHeader>Name it!</ACardHeader>
          <ACardContent class="-mt-6">
            Label your product recommendation, exclusively for your quick
            recall. It will not be shown to your visitors.
            <ATextField
              class="mt-4"
              v-model="newName"
              placeholder="Name..."
              @keydown.enter="saveName"
              ref="newNameField"
              hint="Finish the whole wizard to update the name"
              data-cy="name"
            ></ATextField>
          </ACardContent>
          <ACardActions>
            <div class="grow"></div>
            <AButton
              :disabled="newName.length === 0"
              @click="saveName"
              data-cy="name-ok"
            >
              Continue
            </AButton>
          </ACardActions>
        </ACard>
      </AOverlay>
      <TheWizard
        v-model="productRecommendationPost"
        :steps="steps"
        :create="(data:any) => productRecommendationsStore.create(data, {}, selectedPlugin)"
        :update="(id: number, data:any) => productRecommendationsStore.updateSingle(id, data, {}, selectedPlugin)"
        :loading="loading"
      >
        <template #[`step.type`]>
          <WizardStepProductType
            v-model="productRecommendationPost"
            ref="wizardStepProductType"
          ></WizardStepProductType>
        </template>
        <template #[`step.display`]>
          <WizardStepDisplay
            v-model="productRecommendationPost"
          ></WizardStepDisplay>
        </template>
        <template #[`step.design`]>
          <WizardStepDesign
            v-model="productRecommendationPost"
            :plugin="selectedPlugin"
          ></WizardStepDesign>
        </template>
      </TheWizard>
    </ACard>
    <TheFooter></TheFooter>
  </AContainerWrapper>
</template>
