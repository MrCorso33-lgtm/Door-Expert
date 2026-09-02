
# UI 13 — PDP blocks and clickable project photos

Four more presentation patterns from Saya, in descending order of how much they matter for a salon
that closes most sales by phone.

---

## 1. Trust and delivery block — `ADAPT (light)`, port this

**Where:** `wp-theme/functions.php:2841-2960` (helper functions), rendered on the PDP.

Short, editable statements that answer the questions a phone-first buyer asks before calling:
delivery time, free-delivery threshold, returns, warranty, instalments.

The design decision worth copying is the **two-level fallback**: per-product meta first, site-wide
option second.

```php
/**
 * Tekst o roku isporuke.
 * Po proizvodu: _delivery_text · za cio sajt: opcija door_expert_delivery_text
 *
 * @param int $product_id ID proizvoda, 0 za globalnu vrijednost.
 * @return string
 */
function door_expert_delivery_info( $product_id = 0 ) {
	$text = $product_id ? get_post_meta( $product_id, '_delivery_text', true ) : '';

	if ( ! $text ) {
		$text = get_option(
			'door_expert_delivery_text',
			'2 do 3 radna dana Podgorica · 3 do 5 dana ostatak Crne Gore'
		);
	}

	return $text;
}
```

Why it matters: one special-order door has a six-week lead time while everything else ships in three
days. Without the per-product override you either lie on that product or hedge on all of them. The
same shape covers `_trust_return`, `_trust_warranty`, `_trust_installments`.

**Note:** Saya's helpers `esc_html()` **inside** the getter. That is early escaping, and it means the
value cannot be used in an attribute or passed through `wp_kses_post()` later. Return raw and escape
at the point of output instead, as above.

## 2. "Kombinuje se sa" — goes-well-with — `ADAPT (heavy)`, high sales value

**Where:** `wp-theme/functions.php:3605-3741` (admin editor), `wp-theme/woocommerce/single-product.php:1230-1290`
(front end), `wp-theme/js/admin-kombinuje-editor.js`.

A curated cross-sell that is **per variation**, not per product. Meta key `kombinuje_se_sa` holds
product IDs; the PDP renders a panel per variation and shows only the one matching the current
selection:

```php
<section class="kombinuje-section kombinuje-var-panel"
	data-variation-id="<?php echo (int) $var_id; ?>" style="display:none">
```

Why per variation matters here: the trim that goes with a white door is not the trim that goes with
a walnut one. Woo's native cross-sells are product-level and cannot express that.

**Cost:** this needs an admin UI or the client will never populate it. Saya built a drag-and-drop
meta box with a product search endpoint. That is the heavy part, and it is the reason this is
`ADAPT (heavy)` rather than light. Decide whether the client will actually curate before building
it; an empty cross-sell panel is worse than none.

## 3. Selection confirmation strip — `DROP-IN`

**Where:** `wp-theme/woocommerce/single-product.php:788-800`, driven by `applyConfirmStrip()` in
`wp-theme/js/product-single.js:1624`.

A thin bar under the swatches that spells out the selection in words once it is complete: "Rovere
Naturale, 90 × 200, lijeva". On mobile the swatches scroll out of view by the time the customer
reaches the CTA, so without it they are adding something they can no longer see.

Pair it with the sticky mobile CTA (`product-single.js:1004-1027`, an `IntersectionObserver` on the
add-to-cart button) and the bottom of a long PDP stops being a dead end.

## 4. Clickable hotspots on project photos — `ADAPT (heavy)`, distinctive

**Where:** `wp-theme/functions.php:3241-3604` (admin editor + product search AJAX),
`wp-theme/single-projekti.php:64-90` (front end), `wp-theme/js/admin-hotspot-editor.js`.

A finished-project photo with dots placed on it; each dot links to the product used in that spot.
Coordinates and product IDs live in one JSON meta field:

```php
$hotspots_raw = get_post_meta( $pid, 'project_hotspots', true );
$hotspots     = array();

if ( $hotspots_raw ) {
	$decoded = json_decode( $hotspots_raw, true );

	if ( JSON_ERROR_NONE === json_last_error() ) {
		$hotspots = (array) $decoded;
	}
}
```

The front end then derives the "products used in this project" list from the same JSON, deduplicated,
so there is no second field to keep in sync:

```php
$linked   = array();
$seen_ids = array();

foreach ( $hotspots as $spot ) {
	$id = (int) ( $spot['product_id'] ?? 0 );

	if ( ! $id || in_array( $id, $seen_ids, true ) ) {
		continue;
	}

	$product = wc_get_product( $id );

	if ( ! $product ) {
		continue;
	}

	// Varijacija je vidljiva ako joj je roditelj objavljen.
	$is_live = 'publish' === $product->get_status()
		|| ( $product->is_type( 'variation' ) && 'publish' === get_post_status( $product->get_parent_id() ) );

	if ( $is_live ) {
		$linked[]   = $product;
		$seen_ids[] = $id;
	}
}
```

That variation-parent status check is the non-obvious bit: a variation's own post status is
`publish` only incidentally, so checking the parent is what keeps a hidden product out of the list.

**Cost:** same as §2. The value is in the admin editor, which is ~360 lines plus a JS canvas for
placing dots. Worth it for a company selling rooms rather than SKUs, which a door and tile salon
arguably is. Not a first-month feature.

## 5. Priority

If you port one thing from this document, port **§1 trust and delivery**. It is an afternoon of work
and it answers the questions that otherwise become phone calls.

§3 is a good second: small, no admin surface, immediate mobile benefit.

§2 and §4 are both real features with real admin cost. Neither should be started before the quote
cart from `02-PORT-quote-cart.md` is live.
