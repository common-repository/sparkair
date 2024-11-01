import { defineStore } from "pinia";
import type { SparkPlugin } from "./registry";

interface ILicense {
  key: string;
  valid: boolean;
  expiryDate?: string;
  isExpired?: boolean;
  domainsRegistered?: number;
  domainCount?: number;
  domainRegistered?: boolean;
  currentHost: string;
  error?: string;
}

const useLicenseStore = defineStore("license", {
  state: () =>
    ({ licenses: {}, fetching: false, activating: false } as {
      licenses: Record<string, ILicense>;
      fetching: boolean;
      activating: boolean;
    }),
  getters: {
    isFetching: (state) => state.fetching,
    isActivating: (state) => state.activating,
  },
  actions: {
    async fetch(
      plugin: SparkPlugin,
      { force }: { force: boolean } = { force: false }
    ) {
      this.fetching = true;
      try {
        const license = await plugin.api.get("/license", { params: { force } });
        const licenseData = license?.data.data;
        if (licenseData) {
          this.licenses[plugin.meta.slug] = licenseData;
        }
      } catch (err) {
        // pass
      }

      this.fetching = false;
      return this.licenses[plugin.meta.slug];
    },
    async activate(plugin: SparkPlugin) {
      this.activating = true;
      try {
        const license = await plugin.api.post("/license/activate");
        const licenseData = license?.data.data;
        if (licenseData) {
          this.licenses[plugin.meta.slug] = licenseData;
        }
      } catch (err) {
        // pass
      }
      this.activating = false;
      return this.licenses[plugin.meta.slug];
    },
  },
});

export default useLicenseStore;
