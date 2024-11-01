import "./style.css";
import { Carousel } from "./carousel";
import { AddMultipleRecommendationsToCart } from "./add-multiple-recommendations-to-cart";
export {};
declare global {
  interface Window {
    SparkPlugins: {
      carousel: typeof Carousel;
      addMultipleRecommendationsToCart: typeof AddMultipleRecommendationsToCart;
    };
    jQuery: any;
  }
}

window.SparkPlugins = {
  carousel: Carousel,
  addMultipleRecommendationsToCart: AddMultipleRecommendationsToCart,
};
