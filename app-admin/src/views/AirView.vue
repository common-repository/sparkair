<script setup lang="ts">
import ACard from "@/components/common/ACard.vue";
import useAiModelStore from "@/stores/air-models";
import TheHeader from "@/components/TheHeader.vue";
import TheFooter from "@/components/TheFooter.vue";

const aiModelStore = useAiModelStore();
aiModelStore.fetch();
</script>

<template>
  <TheHeader
    title="AI Tools"
    subtitle="Manage and fine-tune your recommendation models"
  >
  </TheHeader>
  <ACard class="relative">
    <router-view></router-view>
  </ACard>
  <TheFooter></TheFooter>
</template>
