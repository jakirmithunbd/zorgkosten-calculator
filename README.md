# Zorgkosten Calculator for Elementor

Custom Elementor addon that rebuilds the multi-step healthcare cost calculator from
https://zorgkosten-inzicht.lovable.app/ (v2 design) as a fully backend-editable widget.

## Install

1. Copy the `zorgkosten-calculator` folder into `wp-content/plugins/`.
2. Activate **Zorgkosten Calculator for Elementor** in WP Admin → Plugins.
3. In Elementor, search for the **Zorgkosten Calculator** widget (category "Zorgkosten") and drop it on a page.

Requires Elementor 3.5+ (tested with 4.2.1).

## The v2 design

- **Two-column layout**: the step content on the left, a cream sidebar on the right
  with **"Uw gegevens"** — clickable chips of every answer so far (click = jump back
  to that step) — and a step illustration with decorative blobs.
- **Segmented progress bar** at the bottom (one segment per step) with
  "Stap X van Y", a "← Terug" text link and a "Volgende →" pill button.
- **Intro**: two-column hero with kicker, title, bullet list and illustration.
- **Result**: full-width overview — split bar (coral = reimbursed, teal = waived,
  striped = uncertain range), own-costs card, "Wat u zelf regelt" card with a
  per-insurer "Declareren bij …" button, machtiging warning with link, and a CTA
  panel ("Hoe gaat het verder?").
- Fonts inherit from the theme / Elementor global settings; the widget adds
  typography controls for titles, display numbers and body text.

## The step flow

The calculator has **9 steps**, but the authorization step is only part of the
flow when the chosen insurer may require one — for every other insurer it is
**8 steps** and the counter and segments adjust automatically.

| # | Step | Notes |
|---|---|---|
| 1 | Insurer | Logo tiles grouped by concern (CSS grid, 2–3 group columns) |
| 2 | Policy | Only the policies of the chosen insurer, plus "Ik weet het niet" |
| 3 | Total deductible | 385–885 grid, plus "Ik weet het niet" |
| 4 | Used deductible | Required; cannot exceed the total chosen in step 3 |
| 5 | How costs are determined | Static content + framed panel |
| 6 | Reimbursement | Cream hero with the percentage and the estimated amount |
| 7 | Invoices | Two variants: with / without a payment agreement |
| 8 | Authorization (machtiging) | **Only for insurers that may need one**; links to the insurer |
| 9 | Goodwill scheme (coulance) | Ends with "Bekijk uw kosteninschatting" |
| — | Result | Full-width cost overview |

## Reimbursement percentages & ranges

- **Policy known**: that policy's percentage — or a **range** (e.g. Anderzorg
  Basis 60–100%) when the optional "highest %" field is filled in. Ranges show as
  "60% tot 100%", the estimate becomes "€ 1.200 tot € 2.000", and the result's
  split bar gets a striped "uncertain" band.
- **Policy unknown, insurer known**: the lowest and highest percentage across
  *all* policies of that insurer (matching the original app).
- **No insurer / no policies**: the configured default percentage (70%).

When the visitor answers "Ik weet het niet" for the total or used deductible,
the own-costs card shows a range (€ 0 up to the total, or up to the configured
€ 885 ceiling) with an explanatory note.

## What is editable from Elementor

Everything ships pre-filled with the exact content and data of the original app.

- **Intro screen** – kicker, title, description, bullet list, button, illustration + alt.
- **Insurers** (repeater) – group, name, logo (upload or bundled fallback URL),
  payment-agreement toggle, machtiging toggle, **declaration URL** and optional
  **authorization URL** per insurer. 32 insurers pre-filled with all links.
- **Policies** (repeater) – insurer, policy, % (lowest), optional % (highest),
  tariff basis, note. All 70 policies pre-filled.
- **Navigation & sidebar** – back/next/restart/adjust labels, step counter,
  help/warning labels, "Ik weet het niet", sidebar title/empty text/chip texts.
- **Steps 1–9** – every title, help text, message variant, WYSIWYG block,
  validation error, list and button label.
- **Result screen** – every label, note and warning, the disclaimer, CTA title/
  text/button + link.
- **Calculation** – average invoice (€2000), personal contribution (€250),
  deductible options, unknown-deductible ceiling (€885), default %.
- **Sidebar illustrations** – the four step images + alt texts (bundled by default).
- **Style tab** – all colors (coral primary, teal goodwill, cream secondary, …),
  max width, the desktop "app frame" toggle, and typography for titles, display
  numbers and body.

## Bundled assets

- `assets/logos/` – all 31 insurer logos (used by default; uploads override).
- `assets/img/` – the 5 illustrations (intro, verzekering, eigenrisico, gesprek, factuur).

To swap an asset for every new widget at once, replace the file keeping the
same filename.
