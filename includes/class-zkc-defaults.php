<?php
/**
 * Zorgkosten Cost Calculator – default data (insurers, policies, tariff bases).
 *
 * All pricing lives here: reimbursement percentages (and ranges), tariff
 * bases and the per-insurer declaration/authorization links
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZKC_Defaults {

	public static function insurers() {
		// Logos ship with the plugin so the calculator does not depend on an
		// external CDN. Uploading a logo in the repeater still overrides these.
		$logos = ZKC_URL . 'assets/logos/';

		$rows = [
			// group, name, logo, agreement, machtiging, declare url, machtiging url
			[ 'Achmea', 'FBTO', 'fbto.svg', 'yes', '', 'https://www.fbto.nl/zorgverzekering/declaratie', '' ],
			[ 'Achmea', 'De Friesland', 'de-friesland.svg', 'yes', '', 'https://www.defriesland.nl/', '' ],
			[ 'Achmea', 'Interpolis', 'interpolis.svg', 'yes', '', 'https://www.interpolis.nl/service', '' ],
			[ 'Achmea', 'Zilveren Kruis', 'zilveren-kruis.svg', 'yes', '', 'https://www.zilverenkruis.nl/consumenten/service/declareren', '' ],
			[ 'Achmea', 'ZieZo (label Zilveren Kruis)', 'ziezo.svg', 'yes', '', 'https://www.zilverenkruis.nl/ziezo', '' ],
			[ 'Achmea', 'De Christelijke Zorgverzekeraar (label Zilveren Kruis)', 'de-christelijke.svg', 'yes', '', 'https://www.dechristelijkezorgverzekeraar.nl/service-en-contact', '' ],
			[ 'VGZ', 'Univé', 'unive.svg', 'yes', '', 'https://www.unive.nl/zorgverzekering/declareren', '' ],
			[ 'VGZ', 'VGZ', 'vgz.png', 'yes', '', 'https://www.vgz.nl/service-en-contact/declareren', '' ],
			[ 'VGZ', 'VGZbewuzt', 'vgzbewuzt.png', 'yes', '', 'https://www.vgzbewuzt.nl/service-en-contact/declareren', '' ],
			[ 'VGZ', 'ZEKUR', 'zekur.svg', 'yes', '', 'https://www.zekur.nl/klantenservice/zorgkosten-declareren', '' ],
			[ 'VGZ', 'United Consumers', 'united-consumers.svg', 'yes', '', 'https://www.unitedconsumers.com/zorgverzekering', '' ],
			[ 'VGZ', 'IZA', 'iza.png', 'yes', '', 'https://www.iza.nl/service-en-contact/declareren', '' ],
			[ 'VGZ', 'UMC Zorgverzekering', 'umc-zorgverzekering.png', 'yes', '', 'https://www.umczorgverzekering.nl/service-en-contact/declareren', '' ],
			[ 'VGZ', 'IZZ Zorgverzekering', 'izz-zorgverzekering.png', 'yes', '', 'https://izz.nl/', '' ],
			[ 'CZ', 'Nationale-Nederlanden', 'nationale-nederlanden.svg', '', '', 'https://www.nn.nl/Particulier/Klantenservice/Klantenservice-Zorgverzekering.htm', '' ],
			[ 'CZ', 'Ohra', 'ohra.svg', '', '', 'https://www.ohra.nl/zorgverzekering/declareren', '' ],
			[ 'CZ', 'CZ', 'cz.svg', '', '', 'https://www.cz.nl/service-en-contact/zorgkosten-declareren', '' ],
			[ 'CZ', 'CZdirect (label CZ)', 'cz.svg', '', '', 'https://czdirect.cz.nl/', '' ],
			[ 'CZ', 'Just (label CZ)', 'just.svg', '', '', 'https://www.cz.nl/service-en-contact/zorgkosten-declareren', '' ],
			[ 'Menzis', 'VinkVink', 'vinkvink.svg', '', '', 'https://www.vinkvink.nl/', '' ],
			[ 'Menzis', 'Anderzorg', 'anderzorg.svg', '', '', 'https://www.anderzorg.nl/klantenservice', '' ],
			[ 'Menzis', 'Menzis', 'menzis.svg', '', '', 'https://www.menzis.nl/klantenservice/je-zorgkosten-declareren', '' ],
			[ 'DSW', 'DSW', 'dsw.svg', '', '', 'https://www.dsw.nl/consumenten/declareren', '' ],
			[ 'DSW', 'Stad Holland', 'stad-holland.svg', '', '', 'https://www.stadholland.nl/consumenten/declareren', '' ],
			[ 'a.s.r.', 'a.s.r.', 'asr.svg', '', 'yes', 'https://www.asr.nl/', 'https://www.asr.nl/verzekeringen/zorgverzekering/toestemming-vragen' ],
			[ 'a.s.r.', 'Ik kies zelf van a.s.r.', 'ik-kies-zelf-asr.svg', '', 'yes', 'https://www.asr.nl/ikkieszelf', 'https://www.asr.nl/verzekeringen/zorgverzekering/toestemming-vragen' ],
			[ 'Zorg en Zekerheid', 'Zorg en Zekerheid', 'zorg-en-zekerheid.svg', '', 'yes', 'https://www.zorgenzekerheid.nl/declareren/', 'https://www.zorgenzekerheid.nl/service-en-contact' ],
			[ 'ONVZ', 'ONVZ', 'onvz.png', 'yes', 'yes', 'https://www.onvz.nl/declareren', 'https://www.onvz.nl/snel-regelen/toestemming-vragen' ],
			[ 'ONVZ', 'VvAA', 'vvaa.jpeg', 'yes', 'yes', 'https://www.vvaa.nl/verzekeringen/zorgverzekering', 'https://www.vvaa.nl/verzekeringen/zorgverzekering/toestemming-vragen' ],
			[ 'Salland', 'Salland', 'salland.png', '', 'yes', 'https://www.salland.nl/service-contact/declareren', 'https://www.salland.nl/toestemming' ],
			[ 'Eucare', 'Aevitae', 'aevitae.webp', '', 'yes', 'https://www.aevitae.com/', 'https://www.aevitae.com/zorgzaken-regelen/toestemming-aanvragen/' ],
			[ 'Eucare', 'Care4life', 'care4life.png', '', 'yes', 'https://www.care4life.nl/service-contact/declaratie-indienen/', 'https://www.care4life.nl/service-contact/formulieren/' ],
		];

		$defaults = [];
		foreach ( $rows as $r ) {
			$defaults[] = [
				'ins_group'          => $r[0],
				'ins_name'           => $r[1],
				'ins_logo'           => [ 'url' => $logos . $r[2] ],
				'ins_agreement'      => $r[3],
				'ins_machtiging'     => $r[4],
				'ins_declare_url'    => $r[5],
				'ins_machtiging_url' => $r[6],
			];
		}
		return $defaults;
	}

	public static function policies() {
		$rows = [
			// insurer, policy, % (lowest), % (highest, '' = single), basis, note
			[ 'FBTO', 'Zorgverzekering Basis', 65, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'FBTO', 'Zorgverzekering Basis Plus', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'FBTO', 'Zorgverzekering Basis Vrij', 75, '', 'marktconform', 'van het marktconforme of wettelijke tarief bij de meeste zorgverleners' ],
			[ 'De Friesland', 'Zelf Bewust Polis', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'De Friesland', 'Alles Verzorgd Polis', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Interpolis', 'ZorgCompact', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Interpolis', 'ZorgActief', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Zilveren Kruis', 'Basis Budget', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Zilveren Kruis', 'Basis Zeker', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Zilveren Kruis', 'Basis Exclusief', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'ZieZo (label Zilveren Kruis)', 'Basis', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'De Christelijke Zorgverzekeraar (label Zilveren Kruis)', 'Principe Polis', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Select', 60, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Basis Polis', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Geregeld Polis', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Uitgebreid Polis', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZ', 'Basis Keuze', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZ', 'Ruime Keuze', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZ', 'Eigen Keuze', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZbewuzt', 'Basis', 60, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'ZEKUR', 'Zorg Basis', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'ZEKUR', 'Zorg Plus', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Bewuste Keuze', 60, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Basis Keuze', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Ruime Keuze', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Eigen Keuze', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'IZA', 'Basis Keuze', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'IZA', 'Ruime Keuze', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'IZA', 'Eigen Keuze', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'UMC Zorgverzekering', 'Ruime Keuze', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'UMC Zorgverzekering', 'Eigen Keuze', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Bewuzt', 60, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Basis', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Natura', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Combinatie', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Nationale-Nederlanden', 'Zorg Voordelig', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Nationale-Nederlanden', 'Zorg Vrij', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Ohra', 'Zorgverzekering Combinatie', 75, '', 'maximumtarief', 'rekening houdend met het maximumtarief' ],
			[ 'CZ', 'Zorgbewust polis', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'CZ', 'Zorg-op-maatpolis', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'CZ', 'Zorgvariatiepolis', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'CZdirect (label CZ)', 'CZdirect', 65, '', 'afgesproken_andere_zorgverleners', '' ],
			[ 'Just (label CZ)', 'Basic', 60, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'VinkVink', 'Basisverzekering', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Anderzorg', 'Anderzorg Basis', 60, 100, 'gemiddeld_gecontracteerd', '60-100% afhankelijk van soort zorg' ],
			[ 'Menzis', 'Basis Voordelig', 70, '', 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'Menzis', 'Basis', 70, '', 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'Menzis', 'Basis Vrij', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'DSW', 'Zorgpolis', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Stad Holland', 'Zorgpolis', 75, '', 'wmg_nza', '' ],
			[ 'a.s.r.', 'Bewuste keuze', 65, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'a.s.r.', 'Ruime keuze', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'a.s.r.', 'Eigen keuze', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Ik kies zelf van a.s.r.', 'Goede keuze', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Ik kies zelf van a.s.r.', 'Juiste Keuze', 65, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Ik kies zelf van a.s.r.', 'Vrije Keuze', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Zorg en Zekerheid', 'Zorg Gemak Polis', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Zorg en Zekerheid', 'Zorg Zeker Polis', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Zorg en Zekerheid', 'Zorg Vrij Polis', 80, '', 'marktconform', 'van het in de markt gebruikelijke tarief' ],
			[ 'ONVZ', 'Bewuste Keuze', 65, '', 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'ONVZ', 'Vrije Keuze', 75, '', 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'VvAA', 'Zorgverzekering', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'VvAA', 'Natura zorgverzekering', 70, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Salland', 'Basispolis', 85, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Aevitae', 'Basisverzekering Natura Select', 65, '', 'afgesproken_andere_zorgverleners', '' ],
			[ 'Aevitae', 'Basisverzekering Natura', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Aevitae', 'Basisverzekering Combinatie', 80, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Care4life', 'Basisverzekering Natura Select', 65, '', 'afgesproken_andere_zorgverleners', '' ],
			[ 'Care4life', 'Basisverzekering Natura', 75, '', 'gemiddeld_gecontracteerd', '' ],
			[ 'Care4life', 'Basisverzekering Combinatie', 80, '', 'gemiddeld_gecontracteerd', '' ],
		];

		$defaults = [];
		foreach ( $rows as $r ) {
			$defaults[] = [
				'pol_insurer'        => $r[0],
				'pol_name'           => $r[1],
				'pol_percentage'     => $r[2],
				'pol_percentage_max' => $r[3],
				'pol_basis'          => $r[4],
				'pol_note'           => $r[5],
			];
		}
		return $defaults;
	}

	public static function bases() {
		return [
			[
				'basis_key'   => 'gemiddeld_gecontracteerd',
				'basis_label' => 'gemiddeld gecontracteerd tarief',
			],
			[
				'basis_key'   => 'wmg_nza',
				'basis_label' => 'NZa-tarief (Wmg-tarief)',
			],
			[
				'basis_key'   => 'marktconform',
				'basis_label' => 'marktconform tarief',
			],
			[
				'basis_key'   => 'maximumtarief',
				'basis_label' => 'maximumtarief van de verzekeraar',
			],
			[
				'basis_key'   => 'afgesproken_andere_zorgverleners',
				'basis_label' => 'tarief dat de verzekeraar heeft afgesproken met andere zorgverleners',
			],
		];
	}

	public static function basis_options() {
		return [
			'gemiddeld_gecontracteerd'         => esc_html__( 'Gemiddeld gecontracteerd tarief', 'zorgkosten-calculator' ),
			'wmg_nza'                          => esc_html__( 'Wettelijk / Wmg-tarief (NZa)', 'zorgkosten-calculator' ),
			'marktconform'                     => esc_html__( 'Marktconform tarief', 'zorgkosten-calculator' ),
			'maximumtarief'                    => esc_html__( 'Maximumtarief van de verzekeraar', 'zorgkosten-calculator' ),
			'afgesproken_andere_zorgverleners' => esc_html__( 'Tarief afgesproken met andere zorgverleners', 'zorgkosten-calculator' ),
		];
	}
}
