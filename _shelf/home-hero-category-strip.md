# Hero traka sa 4 kategorije (naslovna)

- **Naziv:** Hero category quick-links strip (`.hero__categories`)
- **Vraća se u:** `wp-content/themes/door-expert/front-page.php` — unutar **hero SLIDE 1**,
  odmah poslije `.hero__content` bloka (`</div></div>`), a prije `</div><!-- /SLIDE 1 -->`.
- **CSS:** `wp-content/themes/door-expert/assets/css/hero.css` — blok `.hero__categories` /
  `.hero__cat-*` + responsive fragmenti (1024px, 768px, 480px). Svi izvučeni ovdje ispod.
- **Šta predstavlja:** tamna poluprovidna traka preko dna hero slajda 1 sa 4 brze veze ka
  glavnim kategorijama (Sobna vrata, Sigurnosna vrata, Keramičke pločice, Dekorativni umivaonici),
  svaka sa ikonicom, nazivom i podnaslovom.
- **Razlog sklanjanja:** klijent želi da se ukloni sa naslovne (moguć povratak kasnije).
- **Datum sklanjanja:** 2026-08-14

---

## HTML (PHP) — ide u `front-page.php` (hero slide 1)

```php
      <!-- Category quick-links bar -->
      <!-- Research: Visual research – "curated showroom route, hero + three category gateways" -->
      <div class="hero__categories" role="navigation" aria-label="Brzi pristup kategorijama">
        <div class="hero__categories-inner">

          <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="hero__cat-item">
            <span class="hero__cat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="18" height="20" rx="1"/><line x1="9" y1="12" x2="9" y2="12.5"/></svg>
            </span>
            <span class="hero__cat-label">
              <span class="hero__cat-name">Sobna vrata</span>
              <span class="hero__cat-sub">Klizna, staklena, standardna</span>
            </span>
          </a>

          <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="hero__cat-item">
            <span class="hero__cat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            <span class="hero__cat-label">
              <span class="hero__cat-name">Sigurnosna vrata</span>
              <span class="hero__cat-sub">Za stan i kucu, sertifikovana</span>
            </span>
          </a>

          <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="hero__cat-item">
            <span class="hero__cat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/></svg>
            </span>
            <span class="hero__cat-label">
              <span class="hero__cat-name">Keramicke plocice</span>
              <span class="hero__cat-sub">Spanski brendovi, 7 kategorija</span>
            </span>
          </a>

          <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="hero__cat-item">
            <span class="hero__cat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16v4a4 4 0 01-4 4H8a4 4 0 01-4-4v-4z"/><path d="M6 12V7a2 2 0 012-2h8a2 2 0 012 2v5"/><line x1="12" y1="20" x2="12" y2="22"/></svg>
            </span>
            <span class="hero__cat-label">
              <span class="hero__cat-name">Dekorativni umivaonici</span>
              <span class="hero__cat-sub">Bathco Spanija, kameni, nadgradni</span>
            </span>
          </a>

        </div>
      </div>
```

---

## CSS — ide u `assets/css/hero.css`

Glavni blok (bio poslije `.hero__savings` / prije `/* Slide 2: Promo block */`):

```css
/* ── Category quick-links (Slide 1 only) ────────────────────── */
.hero__categories {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 3;
  background: rgba(30, 26, 22, 0.55);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-top: 1px solid rgba(255,255,255,0.08);
}

.hero__categories-inner {
  max-width: var(--container-max);
  margin: 0 auto;
  padding: 0 var(--space-8);
  display: flex;
  align-items: stretch;
}

.hero__cat-item {
  flex: 1;
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-4) var(--space-5);
  text-decoration: none;
  color: rgba(255,255,255,0.80);
  font-family: var(--font-ui);
  font-size: var(--text-sm);
  font-weight: 500;
  border-right: 1px solid rgba(255,255,255,0.08);
  transition: background var(--duration-base) var(--ease-out),
              color var(--duration-base) var(--ease-out);
  position: relative;
}

.hero__cat-item:last-child {
  border-right: none;
}

.hero__cat-item::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--color-jantar);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform var(--duration-base) var(--ease-out);
}

.hero__cat-item:hover {
  background: rgba(255,255,255,0.06);
  color: var(--color-white);
}

.hero__cat-item:hover::after {
  transform: scaleX(1);
}

.hero__cat-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(160,120,64,0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background var(--duration-base) var(--ease-out);
}

.hero__cat-item:hover .hero__cat-icon {
  background: rgba(160,120,64,0.45);
}

.hero__cat-icon svg {
  width: 16px;
  height: 16px;
  stroke: var(--color-sampanjac);
}

.hero__cat-label {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.hero__cat-name {
  font-weight: 600;
  font-size: var(--text-sm);
  color: var(--color-white);
  line-height: 1.2;
}

.hero__cat-sub {
  font-size: 11px;
  color: rgba(255,255,255,0.50);
  font-weight: 400;
}
```

Responsive fragmenti (bili unutar postojećih `@media` blokova u hero.css):

```css
/* unutar @media (max-width: 1024px) */
.hero__categories-inner {
  padding: 0 var(--space-6);
}

/* unutar @media (max-width: 768px) */
.hero__categories {
  display: none; /* simplified on mobile per research */
}

/* cijeli @media (max-width: 480px) blok je bio samo za ovu traku */
@media (max-width: 480px) {
  .hero__categories-inner {
    flex-direction: column;
  }
  .hero__cat-item {
    border-right: none;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    padding: var(--space-3) var(--space-4);
  }
}
```
