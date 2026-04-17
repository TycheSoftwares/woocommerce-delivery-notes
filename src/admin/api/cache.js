let cacheStore = {};
let promiseStore = {};

/**
 * Cached Fetch
 *
 * Handles fetch-once logic for API calls.
 *
 * @param {string} key Unique cache key
 * @param {Function} fetcher Function returning Promise
 * @param {boolean} forceRefresh Force reload
 *
 * @since 7.0
 */
export function cachedFetch(key, fetcher, forceRefresh = false) {
    if (cacheStore[key] && !forceRefresh) {
        return Promise.resolve(cacheStore[key]);
    }

    if (promiseStore[key] && !forceRefresh) {
        return promiseStore[key];
    }

    promiseStore[key] = fetcher()
        .then((data) => {
            cacheStore[key] = data;
            promiseStore[key] = null;
            return data;
        })
        .catch((error) => {
            promiseStore[key] = null;
            throw error;
        });

    return promiseStore[key];
}

/**
 * Update Cache
 *
 * @param {string} key Cache key
 * @param {*} data Cached data
 *
 * @since 7.0
 */
export function updateCache(key, data) {
    cacheStore[key] = data;
}

/**
 * Clear Cache
 *
 * @param {string} key Cache key
 *
 * @since 7.0
 */
export function clearCache(key) {
    cacheStore[key] = null;
}
