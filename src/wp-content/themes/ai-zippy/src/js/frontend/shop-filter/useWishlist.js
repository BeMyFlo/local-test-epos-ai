import { useState, useCallback } from "react";

const STORAGE_KEY = "shop-wishlist";

function readStoredIds() {
	try {
		const raw = JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
		return Array.isArray(raw) ? raw : [];
	} catch {
		return [];
	}
}

/**
 * Wishlist state backed by localStorage — there's no REST endpoint or user
 * meta behind this yet, so it's per-browser only and won't follow a customer
 * across devices or survive clearing site data.
 */
export default function useWishlist() {
	const [ids, setIds] = useState(readStoredIds);

	const toggle = useCallback((productId) => {
		setIds((prev) => {
			const next = prev.includes(productId)
				? prev.filter((id) => id !== productId)
				: [...prev, productId];
			localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
			return next;
		});
	}, []);

	const isWishlisted = useCallback((productId) => ids.includes(productId), [ids]);

	return { isWishlisted, toggle };
}
