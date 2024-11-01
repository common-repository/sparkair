const CLASS_INDICATOR_DOT = "sp-carousel-indicator-dot";
const CLASS_INDICATOR_DOT_ACTIVE = "sp-carousel-indicator-dot-active";

export class Carousel {
  protected wrapper!: HTMLElement;
  protected containerSelector!: string;
  protected container!: HTMLElement;
  protected indicator?: HTMLElement | null;
  protected indicatorOverlay?: HTMLElement | null;
  protected indicatorItemContainer?: HTMLElement | null;
  protected currentItem: number;
  protected items!: HTMLElement[];
  protected positions: Array<Array<number>>;
  protected scrolling: boolean;
  protected slideAutomatically: boolean;
  protected indicatorVariant: "dots" | "window";
  protected numberPerRow!: number;
  protected useThemeColumnsSetting!: boolean;
  protected sliderInterval!: any;
  protected hovering: boolean;
  protected isDebug = false;
  protected scrollingTimeout: any | null = null;

  constructor(
    wrapper: HTMLElement,
    containerSelector: string,
    options: {
      slideAutomatically: boolean;
      indicatorVariant: "dots" | "window";
      debug?: boolean;
    } = {
      slideAutomatically: false,
      indicatorVariant: "dots",
      debug: false,
    }
  ) {
    this.wrapper = wrapper;
    this.hovering = false;
    this.slideAutomatically = options.slideAutomatically;
    this.indicatorVariant = options.indicatorVariant;
    this.containerSelector = containerSelector;
    this.positions = [];
    this.currentItem = 0;
    this.scrolling = false;
    this.isDebug = options.debug ?? false;
    this.log("Creating carousel");
    this.init();
    this.log("Carousel created");
  }

  init() {
    this.log("Refreshing carousel");

    if (!this.wrapper || !(this.wrapper instanceof HTMLElement)) {
      console.error("Wrapper element not valid, could not load carousel");
      return;
    }

    if (this.wrapper.dataset.spCarousel === "true") {
      this.log("Carousel already initialized");
      return;
    }

    this.wrapper.dataset.spCarousel = "true";

    const container = this.wrapper.querySelector(this.containerSelector);

    if (!container || !(container instanceof HTMLElement)) {
      console.error(
        "Container not found, could not load carousel",
        this.containerSelector
      );
      return;
    }
    this.container = container;

    const indicator = this.wrapper.querySelector(
      "[data-sp-carousel-indicator]"
    );

    if (indicator && indicator instanceof HTMLElement) {
      this.indicator = indicator;
    }

    this.items =
      Array.prototype.slice.call(
        this.container.querySelectorAll(".sparkwoo-pr-item")
      ) ?? [];

    if (!this.items || this.items.length === 0) {
      console.error("No items found, could not load carousel");
      return;
    }

    this.container.classList.add(
      "!relative",
      "!snap-x",
      "!flex",
      "!flex-nowrap",
      "!flex-row",
      "!overflow-x-auto",
      "!overflow-y-hidden",
      "sp-hide-scrollbar",
      "!-my-4",
      "!py-4"
    );

    this.container.addEventListener("scroll", () => {
      this.scrolling = true;
      if (this.scrollingTimeout) {
        clearTimeout(this.scrollingTimeout);
      }
      this.scrollingTimeout = setTimeout(() => {
        this.scrolling = false;
        this.update();
      }, 100);
      this.update();
    });

    this.wrapper
      .querySelectorAll("[data-sp-carousel-go]")
      .forEach((element) => {
        element.addEventListener("click", () => {
          if (element instanceof HTMLElement) {
            this.go(element.dataset.spCarouselGo ?? "next");
          }
        });
      });

    this.items.forEach((item, idx) => {
      if (idx !== 0) {
        item.classList.remove("first");
      }
      item.classList.remove("last");
      if (idx === this.items.length - 1) {
        item.classList.add("last");
      }
      item.classList.add("!snap-start");
    });

    this.refresh();

    this.initSliderInterval();

    window.addEventListener("resize", () => {
      this.refresh();
    });

    setTimeout(() => this.refresh(), 500);
  }

  refresh() {
    this.calculatePositions();
    this.refreshIndicator();
    this.update();
  }

  update() {
    this.log("Updating carousel");
    const currentPositionPercentage =
      this.currentScrollLeft / this.containerWidth;
    this.currentItem = Math.round(
      currentPositionPercentage * this.items.length
    );

    this.determineButtonVisibility();
    this.updateCurrentIndicator();
  }

  initSliderInterval() {
    this.log("Initializing slider interval");
    this.wrapper.onmouseenter = () => {
      this.hovering = true;
      this.cancelAutoSlide();
    };
    this.wrapper.onmouseover = () => {
      this.hovering = true;
      this.cancelAutoSlide();
    };
    this.wrapper.onmouseleave = () => {
      this.hovering = false;
      this.startAutoSlide();
    };

    if (this.sliderInterval) {
      this.cancelAutoSlide();
    }
    this.startAutoSlide();
  }

  startAutoSlide() {
    if (!this.slideAutomatically) return;
    if (this.sliderInterval) {
      this.cancelAutoSlide();
    }
    this.sliderInterval = setInterval(() => {
      if (this.hovering) {
        return;
      }
      if (this.atEnd) {
        this.go(0);
      } else {
        this.go("next");
      }
    }, 5000);
  }

  cancelAutoSlide() {
    clearInterval(this.sliderInterval);
  }

  get onePage() {
    return this.items.length <= this.numberPerRow;
  }

  refreshIndicator() {
    this.log("Initializing indicator");
    if (this.indicator) {
      this.indicator.replaceChildren();
      // Indicator bar
      this.indicator.classList.add(
        "flex",
        "justify-center",
        "items-center",
        "mb-4"
      );

      // A dot for each item
      this.indicatorItemContainer = document.createElement("div");
      this.indicatorItemContainer.classList.add(
        "flex",
        "relative",
        "space-x-2.5",
        "mt-2"
      );

      (this.indicator as HTMLElement).appendChild(this.indicatorItemContainer);

      if (this.onePage) {
        this.log("One page, skipping indicator");
        return;
      }

      this.items.forEach((_, i) => {
        const indicatorItem = document.createElement("div");
        indicatorItem.classList.add(
          CLASS_INDICATOR_DOT,
          "rounded-full",
          "cursor-pointer"
        );
        if (this.indicatorVariant === "window") {
          indicatorItem.classList.add("w-2", "h-2", "bg-gray-700/50", "z-30");
        } else if (this.indicatorVariant === "dots") {
          if (i % this.numberPerRow !== 0) return;
          indicatorItem.classList.add(
            "w-2.5",
            "h-2.5",
            "border-2",
            "border-gray-300",
            "hover:border-gray-500",
            "transition-all",
            "border-solid"
          );
        }
        indicatorItem.onclick = () => this.go(i);
        (this.indicatorItemContainer as HTMLElement).appendChild(indicatorItem);
      });

      // Overlay indicator
      if (this.indicatorVariant === "window") {
        this.indicatorOverlay = document.createElement("div");
        this.indicatorOverlay.classList.add(
          "h-3",
          "rounded-full",
          "absolute",
          "z-20",
          "!-mx-1",
          "!-mt-0.5",
          "px-1",
          "ring-2",
          "ring-gray-500/75",
          "bg-gray-300"
        );
        this.indicatorItemContainer.appendChild(this.indicatorOverlay);
      }
      this.updateCurrentIndicator();
    } else {
      this.log("No indicator found");
    }
  }

  updateCurrentIndicator() {
    this.log("Updating current indicator");
    if (this.indicatorVariant === "window") {
      if (!this.indicator) return;
      if (!this.indicatorOverlay) return;
      this.indicatorOverlay.setAttribute(
        "style",
        `
        width: calc(0.5rem + ${
          (this.indicator.clientWidth / this.containerWidth) * 100
        }%);
        left: ${(this.currentScrollLeft / this.containerWidth) * 100}%;
      `
      );
    } else if (this.indicatorVariant === "dots") {
      const activeClasses = [
        "border-gray-700",
        "bg-gray-700",
        CLASS_INDICATOR_DOT_ACTIVE,
      ];
      this.indicatorItemContainer
        ?.querySelectorAll(`.${CLASS_INDICATOR_DOT}`)
        .forEach((item, index) => {
          if (
            (index - 1) * this.numberPerRow < this.currentItem &&
            index * this.numberPerRow >= this.currentItem
          ) {
            item.classList.add(...activeClasses);
          } else {
            item.classList.remove(...activeClasses);
          }
        });
    }
  }

  calculatePositions() {
    this.log("Calculating positions");
    this.positions = this.items.map((item) => [
      item.offsetLeft,
      item.offsetLeft + item.offsetWidth,
    ]);
    this.numberPerRow = Math.round(
      this.container.clientWidth / this.items[0].scrollWidth
    );
  }

  next() {
    this.log("Going to next");
    this.go("next");
  }

  prev() {
    this.log("Going to prev");
    this.go("prev");
  }

  determineButtonVisibility() {
    this.log("Determining button visibility");
    const prevBtns = this.wrapper.querySelectorAll(
      "[data-sp-carousel-go='prev']"
    );
    const nextBtns = this.wrapper.querySelectorAll(
      "[data-sp-carousel-go='next']"
    );

    if (this.onePage) {
      prevBtns.forEach((btn) => btn.classList.add("hidden"));
      nextBtns.forEach((btn) => btn.classList.add("hidden"));
    } else {
      prevBtns.forEach((btn) => btn.classList.remove("hidden"));
      nextBtns.forEach((btn) => btn.classList.remove("hidden"));
    }

    if (this.atStart) {
      prevBtns.forEach((btn) => btn.classList.add("opacity-0"));
    } else {
      prevBtns.forEach((btn) => btn.classList.remove("opacity-0"));
    }
    if (this.atEnd) {
      nextBtns.forEach((btn) => btn.classList.add("opacity-0"));
    } else {
      nextBtns.forEach((btn) => btn.classList.remove("opacity-0"));
    }
  }

  get currentScrollLeft() {
    return Math.ceil(this.container.scrollLeft);
  }

  get currentScrollRight() {
    return Math.floor(this.currentScrollLeft + this.container.offsetWidth);
  }

  get containerWidth() {
    return this.container.scrollWidth;
  }

  get fullWidth() {
    return this.positions[this.positions.length - 1][1];
  }

  get atStart() {
    return this.currentScrollLeft === 0;
  }

  get atEnd() {
    return this.currentScrollRight > this.containerWidth - 30;
  }

  go(to: string | number) {
    this.log("Going to: " + to);

    if (this.scrolling) return;
    this.calculatePositions();

    const itemSteps = this.indicatorVariant === "dots" ? this.numberPerRow : 1;

    let newItem = this.currentItem;
    if (typeof to === "string") {
      if (
        (this.currentScrollLeft === 0 && to === "prev") ||
        (this.atEnd && to === "next")
      ) {
        return;
      }

      if (to === "next") {
        newItem = Math.min(
          this.currentItem + itemSteps,
          this.items.length - itemSteps
        );
      } else if (to === "prev") {
        newItem = Math.max(this.currentItem - itemSteps, 0);
      }
    } else if (typeof to === "number") {
      newItem = to;
    }

    this.container.scrollTo({
      left: this.positions[newItem][0],
      behavior: "smooth",
    });
  }

  log(text: string) {
    if (this.isDebug) {
      console.log("[SPARKPLUGINS CAROUSEL DEBUG]: " + text);
    }
  }
}
