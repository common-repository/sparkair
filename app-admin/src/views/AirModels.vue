<script setup lang="ts">
import ACard from "@/components/common/ACard.vue";
import ATable, { type ATableHeader } from "@/components/common/ATable.vue";
import useAiModelStore, { type IAirModel } from "@/stores/air-models";
import TheHeader from "@/components/TheHeader.vue";
import { storeToRefs } from "pinia";
import {
  PencilIcon,
  PlusIcon,
  TrashIcon,
  SparklesIcon,
  RocketLaunchIcon,
  CheckBadgeIcon,
} from "@heroicons/vue/24/outline";
import {
  CheckBadgeIcon as CheckBadgeIconSolid,
  CheckCircleIcon,
} from "@heroicons/vue/24/solid";
import AButton from "@/components/common/AButton.vue";
import ATooltip from "@/components/common/ATooltip.vue";
import AConfirmDialog from "@/components/common/AConfirmDialog.vue";
import useRegistryStore from "@/stores/registry";
import { computed, onUnmounted, ref } from "vue";
import ACardHeader from "@/components/common/ACardHeader.vue";
import ABadge from "@/components/common/ABadge.vue";
import dayjs from "dayjs";
import localizedFormat from "dayjs/plugin/localizedFormat";
import ALoadingIcon from "@/components/common/ALoadingIcon.vue";
import { onBeforeRouteLeave } from "vue-router";
import { ExclamationCircleIcon, XCircleIcon } from "@heroicons/vue/20/solid";
dayjs.extend(localizedFormat);

const registryStore = useRegistryStore();

const aiModelStore = useAiModelStore();
aiModelStore.fetch();

const { all, isFetching, isDeletingSingle } = storeToRefs(aiModelStore);

const aiModels = computed(() =>
  all.value.sort((a, b) => a.name.localeCompare(b.name))
);

const headers = [
  { text: "AI Model", value: "name" },
  { text: "RMSE", value: "rmse" },
  { text: "Active", value: "active" },
  { text: "Training status", value: "trainingStatus" },
  { text: "# users", value: "usersCount" },
  { text: "# products", value: "itemsCount" },
  { text: "", value: "actions", align: "right" },
] as ATableHeader[];

const sparkAirPlugin = registryStore.plugins["sparkair"];
const api = aiModelStore.getApi({ plugin: sparkAirPlugin });

const isSubmittingTraining = ref<number | null>(0);

const trainModel = async (model: IAirModel) => {
  isSubmittingTraining.value = model.id ?? null;
  try {
    const newModelResponse = await api.post(
      `/ai-tools/models/${model.id}/train`
    );
    const newModel = newModelResponse?.data?.data;
    if (newModel?.id) {
      aiModelStore.updateItemInStore(newModel.id, newModel);
    }
  } finally {
    isSubmittingTraining.value = 0;
  }
};

const trainingAnyModel = computed(() => {
  return (
    !!isSubmittingTraining.value ||
    all.value.some(
      (m) =>
        m.trainingInitiatedDateTime.length &&
        !m.trainingErroredDateTime.length &&
        !m.trainingFinishedDateTime.length
    )
  );
});

const isLoadingActiveModel = ref(false);

const activateModel = async (model: IAirModel) => {
  isLoadingActiveModel.value = true;
  await api.post(`/ai-tools/models/${model.id}/activate`);
  try {
    await aiModelStore.fetch({
      force: true,
    });
  } finally {
    isLoadingActiveModel.value = false;
  }
};

const deleteConfirmOpen = ref(false);

const updater = setInterval(() => {
  aiModelStore.fetch({
    force: true,
  });
}, 10 * 1000);

const cancelUpdater = () => {
  if (updater) {
    clearInterval(updater);
  }
};

onUnmounted(() => {
  cancelUpdater();
});

onBeforeRouteLeave(() => {
  cancelUpdater();
});
</script>

<template>
  <ACardHeader>
    Recommendation models trained with SparkAIR
    <template #subheader>
      This section is for the more advanced users
    </template>
    <template #actions>
      <AButton :to="{ name: 'model-manager' }" :icon="PlusIcon">
        Create new
      </AButton>
    </template>
  </ACardHeader>
  <ATable :items="aiModels" :loading="isFetching" :headers="headers">
    <template #[`item.rmse`]="{ item }">
      <ATooltip
        v-if="item.rmse"
        text="Root Mean Squared Error of the model. This is the model performance indicator. The closer the value to 0, the better the recommendations are."
      >
        <span> {{ item.rmse.toFixed(3) }}</span>
      </ATooltip>
      <span v-else>-</span>
    </template>
    <template #[`item.usersCount`]="{ item }">
      <span v-if="item.usersCount">{{ item.usersCount }}</span>
      <span v-else>-</span>
    </template>
    <template #[`item.itemsCount`]="{ item }">
      <span v-if="item.itemsCount">{{ item.itemsCount }}</span>
      <span v-else>-</span>
    </template>
    <template #[`item.active`]="{ item }">
      <div class="relative">
        <ATooltip
          v-if="item.modelActivatedDateTime"
          :text="`Active model trained on ${dayjs(
            item.trainingLastSuccessDateTime
          ).format('lll')}`"
        >
          <ABadge color="aprimary"> Currently active </ABadge>
        </ATooltip>
        <ABadge v-else color="gray">Inactive</ABadge>
        <ALoadingIcon
          class="absolute -right-6 top-1"
          v-if="isLoadingActiveModel"
          size="w-3 h-3"
        ></ALoadingIcon>
      </div>
    </template>
    <template #[`item.trainingStatus`]="{ item }">
      <div class="relative flex items-center space-x-1">
        <ALoadingIcon
          v-if="isSubmittingTraining === item.id"
          size="w-4 h-4 shrink-0"
        ></ALoadingIcon>
        <template
          v-else-if="
            !item.trainingInitiatedDateTime.length ||
            item.trainingFinishedDateTime.length
          "
        >
          <CheckCircleIcon
            class="h-5 w-5 shrink-0"
            :class="{
              'text-gray-200': !item.trainingInitiatedDateTime.length,
              'text-green-600': item.trainingFinishedDateTime.length,
            }"
          ></CheckCircleIcon>
          <ATooltip
            v-if="
              item.trainingFinishedDateTime.length &&
              item.paramsHashCurrent !== item.paramsHashTrained
            "
            text="Trained model does not have the same parameters as the current model."
          >
            <ExclamationCircleIcon class="ml-1 h-5 w-5 shrink-0 text-amber-600">
            </ExclamationCircleIcon>
          </ATooltip>
        </template>
        <XCircleIcon
          v-else-if="item.trainingErroredDateTime.length"
          class="h-5 w-5 text-red-600"
        >
        </XCircleIcon>
        <ALoadingIcon size="w-4 h-4" v-else></ALoadingIcon>
        <span class="whitespace-nowrap pl-2 text-xs">
          <template v-if="isSubmittingTraining === item.id">
            Submitting training request...
          </template>
          <ATooltip
            v-else-if="item.trainingFinishedDateTime.length"
            text="Last trained date"
          >
            <span class="whitespace-nowrap">
              {{ dayjs(item.trainingFinishedDateTime).format("lll") }}</span
            >
          </ATooltip>
          <template v-else-if="!item.trainingInitiatedDateTime">
            <template v-if="item.rmse || item.usersCount || item.itemsCount">
              Submitted for training
            </template>
            <template v-else> Not trained yet </template>
          </template>
          <template v-else-if="item.trainingErroredDateTime">
            {{ item.trainingStatusMessage }} Please try to again and contact us
            if error reoccurs.
          </template>
          <template v-else-if="item.trainingStartedDateTime">
            Training...
          </template>
          <template v-else-if="item.trainingInitiatedDateTime">
            Queued for training...
          </template>
        </span>
      </div>
    </template>
    <template #[`item.actions`]="{ item }">
      <div class="space-x-4">
        <ATooltip
          :disabled="
            !!item.modelActivatedDateTime.length ||
            !!isSubmittingTraining ||
            isLoadingActiveModel
          "
          text="Set as active model"
        >
          <AButton
            @click="activateModel(item)"
            :icon="
              !!item.modelActivatedDateTime.length
                ? CheckBadgeIconSolid
                : CheckBadgeIcon
            "
            icon-only
            small
            :disabled="
              !!item.modelActivatedDateTime.length ||
              !!isSubmittingTraining ||
              isLoadingActiveModel
            "
          ></AButton>
        </ATooltip>
        <ATooltip
          :disabled="!!isSubmittingTraining || isLoadingActiveModel"
          text="Train"
        >
          <AButton
            @click="trainModel(item)"
            :icon="RocketLaunchIcon"
            icon-only
            small
            :loading="isSubmittingTraining === item.id"
            :disabled="!!isSubmittingTraining || isLoadingActiveModel"
          ></AButton>
        </ATooltip>
        <ATooltip text="Edit">
          <AButton
            :to="{
              name: 'model-manager',
              params: { modelId: item.id },
            }"
            :icon="PencilIcon"
            icon-only
            small
          ></AButton>
        </ATooltip>
        <AConfirmDialog
          v-model="deleteConfirmOpen"
          title="Are you sure?"
          confirm-text="Yes"
          @confirm="aiModelStore.deleteSingle(item.id)"
        >
          Are you sure you want to delete this model?
          <template #activator="{ on }">
            <ATooltip
              text="Delete"
              :disabled="!!item.modelActivatedDateTime.length"
            >
              <AButton
                v-on="on"
                :icon="TrashIcon"
                icon-only
                small
                :loading="isDeletingSingle(item.id)"
                :disabled="!!item.modelActivatedDateTime.length"
              ></AButton>
            </ATooltip>
          </template>
        </AConfirmDialog>
      </div>
    </template>
  </ATable>
</template>
