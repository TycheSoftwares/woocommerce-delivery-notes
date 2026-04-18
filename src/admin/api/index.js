import apiFetch from "@wordpress/api-fetch";
import { handleResponse } from "./utils";
import { cachedFetch, updateCache, clearCache } from "./cache";

/**
 * Create API Helper
 *
 * Generates fetch/save/cache functions
 *
 * @param {string} endpoint
 * @since 7.0
 */
export function createApi(endpoint) {
    const CACHE_KEY = endpoint;

    return {
        fetch(forceRefresh = false) {
            return cachedFetch(
                CACHE_KEY,
                () =>
                    apiFetch({
                        path: `/wcdn/v1/${endpoint}`,
                    }).then(handleResponse),
                forceRefresh
            );
        },

        save(data) {
            return apiFetch({
                path: `/wcdn/v1/${endpoint}`,
                method: "POST",
                data,
            })
                .then(handleResponse)
                .then((response) => {
                    const result = response || {};
                    updateCache(CACHE_KEY, result);
                    return result;
                });
        },

        clearCache() {
            clearCache(CACHE_KEY);
        },
    };
}
