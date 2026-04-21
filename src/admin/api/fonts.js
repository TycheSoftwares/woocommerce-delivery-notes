import apiFetch from "@wordpress/api-fetch";
import { handleResponse } from "./utils";

export function fetchStatus() {
    return apiFetch({ path: "/wcdn/v1/fonts" }).then(handleResponse);
}

export function uploadFont(file, weight = "regular") {
    const formData = new FormData();
    formData.append("font_file", file);
    formData.append("weight", weight);

    return apiFetch({
        path: "/wcdn/v1/fonts",
        method: "POST",
        body: formData,
    }).then(handleResponse);
}

export function deleteFont(weight = "regular") {
    return apiFetch({
        path: `/wcdn/v1/fonts?weight=${weight}`,
        method: "DELETE",
    }).then(handleResponse);
}
