# Variable products — parity checklist for the pill bridge you already built

**This document replaces an earlier version of `03` and contradicts it on purpose.** The earlier one
was written blind, before the Door Expert repo was available. It assumed you would build a fully
custom variation selector with its own state and its own AJAX add-to-cart endpoint, the way Saya did.
You built something different and, in most respects, better: the pills are a visual layer over real
WooCommerce `<select>` elements, and WooCommerce itself does the matching.

**Keep that architecture.** Everything below sits on top of it. Nothing here asks you to replace
`variations_form`, and §2 explains why the old document's headline argument does not apply to your
code at all.

Like [`08-PARITY-faceting-seo-ajax.md`](08-PARITY-faceting-seo-ajax.md), this was written **with your
repo in hand**. Every function name, id, CSS class and hook below was read out of
`wp-content/themes/door-expert/` as it stands on 2026-09-26, not guessed.

**You cannot read the Saya repo, so nothing here points at it for substance.** Where Saya's code is
carried over, it is reproduced in full and rewritten against your DOM. The one thing you will not
find below is a line-number reference you are expected to go and look up.

---

## 1. Where you actually are

| File | What it does today |
|---|---|
| `template-parts/product/single.php:249-282` | Renders `.variations_form` with `data-product_variations`, one `.product-variants` row per variation attribute, each holding a real `wc_dropdown_variation_attribute_options()` select plus an empty `.product-variants__pills` container |
| `template-parts/product/single.php:321-328` | `wp.template()` markup templates WC needs, because you do not route through `woocommerce_variable_add_to_cart()` |
| `assets/js/product.js:164-438` | The bridge: builds pills from `select.options`, clicks write to the select and trigger jQuery `change`, WC does the rest |
| `inc/product-variations.php` | Orderability and availability text. **Not** what the old `03` meant by that filename |
| `inc/product.php:66-91` | `door_expert_stock_display()`, one source of truth shared by PHP and JS through `wp_localize_script` |
| `functions.php:210-236` | Enqueues `wc-add-to-cart-variation` as a **dependency** of `product.js`, so WC initialises first |

This is a good design and the comments in it are better than Saya's. Six things are missing, and
three of them are real bugs rather than polish.

| Behaviour | Saya | Door Expert | Verdict |
|---|---|---|---|
| Matching engine owner | hand-written JS | WooCommerce | **Yours is better. Do not change it.** |
| Impossible option is greyed, not removed | ✅ | ❌ **it disappears** | §3 — fix first |
| Works above 30 variations | ✅ | ❌ **silently switches off** | §4 — tiles hit this |
| Shopper can change an earlier pick | ✅ | ❌ dead end | §5 |
| Mobile sticky CTA adds to cart | ✅ | ❌ **does nothing on variable** | §6 |
| CTA reflects "no price yet" | ✅ | ❌ always promises a price | §7 |
| `aria-pressed` on the selected option | ❌ | ✅ | **you are ahead** |
| Per-row "selected" readout | strip at the bottom | ✅ per row | **you are ahead**, see §8.5 |

## 2. Correction: you do not need a custom add-to-cart handler

The old `03` opened with this, and it was the stated reason to port at all:

> The add-to-cart handler alone justifies the port. WooCommerce's own AJAX endpoint **cannot** add a
> variation that has an "Any" attribute from a custom UI; it throws before any filter can intervene.

That is true of the path Saya uses and **false for the path you use.** The trap lives in
`?wc-ajax=add_to_cart`, which loads the variation's own stored attributes and throws
`"<Attribute> is a required field"` when an "Any" attribute comes back as an empty string. Your PDP
does not go anywhere near it: `.product-cta-form` is a real `method="post"` form, so submission lands
in `WC_Form_Handler::add_to_cart_action()` → `add_to_cart_handler_variable()`, which reads
`$_REQUEST['attribute_*']` from your selects and only complains when a value is **absent**. Your
selects always post a value once a pill is active.

So: **do not add `door_expert_add_to_cart_ajax()`. Delete it from your notes.** The old document's
`inc/product-variations.php` snippet, its `.variation-row` markup contract and its
`assets/js/variations.js` are all superseded, and the filename it wanted is already taken by
something unrelated.

Two smaller things the old document proposed that you should also skip:

- **`door_expert_hidden_pdp_attributes()`**, a list of variation attributes not offered as a choice.
  Your template already achieves this structurally: variation attributes become pills, everything
  else goes to Specifikacije. A second mechanism would be a second source of truth.
- **`get_available_variations()` emitted as `<script type="application/json">`.** You already emit it
  as `data-product_variations` where WC expects it. §4 adds a *different*, much smaller payload for a
  different reason.

What follows is what Saya's engine genuinely does better, expressed as additions to your files.

---

## 3. Gap 1 — an impossible option vanishes instead of greying out

**Effort: half a day. Highest value of the six. Do this first.**

### 3.1 What happens today

`assets/js/product.js:305-320` builds pills by walking `select.options` and greys a pill when
`opt.disabled` is true:

```js
if ( opt.disabled ) {
  pill.disabled = true;
  pill.classList.add( 'is-disabled' );
  pill.title = 'Nedostupno uz trenutni izbor';
}
```

WooCommerce never sets `disabled` on those options. In `update_variation_values` it rebuilds each
select from a pristine snapshot, tags the options that are reachable given the *other* current
selections, and then throws the rest away:

```js
// Detach unattached.
new_attr_select.find( 'option:not(.attached)' ).remove();
...
// Detach fully disabled options.
new_attr_select.find( 'option.attached:not(.enabled)' ).remove();
```

So that branch is dead code, `.product-variant-pill.is-disabled` in `assets/css/product.css:572-580`
has never rendered, and the actual behaviour is that the pill **disappears**.

**Confirm in 30 seconds.** Open a variable door with two attributes, pick a size, and count the pills
in the colour row before and after. If the count drops, this is your site.

### 3.2 Why it matters commercially

Three separate problems, all from the same cause:

1. **The row reflows under the cursor.** The shopper moves toward a pill and it moves. This is the
   exact behaviour [`08`](08-PARITY-faceting-seo-ajax.md) §5 tells you not to accept on filters
   ("Do not hide zero-result options. Grey them."), and the reasoning is identical here.
2. **The catalogue looks smaller than it is.** A door that exists in walnut but not in walnut at
   90×200 shows no walnut pill once 90×200 is picked. The shopper concludes you do not sell walnut
   doors and leaves. Greyed-and-struck-through says "we have it, not in this size", which is a
   conversation, not a dead end.
3. **It sets up §5.** You cannot click an option that is not in the DOM, so the shopper can never
   change their mind about the first attribute they picked.

### 3.3 The fix in one sentence

Stop building pills from `select.options`. Render the **full** option list server side, compute
availability from your own variation map, and keep the select purely as the selection store and POST
carrier.

Three additions: the option list (§3.4), the map (§4.2), and the rewritten bridge (§9).

### 3.4 The full option list, per row

Add to the end of `inc/product-variations.php`. It mirrors what
`wc_dropdown_variation_attribute_options()` does internally, including term ordering, so the pills
and the select can never disagree about labels or order.

```php
/**
 * Puna lista opcija jednog varijacijskog atributa, u poretku iz admina.
 *
 * Isti podatak koji wc_dropdown_variation_attribute_options() stavlja u <select>,
 * ali kao niz: pilule ga citaju iz data-options i time prestaju da zavise od
 * select.options, iz kojeg WC BRISE nemoguce opcije.
 *
 * @param WC_Product $product        Roditeljski proizvod.
 * @param string     $attribute_name Naziv atributa (npr. `pa_boja`).
 * @param array      $options        Vrijednosti iz get_variation_attributes().
 * @return array<int,array{value:string,label:string}>
 */
function door_expert_variation_option_list( $product, $attribute_name, $options ) {
	$list = array();

	if ( taxonomy_exists( $attribute_name ) ) {
		// wc_get_product_terms drzi poredak iz admina, isti kao u <select>-u.
		$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );

		foreach ( $terms as $term ) {
			if ( ! in_array( $term->slug, $options, true ) ) {
				continue;
			}

			$list[] = array(
				'value' => $term->slug,
				'label' => apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ),
			);
		}

		return $list;
	}

	foreach ( $options as $option ) {
		$list[] = array(
			'value' => $option,
			'label' => apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ),
		);
	}

	return $list;
}
```

Then in `template-parts/product/single.php`, extend the row wrapper at line 259:

```php
<div class="product-variants"
	data-attribute="attribute_<?php echo esc_attr( $de_attr_id ); ?>"
	data-options="<?php echo esc_attr( wp_json_encode( door_expert_variation_option_list( $de_product, $de_attr_name, $de_attr_options ) ) ); ?>">
```

Your three variation attributes (`pa_boja`, `pa_dimenzije-vrata`, `pa_dimenzije-plocica`) are all
global taxonomies, so the first branch is the one that runs. The second exists for per-product custom
attributes; if you ever add one, be aware that WooCommerce compares custom attribute values through
`sanitize_title()` in some paths and raw in others, so prefer global attributes.

### 3.5 CSS: a greyed pill is still clickable

Replace `assets/css/product.css:571-580`. The change is `cursor`, and it is not cosmetic: the pill
now does something when clicked (§5), and `not-allowed` on a working button is worse than no signal
at all.

```css
/*
 * Kombinacija nije moguca uz trenutni izbor. Siva i precrtana, ali KLIKABILNA:
 * klik oslobadja izbor koji je blokira (kaskada, vidi product.js). Zato pointer,
 * ne not-allowed.
 */
.product-variant-pill.is-disabled {
  opacity: 0.35;
  cursor: pointer;
  text-decoration: line-through;
}

.product-variant-pill.is-disabled:hover {
  opacity: 0.6;
  border-color: var(--color-sand, #DDD5C4);
}
```

The new bridge also stops setting the `disabled` property and does not set `aria-disabled`, because
the button is genuinely not disabled. Saya sets `aria-disabled="true"` on its unavailable options
while leaving them clickable, which is a contradiction a screen reader user has to work around. Do
not carry that over.

---

## 4. Gap 2 — above 30 variations the whole enhancement switches itself off

**Effort: an hour once §3 is in. This is the one that will bite the tiles catalogue.**

### 4.1 What happens today

`template-parts/product/single.php:93-96` respects WooCommerce's AJAX threshold, correctly:

```php
$de_var_threshold = apply_filters( 'woocommerce_ajax_variation_threshold', 30, $de_product );
$de_var_json      = count( $de_product->get_children() ) <= $de_var_threshold
	? $de_product->get_available_variations()
	: false;
```

Above the threshold the attribute becomes `data-product_variations="false"`, and inside
`wc-add-to-cart-variation.js` that flips one switch:

```js
this.useAjax = false === this.variationData;
```

`onUpdateAttributes` then returns immediately:

```js
if ( form.useAjax ) {
	return;
}
```

Two consequences, both silent:

- **No option filtering at all.** Every option in every row stays present and enabled, whether or not
  a matching variation exists. The shopper picks a combination, waits for an AJAX round trip, and is
  told "Ova kombinacija trenutno nije dostupna." Trial and error, one network request per attempt.
- **`woocommerce_update_variation_values` never fires.** That is the only event your bridge listens
  to (`product.js:368`), so `syncPills()` runs once at page load and then never again. Pills keep
  whatever state they were born with.

**Confirm in 30 seconds.** `view-source` a product with more than 30 variations and search for
`data-product_variations="false"`.

### 4.2 Why tiles cross the threshold and doors do not

A door is two attributes: three sizes times two or three finishes, comfortably under 30. A ceramic
collection is a colour range times a format range, and 6 × 6 = 36. Ribesalbes and Tau collections
routinely go past that. **So this gap does not exist on the product type you built the PDP against,
and appears the day the tiles go in.** That is the worst shape a bug can have.

The cheap fix is to raise the threshold:

```php
add_filter( 'woocommerce_ajax_variation_threshold', function () { return 200; } );
```

Do not do that as the primary fix. `get_available_variations()` is fat: per variation it carries
`price_html`, `availability_html`, a full `image` object with `srcset` and `sizes`, plus every
dimension and weight field. For 60 variations that is comfortably over 100 KB of JSON in the HTML,
on a page whose LCP is a product photo.

Emit a small map instead, sized for exactly what the pills need. Add to `inc/product-variations.php`:

```php
/**
 * Kompaktna mapa varijacija za sloj pilula.
 *
 * WC svoju mapu (data-product_variations) izostavlja iznad 30 varijacija i tada
 * prestaje da filtrira opcije. Kolekcija plocica sa 6 boja i 6 formata je vec
 * preko tog praga, pa pilule moraju imati svoj izvor podataka. Ovdje je samo ono
 * sto pilulama treba: sto manje bajtova, nikad izostavljeno.
 *
 * Prazan string u `attrs` je WC-ov dzoker ("Bilo koja vrijednost"), ne vrijednost.
 *
 * @param WC_Product $product Roditeljski proizvod.
 * @return array<int,array{id:int,attrs:array<string,string>,price:float,stock:string}>
 */
function door_expert_variation_map( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
		return array();
	}

	$map = array();

	foreach ( $product->get_children() as $child_id ) {
		$variation = wc_get_product( $child_id );

		if ( ! $variation instanceof WC_Product_Variation ) {
			continue;
		}

		/*
		 * Namjerno NE koristimo variation_is_visible(): ona je false i kad je cijena
		 * prazna, pa bi dimenzija kojoj cijena jos nije unesena nestala sa PDP-a.
		 * U quote modelu "cijena na upit" je validno stanje. Nepublikovana varijacija
		 * je druga stvar i tu WC ima pravo.
		 */
		if ( 'publish' !== get_post_status( $child_id ) ) {
			continue;
		}

		$map[] = array(
			'id'    => $variation->get_id(),
			'attrs' => $variation->get_variation_attributes(),
			'price' => (float) $variation->get_price(),
			'stock' => $variation->get_stock_status(),
		);
	}

	return $map;
}
```

Emit it once inside the form in `template-parts/product/single.php`, right after the closing `</div>`
of `.variations`:

```php
<?php
/*
 * Inertni podatak, ne asset: sloj pilula racuna dostupnost iz njega kad WC svoju
 * mapu izostavi (iznad 30 varijacija). Isti razlog kao za wp.template() sablone
 * iznad, pa i isto svjesno odstupanje od CLAUDE.md sekcije 4.
 */
?>
<script type="application/json" id="door-expert-variation-map"><?php echo wp_json_encode( door_expert_variation_map( $de_product ), JSON_HEX_TAG ); ?></script>
```

`JSON_HEX_TAG` is there so a `<` that ever reaches a term slug cannot close the script tag early.

`get_variation_attributes()` on a variation returns keys already prefixed (`attribute_pa_boja`), and
an **empty string for an "Any" attribute**. Every comparison in §9 treats that empty string as a
wildcard, which is the single detail Saya's engine gets right and naive matching gets wrong.

### 4.3 What still works above the threshold, so you do not rebuild it

Worth knowing, because it is not obvious: in AJAX mode WC fetches the chosen variation through
`WC_AJAX::get_variation()`, which returns `$product->get_available_variation( $variation_id )` and so
still runs the `woocommerce_available_variation` filter. Your
`door_expert_variation_stock_data()` in `inc/product-variations.php:112-117` therefore keeps
injecting `door_expert_stock_status`, and `show_variation` still arrives with `price_html`, `image`
and `display_price` intact.

**So price, stock, image and quantity clamping already work in both modes.** Only option filtering
and the `woocommerce_update_variation_values` event are lost, and §9 stops depending on both by
listening to the selects' own `change` event instead.

### 4.4 Two WooCommerce settings that will delete pills behind your back

Both interact with variable products in a quote model, and both are easy to get wrong once and never
notice.

**A variation with a blank price is hidden by WooCommerce.** `variation_is_visible()` requires
`'' !== $this->get_price()`, and `get_available_variations()` drops anything it hides. Note this is
about a **blank** price, not a zero: `0` passes, `''` does not. Your
`woocommerce_is_purchasable` filter in `inc/quote-cart.php:92-102` already treats both as
purchasable, so the two halves disagree, and the symptom is the worst kind: the pill renders from
`data-options`, the shopper clicks it, and the button never enables.

The working rule for admin is "type 0, never leave it blank". Belt and braces, so a blank price is
never fatal:

```php
/**
 * Varijacija bez cijene ostaje u ponudi.
 *
 * WC je krije jer variation_is_visible() trazi da cijena nije prazna, pa bi
 * dimenzija bez unesene cijene ispala i iz get_available_variations(). Tada je
 * pilula tu, a dugme se ne otvara - najgori moguci ishod.
 *
 * Cijena 0 i PRAZNA cijena nisu isto: sa 0 WC svejedno prikazuje varijaciju i ovaj
 * filter nije potreban. Radno pravilo za admin ostaje "upisi 0, ne ostavljaj prazno".
 *
 * @param bool $visible      Zatecena vrijednost.
 * @param int  $variation_id ID varijacije.
 * @return bool
 */
function door_expert_variation_visible_without_price( $visible, $variation_id ) {
	if ( $visible ) {
		return $visible;
	}

	return 'publish' === get_post_status( $variation_id );
}
add_filter( 'woocommerce_variation_is_visible', 'door_expert_variation_visible_without_price', 10, 2 );
```

Filter `woocommerce_variation_is_visible`, not `woocommerce_hide_invisible_variations`. The latter
looks like the obvious switch and also un-hides **unpublished** variations, which is not what you
want.

**"Hide out of stock items from the catalog" removes out-of-stock variations too.** In
WooCommerce → Settings → Products → Inventory. Leave it **off**. Your
`inc/product-variations.php` already argues the case for doors ("zaliha je informacija, ne
prepreka"), and that argument holds for tiles as well. With it on, an out-of-stock format silently
leaves `get_available_variations()` and the shopper cannot ask about it.

---

## 5. Gap 3 — the shopper cannot change their mind

**Effort: included in §9. This is the behaviour worth taking from Saya.**

With §3 in place, every option is always on screen. The question is what a click on a greyed one
should do. WooCommerce has no answer, because in its model that option does not exist.

Saya's answer, and it is the right one for a salon catalogue: **keep every pick that is still
possible alongside the new one, and clear only the picks that contradict it.** Concretely, the
shopper picks 90×200, then decides on walnut, which does not come in 90×200. Instead of a dead end,
the size clears, walnut is selected, and the size row reopens with the sizes walnut does come in.

Saya's original, which is what §9 re-implements against your selects:

```js
// Smart cascade: keep any other selection that is still compatible with
// the new key=val. Clear anything that would make an impossible combo,
// regardless of whether it was auto-selected or explicitly chosen.
attrRows.forEach( function ( otherRow ) {
    if ( otherRow.classList.contains( 'variation-row--hidden' ) ) return;
    var otherKey = otherRow.dataset.attrKey;
    if ( otherKey === key ) return;
    if ( ! selectedAttrs[ otherKey ] ) return;

    var testAttrs = {};
    testAttrs[ key ]      = val;
    testAttrs[ otherKey ] = selectedAttrs[ otherKey ];
    if ( ! isComboAvailable( testAttrs ) ) {
        delete selectedAttrs[ otherKey ];
        autoSelectedKeys.delete( otherKey );
        otherRow.querySelectorAll( '.variation-opt' ).forEach( function ( b ) { b.classList.remove( 'active' ); } );
    }
} );
```

The test is deliberately **pairwise**, new value against one other value at a time, rather than
against the whole selection at once. Testing the whole selection would clear more than necessary:
with three attributes, two existing picks can each be fine with the new value while the three
together are impossible, and clearing both when clearing one would do is the kind of thing that makes
a selector feel like it is fighting you.

One coupling to know about when this runs against real `<select>` elements rather than Saya's own
state object: WooCommerce may already have removed the `<option>` you are about to select, because it
was impossible under the *previous* selection. `jQuery.val()` on a missing option fails silently and
the click appears to do nothing. §9 handles it with `ensureOption()`, which re-appends the option
before setting it. That is safe, because WC rebuilds each select from its own pristine snapshot on
the next pass and never reads the pruned DOM back.

---

## 6. Gap 4 — the mobile sticky CTA does nothing on a variable product

**Effort: 15 minutes. Mobile is your primary conversion channel.**

`template-parts/product/single.php:504` renders the sticky bar's primary button as:

```php
<a href="<?php echo esc_url( $de_product->add_to_cart_url() ); ?>" class="btn-product-primary" style="flex:2;" rel="nofollow">
```

`WC_Product_Simple` overrides `add_to_cart_url()` to produce `?add-to-cart=<id>`. `WC_Product_Variable`
does not override it, so it inherits the base implementation, which returns the plain permalink.

That gives you two distinct problems:

| Product type | What the button does | What it should do |
|---|---|---|
| Variable | links to the page it is already on: **nothing happens** | add the chosen variation |
| Simple | `?add-to-cart=<id>`, ignoring `#qty-input`: **always adds 1** | add the chosen quantity |

The second one is quietly expensive on tiles, where the calculator has just written the required m²
into the quantity field and the shopper taps the big button at the bottom of the screen.

**Confirm in 30 seconds.** Open a variable product on a phone viewport and look at the sticky
button's `href`. If it equals the page URL, this is your site.

The fix uses the HTML5 `form` attribute, so one `<button>` outside the form can submit it. This keeps
the no-JS path alive for both product types, which a JS proxy click would not.

Three edits in `template-parts/product/single.php`:

**1.** Give the form an id (line 249):

```php
<form id="product-cta-form" class="cart product-cta-form<?php echo $de_is_variable ? ' variations_form' : ''; ?>" method="post" ...>
```

**2.** Move `add-to-cart` off the main button and into a hidden input, so that *any* submit button in
the form works. Variable products already have this input at line 365; add it for simple ones, and
drop `name`/`value` from the button (line 348):

```php
<?php if ( ! $de_is_variable ) : ?>
	<input type="hidden" name="add-to-cart" value="<?php echo absint( $de_id ); ?>" />
<?php endif; ?>

<div class="product-cta-group">
	<button type="submit" class="btn-product-primary<?php echo $de_is_variable ? ' single_add_to_cart_button' : ''; ?>" id="btn-add-to-cart">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
		<span class="btn-product-primary__label">Dodaj u ponudu</span>
	</button>
	...
</div>
```

The `<span>` matters for §7: the button also holds an SVG, so swapping `textContent` on the button
itself would delete the icon.

**3.** Turn the sticky link into a submit button for that form (line 504):

```php
<?php
/*
 * Submit iste forme, ne link: add_to_cart_url() za varijabilni proizvod vraca
 * permalink (WC_Product_Variable ne prepisuje baznu metodu), pa je dugme vodilo
 * na stranicu na kojoj kupac vec jeste. Uz form="" ide i izabrana kolicina, koju
 * je kod plocica upravo upisao kalkulator.
 */
?>
<button type="submit" form="product-cta-form" class="btn-product-primary" id="btn-sticky-add" style="flex:2;">
	<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
	Dodaj u ponudu
</button>
```

`syncSticky()` in §9 mirrors the main button's disabled state onto it, so the sticky bar cannot offer
an add that WooCommerce is going to refuse. Add the matching style:

```css
.product-sticky-mobile .btn-product-primary.disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
```

The same `add_to_cart_url()` behaviour is on the listing card
(`template-parts/shop/product-card.php:159`), where it is *defensible*: for a variable product the
link goes to the PDP, which is where options get chosen. But the label says "Dodaj u ponudu" and it
does not add anything, so either relabel it for variable products or send it somewhere useful, which
§8.4 covers.

---

## 7. Gap 5 — the CTA promises a price that may not exist

**Effort: two lines, inside §9.**

`inc/quote-cart.php:92-102` keeps price-0 products purchasable, and the cart renders "Cijena na upit"
for them. The PDP does not join in: `#btn-add-to-cart` always reads "Dodaj u ponudu", and the price
block shows whatever `get_price_html()` returned for the parent.

Saya switches the CTA per matched variation:

```js
ctaEl.textContent = match && 0 < parseFloat( match.display_price )
	? 'Dodaj u ponudu'
	: 'Zatraži cijenu';
```

§9 does the same through `.btn-product-primary__label`, using the `display_price` that already
arrives on `show_variation` in both normal and AJAX mode. It is a small thing that prevents a
specific bad moment: a shopper who taps "Dodaj u ponudu" expecting a number, gets a line with no
price, and now distrusts the rest of the cart.

---

## 8. Smaller items

### 8.1 Keyboard focus is lost on every pill rebuild

`syncPills()` does `pills.innerHTML = ''`, which destroys the focused element. A keyboard or screen
reader user tabs to a pill, presses Enter, and focus lands on `<body>`. They then have to tab back
through the whole row every single time.

§9 fixes it by noting `document.activeElement`'s `data-value` before the rebuild and calling
`focus()` on the pill that inherits it. Four lines, and it makes the selector usable without a mouse.

### 8.2 The auto-select needs the wildcard guard

Your `autoSelectSingles()` (`product.js:259-279`) fires when a row has exactly one option left. With
an "Any" variation in the mix, "one option left" can mean "this attribute is irrelevant to the
current selection", and auto-picking then invents a constraint the shopper never expressed.

Saya guards it by requiring at least one **matching** variation to actually name a value for that
attribute:

```js
// Skip auto-select if no matching variation actually specifies this attr
// (all have empty = wildcard → attr is irrelevant for current selection)
```

Carried into §9 as the `specific` check. Also note `autoSelectSingles()` is dead above the 30
variation threshold today, for the same reason as everything else in §4: it reads `select.options`,
which nobody is pruning.

### 8.3 What to leave alone

- **`price_html` being empty when all variations cost the same.** Your comment at `product.js:387`
  documents this and the guard is correct. It looks like a bug and is not.
- **`door_expert_stock_display()` as the single source of truth** for PHP and JS. Saya has no
  equivalent and its stock text drifted between the two. Keep yours.
- **The real `<select>` in the DOM.** It is the no-JS path, the POST carrier and WC's state. The
  `.is-enhanced` trick that only visually hides it once pills exist is exactly right.
- **`wc-add-to-cart-variation` as a script dependency** rather than an enqueue plus a prayer about
  ordering. §9 depends on WC having initialised first.

### 8.4 Preselecting a variation from the listing

Free, and worth taking. `wc_dropdown_variation_attribute_options()` reads
`$_REQUEST['attribute_<taxonomy>']` when deciding what is selected, so
`/proizvod/tavola/?attribute_pa_boja=hrast` lands on the PDP with hrast already active, no JS and no
extra handler. WC applies `get_variation_default_attribute()` the same way, so admin defaults work
too.

What is missing is the link. Today `template-parts/shop/product-card.php:28` uses a bare
`get_permalink()`, so filtering the shop by colour and clicking through loses the colour, and the
grid's per-card image does not match the PDP the shopper arrives at. For tiles that reads as broken
filtering.

```php
/**
 * Link na PDP sa pretodabranim varijacijskim atributima.
 *
 * WC sam cita $_REQUEST['attribute_*'] u wc_dropdown_variation_attribute_options(),
 * pa je za pretodabir dovoljan query parametar - nema JS-a ni dodatnog handlera.
 * Bez ovoga kupac koji filtrira plocice po boji "hrast" dolazi na PDP na kojem
 * nije izabrano nista, i to izgleda kao da filter nije radio.
 *
 * @param WC_Product                 $product   Proizvod sa kartice.
 * @param array<string,string|array> $selection Aktivni filteri: `pa_boja => hrast`.
 * @return string
 */
function door_expert_product_link_with_selection( $product, $selection = array() ) {
	$url = get_permalink( $product->get_id() );

	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) || empty( $selection ) ) {
		return $url;
	}

	$args = array();

	foreach ( $product->get_variation_attributes() as $taxonomy => $options ) {
		if ( empty( $selection[ $taxonomy ] ) ) {
			continue;
		}

		foreach ( (array) $selection[ $taxonomy ] as $value ) {
			if ( in_array( $value, $options, true ) ) {
				// Prvi koji ovaj proizvod stvarno ima; dvije vrijednosti istog
				// atributa ne mogu biti izabrane na PDP-u.
				$args[ 'attribute_' . sanitize_title( $taxonomy ) ] = $value;
				break;
			}
		}
	}

	return empty( $args ) ? $url : add_query_arg( $args, $url );
}
```

Feed `$selection` from whatever `inc/shop.php` already parsed out of the request, so the sidebar, the
query and the link cannot drift apart. Deliberately generic: it iterates whatever variation
attributes the product has rather than hardcoding `pa_boja`, because on Saya a card that hardcoded
one attribute silently stopped preselecting for every attribute added later.

**On SEO:** this puts `?attribute_*` on PDP links. That is safe as things stand.
`door_expert_is_filter_url()` in `inc/filters-seo.php:88-97` is scoped to `is_shop() ||
is_product_taxonomy()`, so it will not touch a product page, and WordPress's own `rel_canonical`
emits the clean permalink on singular views. Verify once with
`curl -s '<pdp>?attribute_pa_boja=hrast' | grep canonical` and move on. If you ever extend
`filters-seo.php` to products, add these keys there rather than dropping the preselection.

### 8.5 Two things you have that Saya's documents recommend building. Do not build them twice.

- [`13-UI-PDP-AND-PROJECTS.md`](13-UI-PDP-AND-PROJECTS.md) §3 proposes a "selection confirmation
  strip" spelling out the choice in words near the CTA, because on mobile the swatches scroll out of
  view. Your `.product-variants__selected` readout in each row's label does the same job and does it
  better, since it says which attribute each value belongs to. Skip the strip.
- [`11-UI-SWATCHES.md`](11-UI-SWATCHES.md) is still worth reading for tiles, and only for tiles. A
  pill reading "Bijela" is fine for a door finish and useless for a ceramic texture. You already have
  `.product-swatch` CSS at `assets/css/product.css:675-700`, unused, from the prototype. When you
  wire it, render swatches for `pa_boja` and pills for everything else: both are the same
  `data-options` loop with a different element, and both drive the same select.

### 8.6 Out of scope here, but adjacent

`#qty-input` has `max="99"` hardcoded (`single.php:340`), and the tile calculator writes
`Math.ceil( withReserve )` into it (`product.js:153`). A 120 m² job clamps to 99 with no message.
That belongs to [`05-PORT-tile-calculator.md`](05-PORT-tile-calculator.md), but it is one attribute
and you are in the file anyway.

---

## 9. The variations block of `product.js`, complete

Replaces `assets/js/product.js:164-438` in full. Everything above is in here; nothing else in the
file changes. It reuses your existing outer-scope variables (`mainImg`, `thumbs`, `qtyInput`, `calc`,
`pricePerM2`, `clampQty`, `recalc`), so it drops in where the current block sits.

Syntax-checked with `node --check`. Never executed inside Door Expert.

```js
  /* ── Varijacije: pilule nad WC selectima ────────────────── */
  /*
   * Podjela posla, nepromijenjena: skriveni WC <select>-ovi su izvor istine za
   * IZBOR i za POST forme, wc-add-to-cart-variation.js radi poklapanje varijacije,
   * cijenu i variation_id. Pilule su prikaz.
   *
   * Sta se mijenja: pilule se vise ne grade iz select.options. WC iz selecta BRISE
   * opcije koje nisu moguce uz trenutni izbor (update_variation_values, "Detach
   * unattached"), pa je pilula nestajala umjesto da posivi, red se prelamao pod
   * kursorom, a `.is-disabled` stil nikad nije bio upotrijebljen. Opcije sada
   * dolaze iz data-options, a dostupnost racunamo iz sopstvene mape varijacija.
   */
  var variationsForm = document.querySelector( '.product-page .variations_form' );
  var variationMapEl = document.getElementById( 'door-expert-variation-map' );

  if ( variationsForm && window.jQuery ) {
    window.jQuery( function ( $ ) {
      var $form = $( variationsForm );
      var priceEl = document.getElementById( 'product-price-current' );
      var priceDefault = priceEl ? priceEl.innerHTML : '';

      /*
       * Nasa mapa, ne WC-ova. WC svoju izostavlja iznad 30 varijacija i tada preskace
       * cijelo filtriranje opcija (useAjax), pa bi na kolekciji plocica sa 36 varijacija
       * svaka opcija zauvijek izgledala dostupno. Ova je uvijek tu.
       */
      var vmap = [];

      if ( variationMapEl ) {
        try {
          vmap = JSON.parse( variationMapEl.textContent || variationMapEl.innerHTML ) || [];
        } catch ( e ) {
          vmap = [];
        }
      }

      var imgDefaultSrc = mainImg ? mainImg.getAttribute( 'src' ) : '';
      var imgDefaultAlt = mainImg ? mainImg.getAttribute( 'alt' ) : '';
      var calcDefaultPrice = calc ? pricePerM2 : 0;

      var availEl = document.getElementById( 'product-availability' );
      var availTextEl = availEl ? availEl.querySelector( '.product-availability__text' ) : null;
      var availSubEl = document.getElementById( 'product-availability-sub' );
      var availNoteEl = document.getElementById( 'product-cta-note' );
      var availDefault = availEl ? availEl.getAttribute( 'data-default-status' ) : 'instock';

      var ctaEl = document.getElementById( 'btn-add-to-cart' );
      var ctaLabelEl = ctaEl ? ctaEl.querySelector( '.btn-product-primary__label' ) : null;
      var ctaLabelDefault = ctaLabelEl ? ctaLabelEl.textContent : '';
      var stickyEl = document.getElementById( 'btn-sticky-add' );

      var autoPicked = {};
      var manualClearKey = null;
      var autoBusy = false;

      function applyStock( status ) {
        var states = window.doorExpertStock;

        if ( ! availEl || ! states ) {
          return;
        }

        var state = states[ status ] || states.outofstock;

        if ( ! state ) {
          return;
        }

        availEl.classList.remove(
          'product-availability--in-stock',
          'product-availability--backorder',
          'product-availability--out-of-stock'
        );
        availEl.classList.add( 'product-availability--' + state.modifier );

        if ( availTextEl ) {
          availTextEl.textContent = state.label;
        }
        if ( availSubEl ) {
          availSubEl.textContent = state.sub;
        }
        if ( availNoteEl ) {
          availNoteEl.textContent = state.note || '';
          availNoteEl.hidden = ! state.note;
        }
      }

      function variantWraps() {
        return variationsForm.querySelectorAll( '.product-variants[data-attribute]' );
      }

      function wrapFor( key ) {
        return variationsForm.querySelector( '.product-variants[data-attribute="' + key + '"]' );
      }

      function selectFor( key ) {
        var wrap = wrapFor( key );
        return wrap ? wrap.querySelector( 'select' ) : null;
      }

      /** Trenutni izbor citamo iz selecta - oni su izvor istine, ne nase stanje. */
      function chosen() {
        var out = {};

        Array.prototype.forEach.call( variantWraps(), function ( wrap ) {
          var select = wrap.querySelector( 'select' );

          if ( select && select.value ) {
            out[ wrap.getAttribute( 'data-attribute' ) ] = select.value;
          }
        } );

        return out;
      }

      /**
       * Prva varijacija koja odgovara zadatim atributima, ili null.
       * Prazan string u varijaciji je WC-ov dzoker ("Bilo koja"), ne vrijednost -
       * naivno poklapanje ga tretira kao vrijednost i nikad ne nadje varijaciju.
       */
      function findVariation( attrs ) {
        for ( var i = 0; i < vmap.length; i++ ) {
          var v = vmap[ i ];
          var ok = true;

          for ( var key in attrs ) {
            if ( ! attrs.hasOwnProperty( key ) ) {
              continue;
            }

            var have = v.attrs[ key ];

            if ( undefined !== have && '' !== have && have !== attrs[ key ] ) {
              ok = false;
              break;
            }
          }

          if ( ok ) {
            return v;
          }
        }

        return null;
      }

      function comboPossible( attrs ) {
        return null !== findVariation( attrs );
      }

      /** Da li je `value` moguca u redu `key` uz sve OSTALE izbore. */
      function optionPossible( key, value, current ) {
        var test = {};

        for ( var k in current ) {
          if ( current.hasOwnProperty( k ) && k !== key ) {
            test[ k ] = current[ k ];
          }
        }

        test[ key ] = value;

        return comboPossible( test );
      }

      function optionsFor( wrap ) {
        var raw = wrap.getAttribute( 'data-options' );

        if ( ! raw ) {
          return null;
        }

        try {
          return JSON.parse( raw );
        } catch ( e ) {
          return null;
        }
      }

      /* Ako sablon jos nema data-options, radimo kao prije: bolje osiromaseno nego prazno. */
      function fallbackOptions( select ) {
        return Array.prototype.filter.call( select.options, function ( o ) {
          return '' !== o.value;
        } ).map( function ( o ) {
          return { value: o.value, label: o.textContent };
        } );
      }

      function hasOption( select, value ) {
        return Array.prototype.some.call( select.options, function ( o ) {
          return o.value === value;
        } );
      }

      function ensureOption( select, value, label ) {
        if ( hasOption( select, value ) ) {
          return;
        }

        /*
         * WC je ovu <option> izbrisao jer nije bila moguca uz PRETHODNI izbor.
         * Vracamo je, inace jQuery .val() tiho ne uradi nista i klik na posivjelu
         * pilulu ne radi. WC pri svom sljedecem prolazu svejedno gradi listu iz
         * sopstvenog snapshota, ne odavde, pa mu ovim ne kvarimo stanje.
         */
        var option = document.createElement( 'option' );
        option.value = value;
        option.textContent = label || value;
        select.appendChild( option );
      }

      function buildPills( wrap ) {
        var key = wrap.getAttribute( 'data-attribute' );
        var select = wrap.querySelector( 'select' );
        var pills = wrap.querySelector( '.product-variants__pills' );
        var selectedOut = wrap.querySelector( '.product-variants__selected' );

        if ( ! select || ! pills ) {
          return;
        }

        var opts = optionsFor( wrap ) || fallbackOptions( select );
        var current = chosen();
        var active = select.value;

        /* Pilule se rusi i gradi iznova, pa bi fokus tastature otisao na <body>. */
        var focused = document.activeElement;
        var refocus = focused && pills.contains( focused ) ? focused.getAttribute( 'data-value' ) : null;

        pills.innerHTML = '';

        opts.forEach( function ( opt ) {
          var pill = document.createElement( 'button' );
          pill.type = 'button';
          pill.className = 'product-variant-pill';
          pill.setAttribute( 'data-value', opt.value );
          pill.textContent = opt.label;

          var isActive = opt.value === active;
          pill.classList.toggle( 'is-active', isActive );
          pill.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );

          /*
           * Nemoguca kombinacija je siva, ali KLIKABILNA, i zato nema disabled ni
           * aria-disabled. Klik oslobadja izbor koji je blokira. Bez toga kupac koji
           * se predomislio ("ipak hrast") nema gdje da klikne i zaklucuje da hrast
           * ne postoji.
           */
          if ( ! isActive && ! optionPossible( key, opt.value, current ) ) {
            pill.classList.add( 'is-disabled' );
            pill.title = 'Nije dostupno uz trenutni izbor. Klik mijenja izbor.';
          }

          pill.addEventListener( 'click', function () {
            pick( key, opt.value === select.value ? '' : opt.value, opt.label );
          } );

          pills.appendChild( pill );

          if ( refocus && opt.value === refocus ) {
            pill.focus();
          }
        } );

        if ( selectedOut ) {
          var label = '';

          opts.forEach( function ( opt ) {
            if ( opt.value === active ) {
              label = opt.label;
            }
          } );

          selectedOut.textContent = label;
        }

        // Select sklanjamo tek kad pilule postoje - bez JS-a ostaje upotrebljiv dropdown.
        wrap.classList.add( 'is-enhanced' );
      }

      function syncPills() {
        Array.prototype.forEach.call( variantWraps(), buildPills );
      }

      function releaseAutoPicked( exceptKey ) {
        Object.keys( autoPicked ).forEach( function ( key ) {
          if ( key === exceptKey ) {
            return;
          }

          var select = selectFor( key );

          if ( select ) {
            select.value = ''; // Tiho: jedan 'change' okidamo na kraju, za sve odjednom.
          }

          delete autoPicked[ key ];
        } );
      }

      /**
       * Upisi vrijednost jednog atributa u njegov <select>.
       *
       * Razlika prema WC-u: prije upisa oslobadjamo izbore u DRUGIM redovima koji bi
       * ovaj ucinili nemogucim, umjesto da nemogucu opciju sklonimo. Tako svaki klik
       * uvijek nesto uradi, a ostaje sve sto je i dalje moguce.
       */
      function pick( key, value, label ) {
        var select = selectFor( key );

        if ( ! select ) {
          return;
        }

        delete autoPicked[ key ];
        releaseAutoPicked( key );

        // Namjerno ponistavanje ne smije odmah biti ponisteno auto-izborom.
        manualClearKey = '' === value ? key : null;

        if ( '' !== value ) {
          Array.prototype.forEach.call( variantWraps(), function ( wrap ) {
            var otherKey = wrap.getAttribute( 'data-attribute' );
            var otherSelect = wrap.querySelector( 'select' );

            if ( otherKey === key || ! otherSelect || ! otherSelect.value ) {
              return;
            }

            var test = {};
            test[ key ] = value;
            test[ otherKey ] = otherSelect.value;

            if ( ! comboPossible( test ) ) {
              otherSelect.value = '';
              delete autoPicked[ otherKey ];
            }
          } );

          ensureOption( select, value, label );
        }

        $( select ).val( value ).trigger( 'change' );
      }

      /**
       * Kad u nekom drugom redu ostane tacno jedna moguca opcija, biramo je umjesto
       * kupca. Pamtimo sta je izabrala masina (autoPicked) da bismo to pustili cim
       * kupac promijeni drugi atribut.
       */
      function autoSelectSingles() {
        Array.prototype.forEach.call( variantWraps(), function ( wrap ) {
          var key = wrap.getAttribute( 'data-attribute' );
          var select = wrap.querySelector( 'select' );

          if ( ! select || select.value || key === manualClearKey ) {
            return;
          }

          var current = chosen();
          var opts = optionsFor( wrap ) || fallbackOptions( select );
          var free = opts.filter( function ( opt ) {
            return optionPossible( key, opt.value, current );
          } );

          if ( 1 !== free.length ) {
            return;
          }

          /*
           * Ako nijedna varijacija koja odgovara trenutnom izboru ne precizira ovaj
           * atribut (sve imaju dzoker), atribut je nebitan i ne biramo ga umjesto kupca.
           */
          var specific = vmap.some( function ( v ) {
            for ( var k in current ) {
              if ( ! current.hasOwnProperty( k ) || k === key || autoPicked[ k ] ) {
                continue;
              }

              var have = v.attrs[ k ];

              if ( undefined !== have && '' !== have && have !== current[ k ] ) {
                return false;
              }
            }

            var mine = v.attrs[ key ];

            return undefined !== mine && '' !== mine;
          } );

          if ( ! specific ) {
            return;
          }

          autoPicked[ key ] = true;
          ensureOption( select, free[ 0 ].value, free[ 0 ].label );
          $( select ).val( free[ 0 ].value ).trigger( 'change' );
        } );
      }

      function setMainImage( src, srcset, alt ) {
        if ( ! mainImg || ! src ) {
          return;
        }

        mainImg.src = src;

        if ( srcset ) {
          mainImg.srcset = srcset;
        } else {
          mainImg.removeAttribute( 'srcset' );
        }

        mainImg.alt = alt || imgDefaultAlt;
      }

      /* Mobilna traka je submit iste forme, pa mora dijeliti i stanje dugmeta. */
      function syncSticky() {
        if ( ! stickyEl || ! ctaEl ) {
          return;
        }

        stickyEl.classList.toggle(
          'disabled',
          ctaEl.disabled || ctaEl.classList.contains( 'disabled' )
        );
      }

      syncPills();
      syncSticky();

      /*
       * Vezujemo se na 'change' selecta, ne na WC-ov 'woocommerce_update_variation_values':
       * taj event iznad 30 varijacija nikad ne dodje, jer WC tada preskoci
       * onUpdateAttributes. Nasa dostupnost ne zavisi od WC-ovog rezima.
       */
      $form.on( 'change', '.variations select', function () {
        syncPills();
        syncSticky();

        if ( autoBusy ) {
          return;
        }

        autoBusy = true;
        autoSelectSingles();
        autoBusy = false;
      } );

      // Klik na "Ponisti izbor": sve krece ispocetka, pa i masinski izbori.
      $form.on( 'click', '.reset_variations', function () {
        autoPicked = {};
        manualClearKey = null;
      } );

      $form.on( 'show_variation', function ( event, variation ) {
        // price_html je prazan kad su sve varijacije iste cijene - tad ostaje cijena roditelja.
        if ( priceEl && variation && variation.price_html ) {
          priceEl.innerHTML = variation.price_html;
        }

        if ( variation ) {
          applyStock( variation.door_expert_stock_status || availDefault );
        }

        /* Bez cijene dugme ne smije obecavati ponudu sa cijenom. */
        if ( ctaLabelEl && variation ) {
          ctaLabelEl.textContent = 0 < parseFloat( variation.display_price )
            ? ctaLabelDefault
            : 'Zatrazite cijenu';
        }

        /*
         * Kalkulator cita data-price iz roditelja, a to je kod varijabilnog proizvoda
         * najniza cijena iz opsega - pogresna cim formati imaju razlicit EUR/m².
         */
        if ( calc && variation && variation.display_price ) {
          pricePerM2 = parseFloat( variation.display_price ) || 0;
          recalc();
        }

        if ( variation && variation.image && variation.image.src ) {
          setMainImage( variation.image.src, variation.image.srcset, variation.image.alt );
          thumbs.forEach( function ( t ) {
            t.classList.remove( 'is-active' );
          } );
        }

        if ( qtyInput && variation ) {
          if ( variation.max_qty ) {
            qtyInput.setAttribute( 'max', variation.max_qty );
          }
          if ( variation.min_qty ) {
            qtyInput.setAttribute( 'min', variation.min_qty );
          }
          qtyInput.value = clampQty( parseInt( qtyInput.value, 10 ) );
        }

        syncPills();
        syncSticky();
      } );

      $form.on( 'hide_variation reset_data', function () {
        if ( priceEl ) {
          priceEl.innerHTML = priceDefault;
        }

        if ( ctaLabelEl ) {
          ctaLabelEl.textContent = ctaLabelDefault;
        }

        if ( calc ) {
          pricePerM2 = calcDefaultPrice;
          recalc();
        }

        applyStock( availDefault );
        setMainImage( imgDefaultSrc, '', imgDefaultAlt );

        if ( thumbs.length ) {
          thumbs.forEach( function ( t, i ) {
            t.classList.toggle( 'is-active', 0 === i );
          } );
        }

        syncPills();
        syncSticky();
      } );
    } );
  }
```

Two notes on how this interacts with WooCommerce, since both look like accidents and are not:

- **`pick()` clears other selects silently and triggers `change` only once, on the target.** WC's
  `onUpdateAttributes` reads every select through `getChosenAttributes()`, so one event is enough for
  it to see all of them. Your existing `releaseAutoPicked()` already relied on this; it is preserved.
- **Binding to `change` on the selects rather than to `woocommerce_update_variation_values`.** Our
  handler runs after WC's, because WC binds at `$(document).ready` and `wc-add-to-cart-variation` is
  declared as a dependency of `product.js`. By the time we recompute, WC has finished its own pass.

## 10. Do not do these

- **Do not replace `variations_form` with a hand-written engine.** You would inherit Saya's problem:
  matching logic in the theme, drifting from WooCommerce's, with two definitions of "available".
- **Do not add a custom AJAX add-to-cart endpoint.** §2. The form POST path handles "Any" attributes
  correctly and needs no nonce plumbing, no rate limiter and no error-notice marshalling.
- **Do not remove the real `<select>`** or hide it before the pills exist. It is the no-JS path and
  WC's state.
- **Do not hide unavailable options.** Grey them. §3.2.
- **Do not raise `woocommerce_ajax_variation_threshold` as the fix for §4** without measuring the
  HTML weight first.
- **Do not set `disabled` or `aria-disabled` on a greyed pill.** It is clickable by design.

## 11. Verify

Variation UI, on a variable door with two attributes:

1. Pick a size. Options in the colour row that have no variation with that size are **greyed and
   struck through, still present, still clickable**. Count the pills before and after: the count must
   not change.
2. Click a greyed colour. The colour becomes active and the **size clears**, rather than nothing
   happening.
3. Click a greyed colour whose size is still compatible with a *third* attribute's pick. That third
   pick must survive. Only genuinely contradicting picks clear.
4. Click the active pill again. It deselects and every option returns to normal.
5. Pick one attribute such that exactly one option remains possible in another row. It auto-selects.
6. "Poništi izbor" clears everything, including machine picks, and the price returns to the parent's
   range.
7. Tab to a pill, press Enter, check focus is still in the row and not on `<body>`.

Above the threshold, on a tile collection with more than 30 variations:

8. `view-source` shows `data-product_variations="false"` **and** a populated
   `#door-expert-variation-map`.
9. Everything in 1 to 7 behaves identically to the door. This is the whole point of §4.
10. Price, availability text, gallery image and the m² price all still update per variation, served
    by WC's AJAX round trip.

Add to cart:

11. On a variable product, submit with a full selection: a cart line for the **variation**, not the
    parent, with the chosen quantity.
12. On a variable product with an "Any <attribute>" variation, add to cart. No
    `"<Attribute> is a required field"` notice. If you see one, the select for that attribute posted
    an empty value, which means a pill did not write to it.
13. Phone viewport, variable product, full selection, tap the sticky bar button: the item lands in
    the cart. Before this work it reloaded the same page.
14. Phone viewport, tile, set quantity to 24, tap the sticky button: the line shows **24**, not 1.
15. With no selection made, the sticky button is visibly disabled alongside the main one.
16. A variation priced `0` shows "Zatražite cijenu" and **still adds to the cart**.
17. A variation with a **blank** price renders its pill and can be added. If the pill is missing,
    `door_expert_variation_visible_without_price()` is not loaded.

Without JavaScript:

18. Disable JS. The selects render as normal dropdowns, choosing values and submitting adds the right
    variation. The sticky button still submits, because it is a real submit button.

## 12. File map

| File | Change |
|---|---|
| `inc/product-variations.php` | add `door_expert_variation_map()`, `door_expert_variation_option_list()`, `door_expert_variation_visible_without_price()`, `door_expert_product_link_with_selection()`. Existing orderability and availability code **unchanged** |
| `template-parts/product/single.php` | `data-options` on `.product-variants`; `#door-expert-variation-map` script; `id="product-cta-form"`; hidden `add-to-cart` input for simple products; `.btn-product-primary__label` span; sticky link becomes a submit button |
| `assets/js/product.js` | replace the variations block (lines 164 to 438) with §9 |
| `assets/css/product.css` | `.product-variant-pill.is-disabled` cursor and hover; `.product-sticky-mobile .btn-product-primary.disabled` |
| `template-parts/shop/product-card.php` | optional, §8.4: use `door_expert_product_link_with_selection()` for the card links |
| `inc/product.php` | **unchanged** |
| `inc/quote-cart.php` | **unchanged** |
| WooCommerce → Settings → Products → Inventory | confirm "Hide out of stock items from the catalog" is **off**, §4.4 |

## 13. Standing caveats

- Written against the Door Expert theme as of 2026-09-26. Line numbers drift; re-check with `grep -n`
  before trusting one.
- The PHP passes `php -l` and the JS passes `node --check`. **Neither has run inside Door Expert.**
  Reviewed drafts, not tested code, which is what §11 is for.
- Three statements about WooCommerce's internals drive the whole document: that unavailable options
  are removed rather than disabled (§3.1), that the AJAX threshold disables option filtering
  entirely (§4.1), and that `add_to_cart_url()` on a variable product is just the permalink (§6).
  Each comes with a check that takes under a minute. Run all three before doing the work, because if
  any is false on your WooCommerce version the corresponding section is unnecessary.
