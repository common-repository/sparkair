<script setup lang="ts">
import AButton from "./AButton.vue";
import { toTypedSchema } from "@vee-validate/yup";
import { useForm } from "vee-validate";
import { computed, ref, type Ref } from "vue";
import ASidebarOverlay from "./ASidebarOverlay.vue";
import AConfirmDialog from "./AConfirmDialog.vue";

const props = withDefaults(
  defineProps<{
    name: string;
    schema: any;
    defaults?: Record<string, any>;
    store: any;
    type?: "sidebar" | "inline";
    storeUrlParams?: Record<string, any>;
    keyId?: string;
    keyText?: string;
    hideFooter?: boolean;
    loading?: boolean;
  }>(),
  {
    defaults: () => ({}),
    type: "sidebar",
    storeUrlParams: () => ({}),
    keyId: "id",
    keyText: "name",
    loading: false,
  }
);

const emit = defineEmits(["saved", "closed", "deleted", "open"]);

const close = () => {
  emit("closed");
  sidebarOpen.value = false;
};

const save = async () => {
  const valid = await validate();
  if (!valid.valid) {
    console.log(valid);
    return;
  }

  let object;
  if (values[props.keyId]) {
    object = await props.store.updateSingle(values[props.keyId], values, {
      urlParams: props.storeUrlParams,
    });
  } else {
    object = await props.store.create(values, {
      urlParams: props.storeUrlParams,
    });
  }
  reset();
  emit("saved", object);
  close();
};

const { values, validate, errors, setValues, resetForm } = useForm({
  validationSchema: toTypedSchema(props.schema),
});

const reset = () => {
  resetForm({ values: { ...props.defaults } });
};
reset();

const isLoading = computed(
  () =>
    props.loading ||
    props.store.isCreating ||
    props.store.isUpdatingSingle(values?.[props.keyId]) ||
    props.store.isDeletingSingle(objectToDelete.value?.[props.keyId])
);

const sidebarOpen = ref(false);

const title = computed(() => {
  if (values?.[props.keyId]) {
    return `${props.name} bewerken`;
  }
  return `${props.name} toevoegen`;
});

const open = (object?: any) => {
  setValues(object ?? {});
  sidebarOpen.value = true;
  emit("open");
};

const deleteByObject = async (object?: any) => {
  objectToDelete.value = object ?? values ?? null;
  deleteConfirmOpen.value = true;
};

const deleteConfirmOpen = ref(false);
const objectToDelete: Ref<any | null> = ref(null);
const deleteOk = async () => {
  if (!objectToDelete.value) return;
  await props.store.deleteSingle(objectToDelete.value[props.keyId], {
    urlParams: props.storeUrlParams,
  });
  emit("deleted");
  close();
};
const deleteCancel = () => {
  deleteConfirmOpen.value = false;
  objectToDelete.value = null;
};

defineExpose({ deleteByObject, open, setValues, resetForm });
</script>

<template>
  <ASidebarOverlay
    v-if="type === 'sidebar'"
    :title="title"
    v-model="sidebarOpen"
  >
    <slot
      name="default"
      :errors="errors"
      :setValues="setValues"
      :values="values"
      :close="close"
      :save="save"
      :deleteByObject="deleteByObject"
      :loading="isLoading"
      :keyId="keyId"
    ></slot>
    <template #footer>
      <AButton v-if="values[keyId]" @click="deleteByObject()" outlined>
        Delete
      </AButton>
      <div class="grow"></div>
      <AButton @click="close()" outlined> Cancel </AButton>
      <AButton @click="save()" :loading="isLoading"> Save </AButton>
    </template>
  </ASidebarOverlay>
  <div v-else-if="type === 'inline'">
    <slot
      name="default"
      :errors="errors"
      :setValues="setValues"
      :values="values"
      :close="close"
      :save="save"
      :deleteByObject="deleteByObject"
      :loading="isLoading"
      :keyId="keyId"
    ></slot>
    <slot
      v-if="!hideFooter"
      name="footer"
      :close="close"
      :save="save"
      :deleteByObject="deleteByObject"
      :loading="isLoading"
      :errors="errors"
      :values="values"
      :keyId="keyId"
    >
      <div class="mt-2 flex space-x-2">
        <AButton
          v-if="values[keyId]"
          @click="deleteByObject()"
          outlined
          icon="delete"
        >
          Delete
        </AButton>
        <div class="grow"></div>
        <AButton @click="close()" outlined> Cancel </AButton>
        <AButton
          @click="save()"
          :loading="isLoading"
          :disabled="!!errors.length"
          icon="content-save"
        >
          Save
        </AButton>
      </div>
    </slot>
  </div>
  <AConfirmDialog
    v-model="deleteConfirmOpen"
    :title="`${name} verwijderen?`"
    @confirm="deleteOk"
    @cancel="deleteCancel"
    :loading="isLoading"
  >
    Are you sure you want to delete this '{{ name.toLowerCase() }}'?
  </AConfirmDialog>
</template>
