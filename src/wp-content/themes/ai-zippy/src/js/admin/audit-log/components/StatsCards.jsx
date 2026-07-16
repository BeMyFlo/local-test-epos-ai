import { __ } from "@wordpress/i18n";

export default function StatsCards({ stats }) {
	const total7d   = stats?.total_7d ?? 0;
	const failed24h = stats?.failed_24h ?? 0;
	const totalAll  = stats?.total_all ?? 0;
	const byEvent   = stats?.by_event_7d ?? {};

	const productEdits7d = (byEvent["wc.product.update"] ?? 0)
		+ (byEvent["wc.product.create"] ?? 0)
		+ (byEvent["wc.product.delete"] ?? 0);

	const pageEdits7d = (byEvent["page.update"] ?? 0)
		+ (byEvent["page.create"] ?? 0)
		+ (byEvent["page.delete"] ?? 0);

	const cards = [
		{ label: __("Events (7 days)",      "ai-zippy"), value: total7d,        tone: "neutral" },
		{ label: __("Failed logins (24h)",  "ai-zippy"), value: failed24h,      tone: failed24h >= 5 ? "danger" : "neutral" },
		{ label: __("Product changes (7d)", "ai-zippy"), value: productEdits7d, tone: "accent" },
		{ label: __("Page changes (7d)",    "ai-zippy"), value: pageEdits7d,    tone: "accent" },
		{ label: __("All-time records",     "ai-zippy"), value: totalAll,       tone: "muted" },
	];

	return (
		<div className="zaud-stats">
			{cards.map((c) => (
				<div key={c.label} className={`zaud-stats__card zaud-stats__card--${c.tone}`}>
					<div className="zaud-stats__value">{Number(c.value).toLocaleString()}</div>
					<div className="zaud-stats__label">{c.label}</div>
				</div>
			))}
		</div>
	);
}
