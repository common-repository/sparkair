import { defineStore } from "pinia";
import type { SparkPlugin } from "./registry";

const useOptionsStore = defineStore("options", {
  state: () =>
    ({ options: {}, fetching: false, updating: false } as {
      options: Record<string, Record<string, boolean | string | null>>;
      fetching: boolean;
      updating: boolean;
    }),
  getters: {
    getOption: (state) => (plugin: SparkPlugin, key: string) =>
      state.options?.[plugin.meta.slug]?.[`${plugin.meta.prefix}${key}`],
    getOptions: (state) => (plugin: SparkPlugin) =>
      state.options[plugin.meta.slug],
    isFetching: (state) => state.fetching,
    isUpdating: (state) => state.updating,
  },
  actions: {
    async fetch(plugin: SparkPlugin) {
      this.fetching = true;
      try {
        const options = await plugin.api.get("/options");
        const optionsData = options?.data.data;
        if (optionsData) {
          this.options[plugin.meta.slug] = optionsData;
        }
      } catch (err) {
        // pass
      }

      this.fetching = false;
      return this.options;
    },
    async update(
      plugin: SparkPlugin,
      options: Record<string, boolean | string | null>
    ) {
      this.updating = true;
      try {
        await plugin.api.patch("/options", options);
      } catch (err) {
        // pass
      }
      this.options[plugin.meta.slug] = {
        ...this.options[plugin.meta.slug],
        ...options,
      };

      this.updating = false;
      return this.options;
    },
  },
});

export default useOptionsStore;
