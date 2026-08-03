# Zorgkosten Calculator for Elementor

Custom Elementor addon that rebuilds the multi-step healthcare cost calculator from
https://zorgkosten-inzicht.lovable.app/ as a fully backend-editable widget.

## Install

1. Copy the `zorgkosten-calculator` folder into `wp-content/plugins/`.
2. Activate **Zorgkosten Calculator for Elementor** in WP Admin → Plugins.
3. In Elementor, search for the **Zorgkosten Calculator** widget (category "Zorgkosten") and drop it on a page.

Requires Elementor 3.5+ (tested with 4.2.1).

## The step flow

The calculator has **9 steps**, but the authorization step is only part of the
flow when the chosen insurer may require one — for every other insurer it is
**8 steps** and the "Stap X van Y" counter adjusts automatically.

| # | Step | Notes |
|---|---|---|
| 1 | Insurer | Logo tiles grouped by concern |
| 2 | Policy | Only the policies of the chosen insurer, plus "Ik weet het niet" |
| 3 | Total deductible | 385–885, plus "Ik weet het niet" |
| 4 | Used deductible | Required; cannot exceed the total chosen in step 3 |
| 5 | How costs are determined | Static content |
| 6 | Reimbursement | Dark hero with the percentage and the estimated amount |
| 7 | Invoices | Two variants: with / without a payment agreement |
| 8 | Authorization (machtiging) | **Only for insurers that may need one** |
| 9 | Goodwill scheme (coulance) | Ends with "Bekijk uw kosteninschatting" |
| — | Result | Costs breakdown |

## What is editable from Elementor

Everything ships pre-filled with the exact content and data of the original app.

**Content tab**
- **General** – brand name, "start over" link, step counter format, Back/Next labels, help prefix, "I don't know" label, footer.
- **Intro screen** – kicker, title, description, start button.
- **Insurers** (repeater) – group/concern name, insurer name, logo (media upload), payment-agreement toggle (betaalovereenkomst), authorization toggle (machtiging). 32 insurers pre-filled. The authorization toggle is what adds step 8 for that insurer.
- **Policies & reimbursement** (repeater) – insurer name (must match an insurer), policy name, reimbursement %, an optional **highest %** for policies that reimburse a range, tariff basis, optional note. All 70 policies pre-filled with the original percentages. Plus fallback % and basis for "I don't know".
- **Step 1–9** – every title, help text, description, WYSIWYG content block, both step-7 variants (with/without payment agreement), the machtiging step, coulance conditions/exclusions (one per line).
- **Step 4** – both validation messages ("Vul een geldig bedrag in.", "Het gebruikte bedrag kan niet hoger zijn dan uw totale eigen risico.").
- **Step 6** – hero kicker/title/basis line, estimate box kicker and note, and two messages: one for a known policy and one for "Ik weet het niet".
- **Calculation** – average invoice amount (€2000), personal contribution (€250), deductible options (385–885), highest deductible assumed when the total is unknown (€885).
- **Result screen** – every label, two summary paragraphs (one for known values with `{invoice} {reimbursed} {waived} {contribution} {deductible} {total}`, one for an unknown used deductible with `{totalMin} {totalMax}`), both accuracy warnings, disclaimer, restart + sign-up buttons (sign-up URL is a link control).

### Reimbursement percentages

Most policies reimburse a single percentage. A few reimburse a **range** — for
example Anderzorg Basis at 60–100% depending on the type of care. Fill in the
optional "highest %" field for those: the step-6 hero and the result then show
`60–100%`, and the money is calculated with the middle of the range (80%).

**Style tab**
- Colors (primary, dark, headings, text, muted, page/card background, borders)
- Card radius, max width
- Typography (step titles, body)
- Button text color + radius

## Calculation logic (same as the original)

- Reimbursed = average invoice x policy % (the middle of the range for range policies; 70% when the policy is unknown)
- Waived (coulance) = average invoice - reimbursed
- Remaining deductible = chosen deductible - already-used amount (min 0)
- Total own costs = personal contribution + remaining deductible

When the visitor answered "Ik weet het niet" for the total deductible or for
the amount already used, the remaining deductible cannot be pinned down, so the
result shows a **range** instead of one number (EUR 0 up to the total, or up to the
configured EUR 885 ceiling when the total itself is unknown) and an amber note
explains why.

## Insurer logos

All 31 logos ship with the plugin in `assets/logos/` and are used by default,
so the calculator does not depend on any external CDN. Each insurer row still
has two logo fields: a **Logo** media control (upload to the site's own media
library) and a **Logo URL (fallback)** text field, pre-filled with the bundled
file. An uploaded logo always wins over the URL.

To swap a logo for every new widget at once, replace the file in
`assets/logos/` keeping the same filename.
