import defaultCrudStore from "./default-crud-store";
import type { defineStore } from "pinia";

export interface IProductRecommendation {
  id?: number;
  name: string;
  productsManager: string;
  pageHooks: string[];
  designSettings: any;
  designStyle: any;
}

export type ProductRecommendationsStore = Omit<
  ReturnType<typeof useProductRecommendationsStore> & {
    items: IProductRecommendation[];
  },
  keyof ReturnType<typeof defineStore>
>;

const useProductRecommendationsStore = defaultCrudStore(
  "product-recommendations",
  "/sp-woo-prpt"
);

export default useProductRecommendationsStore;
