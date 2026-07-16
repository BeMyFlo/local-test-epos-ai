import { useEffect, useState } from "@wordpress/element";
import { Button, TextControl, Notice } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { apiGet, apiPost } from "../../shared/api.js";

export default function SettingsPanel({ onClose }) {
	const [days, setDays]       = useState(90);
	const [loading, setLoading] = useState(true);
	const [saving, setSaving]   = useState(false);
	const [savedAt, setSavedAt] = useState(null);
	const [error, setError]     = useState(null);

	useEffect(() => {
		(async () => {
			try {
				const r = await apiGet("audit-log/settings");
				setDays(r.retention_days || 90);
			} catch (e) {
				setError(e.message || "Failed to load settings");
			} finally {
				setLoading(false);
			}
		})();
	}, []);

	const save = async () => {
		setSaving(true);
		setError(null);
		try {
			const r = await apiPost("audit-log/settings", { retention_days: parseInt(days, 10) || 90 });
			setDays(r.retention_days);
			setSavedAt(Date.now());
		} catch (e) {
			setError(e.message || "Save failed");
		} finally {
			setSaving(false);
		}
	};

	if (loading) return null;

	return (
		<div className="zaud-settings">
			<div className="zaud-settings__row">
				<TextControl
					label={__("Retention (days)", "ai-zippy")}
					type="number"
					value={String(days)}
					onChange={(v) => setDays(v)}
					help={__("Logs older than this many days are deleted automatically (daily). Min 7, max 3650.", "ai-zippy")}
					min={7}
					max={3650}
					__nextHasNoMarginBottom
				/>

				<div className="zaud-settings__actions">
					<Button variant="primary" isBusy={saving} onClick={save}>
						{__("Save", "ai-zippy")}
					</Button>
					<Button variant="tertiary" onClick={onClose}>
						{__("Close", "ai-zippy")}
					</Button>
				</div>
			</div>

			{error && (
				<Notice status="error" isDismissible={false}>{error}</Notice>
			)}
			{savedAt && !error && (
				<Notice status="success" isDismissible={false}>
					{__("Settings saved.", "ai-zippy")}
				</Notice>
			)}
		</div>
	);
}
