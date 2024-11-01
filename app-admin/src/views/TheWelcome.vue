<script setup lang="ts">
import TheFooter from "@/components/TheFooter.vue";
import AButton from "@/components/common/AButton.vue";
import useRegistryStore from "@/stores/registry";
import { ArrowRightIcon } from "@heroicons/vue/24/outline";
import { computed } from "vue";

const registryStore = useRegistryStore();
const { currentPlugin } = registryStore;

const activationContent = computed(() => currentPlugin.activationPageContent);
</script>

<template>
  <div
    class="to-94% relative isolate overflow-hidden bg-gradient-to-t from-[#5f0cc5] from-0% to-[#00004b]"
  >
    <div class="px-6 lg:px-8">
      <div class="flex items-center justify-between pt-6" aria-label="Global">
        <div class="flex lg:flex-1">
          <a
            href="https://sparkplugins.com"
            target="_blank"
            class="-m-1.5 p-1.5"
            rel="noreferrer"
          >
            <img
              class="h-16"
              :src="`${registryStore.imagePrefix}/sparkplugins-logo-white.svg`"
              alt="SparkPlugins.com"
            />
          </a>
        </div>
      </div>
      <div class="mx-auto max-w-4xl py-6 sm:py-8 lg:py-10">
        <div class="text-center">
          <span
            class="text-4xl font-bold leading-10 tracking-tight text-white sm:text-7xl"
          >
            Thank you for choosing SparkPlugins
          </span>
          <div class="mt-6 text-4xl font-thin text-gray-200">
            We make sure we won't let you down
          </div>
          <div class="mt-6 space-x-4">
            <AButton
              v-for="(button, buttonIdx) in activationContent.buttons"
              :to="button.routerObject ?? undefined"
              :href="button.url ?? undefined"
              large
            >
              {{ button.text }}
            </AButton>
            <AButton
              v-if="activationContent.licenseKeyOption"
              :to="{
                name: 'settings',
                params: {
                  pluginSlug: currentPlugin.meta.slug,
                },
                query: {
                  highlight:
                    activationContent.licenseKeyOption.nameWithoutPrefix,
                },
              }"
              large
              text
              :append-icon="ArrowRightIcon"
            >
              Enter license key
            </AButton>
          </div>
        </div>
      </div>
    </div>
    <div class="flex shrink-0 justify-center">
      <div class="flex flex-wrap gap-x-8 gap-y-16 pb-32 pt-12">
        <div
          v-for="(item, itemIdx) in activationContent.gettingStartedItems"
          :key="itemIdx"
          class="overflow-hidden"
        >
          <div class="mx-auto px-6 lg:px-8">
            <div
              class="mx-auto grid max-w-md grid-cols-1 gap-x-8 gap-y-10 sm:gap-y-12"
            >
              <div class="lg:pr-8 lg:pt-4">
                <div class="lg:max-w-lg">
                  <span
                    class="text-base font-semibold leading-7 text-aprimary-200"
                  >
                    {{ item.payoff }}
                  </span>
                  <div
                    class="!mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl"
                  >
                    {{ item.title }}
                  </div>
                  <div class="mt-6 text-lg leading-8 text-gray-300">
                    {{ item.description }}
                  </div>
                </div>
              </div>
              <img
                v-if="item.image"
                :src="`${registryStore.imagePrefix}/${item.image}`"
                alt="Product screenshot"
                class="w-[48rem] max-w-none rounded-xl shadow-xl ring-1 ring-white/10 sm:w-[57rem] md:-ml-4 lg:-ml-0"
                width="2432"
                height="1442"
              />
              <AButton
                v-if="item.button"
                :to="item.button.routerObject ?? undefined"
                :href="item.button.url ?? undefined"
                large
              >
                {{ item.button.text }}
              </AButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <TheFooter></TheFooter>
</template>
