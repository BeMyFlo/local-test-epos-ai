import { useCallback, useEffect, useMemo, useState } from "@wordpress/element";
import { Button, Notice, Spinner, __experimentalHeading as Heading } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { apiGet, apiPost } from "../shared/api.js";
import StatsCards from "./components/StatsCards.jsx";
import FilterBar from "./components/FilterBar.jsx";
import LogTable from "./components/LogTable.jsx";
import EmptyState from "./components/EmptyState.jsx";
import SettingsPanel from "./components/SettingsPanel.jsx";

const DEFAULT_FILTERS = {
	page: 1,
	per_page: 25,
	event_type: "",
	object_type: "",
	user_id: 0,
	from: "",
	to: "",
	search: "",
};

export default function App() {
	const [filters, setFilters] = useState(DEFAULT_FILTERS);
	const [data, setData]       = useState({ items: [], total: 0, pages: 0 });
	const [stats, setStats]     = useState(null);
	const [users, setUsers]     = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError]     = useState(null);
	const [cleanupBusy, setCleanupBusy] = useState(false);
	const [showSettings, setShowSettings] = useState(false);

	// ---------------------- Fetch list -----------------------------------

	const fetchList = useCallback(async (f) => {
		setLoading(true);
		setError(null);
		try {
			const qs = new URLSearchParams();
			Object.entries(f).forEach(([k, v]) => {
				if (v !== "" && v !== 0 && v !== null && v !== undefined) {
					qs.set(k, v);
				}
			});
			const res = await apiGet(`audit-log?${qs.toString()}`);
			setData(res);
		} catch (e) {
			setError(e.message || "Failed to load audit log");
		} finally {
			setLoading(false);
		}
	}, []);

	const fetchStats = useCallback(async () => {
		try {
			const res = await apiGet("audit-log/stats");
			setStats(res);
		} catch {
			// non-fatal
		}
	}, []);

	const fetchUsers = useCallback(async () => {
		try {
			const res = await apiGet("audit-log/users");
			setUsers(res || []);
		} catch {
			// non-fatal
		}
	}, []);

	useEffect(() => { fetchStats(); fetchUsers(); }, [fetchStats, fetchUsers]);
	useEffect(() => { fetchList(filters); }, [filters, fetchList]);

	// ---------------------- Filter actions -------------------------------

	const updateFilters = useCallback((patch) => {
		setFilters((prev) => ({ ...prev, ...patch, page: patch.page ?? 1 }));
	}, []);

	const resetFilters = useCallback(() => {
		setFilters(DEFAULT_FILTERS);
	}, []);

	// ---------------------- Cleanup -------------------------------------

	const runCleanup = useCallback(async () => {
		setCleanupBusy(true);
		try {
			const res = await apiPost("audit-log/cleanup", {});
			alert(__("Cleanup done: %d rows deleted.", "ai-zippy").replace("%d", res.deleted));
			fetchList(filters);
			fetchStats();
		} catch (e) {
			alert(e.message || "Cleanup failed");
		} finally {
			setCleanupBusy(false);
		}
	}, [fetchList, fetchStats, filters]);

	// ---------------------- Render --------------------------------------

	const isEmpty = useMemo(
		() => !loading && data.items.length === 0,
		[loading, data.items.length]
	);

	return (
		<div className="zaud">
			<header className="zaud__header">
				<Heading level={1}>{__("Audit Log", "ai-zippy")}</Heading>
				<div className="zaud__header-actions">
					<Button variant="secondary" onClick={() => setShowSettings((s) => !s)}>
						{showSettings ? __("Hide settings", "ai-zippy") : __("Settings", "ai-zippy")}
					</Button>
					<Button
						variant="secondary"
						isBusy={cleanupBusy}
						onClick={runCleanup}
					>
						{__("Run cleanup now", "ai-zippy")}
					</Button>
				</div>
			</header>

			{showSettings && (
				<SettingsPanel onClose={() => setShowSettings(false)} />
			)}

			<StatsCards stats={stats} />

			<FilterBar
				filters={filters}
				users={users}
				onChange={updateFilters}
				onReset={resetFilters}
			/>

			{error && (
				<Notice status="error" isDismissible={false}>
					{error}
				</Notice>
			)}

			{loading ? (
				<div className="zaud__loading">
					<Spinner />
					<span>{__("Loading…", "ai-zippy")}</span>
				</div>
			) : isEmpty ? (
				<EmptyState onReset={resetFilters} />
			) : (
				<LogTable
					data={data}
					page={filters.page}
					perPage={filters.per_page}
					onPage={(p) => updateFilters({ page: p })}
				/>
			)}
		</div>
	);
}
