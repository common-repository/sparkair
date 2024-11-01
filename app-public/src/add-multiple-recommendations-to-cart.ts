export class AddMultipleRecommendationsToCart {
  protected wrapper!: HTMLElement;
  protected container!: HTMLElement;
  protected summary!: HTMLElement;
  protected priceElement!: HTMLElement;
  protected items!: HTMLElement[];
  protected productList!: HTMLElement[];
  protected checkboxes!: HTMLElement[];
  protected addToCartButton!: HTMLElement;
  protected decimalSeparator!: string;
  protected loading: boolean;

  constructor(
    wrapperSelector: string,
    containerSelector: string,
    summarySelector: string,
    decimalSeparator: string
  ) {
    this.loading = false;
    this.decimalSeparator = decimalSeparator;
    const wrapper = document.querySelector(wrapperSelector);

    if (!wrapper || !(wrapper instanceof HTMLElement)) return;
    this.wrapper = wrapper;

    const container = this.wrapper.querySelector(containerSelector);

    if (!container || !(container instanceof HTMLElement)) return;
    this.container = container;

    const summary = this.wrapper.querySelector(summarySelector);

    if (!summary || !(summary instanceof HTMLElement)) return;
    this.summary = summary;

    this.items = Array.prototype.slice.call(
      this.container.querySelectorAll(".sparkwoo-pr-item")
    );
    this.productList = Array.prototype.slice.call(
      this.summary.querySelectorAll(
        ".sparkwoo-add-to-cart-product-list .sparkwoo-add-to-cart-product"
      )
    );

    const priceElement = this.summary.querySelector(
      ".sparkwoo-add-to-cart-total-price"
    );
    if (!priceElement || !(priceElement instanceof HTMLElement)) return;
    this.priceElement = priceElement;

    const addToCartButton = this.summary.querySelector(
      ".sparkwoo-add-selected-to-cart"
    );
    if (!addToCartButton || !(addToCartButton instanceof HTMLElement)) return;
    this.addToCartButton = addToCartButton;

    this.checkboxes = [];

    this.init();
  }

  init() {
    // this.container.classList.add("gap-4");
    this.items.forEach((item) => {
      item.classList.add("!pt-8");
      const itemOverlay = document.createElement("div");
      itemOverlay.classList.add("absolute", "inset-0", "z-10", "hidden");

      const checkboxId = (Math.random() + 1).toString(36).substring(7);

      const itemCheckboxWrap = document.createElement("div");
      itemCheckboxWrap.classList.add(
        "flex",
        "absolute",
        "top-0",
        "left-0",
        "px-4",
        "pb-3",
        "z-20",
        "w-full"
      );

      const itemCheckbox = document.createElement("input");
      itemCheckbox.setAttribute("id", checkboxId);
      itemCheckbox.setAttribute("name", checkboxId);
      itemCheckbox.setAttribute("type", "checkbox");
      itemCheckbox.setAttribute("checked", "checked");

      const productId = [...item.classList]
        .find((c) => c.startsWith("post-"))
        ?.replace("post-", "");

      itemCheckbox.setAttribute("value", productId ?? "");
      itemCheckbox.classList.add(
        "w-5",
        "h-5",
        "text-gray-600",
        "bg-gray-100",
        "border-gray-300",
        "rounded-lg"
      );
      itemCheckbox.onchange = (v) => this.toggle(v, itemOverlay, item);

      const itemCheckboxLabel = document.createElement("label");
      itemCheckboxLabel.classList.add("w-full", "h-5");
      itemCheckboxLabel.setAttribute("for", checkboxId);

      this.checkboxes.push(itemCheckbox);

      itemCheckboxWrap.appendChild(itemCheckbox);
      itemCheckboxWrap.appendChild(itemCheckboxLabel);
      (item as HTMLElement).appendChild(itemCheckboxWrap);
      (item as HTMLElement).appendChild(itemOverlay);
    });

    this.addToCartButton.onclick = (e) => {
      e.preventDefault();
      this.addSelectedItemsToCart(e);
    };

    this.productList.forEach((p) => {
      const variation = p.querySelector(
        `.sparkwoo-add-to-cart-product-variation-wrapper select`
      ) as HTMLSelectElement;
      if (!variation) return;

      variation.onchange = () => this.update();
    });

    this.update();
  }

  toggle(event: Event, itemOverlay: HTMLElement, item: HTMLElement) {
    const checked = (event.target as HTMLInputElement).checked;
    if (checked) {
      itemOverlay.classList.add("hidden");
      item.classList.remove("opacity-30");
    } else {
      itemOverlay.classList.remove("hidden");
      item.classList.add("opacity-30");
    }

    this.update();
  }

  update() {
    this.priceElement.innerHTML = this.totalPrice
      .toFixed(2)
      .replace(".", this.decimalSeparator);

    let variationsSelected = false;
    this.productList.forEach((p) => {
      const productId = Number(p.dataset.id);
      const variation = p.querySelector(
        `.sparkwoo-add-to-cart-product-variation-wrapper`
      );
      if (variation) {
        if (this.productIds.includes(productId)) {
          variation.classList.remove("hidden");
          variationsSelected = true;
        } else {
          variation.classList.add("hidden");
        }
      }
    });

    const variationsMessage = this.wrapper.querySelector(
      ".sparkwoo-add-to-cart-product-variation-message"
    );
    if (variationsSelected) {
      variationsMessage?.classList.remove("hidden");
    } else {
      variationsMessage?.classList.add("hidden");
    }
  }

  async addSelectedItemsToCart(e: Event) {
    if (this.loading) return;
    this.startLoading();
    for (const p of this.productList) {
      const productId = Number(p.dataset.id);
      if (!this.productIds.includes(productId)) continue;

      const variation = this.getVariation(productId);

      const data = {
        product_sku: "",
        product_id: variation ? String(variation.id) : String(productId),
        quantity: "1",
      } as any;

      const analyticsEventKey = Object.keys(p.dataset).find((k) =>
        k.endsWith("analytics_event")
      );
      if (analyticsEventKey) {
        data[analyticsEventKey] = p.dataset[analyticsEventKey];
      }

      await fetch(`?wc-ajax=add_to_cart`, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: new URLSearchParams(data).toString(),
      });
    }

    window.location.href = (e.target as HTMLAnchorElement).href;
  }

  startLoading() {
    this.loading = true;
    this.wrapper
      .querySelector(".sparkwoo-add-to-cart-product-loading")
      ?.classList.remove("hidden");
  }

  stopLoading() {
    this.loading = false;
    this.wrapper
      .querySelector(".sparkwoo-add-to-cart-product-loading")
      ?.classList.add("hidden");
  }

  get selectedItems() {
    const productIds = this.productIds;
    return this.items.filter((p) => productIds.includes(Number(p.dataset.id)));
  }

  get productIds() {
    return this.checkboxes
      .filter((c) => (c as HTMLInputElement).checked)
      .map((c) => c.getAttribute("value"))
      .filter((c) => !!c)
      .map(Number);
  }

  getProductById(productId: number) {
    return this.productList.find((p) => Number(p.dataset.id) === productId);
  }

  getVariation(productId: number) {
    const variation = this.getProductById(productId)?.querySelector(
      `.sparkwoo-add-to-cart-product-variation-wrapper select`
    ) as HTMLSelectElement;

    if (!variation) {
      return null;
    }

    return {
      id: Number(variation.value),
      price: Number(
        variation.options[variation.selectedIndex].dataset.price ?? 0
      ),
    };
  }

  get totalPrice() {
    const productIds = this.productIds;
    return this.productList
      .filter((p) => productIds.includes(Number(p.dataset.id)))
      .map((p) => {
        const variation = this.getVariation(Number(p.dataset.id));
        return variation?.price ?? Number(p.dataset.price ?? 0);
      })
      .reduce((acc, p) => acc + p, 0);
  }
}
