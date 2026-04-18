import { createRoot } from "react-dom/client";
import { HashRouter } from "react-router-dom";
import DataProvider from "./data/context";
import App from "./App";
import "./App.scss";
import "@fontsource/inter";

window.addEventListener(
	"load",
	function () {
		const container = document.querySelector("div#woocommerce-delivery-notes");

		if (container) {
			const root = createRoot(container);
			root.render(
				<DataProvider>
					<HashRouter>
						<App />
					</HashRouter>
				</DataProvider>
			);
		}
	},
	false
);
