<script setup lang="ts">
import type { SparkPlugin } from "@/stores/registry";
import SettingsOption from "./SettingsOption.vue";
import { CheckCircleIcon, XCircleIcon } from "@heroicons/vue/24/solid";
import { computed, ref, watch } from "vue";
import useLicenseStore from "@/stores/license";
import { storeToRefs } from "pinia";
import ALoadingIcon from "./common/ALoadingIcon.vue";
import { ArrowPathIcon } from "@heroicons/vue/24/outline";
import ATooltip from "./common/ATooltip.vue";

const props = defineProps<{
  plugin: SparkPlugin;
}>();

const licenseStore = useLicenseStore();
const { licenses, fetching, activating } = storeToRefs(licenseStore);

const option = ref(null);

const license = computed(() => licenses.value[props.plugin.meta.slug]);
const valid = computed(() => license.value.valid);
const empty = computed(() => !(option?.value as any)?.value);

const updateLicense = (force = false) => {
  licenseStore.fetch(props.plugin, { force });
};
updateLicense();
const expiryDateFormatted = computed(() => {
  if (!license.value.expiryDate) return "forever";
  const date = new Date(license.value.expiryDate);
  return `until ${date.toLocaleDateString()}`;
});

const activateLicense = () => {
  licenseStore.activate(props.plugin);
};

const loading = computed(() => fetching.value || activating.value);

watch(
  () => props.plugin,
  () => updateLicense()
);
</script>

<template>
  <SettingsOption
    ref="option"
    label="License key"
    type="string"
    option="license_key"
    :plugin="plugin"
    @saved="updateLicense"
    :loading="loading"
  >
    <template #value-hint>
      <div class="inline-flex items-center space-x-1 text-xs">
        <template v-if="loading || !license">
          <ALoadingIcon size="w-3 h-3" class="shrink-0"> </ALoadingIcon>
          <span v-if="fetching"> Validating license... </span>
          <span v-if="activating"> Registering domain... </span>
        </template>
        <template v-else>
          <component
            :is="valid ? CheckCircleIcon : XCircleIcon"
            :class="{
              'text-green-600': valid,
              'text-orange-500': empty,
              'text-red-600': !valid && !empty,
            }"
            class="h-3 w-3 shrink-0"
          ></component>
          <span>
            <span v-if="empty">Please fill out your license key</span>
            <span v-else-if="valid"
              >License valid <strong>{{ expiryDateFormatted }}</strong
              >, <strong>{{ license.domainsRegistered }}</strong> of
              <strong>{{ license.domainCount }}</strong> domains used.</span
            >
            <span v-else>{{ license.error }} </span>
          </span>
          <span>
            <ATooltip text="Validate the license again">
              <ArrowPathIcon
                v-if="!valid && !empty"
                class="h-3 w-3 cursor-pointer text-aprimary-600"
                @click="updateLicense(true)"
              ></ArrowPathIcon>
            </ATooltip>
          </span>
        </template>
      </div>
      <div>
        <span
          class="text-xs"
          v-if="!activating && !fetching && license?.domainRegistered === false"
        >
          <span
            v-if="(license.domainsRegistered ?? 0) < (license.domainCount ?? 0)"
          >
            Please
            <a
              class="cursor-pointer font-semibold text-aprimary-700"
              @click="activateLicense"
              >click here to register this domain</a
            >
            or visit
            <a
              class="font-semibold text-aprimary-700"
              href="https://www.sparkplugins.com/my-subscriptions/"
              >your SparkPlugins.com account</a
            >
            to manage the domains for this license.
          </span>
          <span v-else>
            You have used the maximum number of domains (<strong>{{
              license.domainCount
            }}</strong
            >) in your license. Please
            <a
              class="font-semibold text-aprimary-700"
              :href="plugin.meta.websiteUrl"
              target="_blank"
              >upgrade your license</a
            >
            or
            <a
              class="font-semibold text-aprimary-700"
              href="https://www.sparkplugins.com/my-subscriptions/"
              target="_blank"
              >manage your domains</a
            >.
          </span>
        </span>
      </div>
    </template>
  </SettingsOption>
</template>
