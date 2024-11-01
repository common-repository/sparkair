import {
  createRouter,
  createWebHashHistory,
  RouterView,
  useRouter,
} from "vue-router";
import ProductRecommendationWizard from "@/views/ProductRecommendationWizard.vue";
import TheSettings from "@/views/TheSettings.vue";
import ProductRecommendations from "@/views/ProductRecommendations.vue";
import AirModels from "@/views/AirModels.vue";
import AirModelManager from "@/views/AirModelManager.vue";
import TheAnalytics from "@/views/TheAnalytics.vue";
import TheWelcome from "@/views/TheWelcome.vue";
import useRegistryStore from "@/stores/registry";
import { storeToRefs } from "pinia";
import AirView from "@/views/AirView.vue";

const routes = [
  { name: "welcome", path: "/welcome", component: TheWelcome },
  {
    path: "/product-recommendations",
    children: [
      {
        name: "product-recommendations",
        path: "",
        component: ProductRecommendations,
        beforeEnter: (to: any) => {
          if (to.query?.id) {
            const router = useRouter();
            router.push({
              name: "wizard",
              params: { productRecommendationId: to.query.id },
            });
          }
        },
      },
      {
        name: "wizard",
        path: "wizard/:productRecommendationId?",
        component: ProductRecommendationWizard,
        props: (route: any) => ({
          id: route.params?.productRecommendationId
            ? Number(route.params.productRecommendationId)
            : null,
        }),
      },
    ],
  },
  {
    name: "settings",
    path: "/settings/:pluginSlug?/:tab?",
    component: TheSettings,
    props: (route: any) => ({
      tab: route.params?.tab ? route.params.tab : null,
      pluginSlug: route.params?.pluginSlug ? route.params.pluginSlug : null,
    }),
  },
  {
    name: "analytics",
    path: "/analytics/:pluginSlug?",
    component: TheAnalytics,
    props: (route: any) => ({
      pluginSlug: route.params?.pluginSlug ? route.params.pluginSlug : null,
    }),
  },
  {
    path: "/ai-tools",
    component: AirView,
    children: [
      {
        name: "ai-tools",
        path: "",
        component: AirModels,
      },
      {
        name: "model-manager",
        path: "model-manager/:modelId?",
        component: AirModelManager,
        props: (route: any) => ({
          id: route.params?.modelId ? Number(route.params.modelId) : null,
        }),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHashHistory(window.location.pathname),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const registryStore = useRegistryStore();
  const { page } = storeToRefs(registryStore);

  if (to.path === "/" && page.value) {
    const query = Object.fromEntries(
      new URLSearchParams(window.location.search)
    );
    return next({
      name: page.value,
      query,
    });
  }
  return next();
});

export default router;
