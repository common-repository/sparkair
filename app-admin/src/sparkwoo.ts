import { createApp } from "vue";
import "./style.css";
import App from "./App.vue";
import DeactivateFeedback from "./components/deactivate/DeactivateFeedback.vue";
import router from "./router";
import { createPinia } from "pinia";
import type { IPluginMeta } from "./stores/registry";

const app = createApp(App);
const pinia = createPinia();

app.provide("pluginsData", {
  meta: WP_SPARK_PLUGINS_META,
  data: WP_SPARK_PLUGINS_DATA,
});
app.use(router);
app.use(pinia);

app.mount("#sparkwoo-admin");

app.config.globalProperties.$filters = {
  // toMoment(d: string | number | number[]) {
  //   return moment(d);
  // },
  // momentFormat(m: Moment, f: string) {
  //   return m.format(f);
  // },
  capitalize(string: string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  },
  propertyByDotNotation(object: any, dotNotation: string) {
    return dotNotation.split(".").reduce((o, i) => o[i], object);
  },
};

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties {
    $filters: Record<string, any>;
  }
}

document.addEventListener(
  "click",
  function (event) {
    const element = event.target as HTMLElement;
    if (!element) return;
    const sparkPlugin = WP_SPARK_PLUGINS_META.allSparkPlugins.find(
      (plugin: IPluginMeta) => {
        return element.id === `deactivate-${plugin.slug}`;
      }
    );

    if (sparkPlugin) {
      event.preventDefault();

      const modalWrapper = document.createElement("div");
      modalWrapper.classList.add("sparkwoo-admin");
      modalWrapper.classList.add("sparkwoo-modal-wrapper");

      document.body.appendChild(modalWrapper);

      const deactivateFeedbackModal = createApp(DeactivateFeedback, {
        pluginSlug: sparkPlugin.slug,
        deactivateUrl: element.getAttribute("href") ?? "",
      });
      deactivateFeedbackModal.provide("pluginsData", {
        meta: WP_SPARK_PLUGINS_META,
        data: WP_SPARK_PLUGINS_DATA,
      });
      deactivateFeedbackModal.use(pinia);
      deactivateFeedbackModal.mount(modalWrapper);

      document.addEventListener("sparkwoo:deactivate-cancel", () => {
        modalWrapper.remove();
      });

      // const confirmDeactivation = confirm(
      //   "Are you sure you want to deactivate this plugin?"
      // );
      // if (confirmDeactivation) {
      //   window.location.href = element.getAttribute("href") ?? "";
      //   return;
      // }
      //
    }
  },
  false
);

export default app;
