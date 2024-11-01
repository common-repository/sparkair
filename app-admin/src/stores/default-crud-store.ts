import { defineStore } from "pinia";
import type { AxiosError, AxiosInstance, AxiosRequestConfig } from "axios";
import useRegistryStore, { SparkPlugin } from "./registry";

type SaveOptions = {
  fileUpload?: true;
};

const defaultCrudStore = (
  storeId: string,
  url: string,
  {
    idKey = "id",
    klass = undefined,
  }: {
    idKey?: string;
    klass?: any;
  } = {}
) => {
  const apiUrl = url;

  const determineAxiosRequestConfig = (
    data: any,
    { fileUpload }: SaveOptions
  ) => {
    const options: AxiosRequestConfig = {};
    if (fileUpload) {
      options.method = "POST";
      options.headers = {};
      options.headers["Content-Type"] = "multipart/form-data";
    }
    return options;
  };

  return defineStore({
    id: storeId,
    state: () =>
      ({
        items: [],
        fetching: false,
        fetchingSingle: {},
        creating: false,
        updatingSingle: {},
        deletingSingle: {},
        fetched: false,
        fetchError: false,
      } as {
        items: any[];
        fetching: boolean;
        fetchingSingle: Record<number, boolean>;
        creating: boolean;
        updatingSingle: Record<number, boolean>;
        deletingSingle: Record<number, boolean>;
        fetched: boolean;
        fetchError: boolean;
      }),
    getters: {
      all: (state) => state.items,
      getById: (state) => (id: any) =>
        state.items.find((o: any) => o[idKey] === id),
      getBy: (state) => (key: any, value: any) =>
        state.items.filter((o: any) => o[key] === value),
      getOneBy: (state) => (key: any, value: any) =>
        state.items.find((o: any) => o[key] === value),
      isFetching: (state) => state.fetching,
      isCreating: (state) => state.creating,
      isRefreshing: (state) => state.fetching && state.items.length > 0,
      isFetchingSingle: (state) => (id: number) => state.fetchingSingle[id],
      isUpdatingSingle: (state) => (id: number) => state.updatingSingle[id],
      isDeletingSingle: (state) => (id: number) => state.deletingSingle[id],
    },
    actions: {
      getApi({
        plugin = undefined,
      }: {
        plugin?: SparkPlugin;
      } = {}) {
        const registryStore = useRegistryStore();
        return plugin?.api ?? registryStore.firstInstalledPlugin.api;
      },
      async fetch({
        force = false,
        plugin = undefined,
      }: {
        force?: boolean;
        plugin?: SparkPlugin;
      } = {}) {
        const api = this.getApi({ plugin });

        if (this.fetched && !force) {
          return this.items;
        }

        this.fetching = true;
        this.fetchError = false;
        try {
          const response = await api.get(apiUrl);
          this.items = response.data.data.map((i: any) =>
            klass ? new klass(i) : i
          );
          this.fetched = true;
          this.fetchError = false;
        } catch (e) {
          this.fetchError = true;
        }
        this.fetching = false;
        return this.items;
      },
      async create(
        item: any,
        options: SaveOptions = {},
        plugin: SparkPlugin | undefined = undefined
      ) {
        const api = this.getApi({ plugin });

        this.creating = true;
        let newObject: any = null;
        try {
          const response = await api.post(
            apiUrl,
            item,
            determineAxiosRequestConfig(item, options)
          );
          newObject = response.data.data;
          this.items.push(klass ? new klass(newObject) : newObject);
        } catch (e) {
          console.log(e);
        } finally {
          this.creating = false;
        }
        return newObject;
      },
      async fetchSingle(
        id: number,
        plugin: SparkPlugin | undefined = undefined
      ) {
        const api = this.getApi({ plugin });

        this.fetchingSingle[id] = true;
        let fetchedObject: any = null;
        try {
          const response = await api.get(`${apiUrl}/${id}`);
          fetchedObject = response.data.data;
          this.updateItemInStore(fetchedObject[idKey], fetchedObject);
        } catch (e) {
          console.log(e);
        } finally {
          this.fetchingSingle[id] = false;
        }
        return fetchedObject;
      },
      async updateSingle(
        id: number,
        item: any,
        options: SaveOptions = {},
        plugin: SparkPlugin | undefined = undefined
      ) {
        const api = this.getApi({ plugin });

        this.updatingSingle[id] = true;
        const updatedObject: any = null;
        try {
          const response = await api.put(
            `${apiUrl}/${id}`,
            item,
            determineAxiosRequestConfig(item, options)
          );
          const updatedObject = response.data.data;
          this.updateItemInStore(id, updatedObject);
        } catch (e) {
          console.log(e);
        } finally {
          this.updatingSingle[id] = false;
        }
        return updatedObject;
      },
      async deleteSingle(
        id: number,
        plugin: SparkPlugin | undefined = undefined
      ) {
        const api = this.getApi({ plugin });

        this.deletingSingle[id] = true;
        try {
          await api.delete(`${apiUrl}/${id}`);
          const index = this.items.findIndex((i) => i[idKey] === id);
          this.items.splice(index, 1);
        } catch (e) {
          return false;
        } finally {
          this.deletingSingle[id] = false;
        }
        return true;
      },
      updateItemInStore(id: number, item: any) {
        const index = this.items.findIndex((i) => i[idKey] === id);
        if (index !== -1) {
          this.items[index] = klass ? new klass(item) : item;
        }
      },
    },
  });
};

export default defaultCrudStore;
