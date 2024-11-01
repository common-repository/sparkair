import { defineStore } from "pinia";
import type { SparkPlugin } from "./registry";

interface IAnalyticsSparklineDataPoint {
  date: string;
  value: number;
}

interface IAnalyticsSparklineData {
  timeline: IAnalyticsSparklineDataPoint[];
  timelineCompare: IAnalyticsSparklineDataPoint[];
  total: number;
  totalCompare: number;
}

interface IAnalyticsTopProduct {
  value: number;
  url: string;
  title: string;
}

interface IAnalyticsTopRecommendationShown {
  value: number;
  productsManager: string;
  placementHook: string;
}

interface IAnalyticsTotalRevenuePlugins {
  revenue: number;
  productsManager: string;
}

enum AnalyticsGraphDataType {
  Render = "render",
  Click = "click",
  Conversion = "conversion",
  ClickRate = "click-rate",
  ConversionRate = "conversion-rate",
  Revenue = "revenue",
}

enum AnalyticsTopProductsType {
  Clicked = "clicked",
  Converted = "converted",
}

const useAnalyticsStore = defineStore("analytics", {
  state: () =>
    ({
      graphData: {},
      fetching: {},
      topProductsData: {},
      fetchingTopProducts: {},
      topRecommendationsShown: [],
      fetchingTopRecommendationsShown: false,
      totalRevenuePlugins: [],
      fetchingTotalRevenuePlugins: false,
    } as {
      graphData: Record<string, IAnalyticsSparklineData>;
      fetching: Record<string, boolean>;
      topProductsData: Record<string, IAnalyticsTopProduct[]>;
      fetchingTopProducts: Record<string, boolean>;
      topRecommendationsShown: IAnalyticsTopProduct[];
      fetchingTopRecommendationsShown: boolean;
      totalRevenuePlugins: IAnalyticsTotalRevenuePlugins[];
      fetchingTotalRevenuePlugins: boolean;
    }),
  getters: {
    isFetching: (state) => (type: string) => state.fetching[type],
    totalRevenue: (state) =>
      state.totalRevenuePlugins
        .map((p) => p.revenue)
        .reduce((a, b) => a + b, 0),
  },
  actions: {
    async fetch(
      plugin: SparkPlugin,
      {
        graphDataType,
        force,
        params,
      }: {
        graphDataType?: AnalyticsGraphDataType;
        force?: boolean;
        params?: Record<string, string>;
      } = {
        force: false,
        params: {},
      }
    ) {
      if (!graphDataType) return [];
      this.fetching[graphDataType] = true;

      const urlMap = {
        [AnalyticsGraphDataType.Render]: "/product-renders",
        [AnalyticsGraphDataType.Click]: "/product-clicks",
        [AnalyticsGraphDataType.Conversion]: "/product-conversions",
        [AnalyticsGraphDataType.ClickRate]: "/product-click-rate",
        [AnalyticsGraphDataType.ConversionRate]: "/product-conversion-rate",
        [AnalyticsGraphDataType.Revenue]: "/revenue",
      };

      try {
        const analyticsResponse = await plugin.api.get(
          `/analytics${urlMap[graphDataType]}`,
          {
            params,
          }
        );
        const analyticsData = analyticsResponse?.data.data;
        if (analyticsData) {
          this.graphData[graphDataType] = analyticsData;
        }

        this.fetching[graphDataType] = false;
        return this.graphData[graphDataType];
      } catch (e) {
        console.log(e);
        return [];
      }
    },
    async fetchTopProducts(
      plugin: SparkPlugin,
      {
        topProductsType,
        force,
        params,
      }: {
        topProductsType?: AnalyticsTopProductsType;
        force?: boolean;
        params?: Record<string, string>;
      } = {
        force: false,
        params: {},
      }
    ) {
      if (!topProductsType) return [];
      this.topProductsData[topProductsType] = [];
      this.fetchingTopProducts[topProductsType] = true;

      const urlMap = {
        [AnalyticsTopProductsType.Clicked]: "/top-products-clicked",
        [AnalyticsTopProductsType.Converted]: "/top-products-converted",
      };

      try {
        const analyticsResponse = await plugin.api.get(
          `/analytics${urlMap[topProductsType]}`,
          { params }
        );
        const analyticsData = analyticsResponse?.data.data;
        if (analyticsData) {
          this.topProductsData[topProductsType] = analyticsData;
        }

        this.fetchingTopProducts[topProductsType] = false;
        return this.topProductsData[topProductsType];
      } catch (e) {
        console.log(e);
        return [];
      }
    },
    async fetchTopRecommendationsShown(
      plugin: SparkPlugin,
      {
        force,
        params,
      }: { force?: boolean; params?: Record<string, string> } = {
        force: false,
        params: {},
      }
    ) {
      this.topRecommendationsShown = [];
      this.fetchingTopRecommendationsShown = true;
      try {
        const analyticsResponse = await plugin.api.get(
          `/analytics/top-recommendations-shown`,
          { params }
        );
        const analyticsData = analyticsResponse?.data.data;
        if (analyticsData) {
          this.topRecommendationsShown = analyticsData;
        }

        this.fetchingTopRecommendationsShown = false;
        return this.topRecommendationsShown;
      } catch (e) {
        console.log(e);
        return [];
      }
    },
    async fetchTotalRevenuePlugins(
      plugin: SparkPlugin,
      { force }: { force?: boolean } = {
        force: false,
      }
    ) {
      if (!force && this.totalRevenuePlugins.length) {
        return this.totalRevenuePlugins;
      }
      this.totalRevenuePlugins = [];
      this.fetchingTotalRevenuePlugins = true;
      try {
        const analyticsResponse = await plugin.api.get(
          `/analytics/total-revenue`
        );
        const analyticsData = analyticsResponse?.data.data;
        if (analyticsData) {
          this.totalRevenuePlugins = analyticsData;
        }

        this.fetchingTotalRevenuePlugins = false;
        return this.totalRevenuePlugins;
      } catch (e) {
        console.log(e);
        return [];
      }
    },
  },
});

export default useAnalyticsStore;

export {
  AnalyticsGraphDataType,
  AnalyticsTopProductsType,
  type IAnalyticsSparklineData,
  type IAnalyticsSparklineDataPoint,
  type IAnalyticsTotalRevenuePlugins,
};
