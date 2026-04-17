import { dispatch } from "@wordpress/data";
import { store as coreDataStore } from "@wordpress/core-data";

export const registerEntities = () => {
	dispatch(coreDataStore).addEntities([
		{
			kind: "wcdn",
			name: "settings",
			baseURL: "/wp/v2/wcdn/settings",
		},
		{
			kind: "wcdn",
			name: "logs",
			baseURL: "/wp/v2/wcdn/logs",
		},
		{
			kind: "wcdn",
			name: "reports",
			baseURL: "/wp/v2/wcdn/reports",
		},
	]);
};
