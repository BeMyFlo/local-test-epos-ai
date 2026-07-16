import { __ } from "@wordpress/i18n";
import { Button } from "@wordpress/components";

export default function EmptyState({ onReset }) {
	return (
		<div className="zaud-empty">
			<div className="zaud-empty__icon">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
					<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
					<rect x="9" y="3" width="6" height="4" rx="1" />
					<line x1="9" y1="12" x2="15" y2="12" />
					<line x1="9" y1="16" x2="13" y2="16" />
				</svg>
			</div>
			<h2>{__("No events match your filters", "ai-zippy")}</h2>
			<p>{__("Try adjusting the date range, event type, or search term.", "ai-zippy")}</p>
			<Button variant="primary" onClick={onReset}>
				{__("Reset filters", "ai-zippy")}
			</Button>
		</div>
	);
}
