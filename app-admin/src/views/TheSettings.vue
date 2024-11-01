<script setup lang="ts">
import AContainerWrapper from "@/components/common/AContainerWrapper.vue";
import ACard from "@/components/common/ACard.vue";
import ASelect from "@/components/common/ASelect.vue";
import SettingsSection from "@/components/SettingsSection.vue";
import TheFooter from "@/components/TheFooter.vue";
import TheHeader from "@/components/TheHeader.vue";
import SettingsOption from "@/components/SettingsOption.vue";
import { computed, ref, watch } from "vue";
import AButton from "@/components/common/AButton.vue";
import { ListboxButton } from "@headlessui/vue";
import { ChevronDownIcon } from "@heroicons/vue/24/solid";
import BadgeFreePro from "@/components/BadgeFreePro.vue";
import useOptionsStore from "@/stores/options";
import { storeToRefs } from "pinia";
import ALoadingOverlay from "@/components/common/ALoadingOverlay.vue";
import useRegistryStore, { SparkPlugin } from "@/stores/registry";
import { useRouter, useRoute } from "vue-router";
import SettingsOptionLicense from "@/components/SettingsOptionLicense.vue";
import SettingsOptionCache from "@/components/SettingsOptionCache.vue";
import SettingsOptionClearAnalytics from "@/components/SettingsOptionClearAnalytics.vue";

const props = defineProps<{
  pluginSlug?: string;
  tab?: string;
}>();

const router = useRouter();
const route = useRoute();

const registryStore = useRegistryStore();
const { plugins } = storeToRefs(registryStore);

const updateRoute = (plugin: SparkPlugin) => {
  router.replace({
    ...route,
    params: { ...route.params, pluginSlug: plugin.meta.slug },
  });
};

const optionsStore = useOptionsStore();
const { options, isFetching } = storeToRefs(optionsStore);

const selectedPlugin = computed(() =>
  props.pluginSlug
    ? plugins.value[props.pluginSlug]
    : Object.values(plugins.value)[0]
);

const fetchOptions = () => {
  optionsStore.fetch(selectedPlugin.value);
};

if (!props.pluginSlug) {
  updateRoute(Object.values(plugins.value)[0]);
} else {
  fetchOptions();
}

watch(
  () => props.pluginSlug,
  () => {
    fetchOptions();
  }
);
</script>

<template>
  <AContainerWrapper>
    <TheHeader
      title="Settings"
      subtitle="Configuring your personalized experience"
    >
      <template #actions>
        <div v-if="Object.values(plugins).length > 1">
          <ASelect
            :model-value="selectedPlugin"
            :items="Object.values(plugins)"
            return-object
            @update:model-value="(p) => updateRoute(p)"
          >
            <template #button>
              <ListboxButton class="w-full">
                <AButton :append-icon="ChevronDownIcon" class="w-full">
                  <span class="truncate">
                    Configuring
                    <strong>{{ selectedPlugin?.meta.name }}</strong>
                    <BadgeFreePro
                      v-if="selectedPlugin?.meta.isPro"
                      class="ml-2"
                      pro
                      check
                    ></BadgeFreePro>
                  </span>
                </AButton>
              </ListboxButton>
            </template>
            <template #item="{ item }">
              {{ item.meta.name }}
              <BadgeFreePro
                v-if="item.meta.isPro"
                class="ml-2"
                pro
                check
              ></BadgeFreePro>
            </template>
          </ASelect>
        </div>
      </template>
    </TheHeader>
    <ACard class="relative">
      <ALoadingOverlay :model-value="isFetching"></ALoadingOverlay>
      <div class="divide-y divide-gray-100">
        <SettingsSection
          v-if="!selectedPlugin.meta.isPro"
          title="Go PRO"
          :subtitle="`Unlock a world of enhanced possibilities, unparalleled support, and skyrocket your sales with the PRO version of the ${selectedPlugin.meta.name}-plugin!`"
          :plugin="selectedPlugin"
        >
          <SettingsOption
            label="Check out PRO version"
            type="button"
            :plugin="selectedPlugin"
            :href="selectedPlugin.meta.websiteUrl"
          >
          </SettingsOption>
        </SettingsSection>
        <SettingsSection
          title="License information"
          subtitle="Fill out your license key for enhanced experience and full support"
          :options="['license_key']"
          :plugin="selectedPlugin"
        >
          <SettingsOptionLicense
            :plugin="selectedPlugin"
          ></SettingsOptionLicense>
        </SettingsSection>
        <SettingsSection
          title="Multi-language"
          subtitle="Configure the plugin to work with multiple languages for your audience"
          :plugin="selectedPlugin"
          :options="['multi_language_enabled']"
        >
          <SettingsOption
            label="Enable multi-language"
            type="boolean"
            option="multi_language_enabled"
            :plugin="selectedPlugin"
          >
            <template #value-hint>
              When enabled, use a plugin like
              <a
                href="https://wordpress.org/plugins/loco-translate/"
                target="_blank"
                >Loco Translate</a
              >
              to translate or customize titles etc. Otherwise, the plugin will
              allow you to fill in a single entry.
            </template>
          </SettingsOption>
        </SettingsSection>

        <SettingsSection
          title="Webshop"
          subtitle="Configure the plugin to enable features on your webshop"
          :plugin="selectedPlugin"
          :options="['enable_sorting_by_ai']"
        >
          <SettingsOption
            label="Add AI product sorting"
            type="boolean"
            option="enable_sorting_by_ai"
            :plugin="selectedPlugin"
          >
            <template #value-hint>
              When enabled, your customers will get an option to sort products
              by AI, showing the most personal recommendations first.
            </template>
          </SettingsOption>
        </SettingsSection>
        <SettingsSection
          title="Cache"
          subtitle="We use caching for performance improvements. If you notice something odd, please clear this first."
          :plugin="selectedPlugin"
        >
          <SettingsOptionCache :plugin="selectedPlugin"></SettingsOptionCache>
        </SettingsSection>
        <SettingsSection
          title="Notifications"
          subtitle="Configure what you want the plugin to notify you about."
          :options="[
            'dismissed_subscribed_to_mail_list_free',
            'dismissed_rvp_data_option',
          ]"
          :plugin="selectedPlugin"
        >
          <SettingsOption
            label="Dismiss the newsletter notification"
            type="boolean"
            option="dismissed_subscribed_to_mail_list_free"
            :plugin="selectedPlugin"
          >
          </SettingsOption>
          <SettingsOption
            label="Dismiss the SparkRVP message"
            type="boolean"
            option="dismissed_rvp_data_option"
            :plugin="selectedPlugin"
          >
            <template #value-hint>
              Dismiss the SparkRVP suggestion in case you haven't installed
              SparkRVP yet
            </template>
          </SettingsOption>
        </SettingsSection>
        <SettingsSection
          title="Consent"
          subtitle="Using data"
          :options="['consent_data_option']"
          :plugin="selectedPlugin"
        >
          <SettingsOption
            label="Allow utilizing anonymous shopping data"
            type="boolean"
            option="consent_data_option"
            :plugin="selectedPlugin"
          >
            <template #value-hint>
              To generate an AI experience, we need to process your shop data on
              our servers. It is completely anonymous and we do not store any
              personal data or distribute it to third parties.
            </template>
          </SettingsOption>
        </SettingsSection>
        <SettingsSection
          title="Analytics"
          subtitle="We collect data to show you some statistics in our PRO plugins."
          :plugin="selectedPlugin"
        >
          <SettingsOptionClearAnalytics :plugin="selectedPlugin">
          </SettingsOptionClearAnalytics>
        </SettingsSection>
        <SettingsSection
          title="Uninstallation"
          subtitle="Configure what you want the plugin to do upon uninstallation, which, of course, we regret to see."
          :options="[
            'delete_posts_on_uninstall',
            'delete_options_on_uninstall',
          ]"
          :plugin="selectedPlugin"
        >
          <SettingsOption
            label="Remove all options"
            type="boolean"
            option="delete_options_on_uninstall"
            :plugin="selectedPlugin"
          >
          </SettingsOption>
          <SettingsOption
            label="Remove all posts"
            type="boolean"
            option="delete_posts_on_uninstall"
            :plugin="selectedPlugin"
          >
            <template v-if="Object.values(plugins).length > 1" #value-hint>
              Uninstalling this as being the last Product
              Recommendations-plugin, will remove all posts.
            </template>
          </SettingsOption>
        </SettingsSection>
      </div>
    </ACard>
    <TheFooter></TheFooter>
  </AContainerWrapper>
</template>
