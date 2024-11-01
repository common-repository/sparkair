import { number, type InferType, string, boolean, object } from "yup";
import defaultCrudStore from "./default-crud-store";
import { defineStore } from "pinia";

export const airModelSchema = object({
  id: number().nullable(),
  name: string().required(),

  modelActivatedDateTime: string().nullable().default(null),
  trainingInitiatedDateTime: string().nullable().default(null),
  trainingStartedDateTime: string().nullable().default(null),
  trainingFinishedDateTime: string().nullable().default(null),
  trainingErroredDateTime: string().nullable().default(null),
  trainingStatusMessage: string().nullable().default(null),
  trainingLastSuccessDateTime: string().nullable().default(null),

  tuneAutomatically: boolean().required().default(true),
  features: number().required().default(17).min(5).max(20),
  ratingPerOrder: number().required().default(4),
  ratingCountOrder: number().required().default(2),
  ratingPerView: number().required().default(0.5),
  ratingCountView: number().required().default(8),

  rmse: string().nullable(),
  usersCount: number().nullable(),
  itemsCount: number().nullable(),

  documentId: string(),
});

export interface IAirModel extends InferType<typeof airModelSchema> {}

export type AirModelsStore = Omit<
  ReturnType<typeof useAirModelsStore> & {
    items: IAirModel[];
  },
  keyof ReturnType<typeof defineStore>
>;

const useAirModelsStore = defaultCrudStore(
  "air-base-models",
  "/sparkair-model"
);

export default useAirModelsStore;
