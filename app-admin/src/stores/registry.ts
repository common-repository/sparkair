import axios, { type AxiosInstance } from "axios";
import { defineStore } from "pinia";
import { computed, inject, ref } from "vue";
import type { RouteRecordRaw } from "vue-router";

export interface IPlacementHook {
  key: string;
  actionName: string;
  name: string;
  description: string;
}
export interface IProductsManager {
  slug: string;
  title: string;
  description: string;
  shortcode: string;
}

export interface IProductsManagerExtended extends IProductsManager {
  installed: boolean;
  soon: boolean;
  isPro: boolean;
}

export interface IPluginMeta {
  apiUrl: string;
  websiteUrl: string;
  extra: {
    productsManager?: IProductsManager;
  };
  groups: string[];
  installed: boolean;
  soon: boolean;
  isPro: boolean;
  name: string;
  prefix: string;
  slug: string;
  test: boolean;
  version: string;
}

interface IPluginOption {
  name: string;
  nameWithoutPrefix: string;
  prefix: string;
  value: boolean | string;
}

interface IGettingStartedItem {
  payoff: string;
  title: string;
  description: string;
  image: string;
  button?: IActivationPageButton;
}

interface IActivationPageButton {
  text: string;
  url?: string;
  routerObject?: RouteRecordRaw;
}

interface ISparkPlugin {
  apiUrl: string;
  meta: IPluginMeta;
  productRecommendations?: {
    placementHooks: IPlacementHook[];
  };
  rvpInstalled?: boolean;
  rvpUrl?: string;
  api: AxiosInstance;
  getPlacementHookByKey: (key: string) => IPlacementHook | undefined;
  options: Record<string, IPluginOption>;
  activationPageContent: {
    gettingStartedItems: IGettingStartedItem[];
    licenseKeyOption?: IPluginOption;
    buttons: IActivationPageButton[];
  };
}

export class SparkPlugin implements ISparkPlugin {
  apiUrl!: string;
  meta!: IPluginMeta;
  productRecommendations?: {
    placementHooks: IPlacementHook[];
  };
  options!: Record<string, IPluginOption>;
  activationPageContent!: {
    gettingStartedItems: IGettingStartedItem[];
    licenseKeyOption?: IPluginOption | undefined;
    buttons: IActivationPageButton[];
  };
  api: AxiosInstance;
  rvpInstalled!: boolean;
  rvpUrl!: string;

  constructor(obj: ISparkPlugin, nonce: string) {
    obj && Object.assign(this, obj);

    this.api = axios.create({
      baseURL: this.apiUrl,
      headers: {
        "X-WP-Nonce": nonce,
      },
      withCredentials: true,
    });
  }

  getPlacementHookByKey(key: string): IPlacementHook | undefined {
    return this.productRecommendations?.placementHooks.find(
      (hook) => hook.key === key
    );
  }
}

export enum Env {
  Development = "dev",
  Production = "prod",
}

const useRegistryStore = defineStore("registry", () => {
  const pluginsData = <any>inject("pluginsData");

  const env = ref(pluginsData.meta.env);
  const currencySymbol = ref(pluginsData.meta.currencySymbol);
  const imagePrefix = ref(pluginsData.meta.imagePrefix);
  const nonce = ref(pluginsData.meta.nonce);
  const page = ref(pluginsData.meta.page);
  const allSparkPlugins = ref(
    pluginsData.meta.allSparkPlugins.filter(
      (pm: IPluginMeta) => !pm.test || pluginsData.meta.env === Env.Development
    )
  );
  const plugins = ref(
    Object.values<ISparkPlugin>(pluginsData.data)
      .map((pd) => new SparkPlugin(pd, nonce.value))
      .reduce(
        (o: Record<string, SparkPlugin>, plugin) => ({
          ...o,
          [plugin.meta.slug]: plugin,
        }),
        {}
      )
  );
  const currentPlugin = ref(
    plugins.value?.[pluginsData.meta.currentPluginSlug]
  );
  if (env.value === Env.Development) {
    console.log("pluginsData", pluginsData);
    console.log("plugins", plugins.value);
  }

  const getPluginByProductsManagerSlug = (slug: string) => {
    return Object.values(plugins.value).find(
      (p) => p.meta.extra.productsManager?.slug === slug
    );
  };

  const getPluginMetaByProductsManagerSlug = (slug: string) => {
    const installedPlugin = getPluginByProductsManagerSlug(slug);
    if (installedPlugin) {
      return installedPlugin.meta;
    } else {
      return allSparkPlugins.value.find(
        (s: IPluginMeta) => s.extra?.productsManager?.slug === slug
      );
    }
  };

  const getProductsManagers = () => {
    const pms: Record<string, IProductsManagerExtended> = {};
    allSparkPlugins.value.forEach((pluginMeta: IPluginMeta) => {
      const manager = pluginMeta.extra.productsManager;
      if (!manager) return;
      pms[manager.slug] = {
        ...manager,
        installed: pms[manager.slug]?.installed || pluginMeta.installed,
        soon: pms[manager.slug]?.soon || pluginMeta.soon,
        isPro:
          pms[manager.slug]?.isPro ||
          (pluginMeta.isPro && pluginMeta.installed),
      };
    });
    return pms;
  };

  const firstInstalledPlugin = computed(() => {
    return Object.values(plugins.value)[0];
  });

  const formatCurrency = (amount: number, decimals = 2) => {
    return `${currencySymbol.value} ${amount.toFixed(decimals)}`;
  };

  return {
    env,
    currencySymbol,
    imagePrefix,
    nonce,
    page,
    allSparkPlugins,
    plugins,
    getPluginByProductsManagerSlug,
    getPluginMetaByProductsManagerSlug,
    getProductsManagers,
    firstInstalledPlugin,
    currentPlugin,
    formatCurrency,
  };
});

export default useRegistryStore;
