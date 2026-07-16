import { __ } from "@wordpress/i18n";
import { SelectControl, TextControl, Button } from "@wordpress/components";

const EVENT_GROUPS = [
	{
		label: __("All events", "ai-zippy"),
		value: "",
	},
	{
		label: "── Posts ──",
		value: "post.create,post.update,post.delete",
	},
	{
		label: "── Pages ──",
		value: "page.create,page.update,page.delete",
	},
	{
		label: "── Products ──",
		value: "wc.product.create,wc.product.update,wc.product.delete",
	},
	{
		label: "── Categories ──",
		value: "wc.category.create,wc.category.update,wc.category.delete",
	},
	{
		label: "── Brands ──",
		value: "wc.brand.create,wc.brand.update,wc.brand.delete",
	},
	{
		label: "── WC Settings ──",
		value: "wc.config.update",
	},
	{
		label: "── Plugins ──",
		value: "plugin.install,plugin.update,plugin.activate,plugin.deactivate,plugin.delete",
	},
	{
		label: "── Logins ──",
		value: "login.success,login.failed",
	},
	{
		label: "── Failed logins only ──",
		value: "login.failed",
	},
];

export default function FilterBar({ filters, users, onChange, onReset }) {
	const userOptions = [
		{ label: __("All users", "ai-zippy"), value: 0 },
		...users.map((u) => ({ label: u.user_login, value: u.user_id })),
	];

	return (
		<div className="zaud-filters">
			<div className="zaud-filters__row">
				<TextControl
					label={__("Search", "ai-zippy")}
					value={filters.search}
					onChange={(v) => onChange({ search: v })}
					placeholder={__("Title, user…", "ai-zippy")}
					__nextHasNoMarginBottom
				/>

				<SelectControl
					label={__("Event", "ai-zippy")}
					value={filters.event_type}
					options={EVENT_GROUPS}
					onChange={(v) => onChange({ event_type: v })}
					__nextHasNoMarginBottom
				/>

				<SelectControl
					label={__("Actor", "ai-zippy")}
					value={String(filters.user_id)}
					options={userOptions.map((o) => ({ ...o, value: String(o.value) }))}
					onChange={(v) => onChange({ user_id: parseInt(v, 10) || 0 })}
					__nextHasNoMarginBottom
				/>

				<TextControl
					label={__("From", "ai-zippy")}
					type="date"
					value={filters.from}
					onChange={(v) => onChange({ from: v })}
					__nextHasNoMarginBottom
				/>

				<TextControl
					label={__("To", "ai-zippy")}
					type="date"
					value={filters.to}
					onChange={(v) => onChange({ to: v })}
					__nextHasNoMarginBottom
				/>

				<Button variant="tertiary" onClick={onReset}>
					{__("Reset", "ai-zippy")}
				</Button>
			</div>
		</div>
	);
}
