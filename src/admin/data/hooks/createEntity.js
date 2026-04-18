import { useSelect } from "@wordpress/data";
import { store as coreDataStore } from "@wordpress/core-data";

export const createEntity = (/** @type {string} */ name) => {
	return () =>
		useSelect(
			(/** @type {any} */ select) => ({
				data: select(coreDataStore).getEntityRecord("wcdn", name),
				hasResolved: select(coreDataStore).hasFinishedResolution("getEntityRecord", [
					"wcdn",
					name,
				]),
			}),
			[]
		);
};
