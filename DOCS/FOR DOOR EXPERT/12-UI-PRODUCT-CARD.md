
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


