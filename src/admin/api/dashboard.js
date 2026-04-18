import { createApi } from "./index";

const api = createApi("dashboard");

export const fetch = api.fetch;
export const clearCache = api.clearCache;
