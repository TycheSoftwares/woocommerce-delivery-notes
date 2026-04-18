import { createApi } from "./index";

const api = createApi("templates");

export const fetch = api.fetch;
export const save = api.save;
export const clearCache = api.clearCache;
