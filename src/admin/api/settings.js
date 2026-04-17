import { createApi } from "./index";

const api = createApi("settings");

export const fetch = api.fetch;
export const save = api.save;
export const clearCache = api.clearCache;
