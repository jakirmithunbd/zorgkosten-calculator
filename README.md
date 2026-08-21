# Zorgkosten Calculator for Elementor

Elementor widget that rebuilds the **Kostenkompas GGZ** app
(https://zorgkosten-inzicht.lovable.app/) 1:1, with every text, amount, image
and link editable from the Elementor editor.

## Install

1. Copy the `zorgkosten-calculator` folder into `wp-content/plugins/`.
2. Activate **Zorgkosten Calculator for Elementor** in WP Admin → Plugins.
3. In Elementor, search for the **Zorgkosten Calculator** widget (category
   "Zorgkosten") and drop it on a page.

Requires Elementor 3.5+ (tested with 4.2.1).

## The step flow

**10 steps**, but the authorization step only appears for insurers that may
require one — for every other insurer it is **9 steps**, and the counter and
progress segments adjust automatically.

| # | Step | Notes |
|---|---|---|
| 1 | Insurer | Logo tiles grouped by concern |
| 2 | Policy | Policies of the chosen insurer + "Ik weet het niet" |
| 3 | Total deductible | 385–885 grid + "Ik weet het niet" |
| 4 | Used deductible | Required; cannot exceed the total from step 3 |
| 5 | How costs are determined | Three icon cards + NZa link |
| 6 | Reimbursement | Green percentage + worked example per part |
| 7 | Coulanceregeling | Waived amount + conditions/exclusions per agreement |
| 8 | Eigen bijdrage | € 250, three icon cards, "Goed om te weten" |
| 9 | Toestemming (machtiging) | **Only for insurers that need one** |
| 10 | Hoe betaalt u uw zorg? | Four numbered steps per agreement type |
| — | Result | Full cost overview with a breakdown table |

Navigation: a bottom bar with "← Terug", **clickable progress segments** (jump
back to any visited step), and "Volgende →". From step 2 a cream sidebar shows
**"Uw gegevens"** — clickable chips of every answer — plus a step illustration.

## Amounts and calculation

- Diagnostics **€ 2.000** + treatment **€ 4.000** = whole trajectory **€ 6.000**
- Personal contribution **€ 250**
- Reimbursement:
  - *policy known* → that policy's percentage, or a **range** when the optional
    "highest %" is filled in (e.g. Anderzorg 60% tot 100%);
  - *policy unknown, insurer known* → lowest–highest across all that insurer's
    policies;
  - *no insurer* → the configured default (70%).
- Every amount is split into reimbursed / not reimbursed per part.
- Unknown deductibles produce a range (€ 0 up to the total, or up to the
  configured € 885 ceiling) with an explanatory note.

## What is editable from Elementor

Everything, organised one section per step:

- **Intro screen** – kicker, title, text, bullets, button, illustration.
- **Insurers** (repeater) – group, name, logo, payment-agreement toggle,
  authorization toggle (adds step 9), declaration URL, authorization URL.
  32 insurers pre-filled.
- **Policies** (repeater) – insurer, policy, % (lowest), optional % (highest),
  tariff basis, note. 70 policies pre-filled. Plus the tariff-basis wording.
- **Navigation & sidebar** – all button/link labels, step counter, progress
  tooltips, help/warning labels, sidebar title and chip texts.
- **Steps 1–10** – every title, subtitle, help text, list, icon card, numbered
  step, validation message and per-agreement variant.
- **Result screen** – every label, table heading, note, warning, disclaimer and
  the CTA.
- **Calculation** – the two average amounts, contribution, deductible options,
  unknown-deductible ceiling, default %.
- **Sidebar illustrations** – four images + alt texts.
- **Style tab** – all colours (coral, teal, green, uncovered red, cream …),
  max width, desktop app-frame toggle, typography.

Icon cards choose from the reference app's icon set: landmark, clock, users,
coins, shield-check.

## File structure

```
zorgkosten-calculator.php            Bootstrap: requirements, assets, registration
includes/
  widget-cost-calculator.php         Widget class (wires the traits below)
  class-zkc-defaults.php             ALL DATA: insurers, links, policies + %s, bases
  trait-zkc-controls-general.php     Intro, insurers, policies, navigation, calc, images
  trait-zkc-controls-steps.php       Steps 1-5
  trait-zkc-controls-money.php       Steps 6-8 (reimbursement, coulance, contribution)
  trait-zkc-controls-final.php       Steps 9-10 (machtiging, payment)
  trait-zkc-controls-result.php      Result screen
  trait-zkc-controls-style.php       Style tab
  trait-zkc-render.php               Builds the JSON config for the frontend
assets/
  js/calculator.js                   The stepper (flow, calculations, rendering)
  css/calculator.css                 All styling (design tokens at the top)
  logos/ img/ fonts/                 31 logos, 5 illustrations, Archia font
```

To check or change pricing, edit `includes/class-zkc-defaults.php` — every
percentage, range and per-insurer link lives there.

## Fonts

Headings, display numbers and eyebrow labels use **Archia** (bundled), body
text uses **Ubuntu** — the same pairing as the reference app. Elementor's
typography controls override both.
