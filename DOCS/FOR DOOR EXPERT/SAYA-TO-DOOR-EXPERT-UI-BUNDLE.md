# Saya Group → Door Expert — UI and presentation patterns (bundle 2 of 2)

Second of two bundles. The first, `SAYA-TO-DOOR-EXPERT-BUNDLE.md`, covers architecture and logic:
quote cart, variation matching, lightbox, m² calculator. **This one covers how things look** and the
small data models behind the look.

The two are deliberately separate. Bundle 1 is what makes the site work. Bundle 2 is what makes it
read as a salon rather than a catalogue. Read bundle 1 first.

**To the agent receiving this:** each document is delimited by a
`<!-- ===== FILE: <name> ===== -->` marker. Split back into files under `DOCS/FOR DOOR EXPERT/`
in the Door Expert repo, or read straight through in order.

Source site: Saya Group (ceramic tiles and bathroom fixtures, Serbia), custom WordPress theme.
Target site: Door Expert (doors, Spanish tiles, decorative basins; Podgorica, Montenegro).

The audit was read-only. No file on the source site was modified. Snippets are syntax-checked but
have never run inside Door Expert.

---



<!-- ===== FILE: 10-UI-README.md ===== -->

# For Door Expert — UI and presentation patterns (package 2 of 2)

The first package (`00` through `05`, bundled as `SAYA-TO-DOOR-EXPERT-BUNDLE.md`) covers
**architecture and logic**: the quote cart, variation matching, the lightbox, the m² calculator.

This second package covers **how things look and the small data models behind them**. It was added
after the first was delivered, because the first audit followed the brief's five targets and the
brief did not ask about presentation. Both packages are read-only analysis of the same Saya Group
site; neither modified it.

Keep the two apart. Package 1 is what makes the site work. Package 2 is what makes it look like a
salon rather than a catalogue.

---

## Contents

| File | What it covers |
|---|---|
| [`11-UI-SWATCHES.md`](11-UI-SWATCHES.md) | **Start here.** Colour and texture swatches: using the product photo itself as the swatch, the three-tier fallback chain, the unavailable-combination hatch, the site-wide colour map. Full adapted PHP and CSS. |
| [`12-UI-PRODUCT-CARD.md`](12-UI-PRODUCT-CARD.md) | Ambient-first card: product shown in a room with a solo strip underneath, the square-crop `srcset` trap that comes with it, badges, per-colour image swap on the filtered archive. |
| [`13-UI-PDP-AND-PROJECTS.md`](13-UI-PDP-AND-PROJECTS.md) | Trust and delivery block with per-product override, per-variation "goes well with", selection confirmation strip, clickable hotspots on finished-project photos. |

## The short version

**The one thing to take:** a tile is a texture and a door is a wood grain, so a flat colour circle
communicates nothing. Saya renders the variation's own photo as a 36px circular swatch, and falls
back through term colour meta to a deterministic hash so the UI is never blank. That is `11`.

**The second thing:** show the product in a room. A tile photographed flat is a grey rectangle in a
grid of grey rectangles. That is `12`.

**The trap that comes with the second thing:** a landscape photo inside a square `object-fit: cover`
frame renders blurry on desktop and sharp on phones, because `srcset` chooses by width while the
square crop is decided by height. If Door Expert uses square product frames, this will bite. `12` §2
explains the fix and why the obvious fix does not work.

## Effort ranking

| Pattern | Effort | Payoff |
|---|---|---|
| Texture swatches (`11`) | low | high, on every product page |
| Trust and delivery block (`13` §1) | low | high, removes phone questions |
| Confirmation strip (`13` §3) | low | mobile only, but real |
| Ambient-first card (`12`) | medium | high, changes how the catalogue reads |
| Per-colour image swap on archive (`12` §4) | medium | filtering looks broken without it |
| "Goes well with" (`13` §2) | high, needs admin UI | good, if the client will curate |
| Project hotspots (`13` §4) | high, needs admin UI | distinctive, not a first-month feature |

## Same caveats as package 1

- Every `file:line` was verified against the Saya working tree when written; re-check with `grep -n`
  before trusting an exact number.
- PHP and CSS snippets are syntax-checked but **have never run inside Door Expert**. Reviewed drafts,
  not tested code.
- Where Saya's original is weak, these documents say so rather than transcribing it. Two called out:
  the swatches never set `aria-pressed`, and the trust helpers escape too early to be reusable in
  attributes.


<!-- ===== FILE: 11-UI-SWATCHES.md ===== -->

# UI 11 — Colour and texture swatches

**Verdict: `ADAPT (light)` · The single most transferable UI pattern in this repo**

A tile is a texture, not a colour. A door is a wood grain, not a colour. Printing a flat hex circle
for "Rovere Naturale" tells the customer nothing. Saya solves this with a three-tier swatch that
falls back gracefully, and the top tier is **the product photo itself cropped into a circle**.

This is the pattern you asked about, and it is the one worth copying verbatim.

---

## 1. How it works

Three tiers, decided per option at render time:

| Tier | When | What renders |
|---|---|---|
| **1. Image swatch** | The variation for this option has its own image | A 36px circle filled with that variation's photo, `object-fit: cover`. For tiles this is the actual texture; for doors it would be the actual veneer. |
| **2. Hex swatch** | No variation image, but the term has a colour | A 36px circle filled from `color_code` term meta, falling back to the term description. |
| **3. Deterministic fallback** | Neither of the above | `'#' . substr( md5( $option ), 0, 6 )`. Not pretty, but **stable**: the same name always yields the same colour, so the UI never shows a grey blank and never flickers between page loads. |
| **4. Plain pill** | The attribute is not a colour attribute at all | Text button. |

The fallback chain is the part that makes this robust in production. Clients forget to set
`color_code`. With tier 3 the page still looks deliberate.

## 2. Saya source

| Piece | Location |
|---|---|
| Image map built from variations | `wp-theme/woocommerce/single-product.php:535-546` |
| Swatch rendering, all three tiers | `wp-theme/woocommerce/single-product.php:740-780` |
| Hex swatch CSS | `wp-theme/css/product-single.css:1408-1445` |
| Image swatch CSS | `wp-theme/css/product-single.css:1446-1478` |
| Unavailable state (diagonal hatch) | `wp-theme/css/product-single.css:1219-1240` |
| Site-wide colour map, slug → hex | `wp-theme/functions.php:2553-2577` (`saya_boja_color_map()`) |
| Same pattern in collection filters | `wp-theme/js/kolekcije-single.js` (colour dropdown, image-or-hex dot) |

## 3. Dependencies

None beyond WooCommerce. No jQuery, no library, no build step. The image map is derived from
`get_available_variations()`, which you are already calling for the variation selector in
`03-PORT-variations.md`.

## 4. Adapted code

### Building the image map

Put this next to where you already call `get_available_variations()`, so it costs nothing extra.

```php
<?php
/**
 * Mapa slika po opciji atributa: $images['attribute_pa_boja']['hrast'] => URL.
 *
 * Gradi se iz varijacija, pa uzorak pokazuje stvarnu teksturu proizvoda
 * umjesto ravne boje. Za pločice je to sam dezen, za vrata furnir.
 *
 * @param array $available_variations Rezultat get_available_variations().
 * @return array
 */
function door_expert_variation_swatch_images( $available_variations ) {
	$images = array();

	foreach ( $available_variations as $variation ) {
		foreach ( $variation['attributes'] as $key => $value ) {
			if ( ! $value ) {
				continue;
			}

			$image_id  = ! empty( $variation['image_id'] ) ? (int) $variation['image_id'] : 0;
			$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';

			if ( ! $image_url ) {
				$image_url = $variation['image']['src'] ?? '';
			}

			if ( $image_url ) {
				$images[ $key ][ $value ] = $image_url;
			}
		}
	}

	return $images;
}
```

### Resolving a colour to a hex value

```php
<?php
/**
 * Boja termina kao hex.
 *
 * Redoslijed: color_code meta → opis termina → determinisan fallback iz naziva.
 * Fallback nije lijep, ali je stabilan: isti naziv uvijek daje istu boju, pa
 * uzorak nikad nije prazan i ne mijenja se izmedju učitavanja.
 *
 * @param WP_Term|null $term Termin atributa.
 * @param string       $name Naziv opcije, za fallback.
 * @return string Hex u obliku #rrggbb.
 */
function door_expert_swatch_hex( $term, $name ) {
	$hex = '';

	if ( $term && ! is_wp_error( $term ) ) {
		$hex = (string) get_term_meta( $term->term_id, 'color_code', true );

		if ( ! $hex && $term->description ) {
			$hex = trim( $term->description );
		}
	}

	if ( ! preg_match( '/^#[0-9a-fA-F]{3,6}$/', $hex ) ) {
		$hex = '#' . substr( md5( $name ), 0, 6 );
	}

	return $hex;
}
```

### Rendering one option

```php
<?php
/**
 * Jedna opcija atributa. Bira uzorak sa slikom, uzorak sa bojom ili obično dugme.
 *
 * @param string $attr_key   Ključ atributa, npr. attribute_pa_boja.
 * @param string $taxonomy   Taksonomija, npr. pa_boja.
 * @param string $option     Slug ili naziv opcije.
 * @param bool   $is_colour  Da li je atribut vizuelni.
 * @param array  $images     Mapa iz door_expert_variation_swatch_images().
 */
function door_expert_render_variation_option( $attr_key, $taxonomy, $option, $is_colour, $images ) {
	$common = sprintf(
		'data-attr-key="%s" data-value="%s" aria-label="%s" title="%s"',
		esc_attr( $attr_key ),
		esc_attr( $option ),
		esc_attr( $option ),
		esc_attr( $option )
	);

	if ( ! $is_colour ) {
		printf(
			'<button type="button" class="variation-opt" %s>%s</button>',
			$common, // phpcs:ignore WordPress.Security.EscapeOutput -- već escapovano iznad.
			esc_html( $option )
		);
		return;
	}

	$image_url = $images[ $attr_key ][ $option ] ?? '';

	if ( $image_url ) {
		printf(
			'<button type="button" class="variation-opt variation-opt--img-swatch" %s><img src="%s" alt="%s" draggable="false"></button>',
			$common, // phpcs:ignore WordPress.Security.EscapeOutput -- već escapovano iznad.
			esc_url( $image_url ),
			esc_attr( $option )
		);
		return;
	}

	$term = get_term_by( 'name', $option, $taxonomy );
	$hex  = door_expert_swatch_hex( $term, $option );

	printf(
		'<button type="button" class="variation-opt variation-opt--swatch" %s style="--swatch-color:%s"></button>',
		$common, // phpcs:ignore WordPress.Security.EscapeOutput -- već escapovano iznad.
		esc_attr( $hex )
	);
}
```

### Site-wide colour map

Useful on the archive and brand pages where you have a term slug but no variation image. Adapted to
Montenegrin spellings, and worth extending as the catalogue grows.

```php
<?php
/**
 * Slug boje → hex. Jedino mjesto, koristi se na arhivi i stranici brenda.
 *
 * @return array
 */
function door_expert_colour_map() {
	return array(
		'bijela'      => '#FFFFFF',
		'bez'         => '#F5F0E8',
		'siva'        => '#B0B0B0',
		'crna'        => '#1A1A1A',
		'braon'       => '#8B6347',
		'hrast'       => '#C19A6B',
		'orah'        => '#6B4423',
		'trešnja'     => '#8B3A2E',
		'plava'       => '#4682B4',
		'tirkiz'      => '#009688',
		'zelena'      => '#3A9E3A',
		'crvena'      => '#DC143C',
		'žuta'        => '#FFD700',
		'narandžasta' => '#FF8C00',
		'roze'        => '#FFB6C1',
		'ljubičasta'  => '#9370DB',
		'antracit'    => '#3C3C3C',
		'srebrna'     => '#E0E0E0',
		'zlatna'      => '#D4AF37',
		'bordo'       => '#4A0E0E',
	);
}
```

### CSS

Mobile-first, no media queries needed — the swatch is the same size everywhere. Swap
`--de-accent` for your own accent token.

```css
/* ── Uzorak sa bojom ── */
.variation-opt--swatch {
	width: 36px;
	height: 36px;
	padding: 0;
	border-radius: 50%;
	background-color: var(--swatch-color, #ccc);
	border: 2px solid var(--border, #e0e0e0);
	position: relative;
	overflow: hidden;
	flex-shrink: 0;
	cursor: pointer;
	transition: border-color 0.2s, transform 0.15s;
}

/* Unutrašnji krug ostavlja tanak prsten podloge, pa se bijeli uzorak vidi */
.variation-opt--swatch::after {
	content: '';
	position: absolute;
	inset: 2px;
	border-radius: 50%;
	background-color: var(--swatch-color, #ccc);
}

.variation-opt--swatch:hover,
.variation-opt--img-swatch:hover {
	border-color: var(--de-accent, #b8860b);
	transform: scale( 1.1 );
}

.variation-opt--swatch.active,
.variation-opt--img-swatch.active {
	border-color: var(--de-accent, #b8860b);
	border-width: 2.5px;
	box-shadow: 0 0 0 3px rgba( 184, 134, 11, 0.18 );
}

/* ── Uzorak sa slikom, tekstura proizvoda ── */
.variation-opt--img-swatch {
	width: 36px;
	height: 36px;
	padding: 0;
	border-radius: 50%;
	background: #fff;
	border: 2px solid var(--border, #e0e0e0);
	flex-shrink: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
	cursor: pointer;
	transition: border-color 0.2s, transform 0.15s, box-shadow 0.2s;
}

.variation-opt--img-swatch img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	pointer-events: none;
}

/* ── Nedostupna kombinacija ──
   Namjerno ostaje klikabilno: klik na sivu opciju je legitiman način da
   korisnik promijeni pravac izbora, umjesto da traži gdje da poništi. */
.variation-opt--swatch.is-unavailable,
.variation-opt--img-swatch.is-unavailable {
	opacity: 0.3;
	cursor: pointer;
}

.variation-opt--swatch.is-unavailable::before,
.variation-opt--img-swatch.is-unavailable::before {
	content: '';
	position: absolute;
	inset: 0;
	z-index: 1;
	background: repeating-linear-gradient(
		-45deg,
		transparent,
		transparent 4px,
		rgba( 0, 0, 0, 0.25 ) 4px,
		rgba( 0, 0, 0, 0.25 ) 5px
	);
}

.variation-opt--img-swatch.is-unavailable { position: relative; }
```

## 5. Accessibility notes, and one thing Saya gets wrong

- Every swatch carries `aria-label` and `title` with the option name. Without it a colour circle is
  invisible to a screen reader and unlabelled on hover. **Keep this.**
- The row wrapper carries `role="group"` with `aria-label` set to the attribute label.
- **What Saya does not do:** the swatch buttons never set `aria-pressed`. A screen reader user can
  hear the name but not which one is selected. Fix on the way over:

```js
btn.setAttribute( 'aria-pressed', btn.classList.contains( 'active' ) ? 'true' : 'false' );
```

  Set it wherever you toggle the `active` class in `assets/js/variations.js`.
- **Also worth fixing:** a white tile on a white page needs the border to carry the shape. The
  `::after` inner circle plus a 2px border handles this; do not flatten it to a single background.

## 6. Where else the same pattern appears

The collection page filters build the same image-or-hex dot in JS rather than PHP
(`wp-theme/js/kolekcije-single.js`, colour dropdown):

```js
var dotStyle = b.img
	? 'background-image:url(' + b.img + ');background-size:cover;background-position:center'
	: 'background:' + b.hex;
```

If you build filter swatches on the archive, mirror this so the same colour reads identically in
both places. A colour that looks different on the listing and the product page reads as a bug.

## 7. Verify after dropping it in

- A variation **with** an image renders the photo in the circle, not a colour.
- A variation **without** an image but with `color_code` set renders that colour.
- A term with neither renders a stable colour that does not change on reload.
- A non-colour attribute renders text pills, not circles.
- Picking a combination that excludes an option: that swatch dims to 30% and gets the diagonal
  hatch, and is **still clickable**.
- Hover a swatch: the option name appears as a native tooltip.
- Tab to a swatch with a screen reader: it announces the colour name and, after your fix, whether
  it is pressed.


<!-- ===== FILE: 12-UI-PRODUCT-CARD.md ===== -->

# UI 12 — Product card: ambient-first, solo strip, badges

**Verdict: `ADAPT (light)` as a pattern, `ADAPT (heavy)` as code**

Door Expert already has `template-parts/shop/product-card.php`, so this is not a replacement. It is
the set of decisions Saya's card makes that are worth stealing, with the reasoning behind each.

The headline decision: **the card shows the product in a room, not on white.** A tile photographed
flat is a grey rectangle in a grid of grey rectangles. The same tile on a bathroom wall sells. Doors
have exactly the same problem.

---

## 1. The ambient-first card

The card picks its main image in this order (`template-parts/product-card.php:115-174`):

1. **Ambient image** — the first image in the WooCommerce gallery, treated as "product in a room".
2. For simple products with no gallery ambient, a **slug convention**: `<product-slug>-ambient`.
3. Falling back to the plain product photo.

When an ambient exists, the card renders it `object-fit: cover` in a square frame, and adds a **solo
strip** underneath: a small white band with the bare product shot at `object-fit: contain`. So the
customer sees both the room and the actual tile, without a hover they might never trigger.

When no ambient exists, the product photo renders `contain` instead of `cover`, so a tall or wide
product is never cropped through.

```php
$display_url     = $ambient_url ? $ambient_url : ( $image_url_solo ? $image_url_solo : $image_url );
$display_contain = ! $ambient_url;               // bez ambijenta: contain, ne cover
$solo_url        = $ambient_url ? $image_url_solo : null;  // traka se crta samo uz ambijent
```

**Why a strip and not a hover swap:** a hover-only reveal does not exist on touch, which is most of
the traffic. The strip is always visible.

## 2. The image-size trap that came with it

This is the one bug in the card worth knowing about before you copy the pattern, documented fully in
`DOCS/BITNE FUNKCIONALNOSTI/AMBIJENT_SLIKE_U_GRIDU.md`.

A landscape ambient photo in a square `object-fit: cover` frame renders **blurry on desktop**, sharp
on phones. The reason: `srcset` picks a candidate by **width**, but in a square crop the **height**
decides how much the image is scaled. A 958px-wide landscape photo is a fine 958px-wide candidate
and a badly stretched 958px-tall one. Phones hide it behind high DPR.

The fix is to serve an **already square crop** rather than `large`, and to pick the largest crop that
actually exists for that specific image:

```php
$display_size = $display_contain ? 'large' : door_expert_ambient_image_size( $display_id );
```

A fixed `door_expert_ambient_1200` does not work, because for an original narrower than 1200px that
crop is never generated, WordPress falls back to the original, and `srcset` rebuilds from the
non-square ratio — the exact bug again.

**If Door Expert renders product images in square frames from non-square sources, you will hit this.**
Register two or three square `add_image_size()` crops, write the "largest crop that exists" helper,
and remember that new sizes are only generated on upload, so existing media needs a regeneration
pass.

## 3. Badges

`template-parts/product-card.php:339-347`. Three mutually exclusive badges driven by a `$tab`
argument passed into the partial:

| Badge | Condition |
|---|---|
| `product-badge--akcija` | on sale, shows the computed percentage |
| `product-badge--bestseller` | curated tab |
| `product-badge--novo` | recently published |

The discount percentage is computed, not stored:

```php
$discount = '';
if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
	$discount = '-' . round( ( ( $regular - $sale ) / $regular ) * 100 ) . '%';
}
```

For a **variable** product, Saya first looks for variations that have a real discount
(`sale > 0 AND sale < regular`) rather than trusting `is_on_sale()`, which returns true for edge
cases that produce a `-0%` badge. Worth carrying:

```php
// Ne vjeruj is_on_sale() na varijabilnom proizvodu; nadji varijaciju sa stvarnim popustom.
foreach ( $product->get_children() as $child_id ) {
	$child   = wc_get_product( $child_id );
	$regular = (float) $child->get_regular_price();
	$sale    = (float) $child->get_sale_price();

	if ( 0 < $sale && $sale < $regular ) {
		// ovo je varijacija čiju cijenu i popust treba prikazati na kartici
		break;
	}
}
```

## 4. Variation image swap on the listing

`template-parts/product-card.php:231-260`. The card emits three parallel maps keyed by colour slug:

| Map | Purpose |
|---|---|
| `$var_images` | square ambient crop, for the main frame |
| `$var_images_solo` | uncropped `medium`, for the small corner thumb |
| `$var_images_solo_lg` | uncropped `large`, for the main frame when JS swaps to a solo view |

When the shopper filters the archive by colour, the card swaps to that colour's image instead of
showing the parent's default. Without it, filtering by "hrast" leaves a grid of cards all showing the
white variant, which reads as broken filtering.

There is a fourth map, `$var_naziv_boje_map`, so the card's link can pre-select the right variation
on the PDP. That is the generic permalink helper covered in `01-AUDIT-REPORT.md` §17.

## 5. What not to copy

- The card is **481 lines**. Most of that is Saya's brand SKU rules, collection meta, wishlist state
  and the ambient slug detection. Take the four decisions above, not the file.
- The `$tab` argument is a smell: the partial branches on presentation context passed by the caller.
  If you rebuild this, pass the badge you want rather than a mode name.

## 6. Verify after dropping it in

- A product with a gallery ambient shows the room photo `cover`, with the bare product in the strip
  below.
- A product with **no** ambient shows the product `contain`, uncropped.
- Zoom the browser to 100% on a wide desktop and compare a landscape ambient against the same image
  opened directly. If the card version is visibly softer, the square-crop fix is not in place.
- Filter the archive by a colour: cards must show that colour's image, and clicking through must
  land on the PDP with that variation preselected.
- A variable product on sale in only one variation shows a real percentage, never `-0%`.


<!-- ===== FILE: 13-UI-PDP-AND-PROJECTS.md ===== -->

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
