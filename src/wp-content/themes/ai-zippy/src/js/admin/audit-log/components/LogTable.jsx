import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Button } from "@wordpress/components";

const EVENT_LABELS = {
	"post.create":         { label: "Post created",       tone: "success" },
	"post.update":         { label: "Post updated",       tone: "info" },
	"post.delete":         { label: "Post deleted",       tone: "danger" },
	"page.create":         { label: "Page created",       tone: "success" },
	"page.update":         { label: "Page updated",       tone: "info" },
	"page.delete":         { label: "Page deleted",       tone: "danger" },
	"wc.product.create":   { label: "Product created",    tone: "success" },
	"wc.product.update":   { label: "Product updated",    tone: "info" },
	"wc.product.delete":   { label: "Product deleted",    tone: "danger" },
	"wc.category.create":  { label: "Category created",   tone: "success" },
	"wc.category.update":  { label: "Category updated",   tone: "info" },
	"wc.category.delete":  { label: "Category deleted",   tone: "danger" },
	"wc.brand.create":     { label: "Brand created",      tone: "success" },
	"wc.brand.update":     { label: "Brand updated",      tone: "info" },
	"wc.brand.delete":     { label: "Brand deleted",      tone: "danger" },
	"wc.config.update":    { label: "WC setting changed", tone: "warning" },
	"plugin.install":      { label: "Plugin installed",   tone: "success" },
	"plugin.update":       { label: "Plugin updated",     tone: "info" },
	"plugin.activate":     { label: "Plugin activated",   tone: "success" },
	"plugin.deactivate":   { label: "Plugin deactivated", tone: "warning" },
	"plugin.delete":       { label: "Plugin deleted",     tone: "danger" },
	"login.success":       { label: "Login",              tone: "success" },
	"login.failed":        { label: "Failed login",       tone: "danger" },
};

function formatDate(s) {
	if (!s) return "";
	// MySQL datetime → local string
	const d = new Date(s.replace(" ", "T") + "Z");
	if (Number.isNaN(d.getTime())) return s;
	return d.toLocaleString();
}

function Row({ row }) {
	const [open, setOpen] = useState(false);
	const event = EVENT_LABELS[row.event_type] || { label: row.event_type, tone: "neutral" };

	const hasMeta = row.meta && Object.keys(row.meta).length > 0;

	return (
		<>
			<tr className="zaud-table__row" onClick={() => hasMeta && setOpen(!open)} style={{ cursor: hasMeta ? "pointer" : "default" }}>
				<td className="zaud-table__time">{formatDate(row.created_at)}</td>
				<td className="zaud-table__actor">
					{row.user_login || <span className="zaud-table__muted">—</span>}
					<div className="zaud-table__ip">{row.ip}</div>
				</td>
				<td className="zaud-table__event">
					<span className={`zaud-pill zaud-pill--${event.tone}`}>{event.label}</span>
				</td>
				<td className="zaud-table__object">
					{row.object_label || <span className="zaud-table__muted">—</span>}
					{row.object_id > 0 && (
						<span className="zaud-table__obj-id">#{row.object_id}</span>
					)}
				</td>
				<td className="zaud-table__meta">
					{hasMeta ? (
						<span className="zaud-table__meta-toggle">
							{open ? "▾" : "▸"} {Object.keys(row.meta).join(", ")}
						</span>
					) : (
						<span className="zaud-table__muted">—</span>
					)}
				</td>
			</tr>
			{open && hasMeta && (
				<tr className="zaud-table__detail-row">
					<td colSpan={5}>
						<pre className="zaud-table__detail">{JSON.stringify(row.meta, null, 2)}</pre>
					</td>
				</tr>
			)}
		</>
	);
}

export default function LogTable({ data, page, perPage, onPage }) {
	const { items, total, pages } = data;

	return (
		<div className="zaud-table-wrap">
			<table className="zaud-table">
				<thead>
					<tr>
						<th>{__("Time", "ai-zippy")}</th>
						<th>{__("Actor", "ai-zippy")}</th>
						<th>{__("Event", "ai-zippy")}</th>
						<th>{__("Object", "ai-zippy")}</th>
						<th>{__("Details", "ai-zippy")}</th>
					</tr>
				</thead>
				<tbody>
					{items.map((row) => <Row key={row.id} row={row} />)}
				</tbody>
			</table>

			<div className="zaud-pagination">
				<div className="zaud-pagination__info">
					{__("Total", "ai-zippy")}: <strong>{total.toLocaleString()}</strong>
					{" · "}
					{__("Page", "ai-zippy")} {page} / {pages || 1}
				</div>
				<div className="zaud-pagination__buttons">
					<Button
						variant="secondary"
						onClick={() => onPage(Math.max(1, page - 1))}
						disabled={page <= 1}
					>
						‹ {__("Prev", "ai-zippy")}
					</Button>
					<Button
						variant="secondary"
						onClick={() => onPage(Math.min(pages || 1, page + 1))}
						disabled={page >= (pages || 1)}
					>
						{__("Next", "ai-zippy")} ›
					</Button>
				</div>
			</div>
		</div>
	);
}
