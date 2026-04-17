import { __ } from "@wordpress/i18n";
import { TEXT_DOMAIN } from "../constants";

/**
 * Handle Standard API Response
 */
export function handleResponse(response) {
    if (response.data && response.status === "success") {
        return response.data;
    }

    throw response?.data?.error_description || __("Request failed", TEXT_DOMAIN);
}
