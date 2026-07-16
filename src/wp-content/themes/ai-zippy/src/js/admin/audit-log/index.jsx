import { createRoot, StrictMode } from "@wordpress/element";
import App from "./App.jsx";
import "./audit-log.scss";

const mount = document.getElementById("ai-zippy-audit-log-app");
if (mount) {
	createRoot(mount).render(
		<StrictMode>
			<App />
		</StrictMode>,
	);
}
