# Zorgkosten Calculator for Elementor

Custom Elementor addon that rebuilds the multi-step healthcare cost calculator from
https://zorgkosten-inzicht.lovable.app/ as a fully backend-editable widget.

## Install

1. Copy the `zorgkosten-calculator` folder into `wp-content/plugins/`.
2. Activate **Zorgkosten Calculator for Elementor** in WP Admin → Plugins.
3. In Elementor, search for the **Zorgkosten Calculator** widget (category "Zorgkosten") and drop it on a page.

Requires Elementor 3.5+ (tested with 4.2.1).

## What is editable from Elementor

Everything ships pre-filled with the exact content and data of the original app.

**Content tab**
- **General** – brand name, "start over" link, step counter format, Back/Next labels, help prefix, "I don't know" label, footer.
- **Intro screen** – kicker, title, description, start button.
- **Insurers** (repeater) – group/concern name, insurer name, logo (media upload), payment-agreement toggle (betaalovereenkomst), authorization toggle (machtiging). 32 insurers pre-filled.
- **Policies & reimbursement** (repeater) – insurer name (must match an insurer), policy name, reimbursement %, tariff basis, optional note. All 65+ policies pre-filled with the original percentages. Plus fallback % and basis for "I don't know".
- **Step 1–8** – every title, help text, description, WYSIWYG content block, both step-7 variants (with/without payment agreement), machtiging section, coulance conditions/exclusions (one per line).
- **Calculation** – average invoice amount (€2000), personal contribution (€250), deductible options (385–885), default deductible for "unknown".
- **Result screen** – every label and the summary paragraph with `{invoice} {reimbursed} {waived} {contribution} {deductible} {total}` placeholders, disclaimer, restart + sign-up buttons (sign-up URL is a link control).

**Style tab**
- Colors (primary, dark, headings, text, muted, page/card background, borders)
- Card radius, max width
- Typography (step titles, body)
- Button text color + radius

## Calculation logic (same as the original)

- Reimbursed = average invoice × policy % (default 70% when policy unknown)
- Waived (coulance) = average invoice − reimbursed
- Remaining deductible = chosen deductible − already-used amount (min 0; unknown deductible → default €385, unknown used → €0)
- Total own costs = personal contribution + remaining deductible

## Insurer logos

Each insurer row has two logo fields: a **Logo** media control (upload to the
site's own media library — recommended for production) and a **Logo URL
(fallback)** text field, pre-filled with the original app's logo URLs so the
widget looks right out of the box. An uploaded logo always wins over the URL.
The original URLs for reference:

| Insurer | Original logo URL |
|---|---|
| FBTO | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/57775fcc-c437-422e-b6fa-fe93f9224647/FBTO-logo-liggend-2019.svg |
| De Friesland | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/a9a5962f-321c-4960-beba-185b3340383c/logo_defriesland.svg |
| Interpolis | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/3a766be2-1e77-40dc-8c31-7a959841db99/logo_interpolis.svg |
| Zilveren Kruis | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/274d913b-5677-42f7-a5f6-955065909042/logo_zilverenkruis.svg |
| ZieZo | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/921f9603-42ac-4785-ac3b-1010a2813e54/ziezo.svg |
| De Christelijke Zorgverzekeraar | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/e0da6dcb-948c-4483-b793-caac4e7a51eb/logo_dechristelijkezorgverzekeraar.svg |
| Univé | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/dfccd5c1-029a-405b-9e12-9a15ec6b2c6a/unive-logo-payoff.svg |
| VGZ | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/fb112d4c-096c-41b4-960d-979d2078afc3/Logo_VGZ_nieuw.png |
| VGZbewuzt | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/fde61534-f309-4204-a391-b72daf65fb0c/VGZbewuzt_logo_RGB_2022.png |
| ZEKUR | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/db7b7145-a294-4fe3-8156-9140d8c2fc17/zekur_logo.svg |
| United Consumers | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/36734bfd-4f8d-45da-b6ed-69c15902a513/unitedconsumers-logo-uc.svg |
| IZA | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/a618c5eb-5bb9-4007-8caf-2ad2fed2b41c/IZA_van_VGZ_Compact.png |
| UMC Zorgverzekering | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/e3061a30-8796-4ffc-bdb3-5d1c14b7c71e/Logo_umczorgverzekering.png |
| IZZ Zorgverzekering | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/07c87920-c7ef-40b3-b7e0-9cbc2a0a47f0/IZZCombinatielogo_IZZ-VGZ_FC_RGB-1.png |
| Nationale-Nederlanden | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/294a0dd1-4353-4f02-b7cd-f61ac4052622/logo-nationalenederlanden.svg |
| Ohra | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/5269230b-1017-4f46-8c49-8c89441fe723/logo-ohra.svg |
| CZ / CZdirect | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/1923c4eb-d1f9-49a0-96f3-eaac393ab145/logo_CZ.svg |
| Just | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/d74972fd-d9d0-4923-af27-f2c44e8c622d/logo-JUSTCZ.svg |
| VinkVink | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/f7c2d8ad-dda1-49d7-8662-6377a9adc910/logo-VINKVINK.svg |
| Anderzorg | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/97f74821-1a8f-4739-b0eb-2f0525e7f13d/logo-ANDERZORG.svg |
| Menzis | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/c2bb9a93-b29d-4888-99a0-4012ebf1c521/logo-MENZIS.svg |
| DSW | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/3b9cb72f-648a-4fca-8d77-daa4a5db1738/dsw-tablet-plus-logo.svg |
| Stad Holland | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/1256f2cc-f02f-466c-8318-db57ced14bd6/stadholland-tablet-plus-logo.svg |
| a.s.r. | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/87bbac93-73d4-457e-81b7-e2cf0897f018/a.s.r.zorgverzekering.svg |
| Ik kies zelf van a.s.r. | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/e002c0b2-1d3d-4b56-9135-a7b80dd7f88f/ikkieszelfasr.svg |
| Zorg en Zekerheid | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/db93c499-421b-49c8-9238-10e97234d852/logo-zorgenzekerheid.svg |
| ONVZ | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/514c3577-9f33-48bd-82f3-69185cc517b4/onvz-logo.png |
| VvAA | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/10f53aef-e311-40c9-9697-21a6af1fd3e3/idwzGV5qgk_1785212821621.jpeg |
| Salland | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/0165638b-2ad7-4d98-a023-2a729bbcb2be/Logo-Salland-Zorgverzekeraar.png |
| Aevitae | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/21f057e0-e6f7-4286-84e6-cd1e96cefa18/logo-aevitae.webp |
| Care4life | https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/7e04e2d5-a118-4525-bba7-3ef57b0a8fe4/logo-care4life.png |
