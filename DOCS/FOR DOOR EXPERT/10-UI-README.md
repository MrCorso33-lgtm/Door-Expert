
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


