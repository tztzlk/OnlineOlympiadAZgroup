# Design Context — Онлайн-олимпиада

## Product
Online olympiad platform for school children in Kazakhstan. Parents register their child, pay via Kaspi, child takes a timed test, receives an instant result + certificate + error review. Language: Russian.

## Tech Stack
- Vue 3 SPA, Vue Router v4, Tailwind v4, Laravel backend
- No Google Fonts (system font stack currently: `'Segoe UI', 'Trebuchet MS', Arial`)
- Light mode primary, dark mode toggle exists (both use warm palette)

## Current Palette
```
--bg:          #f5f0e8   warm beige
--bg-alt:      #efe7da
--text:        #2c2c2a
--text-secondary: #5f5a51
--accent:      #c9a84c   warm gold
--accent-hover:#b79133
--card:        #e8dfc8
```

## Design Principles (inferred from code)
1. **Warm and trustworthy** — beige/gold palette, soft shadows, no harsh contrast
2. **Light over dark** — children and parents browse in daylight; warm light mode is the right call
3. **Gentle motion** — stagger entrances, hover lifts, no bounce/elastic easing
4. **Legible hierarchy** — large display headings, readable body at 14–17px
5. **Mobile-first** — sticky CTA on mobile, burger menu, fluid layouts

## Components Map
| Component | Status | Notes |
|-----------|--------|-------|
| Welcome.vue | ✅ Improved | Hero with stagger entrance, fixed image path |
| HowItWorks.vue | ✅ Improved | IntersectionObserver stagger, hover lift |
| Header.vue | — | Transparent→opaque on scroll, mobile drawer |
| Footer.vue | — | Not yet reviewed |
| Reviews.vue | — | Not yet reviewed |
| News.vue | — | Not yet reviewed |

## Open Questions
*To be confirmed by the team:*

1. **Brand personality in 3 words** — how should this feel? (e.g. "encouraging, prestigious, safe" or "competitive, clear, celebratory")
2. **Primary visitor** — parent deciding at home in the evening? A kid? A teacher? This determines tone and trust signals.
3. **References** — any site (unrelated is fine) that captures the right vibe? Any anti-references?

## Typography Direction (pending font decision)
Current system stack is safe but forgettable. Once brand personality is confirmed, a Google Fonts pair will be added — a distinctive display face for headings + a refined body font. Both must be free and load fast (2 families max, `font-display: swap`).

## Animation Standards
- Easing: `cubic-bezier(0.23, 1, 0.32, 1)` (strong ease-out)
- Entrance: `opacity 0 → 1` + `translateY(20px → 0)`, 500ms
- Stagger: `calc(var(--i) * 80ms)` delay per card
- Hover lift: `translateY(-4px)`, 220ms
- Active press: `scale(0.97)`, 100ms, no bounce
- Below-fold: always via `IntersectionObserver`, never on mount
- Guard all hover effects with `@media (hover: hover) and (pointer: fine)`

## Absolute Bans (from impeccable skill)
- No `border-left` / `border-right` > 1px as accent stripe
- No gradient text (`background-clip: text`)
- No `transition: all` — always explicit properties
- No glassmorphism used decoratively
- No bounce/elastic easing
