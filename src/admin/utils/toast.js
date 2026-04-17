import { dispatch } from "@wordpress/data";
import { store as noticesStore } from "@wordpress/notices";

export const toast = {
    success(message) {
        dispatch(noticesStore).createNotice("success", message);
    },

    error(message) {
        dispatch(noticesStore).createNotice("error", message);
    },

    warning(message) {
        dispatch(noticesStore).createNotice("warning", message);
    },

    info(message) {
        dispatch(noticesStore).createNotice("info", message);
    },
};
