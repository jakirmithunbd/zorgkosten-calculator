<?php
/**
 * Zorgkosten Cost Calculator – Elementor Widget (v2 design).
 *
 * Rebuilds the redesigned zorgkosten-inzicht app as a widget: two-column
 * layout with a "Uw gegevens" sidebar, segmented progress bar, per-insurer
 * declaration/authorization links and a fully reworked result screen.
 * Every text, amount, image and link is editable from the Elementor editor.
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

class ZKC_Cost_Calculator_Widget extends Widget_Base {

	public function get_name() {
		return 'zkc_cost_calculator';
	}

	public function get_title() {
		return esc_html__( 'Zorgkosten Calculator', 'zorgkosten-calculator' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	public function get_categories() {
		return [ 'zorgkosten', 'general' ];
	}

	public function get_keywords() {
		return [ 'calculator', 'zorg', 'kosten', 'steps', 'wizard', 'verzekering' ];
	}

	public function get_style_depends() {
		return [ 'zkc-calculator' ];
	}

	public function get_script_depends() {
		return [ 'zkc-calculator' ];
	}

	/* -------------------------------------------------------------------------
	 * Default data
	 * ---------------------------------------------------------------------- */

	private function default_insurers() {
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
			[ 'a.s.r.', 'a.s.r.', 'asr.svg', '', 'yes', 'https://www.asr.nl/', '' ],
			[ 'a.s.r.', 'Ik kies zelf van a.s.r.', 'ik-kies-zelf-asr.svg', '', 'yes', 'https://www.asr.nl/ikkieszelf', '' ],
			[ 'Zorg en Zekerheid', 'Zorg en Zekerheid', 'zorg-en-zekerheid.svg', '', 'yes', 'https://www.zorgenzekerheid.nl/declareren/', 'https://www.zorgenzekerheid.nl/klantenservice/' ],
			[ 'ONVZ', 'ONVZ', 'onvz.png', 'yes', 'yes', 'https://www.onvz.nl/declareren', 'https://www.onvz.nl/klantenservice/machtiging-aanvragen' ],
			[ 'ONVZ', 'VvAA', 'vvaa.jpeg', 'yes', 'yes', 'https://www.vvaa.nl/verzekeringen/zorgverzekering', '' ],
			[ 'Salland', 'Salland', 'salland.png', '', 'yes', 'https://www.salland.nl/service-contact/declareren', '' ],
			[ 'Eucare', 'Aevitae', 'aevitae.webp', '', 'yes', 'https://www.aevitae.com/', '' ],
			[ 'Eucare', 'Care4life', 'care4life.png', '', 'yes', 'https://www.care4life.nl/service-contact/declaratie-indienen/', '' ],
		];

		$defaults = [];
		foreach ( $rows as $r ) {
			$defaults[] = [
				'ins_group'          => $r[0],
				'ins_name'           => $r[1],
				'ins_logo_url'       => $logos . $r[2],
				'ins_agreement'      => $r[3],
				'ins_machtiging'     => $r[4],
				'ins_declare_url'    => $r[5],
				'ins_machtiging_url' => $r[6],
			];
		}
		return $defaults;
	}

	private function default_policies() {
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

	private function default_bases() {
		return [
			[
				'basis_key'   => 'gemiddeld_gecontracteerd',
				'basis_label' => 'gemiddeld gecontracteerd tarief',
				'basis_title' => 'Gemiddeld gecontracteerd tarief',
				'basis_text'  => 'De zorgverzekeraar maakt afspraken met zorgverleners over het tarief voor bepaalde zorg, bijvoorbeeld voor een behandeling in het ziekenhuis. Op basis van deze tarieven wordt het gemiddeld gecontracteerd tarief en de maximale vergoeding berekend.',
			],
			[
				'basis_key'   => 'wmg_nza',
				'basis_label' => 'NZa-tarief (Wmg-tarief)',
				'basis_title' => 'Wettelijk of Wmg-tarief',
				'basis_text'  => 'Soms bepaalt de overheid het tarief voor een soort zorg. Dat is het wettelijk tarief of Wmg-tarief. Zorgverzekeraars gebruiken dit tarief als er geen tarieven zijn afgesproken met zorgverleners voor de zorg die u nodig heeft.',
			],
			[
				'basis_key'   => 'marktconform',
				'basis_label' => 'marktconform tarief',
				'basis_title' => 'Marktconform tarief',
				'basis_text'  => 'Het marktconform tarief is een tarief dat in Nederland redelijk is voor een bepaalde behandeling. De verzekeraar kijkt daarvoor naar wat zorgverleners rekenen voor een bepaalde behandeling en berekent hiermee het marktconforme tarief.',
			],
			[
				'basis_key'   => 'maximumtarief',
				'basis_label' => 'maximumtarief van de verzekeraar',
				'basis_title' => 'Maximumtarief van de verzekeraar',
				'basis_text'  => 'De zorgverzekeraar hanteert een eigen vastgesteld maximumtarief. Boven dit bedrag wordt niets vergoed, ook niet als de werkelijke kosten hoger zijn.',
			],
			[
				'basis_key'   => 'afgesproken_andere_zorgverleners',
				'basis_label' => 'tarief dat de verzekeraar heeft afgesproken met andere zorgverleners',
				'basis_title' => 'Tarief afgesproken met andere zorgverleners',
				'basis_text'  => 'De vergoeding wordt berekend op basis van tarieven die uw zorgverzekeraar heeft afgesproken met andere, wél gecontracteerde zorgverleners voor vergelijkbare zorg.',
			],
		];
	}

	private function basis_options() {
		return [
			'gemiddeld_gecontracteerd'         => esc_html__( 'Gemiddeld gecontracteerd tarief', 'zorgkosten-calculator' ),
			'wmg_nza'                          => esc_html__( 'Wettelijk / Wmg-tarief (NZa)', 'zorgkosten-calculator' ),
			'marktconform'                     => esc_html__( 'Marktconform tarief', 'zorgkosten-calculator' ),
			'maximumtarief'                    => esc_html__( 'Maximumtarief van de verzekeraar', 'zorgkosten-calculator' ),
			'afgesproken_andere_zorgverleners' => esc_html__( 'Tarief afgesproken met andere zorgverleners', 'zorgkosten-calculator' ),
		];
	}

	/* -------------------------------------------------------------------------
	 * Controls
	 * ---------------------------------------------------------------------- */

	protected function register_controls() {
		$this->register_intro_controls();
		$this->register_insurer_controls();
		$this->register_policy_controls();
		$this->register_navigation_controls();
		$this->register_step_text_controls();
		$this->register_machtiging_controls();
		$this->register_coulance_controls();
		$this->register_result_controls();
		$this->register_calculation_controls();
		$this->register_illustration_controls();
		$this->register_style_controls();
	}

	private function register_intro_controls() {
		$this->start_controls_section( 'section_intro', [
			'label' => esc_html__( 'Intro screen', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'intro_kicker', [
			'label'       => esc_html__( 'Kicker (small title)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Persoonlijke kostenindicatie',
			'label_block' => true,
		] );

		$this->add_control( 'intro_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Bereken uw kosten voor uw zorg',
			'label_block' => true,
		] );

		$this->add_control( 'intro_text', [
			'label'   => esc_html__( 'Description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Beantwoord enkele vragen over uw zorgverzekering en ontvang een persoonlijke inschatting van de kosten voor uw traject bij ADHD Medisch Centrum, inclusief eigen risico, de vergoeding van uw verzekeraar en onze coulanceregeling.',
		] );

		$this->add_control( 'intro_bullets', [
			'label'       => esc_html__( 'Bullet points (one per line)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 5,
			'default'     => "In 2 minuten klaar, zonder persoonsgegevens\nGebaseerd op de NZa-tarieven en uw polisvoorwaarden\nEen indicatie van de vergoeding, op basis van de voorwaarden van uw verzekeraar",
		] );

		$this->add_control( 'intro_button', [
			'label'   => esc_html__( 'Start button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Bereken uw kosten',
		] );

		$this->add_control( 'intro_image', [
			'label'       => esc_html__( 'Illustration', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Shown next to the intro text. The bundled image is used when empty.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::MEDIA,
			'default'     => [ 'url' => '' ],
		] );

		$this->add_control( 'intro_image_alt', [
			'label'   => esc_html__( 'Illustration alt text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Rekenmachine met euromunten',
			'label_block' => true,
		] );

		$this->end_controls_section();
	}

	private function register_insurer_controls() {
		$this->start_controls_section( 'section_insurers', [
			'label' => esc_html__( 'Insurers', 'zorgkosten-calculator' ),
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'ins_group', [
			'label'       => esc_html__( 'Group (concern)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'description' => esc_html__( 'Insurers with the same group name are shown under one heading.', 'zorgkosten-calculator' ),
		] );

		$repeater->add_control( 'ins_name', [
			'label'       => esc_html__( 'Insurer name', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'label_block' => true,
		] );

		$repeater->add_control( 'ins_logo', [
			'label'   => esc_html__( 'Logo', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => [ 'url' => '' ],
		] );

		$repeater->add_control( 'ins_logo_url', [
			'label'       => esc_html__( 'Logo URL (fallback)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'description' => esc_html__( 'Used when no logo is uploaded above. Pre-filled with the bundled logo.', 'zorgkosten-calculator' ),
		] );

		$repeater->add_control( 'ins_agreement', [
			'label'       => esc_html__( 'Payment agreement (betaalovereenkomst)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => '',
			'description' => esc_html__( 'Controls the invoice step and the "Wat u zelf regelt" card on the result.', 'zorgkosten-calculator' ),
		] );

		$repeater->add_control( 'ins_machtiging', [
			'label'       => esc_html__( 'Authorization (machtiging) may be required', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => '',
			'description' => esc_html__( 'Adds the authorization step for this insurer.', 'zorgkosten-calculator' ),
		] );

		$repeater->add_control( 'ins_declare_url', [
			'label'       => esc_html__( 'Declaration page URL', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'description' => esc_html__( 'Used for the "Declareren bij …" button on the result screen.', 'zorgkosten-calculator' ),
		] );

		$repeater->add_control( 'ins_machtiging_url', [
			'label'       => esc_html__( 'Authorization info URL (optional)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'description' => esc_html__( 'Used for the "Naar …" button on the authorization step. Falls back to the declaration URL.', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'insurers', [
			'label'       => esc_html__( 'Insurers', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => $this->default_insurers(),
			'title_field' => '{{{ ins_group }}} — {{{ ins_name }}}',
		] );

		$this->end_controls_section();
	}

	private function register_policy_controls() {
		$this->start_controls_section( 'section_policies', [
			'label' => esc_html__( 'Policies & reimbursement', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'policies_note', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'The "Insurer name" of each policy must exactly match a name in the Insurers list.', 'zorgkosten-calculator' ),
			'content_classes' => 'elementor-descriptor',
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'pol_insurer', [
			'label'       => esc_html__( 'Insurer name', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$repeater->add_control( 'pol_name', [
			'label'       => esc_html__( 'Policy name', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$repeater->add_control( 'pol_percentage', [
			'label'       => esc_html__( 'Reimbursement %', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'For a range, this is the lowest percentage.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 100,
			'default'     => 70,
		] );

		$repeater->add_control( 'pol_percentage_max', [
			'label'       => esc_html__( 'Reimbursement % (highest, optional)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Leave empty for a single percentage. Fill it in to show a range such as 60% tot 100%. The estimated amounts then use the range.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 100,
			'default'     => '',
		] );

		$repeater->add_control( 'pol_basis', [
			'label'   => esc_html__( 'Tariff basis', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SELECT,
			'options' => $this->basis_options(),
			'default' => 'gemiddeld_gecontracteerd',
		] );

		$repeater->add_control( 'pol_note', [
			'label'       => esc_html__( 'Note (optional)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$this->add_control( 'policies', [
			'label'       => esc_html__( 'Policies', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => $this->default_policies(),
			'title_field' => '{{{ pol_insurer }}} — {{{ pol_name }}} ({{{ pol_percentage }}}%)',
		] );

		$this->add_control( 'heading_unknown_policy', [
			'label'     => esc_html__( 'Fallback (insurer unknown)', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'default_percentage', [
			'label'       => esc_html__( 'Default reimbursement % (no insurer/policies)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'When the chosen insurer has policies, the unknown-policy estimate uses the lowest and highest percentage of that insurer instead.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 100,
			'default'     => 70,
		] );

		$this->add_control( 'default_basis', [
			'label'   => esc_html__( 'Default tariff basis', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SELECT,
			'options' => $this->basis_options(),
			'default' => 'gemiddeld_gecontracteerd',
		] );

		$this->end_controls_section();
	}

	private function register_navigation_controls() {
		$this->start_controls_section( 'section_navigation', [
			'label' => esc_html__( 'Navigation & sidebar', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'back_label', [
			'label'   => esc_html__( '"Back" text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Terug',
		] );

		$this->add_control( 'next_label', [
			'label'   => esc_html__( '"Next" button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Volgende',
		] );

		$this->add_control( 'restart_label', [
			'label'   => esc_html__( '"Start over" link text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Opnieuw beginnen',
		] );

		$this->add_control( 'adjust_label', [
			'label'   => esc_html__( '"Adjust calculation" link text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Berekening aanpassen',
			'label_block' => true,
		] );

		$this->add_control( 'step_counter_text', [
			'label'       => esc_html__( 'Step counter text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {current} and {total}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Stap {current} van {total}',
		] );

		$this->add_control( 'help_label', [
			'label'   => esc_html__( 'Help box label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Hulp',
		] );

		$this->add_control( 'warning_label', [
			'label'   => esc_html__( 'Warning box label ("Let op")', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Let op',
		] );

		$this->add_control( 'unknown_label', [
			'label'   => esc_html__( '"I don\'t know" option text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Ik weet het niet',
		] );

		$this->add_control( 'fallback_insurer', [
			'label'       => esc_html__( 'Fallback insurer wording', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Used in sentences when no insurer is chosen.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'uw zorgverzekeraar',
			'label_block' => true,
		] );

		$this->add_control( 'heading_sidebar', [
			'label'     => esc_html__( 'Sidebar ("Uw gegevens")', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'sidebar_title', [
			'label'   => esc_html__( 'Sidebar title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw gegevens',
		] );

		$this->add_control( 'sidebar_empty', [
			'label'   => esc_html__( 'Empty text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Nog niets ingevuld',
			'label_block' => true,
		] );

		$this->add_control( 'sidebar_chip_title', [
			'label'   => esc_html__( 'Chip tooltip', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wijzig deze stap',
			'label_block' => true,
		] );

		$this->add_control( 'sidebar_chip_deductible', [
			'label'       => esc_html__( 'Deductible chip text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {amount}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Eigen risico {amount}',
			'label_block' => true,
		] );

		$this->add_control( 'sidebar_chip_used', [
			'label'       => esc_html__( 'Used deductible chip text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {amount}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Al gebruikt {amount}',
			'label_block' => true,
		] );

		$this->end_controls_section();
	}

	private function register_step_text_controls() {

		/* ---- Step 1: insurer ---- */
		$this->start_controls_section( 'section_step1', [
			'label' => esc_html__( 'Step 1 – Insurer', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step1_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Bij welke zorgverzekeraar bent u verzekerd?',
			'label_block' => true,
		] );

		$this->add_control( 'step1_help', [
			'label'   => esc_html__( 'Help text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'U vindt de naam van uw zorgverzekeraar op uw zorgpas, polisblad of in de app van uw zorgverzekeraar.',
		] );

		$this->end_controls_section();

		/* ---- Step 2: policy ---- */
		$this->start_controls_section( 'section_step2', [
			'label' => esc_html__( 'Step 2 – Policy', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step2_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Welk type basisverzekering heeft u?',
			'label_block' => true,
		] );

		$this->add_control( 'step2_subtitle', [
			'label'       => esc_html__( 'Subtitle', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer} for the chosen insurer.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Beschikbare polissen bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'step2_help', [
			'label'   => esc_html__( 'Help text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'U vindt het type basisverzekering op uw polisblad of in de app of online omgeving van uw zorgverzekeraar.',
		] );

		$this->end_controls_section();

		/* ---- Step 3: deductible ---- */
		$this->start_controls_section( 'section_step3', [
			'label' => esc_html__( 'Step 3 – Deductible (eigen risico)', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step3_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Hoe hoog is uw totale eigen risico?',
			'label_block' => true,
		] );

		$this->add_control( 'step3_text', [
			'label'   => esc_html__( 'Description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Het eigen risico is het bedrag dat u eerst zelf betaalt voor zorg uit de basisverzekering. Dit bedrag wordt door uw zorgverzekeraar verrekend en staat los van de persoonlijke bijdrage aan ADHD Medisch Centrum.',
		] );

		$this->add_control( 'step3_help', [
			'label'   => esc_html__( 'Help text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Het totale eigen risico bestaat uit het verplichte eigen risico en eventueel een vrijwillig verhoogd eigen risico.',
		] );

		$repeater = new Repeater();
		$repeater->add_control( 'ded_amount', [
			'label'   => esc_html__( 'Amount (€)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 0,
			'default' => 385,
		] );

		$this->add_control( 'deductible_options', [
			'label'       => esc_html__( 'Deductible options', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'ded_amount' => 385 ],
				[ 'ded_amount' => 485 ],
				[ 'ded_amount' => 585 ],
				[ 'ded_amount' => 685 ],
				[ 'ded_amount' => 785 ],
				[ 'ded_amount' => 885 ],
			],
			'title_field' => '€ {{{ ded_amount }}}',
		] );

		$this->add_control( 'default_deductible', [
			'label'       => esc_html__( 'Highest deductible assumed when unknown (€)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'When the visitor does not know their total deductible, the result shows a range from € 0 up to this amount.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'default'     => 885,
		] );

		$this->end_controls_section();

		/* ---- Step 4: used deductible ---- */
		$this->start_controls_section( 'section_step4', [
			'label' => esc_html__( 'Step 4 – Used deductible', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step4_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Hoeveel van uw eigen risico heeft u dit jaar al gebruikt?',
			'label_block' => true,
		] );

		$this->add_control( 'step4_help', [
			'label'   => esc_html__( 'Help text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'U kunt dit controleren in de app of online omgeving van uw zorgverzekeraar. Houd er rekening mee dat nog niet verwerkte zorgkosten mogelijk niet zichtbaar zijn.',
		] );

		$this->add_control( 'step4_field_label', [
			'label'   => esc_html__( 'Field label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Gebruikt eigen risico',
		] );

		$this->add_control( 'step4_placeholder', [
			'label'   => esc_html__( 'Field placeholder', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'bedrag',
		] );

		$this->add_control( 'step4_error_invalid', [
			'label'       => esc_html__( 'Error: no / invalid amount', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Vul een geldig bedrag in.',
			'label_block' => true,
		] );

		$this->add_control( 'step4_error_max', [
			'label'       => esc_html__( 'Error: higher than the total deductible', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Het gebruikte bedrag kan niet hoger zijn dan uw totale eigen risico.',
			'label_block' => true,
		] );

		$this->end_controls_section();

		/* ---- Step 5: info ---- */
		$this->start_controls_section( 'section_step5', [
			'label' => esc_html__( 'Step 5 – How costs are determined', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step5_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Hoe worden de kosten van uw traject bepaald?',
			'label_block' => true,
		] );

		$this->add_control( 'step5_content', [
			'label'   => esc_html__( 'Content', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<p>De tarieven voor diagnostiek en behandeling in de specialistische GGZ worden landelijk vastgesteld door de Nederlandse Zorgautoriteit (NZa). Deze standaardtarieven gelden voor alle GGZ-instellingen; ADHD Medisch Centrum heeft hier geen invloed op.</p><p>De kosten hangen af van het type zorg en de tijd die wordt besteed. Er wordt gewerkt volgens het zorgprestatiemodel, waarbij onder andere de duur en het type contact en de betrokken zorgverleners bepalend zijn. Hierdoor kan het totale bedrag per patiënt verschillen.</p><p>Meer informatie over de tarieven vastgesteld door de NZa vindt u <a href="https://www.nza.nl/" target="_blank" rel="noopener noreferrer">hier</a>.</p>',
		] );

		$this->add_control( 'step5_panel', [
			'label'   => esc_html__( 'Panel content (framed box)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<h4>Declaratiefactuur en betalingsfactuur</h4><p>De manier waarop wij zorg declareren, hangt af van de afspraken die ADHD Medisch Centrum met uw zorgverzekeraar heeft. Wij maken onderscheid tussen:</p><ul><li><strong>Declaratiefactuur:</strong> bedoeld om de geleverde zorg bij uw zorgverzekeraar te declareren. Vermeldt de geleverde zorgprestaties en het volledige bedrag berekend op basis van NZa-tarieven.</li><li><strong>Betalingsfactuur:</strong> hierop staat het bedrag dat u zelf aan ADHD Medisch Centrum moet betalen.</li></ul><p>Een declaratiefactuur is niet automatisch een betalingsverzoek voor het volledige factuurbedrag. Een betaalovereenkomst regelt hóe de declaratie wordt ingediend en de vergoeding uitbetaald. Dit betekent niet automatisch dat uw zorg volledig wordt vergoed. Uw zorgverzekeraar bepaalt de vergoeding op basis van uw polisvoorwaarden.</p>',
		] );

		$this->end_controls_section();

		/* ---- Step 6: reimbursement ---- */
		$this->start_controls_section( 'section_step6', [
			'label' => esc_html__( 'Step 6 – Reimbursement', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step6_hero_title', [
			'label'       => esc_html__( 'Hero title', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer} for the chosen insurer.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '{insurer} vergoedt naar verwachting',
			'label_block' => true,
		] );

		$this->add_control( 'step6_hero_title_fallback', [
			'label'       => esc_html__( 'Hero title (no insurer chosen)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Uw verzekeraar vergoedt naar verwachting',
			'label_block' => true,
		] );

		$this->add_control( 'step6_hero_basis', [
			'label'       => esc_html__( 'Line under the percentage', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholder: {basis}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'van het {basis}',
			'label_block' => true,
		] );

		$this->add_control( 'step6_estimate_kicker', [
			'label'   => esc_html__( 'Estimate box kicker', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Geschatte vergoeding',
		] );

		$this->add_control( 'step6_estimate_note', [
			'label'       => esc_html__( 'Estimate box note', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholder: {invoice}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'bij een gemiddeld factuurbedrag van ± {invoice}',
			'label_block' => true,
		] );

		$this->add_control( 'step6_message', [
			'label'       => esc_html__( 'Message (policy known)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {percentage} (e.g. 70% or 60% tot 100%), {basis}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Uw zorgverzekeraar vergoedt bij deze basisverzekering naar verwachting {percentage} van het {basis} voor ongecontracteerde GGZ.',
		] );

		$this->add_control( 'step6_message_insurer_range', [
			'label'       => esc_html__( 'Message (policy unknown, insurer known)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {insurer}, {percentage}, {basis}. Uses the lowest and highest percentage of the chosen insurer.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'U weet niet welke basisverzekering u heeft. Bij {insurer} vergoeden de basisverzekeringen {percentage} van het {basis} voor ongecontracteerde GGZ. Daarom tonen wij een minimum- en maximumbedrag.',
		] );

		$this->add_control( 'step6_message_unknown', [
			'label'       => esc_html__( 'Message (no insurer / no policies)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {percentage}, {basis}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Op basis van een algemene inschatting gaan wij uit van een vergoeding van ongeveer {percentage} van het {basis} voor ongecontracteerde GGZ.',
		] );

		$this->add_control( 'step6_basis_kicker', [
			'label'   => esc_html__( '"What does this basis mean?" label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wat betekent deze basis?',
		] );

		$this->add_control( 'step6_accordion_label', [
			'label'       => esc_html__( 'Accordion label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Uitleg over alle vergoedingsbases',
			'label_block' => true,
		] );

		$repeater = new Repeater();
		$repeater->add_control( 'basis_key', [
			'label'   => esc_html__( 'Key', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'gemiddeld_gecontracteerd'         => 'gemiddeld_gecontracteerd',
				'wmg_nza'                          => 'wmg_nza',
				'marktconform'                     => 'marktconform',
				'maximumtarief'                    => 'maximumtarief',
				'afgesproken_andere_zorgverleners' => 'afgesproken_andere_zorgverleners',
			],
			'default' => 'gemiddeld_gecontracteerd',
		] );
		$repeater->add_control( 'basis_label', [
			'label'       => esc_html__( 'Inline label (used in sentences)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );
		$repeater->add_control( 'basis_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );
		$repeater->add_control( 'basis_text', [
			'label' => esc_html__( 'Description', 'zorgkosten-calculator' ),
			'type'  => Controls_Manager::TEXTAREA,
		] );

		$this->add_control( 'bases', [
			'label'       => esc_html__( 'Tariff bases (explanations)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => $this->default_bases(),
			'title_field' => '{{{ basis_title }}}',
		] );

		$this->add_control( 'step6_footnote', [
			'label'   => esc_html__( 'Footnote', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'De vergoeding kan afwijken van hetzelfde percentage van de totale factuur, omdat zorgverzekeraars soms rekenen met een eigen referentietarief.',
		] );

		$this->end_controls_section();

		/* ---- Step 7: invoices ---- */
		$this->start_controls_section( 'section_step7', [
			'label' => esc_html__( 'Step 7 – Invoices', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step7_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Hoe worden uw facturen ingediend?',
			'label_block' => true,
		] );

		$this->add_control( 'heading_step7_yes', [
			'label'     => esc_html__( 'Variant: WITH payment agreement', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step7_yes_badge', [
			'label'       => esc_html__( 'Badge text', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wij hebben een betaalovereenkomst',
			'label_block' => true,
		] );

		$this->add_control( 'step7_yes_intro', [
			'label'       => esc_html__( 'Intro', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'ADHD Medisch Centrum heeft een betaalovereenkomst met {insurer}.',
		] );

		$this->add_control( 'step7_yes_content', [
			'label'   => esc_html__( 'Content', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<h4>Wat betekent dit voor u?</h4><ul><li>Wij declareren de verzekerde zorg rechtstreeks bij uw zorgverzekeraar.</li><li>Uw zorgverzekeraar betaalt de vergoeding rechtstreeks aan ADHD Medisch Centrum.</li><li>U hoeft de zorgfactuur niet zelf bij uw zorgverzekeraar in te dienen.</li></ul>',
		] );

		$this->add_control( 'step7_yes_note_label', [
			'label'       => esc_html__( 'Note label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Let op: eigen risico',
			'label_block' => true,
		] );

		$this->add_control( 'step7_yes_note', [
			'label'   => esc_html__( 'Note text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 5,
			'default' => 'Heeft u nog verplicht of vrijwillig eigen risico openstaan? Dan brengt uw zorgverzekeraar dit bij u in rekening of verrekent uw zorgverzekeraar dit volgens de eigen voorwaarden. U ontvangt hiervoor geen aparte factuur van ADHD Medisch Centrum. Het eigen risico blijft volledig voor uw rekening en valt niet onder de coulanceregeling.',
		] );

		$this->add_control( 'heading_step7_no', [
			'label'     => esc_html__( 'Variant: WITHOUT payment agreement', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step7_no_badge', [
			'label'       => esc_html__( 'Badge text', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Geen betaalovereenkomst',
			'label_block' => true,
		] );

		$this->add_control( 'step7_no_intro', [
			'label'       => esc_html__( 'Intro', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'ADHD Medisch Centrum heeft géén betaalovereenkomst met {insurer}.',
		] );

		$this->add_control( 'step7_no_content', [
			'label'   => esc_html__( 'Content', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<h4>Wat betekent dit voor u?</h4><ul><li>U ontvangt van ADHD Medisch Centrum een digitale of papieren declaratiefactuur.</li><li>U dient deze factuur tijdig zelf in bij uw zorgverzekeraar.</li><li>U levert het volledige vergoedingenoverzicht bij ons aan.</li><li>U betaalt de vergoeding die uw zorgverzekeraar rechtstreeks aan u uitbetaalt binnen 14 dagen door aan ADHD Medisch Centrum.</li></ul><h4>Hoe dient u de factuur in?</h4><p>Omdat wij ongecontracteerde zorg leveren, dient u de factuur eerst zelf in bij uw zorgverzekeraar. Dit kan meestal via de app of website. Na beoordeling ontvangt u een declaratieoverzicht en wordt de vergoeding op uw eigen rekening gestort.</p><h4>Declaratieoverzicht</h4><p>Stuur het declaratieoverzicht per e-mail naar <a href="mailto:facturen@adhdmc.nl">facturen@adhdmc.nl</a>. Aan de hand van dit overzicht stellen wij vast:</p><ul><li>welk bedrag door uw zorgverzekeraar is vergoed;</li><li>welk bedrag met uw eigen risico is verrekend;</li><li>welk deel onder onze coulanceregeling valt;</li><li>welk totaalbedrag u aan ADHD Medisch Centrum betaalt.</li></ul>',
		] );

		$this->add_control( 'step7_no_note_label', [
			'label'       => esc_html__( 'Note label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Let op: eigen risico',
			'label_block' => true,
		] );

		$this->add_control( 'step7_no_note', [
			'label'   => esc_html__( 'Note text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 5,
			'default' => 'Uw zorgverzekeraar kan uw openstaande eigen risico met de vergoeding verrekenen. Het bedrag dat als eigen risico wordt ingehouden, blijft u aan ADHD Medisch Centrum verschuldigd. Het eigen risico valt niet onder de coulanceregeling.',
		] );

		$this->end_controls_section();
	}

	private function register_machtiging_controls() {
		$this->start_controls_section( 'section_step8_machtiging', [
			'label' => esc_html__( 'Step 8 – Authorization (machtiging)', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'machtiging_note', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'This step is only shown for insurers with "Authorization (machtiging) may be required" switched on. For every other insurer the calculator has 8 steps instead of 9.', 'zorgkosten-calculator' ),
			'content_classes' => 'elementor-descriptor',
		] );

		$this->add_control( 'step8_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Mogelijk is een machtiging nodig',
			'label_block' => true,
		] );

		$this->add_control( 'step8_content', [
			'label'   => esc_html__( 'Content', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<p>Voor verzekerden van onder andere a.s.r., Zorg en Zekerheid, ONVZ, VvAA, Salland en Aevitae kan vooraf toestemming van de zorgverzekeraar nodig zijn voordat de zorg kan starten of worden voortgezet. Deze toestemming wordt een <strong>machtiging</strong> genoemd.</p><p>U bent zelf verantwoordelijk voor het controleren of voor uw zorgverzekering, polis of behandeling een machtiging vereist is en voor het tijdig verkrijgen daarvan. ADHD Medisch Centrum verleent binnen redelijke grenzen medewerking en kan, wanneer dit mogelijk is, de machtigingsaanvraag namens u voorbereiden en indienen.</p><p>Uw zorgverzekeraar beoordeelt de aanvraag en beslist of de machtiging wordt verleend. Een aangevraagde of verleende machtiging betekent niet automatisch dat alle zorgkosten volledig worden vergoed.</p>',
		] );

		$this->add_control( 'step8_asks_title', [
			'label'       => esc_html__( 'Framed box heading', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat vragen wij van u?',
			'label_block' => true,
		] );

		$this->add_control( 'step8_asks', [
			'label'   => esc_html__( 'Framed box list (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 7,
			'default' => "Gevraagde informatie en documenten tijdig aanleveren.\nNoodzakelijke formulieren invullen en ondertekenen.\nVragen van ADHD Medisch Centrum of uw zorgverzekeraar tijdig beantwoorden.\nAanvullende informatie verstrekken wanneer daarom wordt gevraagd.\nWijzigingen in uw zorgverzekering of polis direct aan ons doorgeven.",
		] );

		$this->add_control( 'step8_check_title', [
			'label'       => esc_html__( '"Check this" box heading', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. The box is only shown when the insurer has an authorization or declaration URL.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Controleer dit bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'step8_check_text', [
			'label'       => esc_html__( '"Check this" box text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Op de website van {insurer} leest u of een machtiging nodig is en hoe u deze aanvraagt.',
		] );

		$this->add_control( 'step8_check_button', [
			'label'       => esc_html__( '"Check this" button text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Naar {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'step8_footnote', [
			'label'   => esc_html__( 'Closing warning', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => 'Wanneer een machtiging door het ontbreken van uw medewerking niet tijdig kan worden aangevraagd of verkregen, kan het zorgtraject worden beëindigd. Kosten die hierdoor niet worden vergoed, blijven voor uw rekening en vallen niet onder de coulanceregeling.',
		] );

		$this->end_controls_section();
	}

	private function register_coulance_controls() {
		$this->start_controls_section( 'section_step9', [
			'label' => esc_html__( 'Step 9 – Goodwill scheme (coulance)', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step9_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Onze coulanceregeling',
			'label_block' => true,
		] );

		$this->add_control( 'step9_intro', [
			'label'   => esc_html__( 'Intro', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<p>Wij vinden het belangrijk dat onze zorg zo toegankelijk mogelijk blijft. Daarom hanteren wij een coulanceregeling. Dit betekent dat ADHD Medisch Centrum een groot deel van de kosten die niet door uw zorgverzekeraar worden vergoed, kwijtscheldt. U hoeft dat deel dus niet zelf te betalen.</p><p>Om deze regeling mogelijk te maken, vragen wij na het adviesgesprek een eenmalige persoonlijke bijdrage van <strong>€250</strong> voor het volledige diagnostiek- en behandeltraject. Deze bijdrage staat los van het verplichte eigen risico van uw zorgverzekering.</p>',
		] );

		$this->add_control( 'step9_conditions_title', [
			'label'   => esc_html__( 'Conditions heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Voorwaarden',
		] );

		$this->add_control( 'step9_conditions', [
			'label'   => esc_html__( 'Conditions (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 10,
			'default' => "De eigen bijdrage van €250 binnen de betalingstermijn betalen.\nDeclaratiefacturen tijdig bij uw zorgverzekeraar indienen.\nVolledige vergoedingenoverzichten tijdig aan ons verstrekken.\nVergoedingen die uw zorgverzekeraar rechtstreeks aan u uitbetaalt binnen 14 dagen aan ons doorbetalen.\nUw verplichte en eventuele vrijwillige eigen risico betalen.\nOverige betalingsfacturen binnen 14 dagen betalen.\nTijdig en volledig meewerken aan declaraties en machtigingsaanvragen.\nGevraagde informatie en documenten tijdig, volledig en naar waarheid aanleveren.",
		] );

		$this->add_control( 'step9_excluded_title', [
			'label'       => esc_html__( 'Exclusions heading', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat valt niet onder de coulanceregeling?',
			'label_block' => true,
		] );

		$this->add_control( 'step9_excluded', [
			'label'   => esc_html__( 'Exclusions (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 8,
			'default' => "De eigen bijdrage van €250.\nHet verplichte en eventuele vrijwillige eigen risico.\nNo-showfacturen.\nBedragen die uw zorgverzekeraar rechtstreeks aan u heeft uitbetaald.\nZorg die niet onder de verzekerde zorg valt en waarover u vooraf bent geïnformeerd.\nKosten die niet worden vergoed doordat gevraagde informatie of medewerking ontbreekt.",
		] );

		$this->add_control( 'step9_button', [
			'label'       => esc_html__( 'Final button text', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Bekijk uw kosteninschatting',
			'label_block' => true,
		] );

		$this->end_controls_section();
	}

	private function register_result_controls() {
		$this->start_controls_section( 'section_result', [
			'label' => esc_html__( 'Result screen', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'result_kicker', [
			'label'   => esc_html__( 'Kicker', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw kostenoverzicht',
		] );

		$this->add_control( 'result_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Zo zijn uw zorgkosten opgebouwd',
			'label_block' => true,
		] );

		$this->add_control( 'result_intro', [
			'label'       => esc_html__( 'Intro', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {invoice}, {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Een volledig traject kost gemiddeld ± {invoice}. Hieronder ziet u wat {insurer} vergoedt, wat wij kwijtschelden en welk deel u zelf betaalt.',
		] );

		$this->add_control( 'heading_result_invoice', [
			'label'     => esc_html__( 'Invoice panel', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'result_invoice_title', [
			'label'       => esc_html__( 'Panel title', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholder: {invoice}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat gebeurt er met de factuur van ± {invoice}?',
			'label_block' => true,
		] );

		$this->add_control( 'result_invoice_text', [
			'label'   => esc_html__( 'Panel text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Uw werkelijke factuur kan hoger of lager zijn: dat hangt af van de zorg die u nodig heeft en de tijd die daaraan wordt besteed (NZa-tarieven, zorgprestatiemodel). De hele factuur wordt gedekt. U betaalt hier niets van zelf.',
		] );

		$this->add_control( 'result_uncertain_tooltip', [
			'label'       => esc_html__( 'Tooltip on the uncertain (striped) part', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Dit deel is nog onzeker: het hangt af van uw basisverzekering.',
			'label_block' => true,
		] );

		$this->add_control( 'result_reimbursed_label', [
			'label'       => esc_html__( 'Reimbursed label', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {insurer}, {amount}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '{insurer} vergoedt {amount}',
			'label_block' => true,
		] );

		$this->add_control( 'result_reimbursed_text', [
			'label'       => esc_html__( 'Reimbursed description', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {percentage}, {basis}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Dit deel wordt vergoed uit uw basisverzekering ({percentage} van het {basis}).',
		] );

		$this->add_control( 'result_waived_label', [
			'label'       => esc_html__( 'Waived label', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholder: {amount}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wij schelden {amount} kwijt',
			'label_block' => true,
		] );

		$this->add_control( 'result_waived_text', [
			'label'   => esc_html__( 'Waived description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Dit niet-vergoede deel vervalt via onze coulanceregeling. U krijgt hiervoor geen rekening.',
		] );

		$this->add_control( 'result_range_note_policy', [
			'label'       => esc_html__( 'Note: policy unknown (range shown)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {insurer}, {percentage}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Omdat u niet weet welk type basisverzekering u heeft, rekenen wij met de laagste en hoogste vergoeding van {insurer} ({percentage}). Daarom ziet u hier een minimum- en maximumbedrag.',
		] );

		$this->add_control( 'heading_result_own', [
			'label'     => esc_html__( 'Own costs card', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'result_own_kicker', [
			'label'   => esc_html__( 'Card kicker', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw eigen kosten voor het hele traject',
			'label_block' => true,
		] );

		$this->add_control( 'result_own_text', [
			'label'   => esc_html__( 'Card text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Dit staat los van de factuur hierboven: het zijn de twee bedragen die altijd voor uw eigen rekening komen.',
		] );

		$this->add_control( 'result_contribution_label', [
			'label'       => esc_html__( 'Contribution row label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Eenmalige persoonlijke bijdrage',
			'label_block' => true,
		] );

		$this->add_control( 'result_contribution_text', [
			'label'       => esc_html__( 'Contribution row description', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'betaalt u aan ons',
			'label_block' => true,
		] );

		$this->add_control( 'result_deductible_label', [
			'label'       => esc_html__( 'Deductible row label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Eigen risico',
			'label_block' => true,
		] );

		$this->add_control( 'result_deductible_text', [
			'label'       => esc_html__( 'Deductible row description', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'betaalt u aan {insurer}, voor alle zorg uit de basisverzekering',
			'label_block' => true,
		] );

		$this->add_control( 'result_used_unknown_note', [
			'label'   => esc_html__( 'Note: used deductible unknown', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Omdat u niet weet hoeveel eigen risico u al heeft gebruikt, tonen wij een minimum- en maximumbedrag.',
		] );

		$this->add_control( 'heading_result_self', [
			'label'     => esc_html__( '"Wat u zelf regelt" card', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'result_self_kicker', [
			'label'   => esc_html__( 'Card kicker', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wat u zelf regelt',
		] );

		$this->add_control( 'result_self_title_no', [
			'label'       => esc_html__( 'Title (no payment agreement)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'U dient de factuur zelf in bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'result_self_steps', [
			'label'       => esc_html__( 'Numbered steps (one per line)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {insurer}, {amount} (expected reimbursement).', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 5,
			'default'     => "U ontvangt van ons een declaratiefactuur.\nU dient deze in bij {insurer}, meestal via de app of het online portaal.\nU ontvangt ± {amount} van {insurer} en betaalt dit binnen 14 dagen aan ons door.",
		] );

		$this->add_control( 'result_declare_button', [
			'label'       => esc_html__( 'Declare button text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Shown when the insurer has a declaration URL.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Declareren bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'result_self_title_yes', [
			'label'       => esc_html__( 'Title (with payment agreement)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'U hoeft niets in te dienen bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'result_self_text_yes', [
			'label'       => esc_html__( 'Text (with payment agreement)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Wij hebben een betaalovereenkomst met {insurer}. Wij declareren rechtstreeks bij uw zorgverzekeraar, dus u ontvangt van ons geen declaratiefactuur en hoeft zelf niets in te dienen. Alleen uw eigen risico verrekent {insurer} met u.',
		] );

		$this->add_control( 'result_machtiging_label', [
			'label'       => esc_html__( 'Authorization warning label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Let op: machtiging',
			'label_block' => true,
		] );

		$this->add_control( 'result_machtiging_text', [
			'label'       => esc_html__( 'Authorization warning text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => '{insurer} vraagt vaak een machtiging (toestemming) vóórdat de behandeling start. Regel dit op tijd, anders kan de vergoeding worden afgewezen.',
		] );

		$this->add_control( 'result_machtiging_link', [
			'label'       => esc_html__( 'Authorization warning link text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Bekijk dit bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'heading_result_footer', [
			'label'     => esc_html__( 'Notes & CTA', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'result_note_policy_unknown', [
			'label'   => esc_html__( 'Warning: policy unknown (no range)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'default' => 'Omdat u niet weet welk type basisverzekering u heeft, is deze berekening minder nauwkeurig.',
		] );

		$this->add_control( 'result_disclaimer', [
			'label'   => esc_html__( 'Disclaimer', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => '*Resultaat op basis van de door u ingevulde gegevens. De uiteindelijke kosten zijn afhankelijk van de daadwerkelijk geleverde zorg, de verwerking van de declaratie, uw polisvoorwaarden, uw resterende eigen risico en de definitieve vergoeding van uw zorgverzekeraar. Aan deze berekening kunnen geen rechten worden ontleend.',
		] );

		$this->add_control( 'result_cta_title', [
			'label'   => esc_html__( 'CTA title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Hoe gaat het verder?',
			'label_block' => true,
		] );

		$this->add_control( 'result_cta_text', [
			'label'   => esc_html__( 'CTA text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Een traject start altijd met diagnostiek: pas daarna is duidelijk of en welke behandeling passend is. Voor aanmelding heeft u onder andere een verwijzing van uw huisarts nodig. Op onze aanmeldpagina leest u welke stappen en gegevens daarbij horen.',
		] );

		$this->add_control( 'signup_label', [
			'label'   => esc_html__( 'CTA button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Lees over aanmelden',
		] );

		$this->add_control( 'signup_link', [
			'label'   => esc_html__( 'CTA button link', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::URL,
			'default' => [ 'url' => '#aanmelden' ],
		] );

		$this->end_controls_section();
	}

	private function register_calculation_controls() {
		$this->start_controls_section( 'section_calculation', [
			'label' => esc_html__( 'Calculation', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'avg_invoice', [
			'label'   => esc_html__( 'Average invoice amount (€)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 0,
			'default' => 2000,
		] );

		$this->add_control( 'personal_contribution', [
			'label'   => esc_html__( 'Personal contribution (€)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 0,
			'default' => 250,
		] );

		$this->end_controls_section();
	}

	private function register_illustration_controls() {
		$this->start_controls_section( 'section_illustrations', [
			'label' => esc_html__( 'Sidebar illustrations', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'illustrations_note', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Shown in the sidebar next to the steps. The bundled images are used when empty.', 'zorgkosten-calculator' ),
			'content_classes' => 'elementor-descriptor',
		] );

		$images = [
			'verzekering' => [
				'label' => esc_html__( 'Insurance (steps 2)', 'zorgkosten-calculator' ),
				'alt'   => 'Persoon met verzekeringspas en polisvoorwaarden',
			],
			'eigenrisico' => [
				'label' => esc_html__( 'Deductible (steps 3–4)', 'zorgkosten-calculator' ),
				'alt'   => 'Persoon die een munt in een spaarpot doet',
			],
			'gesprek' => [
				'label' => esc_html__( 'Conversation (steps 5–6)', 'zorgkosten-calculator' ),
				'alt'   => 'Behandelaar in gesprek met een patiënt',
			],
			'factuur' => [
				'label' => esc_html__( 'Invoice (steps 7–9 & result)', 'zorgkosten-calculator' ),
				'alt'   => 'Patiënt overhandigt een factuur bij de balie',
			],
		];

		foreach ( $images as $key => $img ) {
			$this->add_control( "illu_{$key}", [
				'label'   => $img['label'],
				'type'    => Controls_Manager::MEDIA,
				'default' => [ 'url' => '' ],
			] );
			$this->add_control( "illu_{$key}_alt", [
				'label'       => sprintf( '%s — %s', $img['label'], esc_html__( 'alt text', 'zorgkosten-calculator' ) ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $img['alt'],
				'label_block' => true,
			] );
		}

		$this->end_controls_section();
	}

	private function register_style_controls() {

		$this->start_controls_section( 'section_style_colors', [
			'label' => esc_html__( 'Colors', 'zorgkosten-calculator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$colors = [
			'color_primary'      => [ 'Primary (coral)', '#EB776C', '--zkc-primary' ],
			'color_primary_soft' => [ 'Primary soft background', '#FFEEEA', '--zkc-primary-soft' ],
			'color_goodwill'     => [ 'Goodwill (teal)', '#3CB3A9', '--zkc-goodwill' ],
			'color_secondary'    => [ 'Secondary background (cream)', '#F8F2EE', '--zkc-secondary' ],
			'color_foreground'   => [ 'Text', '#2F3545', '--zkc-foreground' ],
			'color_muted'        => [ 'Muted text', '#6D6460', '--zkc-muted' ],
			'color_card'         => [ 'Card background', '#FFFFFF', '--zkc-card' ],
			'color_border'       => [ 'Borders', '#E2E3E6', '--zkc-border' ],
			'color_warning_soft' => [ 'Warning background', '#FFF0DD', '--zkc-warning-soft' ],
			'color_success_soft' => [ 'Success background', '#E8F9ED', '--zkc-success-soft' ],
			'color_destructive'  => [ 'Error text', '#CC3430', '--zkc-destructive' ],
		];

		foreach ( $colors as $id => $c ) {
			$this->add_control( $id, [
				'label'     => esc_html__( $c[0], 'zorgkosten-calculator' ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'type'      => Controls_Manager::COLOR,
				'default'   => $c[1],
				'selectors' => [ '{{WRAPPER}} .zkc' => $c[2] . ': {{VALUE}};' ],
			] );
		}

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_layout', [
			'label' => esc_html__( 'Layout', 'zorgkosten-calculator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'max_width', [
			'label'      => esc_html__( 'Max width', 'zorgkosten-calculator' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [ 'px' => [ 'min' => 600, 'max' => 1800 ] ],
			'default'    => [ 'size' => 1500, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .zkc-inner' => 'max-width: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'app_frame', [
			'label'       => esc_html__( 'App frame on desktop', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Constrains the calculator to one screen with internal scrolling on large screens, like the original app.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => 'yes',
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_typo', [
			'label' => esc_html__( 'Typography', 'zorgkosten-calculator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'typo_title',
			'label'    => esc_html__( 'Titles', 'zorgkosten-calculator' ),
			'selector' => '{{WRAPPER}} .zkc .zkc-title, {{WRAPPER}} .zkc .zkc-intro-title',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'typo_display',
			'label'    => esc_html__( 'Display numbers (percentage & amounts)', 'zorgkosten-calculator' ),
			'selector' => '{{WRAPPER}} .zkc .zkc-display',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'typo_body',
			'label'    => esc_html__( 'Body', 'zorgkosten-calculator' ),
			'selector' => '{{WRAPPER}} .zkc',
		] );

		$this->end_controls_section();
	}

	/* -------------------------------------------------------------------------
	 * Render
	 * ---------------------------------------------------------------------- */

	protected function render() {
		$s = $this->get_settings_for_display();

		$lines = static function ( $text ) {
			$out = [];
			foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
				$line = trim( $line );
				if ( '' !== $line ) {
					$out[] = $line;
				}
			}
			return $out;
		};

		$media_or = static function ( $media, $fallback ) {
			return ! empty( $media['url'] ) ? $media['url'] : $fallback;
		};

		// Insurers.
		$insurers = [];
		foreach ( (array) ( $s['insurers'] ?? [] ) as $row ) {
			$logo = '';
			if ( ! empty( $row['ins_logo']['url'] ) ) {
				$logo = $row['ins_logo']['url'];
			} elseif ( ! empty( $row['ins_logo_url'] ) ) {
				$logo = $row['ins_logo_url'];
			}
			$insurers[] = [
				'group'         => (string) ( $row['ins_group'] ?? '' ),
				'name'          => (string) ( $row['ins_name'] ?? '' ),
				'logo'          => $logo,
				'agreement'     => ! empty( $row['ins_agreement'] ),
				'machtiging'    => ! empty( $row['ins_machtiging'] ),
				'declareUrl'    => (string) ( $row['ins_declare_url'] ?? '' ),
				'machtigingUrl' => (string) ( $row['ins_machtiging_url'] ?? '' ),
			];
		}

		// Policies.
		$policies = [];
		foreach ( (array) ( $s['policies'] ?? [] ) as $row ) {
			$min = is_numeric( $row['pol_percentage'] ?? null ) ? (float) $row['pol_percentage'] : 70;
			$max = is_numeric( $row['pol_percentage_max'] ?? null ) ? (float) $row['pol_percentage_max'] : null;

			// A max that is not above the min is simply a single percentage.
			if ( null !== $max && $max <= $min ) {
				$max = null;
			}

			$policies[] = [
				'insurer'       => (string) ( $row['pol_insurer'] ?? '' ),
				'name'          => (string) ( $row['pol_name'] ?? '' ),
				'percentage'    => $min,
				'percentageMax' => $max,
				'basis'         => (string) ( $row['pol_basis'] ?? 'gemiddeld_gecontracteerd' ),
				'note'          => (string) ( $row['pol_note'] ?? '' ),
			];
		}

		// Tariff bases.
		$bases = [];
		foreach ( (array) ( $s['bases'] ?? [] ) as $row ) {
			$bases[] = [
				'key'   => (string) ( $row['basis_key'] ?? '' ),
				'label' => (string) ( $row['basis_label'] ?? '' ),
				'title' => (string) ( $row['basis_title'] ?? '' ),
				'text'  => (string) ( $row['basis_text'] ?? '' ),
			];
		}

		// Deductible options.
		$deductibles = [];
		foreach ( (array) ( $s['deductible_options'] ?? [] ) as $row ) {
			if ( is_numeric( $row['ded_amount'] ?? null ) ) {
				$deductibles[] = (float) $row['ded_amount'];
			}
		}
		if ( empty( $deductibles ) ) {
			$deductibles = [ 385 ];
		}

		$img_base      = ZKC_URL . 'assets/img/';
		$signup_url    = ! empty( $s['signup_link']['url'] ) ? $s['signup_link']['url'] : '#aanmelden';
		$signup_target = ! empty( $s['signup_link']['is_external'] ) ? '_blank' : '';

		$config = [
			'general' => [
				'back'          => (string) $s['back_label'],
				'next'          => (string) $s['next_label'],
				'restart'       => (string) $s['restart_label'],
				'adjust'        => (string) $s['adjust_label'],
				'stepCounter'   => (string) $s['step_counter_text'],
				'helpLabel'     => (string) $s['help_label'],
				'warnLabel'     => (string) $s['warning_label'],
				'unknown'       => (string) $s['unknown_label'],
				'fallbackName'  => (string) $s['fallback_insurer'],
				'sidebarTitle'  => (string) $s['sidebar_title'],
				'sidebarEmpty'  => (string) $s['sidebar_empty'],
				'chipTitle'     => (string) $s['sidebar_chip_title'],
				'chipDeductible'=> (string) $s['sidebar_chip_deductible'],
				'chipUsed'      => (string) $s['sidebar_chip_used'],
				'appFrame'      => ! empty( $s['app_frame'] ),
			],
			'intro' => [
				'kicker'   => (string) $s['intro_kicker'],
				'title'    => (string) $s['intro_title'],
				'text'     => (string) $s['intro_text'],
				'bullets'  => $lines( $s['intro_bullets'] ),
				'button'   => (string) $s['intro_button'],
				'image'    => $media_or( $s['intro_image'] ?? [], $img_base . 'illu-start.png' ),
				'imageAlt' => (string) $s['intro_image_alt'],
			],
			'images' => [
				'verzekering' => [
					'src' => $media_or( $s['illu_verzekering'] ?? [], $img_base . 'illu-verzekering.png' ),
					'alt' => (string) $s['illu_verzekering_alt'],
				],
				'eigenrisico' => [
					'src' => $media_or( $s['illu_eigenrisico'] ?? [], $img_base . 'illu-eigenrisico.png' ),
					'alt' => (string) $s['illu_eigenrisico_alt'],
				],
				'gesprek' => [
					'src' => $media_or( $s['illu_gesprek'] ?? [], $img_base . 'illu-gesprek.png' ),
					'alt' => (string) $s['illu_gesprek_alt'],
				],
				'factuur' => [
					'src' => $media_or( $s['illu_factuur'] ?? [], $img_base . 'illu-factuur.png' ),
					'alt' => (string) $s['illu_factuur_alt'],
				],
			],
			'insurers' => $insurers,
			'policies' => $policies,
			'bases'    => $bases,
			'calc'     => [
				'avgInvoice'        => is_numeric( $s['avg_invoice'] ) ? (float) $s['avg_invoice'] : 2000,
				'contribution'      => is_numeric( $s['personal_contribution'] ) ? (float) $s['personal_contribution'] : 250,
				'defaultPercentage' => is_numeric( $s['default_percentage'] ) ? (float) $s['default_percentage'] : 70,
				'defaultBasis'      => (string) $s['default_basis'],
				'deductibles'       => $deductibles,
				'maxDeductible'     => is_numeric( $s['default_deductible'] ) ? (float) $s['default_deductible'] : 885,
			],
			'steps' => [
				's1' => [
					'title' => (string) $s['step1_title'],
					'help'  => (string) $s['step1_help'],
				],
				's2' => [
					'title'    => (string) $s['step2_title'],
					'subtitle' => (string) $s['step2_subtitle'],
					'help'     => (string) $s['step2_help'],
				],
				's3' => [
					'title' => (string) $s['step3_title'],
					'text'  => (string) $s['step3_text'],
					'help'  => (string) $s['step3_help'],
				],
				's4' => [
					'title'        => (string) $s['step4_title'],
					'help'         => (string) $s['step4_help'],
					'fieldLabel'   => (string) $s['step4_field_label'],
					'placeholder'  => (string) $s['step4_placeholder'],
					'errorInvalid' => (string) $s['step4_error_invalid'],
					'errorMax'     => (string) $s['step4_error_max'],
				],
				's5' => [
					'title'   => (string) $s['step5_title'],
					'content' => (string) $s['step5_content'],
					'panel'   => (string) $s['step5_panel'],
				],
				's6' => [
					'heroTitle'         => (string) $s['step6_hero_title'],
					'heroTitleFallback' => (string) $s['step6_hero_title_fallback'],
					'heroBasis'         => (string) $s['step6_hero_basis'],
					'estimateKicker'    => (string) $s['step6_estimate_kicker'],
					'estimateNote'      => (string) $s['step6_estimate_note'],
					'message'           => (string) $s['step6_message'],
					'messageInsurer'    => (string) $s['step6_message_insurer_range'],
					'messageUnknown'    => (string) $s['step6_message_unknown'],
					'basisKicker'       => (string) $s['step6_basis_kicker'],
					'accordionLabel'    => (string) $s['step6_accordion_label'],
					'footnote'          => (string) $s['step6_footnote'],
				],
				's7' => [
					'title'        => (string) $s['step7_title'],
					'yesBadge'     => (string) $s['step7_yes_badge'],
					'yesIntro'     => (string) $s['step7_yes_intro'],
					'yesContent'   => (string) $s['step7_yes_content'],
					'yesNoteLabel' => (string) $s['step7_yes_note_label'],
					'yesNote'      => (string) $s['step7_yes_note'],
					'noBadge'      => (string) $s['step7_no_badge'],
					'noIntro'      => (string) $s['step7_no_intro'],
					'noContent'    => (string) $s['step7_no_content'],
					'noNoteLabel'  => (string) $s['step7_no_note_label'],
					'noNote'       => (string) $s['step7_no_note'],
				],
				's8' => [
					'title'       => (string) $s['step8_title'],
					'content'     => (string) $s['step8_content'],
					'asksTitle'   => (string) $s['step8_asks_title'],
					'asks'        => $lines( $s['step8_asks'] ),
					'checkTitle'  => (string) $s['step8_check_title'],
					'checkText'   => (string) $s['step8_check_text'],
					'checkButton' => (string) $s['step8_check_button'],
					'footnote'    => (string) $s['step8_footnote'],
				],
				's9' => [
					'title'           => (string) $s['step9_title'],
					'intro'           => (string) $s['step9_intro'],
					'conditionsTitle' => (string) $s['step9_conditions_title'],
					'conditions'      => $lines( $s['step9_conditions'] ),
					'excludedTitle'   => (string) $s['step9_excluded_title'],
					'excluded'        => $lines( $s['step9_excluded'] ),
					'button'          => (string) $s['step9_button'],
				],
			],
			'result' => [
				'kicker'            => (string) $s['result_kicker'],
				'title'             => (string) $s['result_title'],
				'intro'             => (string) $s['result_intro'],
				'invoiceTitle'      => (string) $s['result_invoice_title'],
				'invoiceText'       => (string) $s['result_invoice_text'],
				'uncertainTooltip'  => (string) $s['result_uncertain_tooltip'],
				'reimbursedLabel'   => (string) $s['result_reimbursed_label'],
				'reimbursedText'    => (string) $s['result_reimbursed_text'],
				'waivedLabel'       => (string) $s['result_waived_label'],
				'waivedText'        => (string) $s['result_waived_text'],
				'rangeNotePolicy'   => (string) $s['result_range_note_policy'],
				'ownKicker'         => (string) $s['result_own_kicker'],
				'ownText'           => (string) $s['result_own_text'],
				'contributionLabel' => (string) $s['result_contribution_label'],
				'contributionText'  => (string) $s['result_contribution_text'],
				'deductibleLabel'   => (string) $s['result_deductible_label'],
				'deductibleText'    => (string) $s['result_deductible_text'],
				'usedUnknownNote'   => (string) $s['result_used_unknown_note'],
				'selfKicker'        => (string) $s['result_self_kicker'],
				'selfTitleNo'       => (string) $s['result_self_title_no'],
				'selfSteps'         => $lines( $s['result_self_steps'] ),
				'declareButton'     => (string) $s['result_declare_button'],
				'selfTitleYes'      => (string) $s['result_self_title_yes'],
				'selfTextYes'       => (string) $s['result_self_text_yes'],
				'machtigingLabel'   => (string) $s['result_machtiging_label'],
				'machtigingText'    => (string) $s['result_machtiging_text'],
				'machtigingLink'    => (string) $s['result_machtiging_link'],
				'notePolicyUnknown' => (string) $s['result_note_policy_unknown'],
				'disclaimer'        => (string) $s['result_disclaimer'],
				'ctaTitle'          => (string) $s['result_cta_title'],
				'ctaText'           => (string) $s['result_cta_text'],
				'signupLabel'       => (string) $s['signup_label'],
				'signupUrl'         => $signup_url,
				'signupTarget'      => $signup_target,
			],
		];

		?>
		<div class="zkc" data-zkc-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<div class="zkc-inner">
				<noscript><?php echo esc_html__( 'This calculator requires JavaScript.', 'zorgkosten-calculator' ); ?></noscript>
			</div>
		</div>
		<?php
	}
}
