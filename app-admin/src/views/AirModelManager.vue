<script setup lang="ts">
import AObjectEditor from "@/components/common/AObjectEditor.vue";
import ATextField from "@/components/common/ATextField.vue";
import useAirModelsStore, {
  type IAirModel,
  airModelSchema,
} from "@/stores/air-models";
import useRegistryStore from "@/stores/registry";
import { computed, ref } from "vue";
import ALoadingOverlay from "@/components/common/ALoadingOverlay.vue";
import { storeToRefs } from "pinia";
import { PhotoIcon, UserCircleIcon } from "@heroicons/vue/24/solid";
import ACardContent from "@/components/common/ACardContent.vue";
import ASwitch from "@/components/common/ASwitch.vue";
import ACardActions from "@/components/common/ACardActions.vue";
import AButton from "@/components/common/AButton.vue";
import { CheckIcon, TrashIcon } from "@heroicons/vue/24/outline";
import AOverlay from "@/components/common/AOverlay.vue";
import BadgeFreePro from "@/components/BadgeFreePro.vue";
import ACard from "@/components/common/ACard.vue";
import ACardHeader from "@/components/common/ACardHeader.vue";
import AAlert from "@/components/common/AAlert.vue";
import ABadge from "@/components/common/ABadge.vue";

const props = defineProps<{
  id?: number;
}>();

const modelManager = ref<InstanceType<typeof AObjectEditor>>();

const airModelsStore = useAirModelsStore();
const { isFetching, isFetchingSingle } = storeToRefs(airModelsStore);

const registryStore = useRegistryStore();
const sparkAirPlugin =
  registryStore.plugins.sparkair ?? registryStore.plugins["sparkair-pro"];

const airModel = ref<IAirModel | null>(null);

if (props.id) {
  airModelsStore.fetchSingle(props.id, sparkAirPlugin).then((a) => {
    airModel.value = a;
    modelManager.value?.open(airModel.value);
  });
}
const isLoading = computed(
  () => !!isFetching.value || !!(props.id && isFetchingSingle.value(props?.id))
);
</script>

<template>
  <AObjectEditor
    ref="modelManager"
    name="AI Model"
    :store="airModelsStore"
    :schema="airModelSchema"
    type="inline"
    :defaults="{}"
    @closed="$router.push({ name: 'ai-tools' })"
  >
    <template #default="{ save, loading, errors, values }">
      <ALoadingOverlay :model-value="isLoading"></ALoadingOverlay>
      <ACardContent>
        <div class="divide-y">
          <div
            class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 pt-6 md:grid-cols-3"
          >
            <div>
              <span class="text-base font-semibold leading-7 text-gray-900">
                Basic model information
              </span>
              <div class="mt-1 text-sm leading-6 text-gray-600">
                This information will not affect the predicted recommendations,
                it are just meta settings.
              </div>
            </div>

            <div
              class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2"
            >
              <div class="sm:col-span-4">
                <ATextField
                  label="Model's name"
                  validation-name="name"
                ></ATextField>
              </div>
              <div class="sm:col-span-4">
                <ATextField
                  :model-value="values.documentId"
                  label="Model's training-ID"
                  readonly
                  hint="Just for reference"
                ></ATextField>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-x-8 gap-y-10 py-12 md:grid-cols-3">
            <div>
              <span class="text-base font-semibold leading-7 text-gray-900">
                Fine tuning
              </span>
              <div class="mt-1 text-sm leading-6 text-gray-600">
                These parameters will affect the model's training and so the
                output of the predicted recommendations.
              </div>
            </div>

            <div
              class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2"
            >
              <div class="sm:col-span-4">
                <ASwitch
                  validation-name="tuneAutomatically"
                  label="Automatically tune this model when training"
                ></ASwitch>
              </div>
              <div class="sm:col-span-3">
                <ATextField
                  label="Features"
                  validation-name="features"
                  type="number"
                  :readonly="values.tuneAutomatically"
                ></ATextField>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-x-8 gap-y-10 py-12 md:grid-cols-3">
            <div>
              <span class="text-base font-semibold leading-7 text-gray-900">
                User's implicit likes
              </span>
              <div class="mt-1 text-sm leading-6 text-gray-600">
                The recommendations are all based on what users 'implicitly
                like'. The 'implicit like' is a rating between 0 and 5. In this
                section you can configure what events will contribute to the
                rating.
              </div>
            </div>

            <div class="max-w-2xl space-y-10 md:col-span-2">
              <fieldset>
                <legend class="text-sm font-semibold leading-6 text-gray-900">
                  By Bought Products
                </legend>
                <div class="mt-6">
                  <div class="relative flex gap-x-3">
                    <ATextField
                      label="Rating per order"
                      validation-name="ratingPerOrder"
                      type="number"
                      :readonly="values.tuneAutomatically"
                    ></ATextField>
                    <ATextField
                      label="Max orders to use"
                      validation-name="ratingCountOrder"
                      type="number"
                      :readonly="values.tuneAutomatically"
                    ></ATextField>
                  </div>
                  <div>
                    Every time a user bought a product, the rating will be
                    increased with {{ values.ratingPerOrder }} until a maximum
                    of {{ values.ratingCountOrder }} order(s). For example, in
                    case a product is bought in 3 different orders, the rating
                    will be affected by
                    {{
                      Math.min(
                        values.ratingPerOrder *
                          Math.min(values.ratingCountOrder, 3),
                        5
                      )
                    }}
                    points.
                  </div>
                </div>
              </fieldset>
              <fieldset>
                <legend class="text-sm font-semibold leading-6 text-gray-900">
                  By Viewed Products
                  <ABadge color="aprimary" class="ml-2">With SparkRVP</ABadge>
                </legend>
                <div class="relative mt-6">
                  <AOverlay
                    :model-value="!sparkAirPlugin?.rvpInstalled"
                    absolute
                  >
                    <ACard>
                      <AAlert>
                        The information collected through
                        <a
                          class="font-bold"
                          :href="sparkAirPlugin?.rvpUrl"
                          target="_blank"
                          >SparkRVP</a
                        >, specifically the Recently Viewed Products data for
                        each visitor, provides you extra options for fine-tuning
                        the model here.
                      </AAlert>
                    </ACard>
                  </AOverlay>
                  <div class="relative flex gap-x-3">
                    <ATextField
                      label="Rating per view"
                      validation-name="ratingPerView"
                      type="number"
                      :readonly="values.tuneAutomatically"
                    ></ATextField>
                    <ATextField
                      label="Max views to use"
                      validation-name="ratingCountView"
                      type="number"
                      :readonly="values.tuneAutomatically"
                    ></ATextField>
                  </div>
                  <div>
                    Every time a user views a product, the rating will be
                    increased with {{ values.ratingPerView }} until a maximum of
                    {{ values.ratingCountView }} view(s). For example, in case a
                    product is viewed in 5 times, the rating will be affected by
                    {{
                      Math.min(
                        values.ratingPerView *
                          Math.min(values.ratingCountView, 5),
                        5
                      )
                    }}
                    points.
                  </div>
                </div>
              </fieldset>
            </div>
          </div>
        </div>
      </ACardContent>
    </template>
    <template
      #footer="{ values, deleteByObject, close, save, errors, loading, keyId }"
    >
      <ACardActions>
        <AButton
          v-if="values[keyId] && !values.modelActivatedDateTime"
          @click="deleteByObject()"
          outlined
          :icon="TrashIcon"
        >
          Delete
        </AButton>
        <div class="grow"></div>
        <AButton @click="close()" outlined> Cancel </AButton>
        <AButton
          @click="save()"
          :loading="loading"
          :disabled="!!errors.length"
          :icon="CheckIcon"
        >
          Save
        </AButton></ACardActions
      >
    </template>
  </AObjectEditor>
</template>
