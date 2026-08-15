# Estatein Design System

## Visual authority

The supplied Estatein Figma file is the pinned source of truth. Preserve its dark architectural presentation, exact section order, content hierarchy, photography, and responsive compositions. The Guide page contains stale printed labels on visibly purple swatches; use the implemented purple values below instead.

## Direction contract

**THESIS** — Property discovery feels considered and transparent: large architectural imagery, direct language, and structured facts replace the category's glossy luxury theatrics.

**OWN-WORLD** — Near-black `#141414` and `#1A1A1A` surfaces, quiet `#262626` borders, crisp white type, muted `#999999` copy, vivid `#703BF7` actions, restrained rounded geometry, line icons, star motifs, and modern residential photography.

**STORY** — Visitors meet the promise, see concrete listings and proof, understand the working process, then browse or request help.

**FIRST VIEWPORT** — Announcement and centered navigation lead into a split hero: decisive copy, two actions, and metrics on the left; an edge-to-edge villa image on the right; four service shortcuts anchor the fold. Mobile puts imagery first, then copy and full-width actions.

**FORM** — Persuade mode; established Figma world; faithful responsive reconstruction at 390/1440/1920 with fluid intermediate states.

## Foundations

### Color

| Token | Value | Use |
|---|---:|---|
| `--color-bg` | `#141414` | Page background |
| `--color-surface` | `#1A1A1A` | Navigation, cards, controls |
| `--color-border` | `#262626` | Dividers and card outlines |
| `--color-border-strong` | `#333333` | Interactive boundaries |
| `--color-muted-strong` | `#4D4D4D` | Disabled and quiet decoration |
| `--color-muted` | `#666666` | Secondary decoration |
| `--color-text-dim` | `#808080` | Nonessential metadata |
| `--color-text-muted` | `#999999` | Body copy |
| `--color-primary` | `#703BF7` | Primary actions and focus accents |
| `--color-primary-soft` | `#A685FA` | Hover/highlight details |
| `--color-white` | `#FFFFFF` | Headings and high-emphasis content |

### Typography

Use self-hosted Urbanist with weights 400, 500, 600, and 700 and `font-display: swap`. Body copy is 16–18px on wide screens and 14–16px on mobile. Display headings scale fluidly but never exceed 60px. Headings use balanced line wrapping and moderate negative tracking no tighter than `-0.03em`. Paragraph measure stays near 65–75 characters where the composition allows it.

### Geometry and spacing

- Page content max-width: 1596px at desktop, 1280px at laptop, and viewport minus 32px on mobile.
- Layout switches at 768px and 1600px; intermediate widths remain fluid.
- Primary radii: 8px controls, 10–12px cards, fully rounded only for compact tags and icon buttons.
- Borders are normally a single 1px `#262626` outline with no competing card shadow.
- Section spacing is generous and responsive; related heading/copy groups remain tight.

## Components

- Announcement strip and site navigation share horizontal rhythm but remain distinct bordered surfaces.
- Buttons use a solid purple primary and dark outlined secondary treatment with visible focus and disabled states.
- Cards rely on content, dividers, imagery, and precise spacing; decoration never substitutes for information.
- Filters and forms use persistent labels, dark filled controls, contextual validation, and an announced result region.
- Property and testimonial rails use native horizontal scrolling, scroll snap, and explicit previous/next buttons.
- FAQs use native buttons with `aria-expanded`; content remains usable without JavaScript.
- The property gallery opens in a native `<dialog>` with Escape, close control, and managed focus.

## Responsive behavior

- At 390px the full nav becomes a menu button; split layouts and card grids become single columns or horizontally scrollable rails.
- At 1440px the shell uses reduced side padding and slightly smaller type while preserving the Figma composition.
- At 1920px sections use their full desktop max-width and multi-column grids.
- Touch targets are at least 44px, no control depends on hover, and content remains legible at 200% zoom.

## Motion and accessibility

Motion is limited to the announcement close, navigation panel, scroll controls, accordion disclosure, and gallery dialog. Use an exponential ease-out and honor `prefers-reduced-motion`. Body and placeholder text must meet WCAG AA contrast, focus is always visible, landmark order is semantic, every page has one H1, and status/error messaging is announced.
