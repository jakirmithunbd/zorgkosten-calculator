<?php
/**
 * Zorgkosten Cost Calculator – Elementor Widget.
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
		$cdn  = 'https://zorgkosten-inzicht.lovable.app/__l5e/assets-v1/';
		$rows = [
			// group, name, agreement, machtiging, logo url
			[ 'Achmea', 'FBTO', 'yes', '', $cdn . '57775fcc-c437-422e-b6fa-fe93f9224647/FBTO-logo-liggend-2019.svg' ],
			[ 'Achmea', 'De Friesland', 'yes', '', $cdn . 'a9a5962f-321c-4960-beba-185b3340383c/logo_defriesland.svg' ],
			[ 'Achmea', 'Interpolis', 'yes', '', $cdn . '3a766be2-1e77-40dc-8c31-7a959841db99/logo_interpolis.svg' ],
			[ 'Achmea', 'Zilveren Kruis', 'yes', '', $cdn . '274d913b-5677-42f7-a5f6-955065909042/logo_zilverenkruis.svg' ],
			[ 'Achmea', 'ZieZo (label Zilveren Kruis)', 'yes', '', $cdn . '921f9603-42ac-4785-ac3b-1010a2813e54/ziezo.svg' ],
			[ 'Achmea', 'De Christelijke Zorgverzekeraar (label Zilveren Kruis)', 'yes', '', $cdn . 'e0da6dcb-948c-4483-b793-caac4e7a51eb/logo_dechristelijkezorgverzekeraar.svg' ],
			[ 'VGZ', 'Univé', 'yes', '', $cdn . 'dfccd5c1-029a-405b-9e12-9a15ec6b2c6a/unive-logo-payoff.svg' ],
			[ 'VGZ', 'VGZ', 'yes', '', $cdn . 'fb112d4c-096c-41b4-960d-979d2078afc3/Logo_VGZ_nieuw.png' ],
			[ 'VGZ', 'VGZbewuzt', 'yes', '', $cdn . 'fde61534-f309-4204-a391-b72daf65fb0c/VGZbewuzt_logo_RGB_2022.png' ],
			[ 'VGZ', 'ZEKUR', 'yes', '', $cdn . 'db7b7145-a294-4fe3-8156-9140d8c2fc17/zekur_logo.svg' ],
			[ 'VGZ', 'United Consumers', 'yes', '', $cdn . '36734bfd-4f8d-45da-b6ed-69c15902a513/unitedconsumers-logo-uc.svg' ],
			[ 'VGZ', 'IZA', 'yes', '', $cdn . 'a618c5eb-5bb9-4007-8caf-2ad2fed2b41c/IZA_van_VGZ_Compact.png' ],
			[ 'VGZ', 'UMC Zorgverzekering', 'yes', '', $cdn . 'e3061a30-8796-4ffc-bdb3-5d1c14b7c71e/Logo_umczorgverzekering.png' ],
			[ 'VGZ', 'IZZ Zorgverzekering', 'yes', '', $cdn . '07c87920-c7ef-40b3-b7e0-9cbc2a0a47f0/IZZCombinatielogo_IZZ-VGZ_FC_RGB-1.png' ],
			[ 'CZ', 'Nationale-Nederlanden', '', '', $cdn . '294a0dd1-4353-4f02-b7cd-f61ac4052622/logo-nationalenederlanden.svg' ],
			[ 'CZ', 'Ohra', '', '', $cdn . '5269230b-1017-4f46-8c49-8c89441fe723/logo-ohra.svg' ],
			[ 'CZ', 'CZ', '', '', $cdn . '1923c4eb-d1f9-49a0-96f3-eaac393ab145/logo_CZ.svg' ],
			[ 'CZ', 'CZdirect (label CZ)', '', '', $cdn . '1923c4eb-d1f9-49a0-96f3-eaac393ab145/logo_CZ.svg' ],
			[ 'CZ', 'Just (label CZ)', '', '', $cdn . 'd74972fd-d9d0-4923-af27-f2c44e8c622d/logo-JUSTCZ.svg' ],
			[ 'Menzis', 'VinkVink', '', '', $cdn . 'f7c2d8ad-dda1-49d7-8662-6377a9adc910/logo-VINKVINK.svg' ],
			[ 'Menzis', 'Anderzorg', '', '', $cdn . '97f74821-1a8f-4739-b0eb-2f0525e7f13d/logo-ANDERZORG.svg' ],
			[ 'Menzis', 'Menzis', '', '', $cdn . 'c2bb9a93-b29d-4888-99a0-4012ebf1c521/logo-MENZIS.svg' ],
			[ 'DSW', 'DSW', '', '', $cdn . '3b9cb72f-648a-4fca-8d77-daa4a5db1738/dsw-tablet-plus-logo.svg' ],
			[ 'DSW', 'Stad Holland', '', '', $cdn . '1256f2cc-f02f-466c-8318-db57ced14bd6/stadholland-tablet-plus-logo.svg' ],
			[ 'a.s.r.', 'a.s.r.', '', 'yes', $cdn . '87bbac93-73d4-457e-81b7-e2cf0897f018/a.s.r.zorgverzekering.svg' ],
			[ 'a.s.r.', 'Ik kies zelf van a.s.r.', '', 'yes', $cdn . 'e002c0b2-1d3d-4b56-9135-a7b80dd7f88f/ikkieszelfasr.svg' ],
			[ 'Zorg en Zekerheid', 'Zorg en Zekerheid', '', 'yes', $cdn . 'db93c499-421b-49c8-9238-10e97234d852/logo-zorgenzekerheid.svg' ],
			[ 'ONVZ', 'ONVZ', 'yes', 'yes', $cdn . '514c3577-9f33-48bd-82f3-69185cc517b4/onvz-logo.png' ],
			[ 'ONVZ', 'VvAA', 'yes', 'yes', $cdn . '10f53aef-e311-40c9-9697-21a6af1fd3e3/idwzGV5qgk_1785212821621.jpeg' ],
			[ 'Salland', 'Salland', '', 'yes', $cdn . '0165638b-2ad7-4d98-a023-2a729bbcb2be/Logo-Salland-Zorgverzekeraar.png' ],
			[ 'Eucare', 'Aevitae', '', 'yes', $cdn . '21f057e0-e6f7-4286-84e6-cd1e96cefa18/logo-aevitae.webp' ],
			[ 'Eucare', 'Care4life', '', 'yes', $cdn . '7e04e2d5-a118-4525-bba7-3ef57b0a8fe4/logo-care4life.png' ],
		];

		$defaults = [];
		foreach ( $rows as $r ) {
			$defaults[] = [
				'ins_group'      => $r[0],
				'ins_name'       => $r[1],
				'ins_agreement'  => $r[2],
				'ins_machtiging' => $r[3],
				'ins_logo_url'   => $r[4],
			];
		}
		return $defaults;
	}

	private function default_policies() {
		$rows = [
			// insurer, policy, percentage, basis, note, [percentage max]
			[ 'FBTO', 'Zorgverzekering Basis', 65, 'gemiddeld_gecontracteerd', '' ],
			[ 'FBTO', 'Zorgverzekering Basis Plus', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'FBTO', 'Zorgverzekering Basis Vrij', 75, 'marktconform', 'van het marktconforme of wettelijke tarief bij de meeste zorgverleners' ],
			[ 'De Friesland', 'Zelf Bewust Polis', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'De Friesland', 'Alles Verzorgd Polis', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Interpolis', 'ZorgCompact', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Interpolis', 'ZorgActief', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Zilveren Kruis', 'Basis Budget', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Zilveren Kruis', 'Basis Zeker', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Zilveren Kruis', 'Basis Exclusief', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'ZieZo (label Zilveren Kruis)', 'Basis', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'De Christelijke Zorgverzekeraar (label Zilveren Kruis)', 'Principe Polis', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Select', 60, 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Basis Polis', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Geregeld Polis', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Univé', 'Zorg Uitgebreid Polis', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZ', 'Basis Keuze', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZ', 'Ruime Keuze', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZ', 'Eigen Keuze', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'VGZbewuzt', 'Basis', 60, 'gemiddeld_gecontracteerd', '' ],
			[ 'ZEKUR', 'Zorg Basis', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'ZEKUR', 'Zorg Plus', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Bewuste Keuze', 60, 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Basis Keuze', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Ruime Keuze', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'United Consumers', 'Eigen Keuze', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'IZA', 'Basis Keuze', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'IZA', 'Ruime Keuze', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'IZA', 'Eigen Keuze', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'UMC Zorgverzekering', 'Ruime Keuze', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'UMC Zorgverzekering', 'Eigen Keuze', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Bewuzt', 60, 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Basis', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Natura', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'IZZ Zorgverzekering', 'Variant Combinatie', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Nationale-Nederlanden', 'Zorg Voordelig', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'Nationale-Nederlanden', 'Zorg Vrij', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Ohra', 'Zorgverzekering Combinatie', 75, 'maximumtarief', 'rekening houdend met het maximumtarief' ],
			[ 'CZ', 'Zorgbewust polis', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'CZ', 'Zorg-op-maatpolis', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'CZ', 'Zorgvariatiepolis', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'CZdirect (label CZ)', 'CZdirect', 65, 'afgesproken_andere_zorgverleners', '' ],
			[ 'Just (label CZ)', 'Basic', 60, 'gemiddeld_gecontracteerd', '' ],
			[ 'VinkVink', 'Basisverzekering', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'Anderzorg', 'Anderzorg Basis', 60, 'gemiddeld_gecontracteerd', '60-100% afhankelijk van soort zorg', 100 ],
			[ 'Menzis', 'Basis Voordelig', 70, 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'Menzis', 'Basis', 70, 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'Menzis', 'Basis Vrij', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'DSW', 'Zorgpolis', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Stad Holland', 'Zorgpolis', 75, 'wmg_nza', '' ],
			[ 'a.s.r.', 'Bewuste keuze', 65, 'gemiddeld_gecontracteerd', '' ],
			[ 'a.s.r.', 'Ruime keuze', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'a.s.r.', 'Eigen keuze', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Ik kies zelf van a.s.r.', 'Goede keuze', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Ik kies zelf van a.s.r.', 'Juiste Keuze', 65, 'gemiddeld_gecontracteerd', '' ],
			[ 'Ik kies zelf van a.s.r.', 'Vrije Keuze', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Zorg en Zekerheid', 'Zorg Gemak Polis', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'Zorg en Zekerheid', 'Zorg Zeker Polis', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Zorg en Zekerheid', 'Zorg Vrij Polis', 80, 'marktconform', 'van het in de markt gebruikelijke tarief' ],
			[ 'ONVZ', 'Bewuste Keuze', 65, 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'ONVZ', 'Vrije Keuze', 75, 'gemiddeld_gecontracteerd', 'afhankelijk van soort zorg' ],
			[ 'VvAA', 'Zorgverzekering', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'VvAA', 'Natura zorgverzekering', 70, 'gemiddeld_gecontracteerd', '' ],
			[ 'Salland', 'Basispolis', 85, 'gemiddeld_gecontracteerd', '' ],
			[ 'Aevitae', 'Basisverzekering Natura Select', 65, 'afgesproken_andere_zorgverleners', '' ],
			[ 'Aevitae', 'Basisverzekering Natura', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Aevitae', 'Basisverzekering Combinatie', 80, 'gemiddeld_gecontracteerd', '' ],
			[ 'Care4life', 'Basisverzekering Natura Select', 65, 'afgesproken_andere_zorgverleners', '' ],
			[ 'Care4life', 'Basisverzekering Natura', 75, 'gemiddeld_gecontracteerd', '' ],
			[ 'Care4life', 'Basisverzekering Combinatie', 80, 'gemiddeld_gecontracteerd', '' ],
		];

		$defaults = [];
		foreach ( $rows as $r ) {
			$defaults[] = [
				'pol_insurer'        => $r[0],
				'pol_name'           => $r[1],
				'pol_percentage'     => $r[2],
				'pol_percentage_max' => $r[5] ?? '',
				'pol_basis'          => $r[3],
				'pol_note'           => $r[4],
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

	/* -------------------------------------------------------------------------
	 * Controls
	 * ---------------------------------------------------------------------- */

	protected function register_controls() {
		$this->register_general_controls();
		$this->register_intro_controls();
		$this->register_insurer_controls();
		$this->register_policy_controls();
		$this->register_step_text_controls();
		$this->register_calculation_controls();
		$this->register_result_controls();
		$this->register_style_controls();
	}

	private function register_general_controls() {
		$this->start_controls_section( 'section_general', [
			'label' => esc_html__( 'General', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'brand_name', [
			'label'   => esc_html__( 'Brand name (header)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'ADHD Medisch Centrum',
			'label_block' => true,
		] );

		$this->add_control( 'show_header', [
			'label'   => esc_html__( 'Show header bar', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'restart_label', [
			'label'   => esc_html__( '"Start over" link text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Opnieuw beginnen',
		] );

		$this->add_control( 'step_counter_text', [
			'label'       => esc_html__( 'Step counter text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {current} and {total}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Stap {current} van {total}',
		] );

		$this->add_control( 'back_label', [
			'label'   => esc_html__( '"Back" button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Terug',
		] );

		$this->add_control( 'next_label', [
			'label'   => esc_html__( '"Next" button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Volgende',
		] );

		$this->add_control( 'help_prefix', [
			'label'   => esc_html__( 'Help box prefix', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Hulp:',
		] );

		$this->add_control( 'unknown_label', [
			'label'   => esc_html__( '"I don\'t know" option text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Ik weet het niet',
		] );

		$this->add_control( 'footer_text', [
			'label'   => esc_html__( 'Footer text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '© ADHD Medisch Centrum · Kostenindicatie',
			'label_block' => true,
		] );

		$this->add_control( 'show_footer', [
			'label'   => esc_html__( 'Show footer', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->end_controls_section();
	}

	private function register_intro_controls() {
		$this->start_controls_section( 'section_intro', [
			'label' => esc_html__( 'Intro screen', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'intro_kicker', [
			'label'   => esc_html__( 'Kicker (small title)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Persoonlijke kostenindicatie',
			'label_block' => true,
		] );

		$this->add_control( 'intro_title', [
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Bereken uw kosten',
			'label_block' => true,
		] );

		$this->add_control( 'intro_text', [
			'label'   => esc_html__( 'Description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Beantwoord enkele vragen over uw zorgverzekering en ontvang een persoonlijke inschatting van uw kosten.',
		] );

		$this->add_control( 'intro_button', [
			'label'   => esc_html__( 'Start button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Start de berekening',
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
			'label' => esc_html__( 'Logo', 'zorgkosten-calculator' ),
			'type'  => Controls_Manager::MEDIA,
			'default' => [ 'url' => '' ],
		] );

		$repeater->add_control( 'ins_logo_url', [
			'label'       => esc_html__( 'Logo URL (fallback)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
			'description' => esc_html__( 'Used when no logo is uploaded above.', 'zorgkosten-calculator' ),
		] );

		$repeater->add_control( 'ins_agreement', [
			'label'       => esc_html__( 'Payment agreement (betaalovereenkomst)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => '',
			'description' => esc_html__( 'Controls which "How are invoices submitted?" variant is shown.', 'zorgkosten-calculator' ),
		] );

		$repeater->add_control( 'ins_machtiging', [
			'label'       => esc_html__( 'Authorization (machtiging) may be required', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::SWITCHER,
			'default'     => '',
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
			'description' => esc_html__( 'Leave empty for a single percentage. Fill it in to show a range such as 60–100%. The estimated amount then uses the middle of the range.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 100,
			'default'     => '',
		] );

		$repeater->add_control( 'pol_basis', [
			'label'   => esc_html__( 'Tariff basis', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'gemiddeld_gecontracteerd'         => esc_html__( 'Gemiddeld gecontracteerd tarief', 'zorgkosten-calculator' ),
				'wmg_nza'                          => esc_html__( 'Wettelijk / Wmg-tarief (NZa)', 'zorgkosten-calculator' ),
				'marktconform'                     => esc_html__( 'Marktconform tarief', 'zorgkosten-calculator' ),
				'maximumtarief'                    => esc_html__( 'Maximumtarief van de verzekeraar', 'zorgkosten-calculator' ),
				'afgesproken_andere_zorgverleners' => esc_html__( 'Tarief afgesproken met andere zorgverleners', 'zorgkosten-calculator' ),
			],
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
			'label'     => esc_html__( 'Fallback ("I don\'t know")', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'default_percentage', [
			'label'       => esc_html__( 'Default reimbursement % (unknown policy)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'max'         => 100,
			'default'     => 70,
		] );

		$this->add_control( 'default_basis', [
			'label'   => esc_html__( 'Default tariff basis (unknown policy)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'gemiddeld_gecontracteerd'         => esc_html__( 'Gemiddeld gecontracteerd tarief', 'zorgkosten-calculator' ),
				'wmg_nza'                          => esc_html__( 'Wettelijk / Wmg-tarief (NZa)', 'zorgkosten-calculator' ),
				'marktconform'                     => esc_html__( 'Marktconform tarief', 'zorgkosten-calculator' ),
				'maximumtarief'                    => esc_html__( 'Maximumtarief van de verzekeraar', 'zorgkosten-calculator' ),
				'afgesproken_andere_zorgverleners' => esc_html__( 'Tarief afgesproken met andere zorgverleners', 'zorgkosten-calculator' ),
			],
			'default' => 'gemiddeld_gecontracteerd',
		] );

		$this->end_controls_section();
	}

	private function register_step_text_controls() {

		/* ---- Step 1: insurer ---- */
		$this->start_controls_section( 'section_step1', [
			'label' => esc_html__( 'Step 1 – Insurer', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step1_title', [
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Bij welke zorgverzekeraar bent u verzekerd?',
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
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Welk type basisverzekering heeft u?',
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
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Hoe hoog is uw totale eigen risico?',
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
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Hoeveel van uw eigen risico heeft u dit jaar al gebruikt?',
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
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Hoe worden de kosten van uw traject bepaald?',
			'label_block' => true,
		] );

		$this->add_control( 'step5_content', [
			'label'   => esc_html__( 'Content', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<p>De tarieven voor diagnostiek en behandeling in de specialistische GGZ worden landelijk vastgesteld door de Nederlandse Zorgautoriteit (NZa). Deze standaardtarieven gelden voor alle GGZ-instellingen; ADHD Medisch Centrum heeft hier geen invloed op.</p><p>De kosten hangen af van het type zorg en de tijd die wordt besteed. Er wordt gewerkt volgens het zorgprestatiemodel, waarbij onder andere de duur en het type contact en de betrokken zorgverleners bepalend zijn. Hierdoor kan het totale bedrag per patiënt verschillen.</p><p>Meer informatie over de tarieven vastgesteld door de NZa vindt u <a href="https://www.nza.nl/onderwerpen/geestelijke-gezondheidszorg" target="_blank" rel="noopener">hier</a>.</p>',
		] );

		$this->add_control( 'step5_panel', [
			'label'   => esc_html__( 'Panel content (framed box)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<h4>Declaratiefactuur en betalingsfactuur</h4><p>De manier waarop wij zorg declareren, hangt af van de afspraken die ADHD Medisch Centrum met uw zorgverzekeraar heeft. Wij maken onderscheid tussen:</p><ul><li><strong>Declaratiefactuur:</strong> bedoeld om de geleverde zorg bij uw zorgverzekeraar te declareren. Vermeldt de geleverde zorgprestaties en het volledige bedrag berekend op basis van NZa-tarieven.</li><li><strong>Betalingsfactuur:</strong> hierop staat het bedrag dat u zelf aan ADHD Medisch Centrum moet betalen.</li></ul><p>Een declaratiefactuur is niet automatisch een betalingsverzoek voor het volledige factuurbedrag. Een betaalovereenkomst regelt hóe de declaratie wordt ingediend en de vergoeding uitbetaald — dit betekent niet automatisch dat uw zorg volledig wordt vergoed. Uw zorgverzekeraar bepaalt de vergoeding op basis van uw polisvoorwaarden.</p>',
		] );

		$this->end_controls_section();

		/* ---- Step 6: reimbursement ---- */
		$this->start_controls_section( 'section_step6', [
			'label' => esc_html__( 'Step 6 – Reimbursement', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step6_hero_kicker', [
			'label'   => esc_html__( 'Hero kicker', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw uitkomst',
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
			'description' => esc_html__( 'Placeholders: {percentage} (a number or a range such as 60–100), {basis}, {note}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Uw zorgverzekeraar vergoedt bij deze basisverzekering naar verwachting {percentage}% van het {basis} voor ongecontracteerde GGZ.',
		] );

		$this->add_control( 'step6_message_unknown', [
			'label'       => esc_html__( 'Message (policy unknown)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {percentage}, {basis}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Op basis van een algemene inschatting gaan wij uit van een vergoeding van ongeveer {percentage}% van het {basis} voor ongecontracteerde GGZ.',
		] );

		$this->add_control( 'step6_basis_kicker', [
			'label'   => esc_html__( '"What does this basis mean?" label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wat betekent deze basis?',
		] );

		$this->add_control( 'step6_accordion_label', [
			'label'   => esc_html__( 'Accordion label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uitleg over alle vergoedingsbases',
			'label_block' => true,
		] );

		$repeater = new Repeater();
		$repeater->add_control( 'basis_key', [
			'label'       => esc_html__( 'Key', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				'gemiddeld_gecontracteerd'         => 'gemiddeld_gecontracteerd',
				'wmg_nza'                          => 'wmg_nza',
				'marktconform'                     => 'marktconform',
				'maximumtarief'                    => 'maximumtarief',
				'afgesproken_andere_zorgverleners' => 'afgesproken_andere_zorgverleners',
			],
			'default'     => 'gemiddeld_gecontracteerd',
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
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Hoe worden uw facturen ingediend?',
			'label_block' => true,
		] );

		$this->add_control( 'heading_step7_yes', [
			'label'     => esc_html__( 'Variant: WITH payment agreement', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step7_yes_badge', [
			'label'   => esc_html__( 'Badge text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wij hebben een betaalovereenkomst',
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

		$this->add_control( 'step7_yes_note', [
			'label'   => esc_html__( 'Note panel (Let op)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<h4>Let op: eigen risico</h4><p>Heeft u nog verplicht of vrijwillig eigen risico openstaan? Dan brengt uw zorgverzekeraar dit bij u in rekening of verrekent uw zorgverzekeraar dit volgens de eigen voorwaarden. U ontvangt hiervoor geen aparte factuur van ADHD Medisch Centrum. Het eigen risico blijft volledig voor uw rekening en valt niet onder de coulanceregeling.</p>',
		] );

		$this->add_control( 'heading_step7_no', [
			'label'     => esc_html__( 'Variant: WITHOUT payment agreement', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step7_no_badge', [
			'label'   => esc_html__( 'Badge text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Geen betaalovereenkomst',
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

		$this->add_control( 'step7_no_note', [
			'label'   => esc_html__( 'Note panel (Let op)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<h4>Let op: eigen risico</h4><p>Uw zorgverzekeraar kan uw openstaande eigen risico met de vergoeding verrekenen. Het bedrag dat als eigen risico wordt ingehouden, blijft u aan ADHD Medisch Centrum verschuldigd. Het eigen risico valt niet onder de coulanceregeling.</p>',
		] );

		$this->end_controls_section();

		/* ---- Step 8: authorization (only for insurers that need one) ---- */
		$this->start_controls_section( 'section_step8_machtiging', [
			'label' => esc_html__( 'Step 8 – Authorization (machtiging)', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'machtiging_note', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'This step is only shown for insurers with "Authorization (machtiging) may be required" switched on. For every other insurer the calculator has 8 steps instead of 9.', 'zorgkosten-calculator' ),
			'content_classes' => 'elementor-descriptor',
		] );

		$this->add_control( 'step7_machtiging_title', [
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Mogelijk is een machtiging nodig',
			'label_block' => true,
		] );

		$this->add_control( 'step7_machtiging_content', [
			'label'   => esc_html__( 'Content', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<p>Voor verzekerden van onder andere a.s.r., Zorg en Zekerheid, ONVZ, VvAA, Salland en Aevitae kan vooraf toestemming van de zorgverzekeraar nodig zijn voordat de zorg kan starten of worden voortgezet. Deze toestemming wordt een <strong>machtiging</strong> genoemd.</p><p>U bent zelf verantwoordelijk voor het controleren of voor uw zorgverzekering, polis of behandeling een machtiging vereist is en voor het tijdig verkrijgen daarvan. ADHD Medisch Centrum verleent binnen redelijke grenzen medewerking en kan, wanneer dit mogelijk is, de machtigingsaanvraag namens u voorbereiden en indienen.</p><p>Uw zorgverzekeraar beoordeelt de aanvraag en beslist of de machtiging wordt verleend. Een aangevraagde of verleende machtiging betekent niet automatisch dat alle zorgkosten volledig worden vergoed.</p>',
		] );

		$this->add_control( 'machtiging_asks_title', [
			'label'   => esc_html__( 'Framed box heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wat vragen wij van u?',
			'label_block' => true,
		] );

		$this->add_control( 'machtiging_asks', [
			'label'   => esc_html__( 'Framed box list (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 7,
			'default' => "Gevraagde informatie en documenten tijdig aanleveren.\nNoodzakelijke formulieren invullen en ondertekenen.\nVragen van ADHD Medisch Centrum of uw zorgverzekeraar tijdig beantwoorden.\nAanvullende informatie verstrekken wanneer daarom wordt gevraagd.\nWijzigingen in uw zorgverzekering of polis direct aan ons doorgeven.",
		] );

		$this->add_control( 'machtiging_footnote', [
			'label'   => esc_html__( 'Closing note', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => 'Wanneer een machtiging door het ontbreken van uw medewerking niet tijdig kan worden aangevraagd of verkregen, kan het zorgtraject worden beëindigd. Kosten die hierdoor niet worden vergoed, blijven voor uw rekening en vallen niet onder de coulanceregeling.',
		] );

		$this->end_controls_section();

		/* ---- Step 9: goodwill scheme ---- */
		$this->start_controls_section( 'section_step8', [
			'label' => esc_html__( 'Step 9 – Goodwill scheme (coulance)', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step8_title', [
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Onze coulanceregeling',
			'label_block' => true,
		] );

		$this->add_control( 'step8_intro', [
			'label'   => esc_html__( 'Intro', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::WYSIWYG,
			'default' => '<p>Wij vinden het belangrijk dat onze zorg zo toegankelijk mogelijk blijft. Daarom hanteren wij een coulanceregeling. Dit betekent dat ADHD Medisch Centrum een groot deel van de kosten die niet door uw zorgverzekeraar worden vergoed, kwijtscheldt. U hoeft dat deel dus niet zelf te betalen.</p><p>Om deze regeling mogelijk te maken, vragen wij na het adviesgesprek een eenmalige persoonlijke bijdrage van <strong>€250</strong> voor het volledige diagnostiek- en behandeltraject. Deze bijdrage staat los van het verplichte eigen risico van uw zorgverzekering.</p>',
		] );

		$this->add_control( 'step8_conditions_title', [
			'label'   => esc_html__( 'Conditions heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Voorwaarden',
		] );

		$this->add_control( 'step8_conditions', [
			'label'       => esc_html__( 'Conditions (one per line)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 10,
			'default'     => "De eigen bijdrage van €250 binnen de betalingstermijn betalen.\nDeclaratiefacturen tijdig bij uw zorgverzekeraar indienen.\nVolledige vergoedingenoverzichten tijdig aan ons verstrekken.\nVergoedingen die uw zorgverzekeraar rechtstreeks aan u uitbetaalt binnen 14 dagen aan ons doorbetalen.\nUw verplichte en eventuele vrijwillige eigen risico betalen.\nOverige betalingsfacturen binnen 14 dagen betalen.\nTijdig en volledig meewerken aan declaraties en machtigingsaanvragen.\nGevraagde informatie en documenten tijdig, volledig en naar waarheid aanleveren.",
		] );

		$this->add_control( 'step8_excluded_title', [
			'label'   => esc_html__( 'Exclusions heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wat valt niet onder de coulanceregeling?',
			'label_block' => true,
		] );

		$this->add_control( 'step8_excluded', [
			'label'       => esc_html__( 'Exclusions (one per line)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 8,
			'default'     => "De eigen bijdrage van €250.\nHet verplichte en eventuele vrijwillige eigen risico.\nNo-showfacturen.\nBedragen die uw zorgverzekeraar rechtstreeks aan u heeft uitbetaald.\nZorg die niet onder de verzekerde zorg valt en waarover u vooraf bent geïnformeerd.\nKosten die niet worden vergoed doordat gevraagde informatie of medewerking ontbreekt.",
		] );

		$this->add_control( 'step8_button', [
			'label'   => esc_html__( 'Final button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Bekijk uw kosteninschatting',
			'label_block' => true,
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

	private function register_result_controls() {
		$this->start_controls_section( 'section_result', [
			'label' => esc_html__( 'Result screen', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'result_kicker', [
			'label'   => esc_html__( 'Kicker', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw resultaat',
		] );

		$this->add_control( 'result_title', [
			'label'   => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw persoonlijke kosteninschatting',
			'label_block' => true,
		] );

		$this->add_control( 'result_avg_label', [
			'label'   => esc_html__( 'Average invoice label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Gemiddeld factuurbedrag',
			'label_block' => true,
		] );

		$this->add_control( 'result_avg_note', [
			'label'   => esc_html__( 'Average invoice note', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Dit is een gemiddeld bedrag voor een volledig traject. Het werkelijke factuurbedrag verschilt per patiënt en kan hoger of lager uitvallen, omdat het afhangt van de zorg die u nodig heeft en de tijd die daaraan wordt besteed (NZa-tarieven, zorgprestatiemodel).',
		] );

		$this->add_control( 'result_reimbursed_label', [
			'label'       => esc_html__( 'Reimbursed label', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {amount}, {percentage}, {basis}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Uw zorgverzekeraar vergoedt ± {amount}',
			'label_block' => true,
		] );

		$this->add_control( 'result_reimbursed_text', [
			'label'   => esc_html__( 'Reimbursed description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Dit deel wordt vergoed uit uw basisverzekering ({percentage}% van het {basis}).',
		] );

		$this->add_control( 'result_waived_label', [
			'label'       => esc_html__( 'Waived label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'ADHD Medisch Centrum scheldt ± {amount} kwijt',
			'label_block' => true,
		] );

		$this->add_control( 'result_waived_text', [
			'label'   => esc_html__( 'Waived description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Dit niet-vergoede deel schelden wij kwijt via onze coulanceregeling. Wij betalen dit niet aan u of aan uw zorgverzekeraar: het bedrag komt simpelweg te vervallen, dus u hoeft het niet zelf te betalen.',
		] );

		$this->add_control( 'result_own_kicker', [
			'label'   => esc_html__( 'Own costs kicker', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw eigen kosten',
		] );

		$this->add_control( 'result_own_title', [
			'label'   => esc_html__( 'Own costs title', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wat betaalt u zelf?',
			'label_block' => true,
		] );

		$this->add_control( 'result_contribution_label', [
			'label'   => esc_html__( 'Contribution row label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Eenmalige persoonlijke bijdrage',
			'label_block' => true,
		] );

		$this->add_control( 'result_contribution_text', [
			'label'   => esc_html__( 'Contribution row description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Betaalt u aan ADHD Medisch Centrum.',
			'label_block' => true,
		] );

		$this->add_control( 'result_deductible_label', [
			'label'   => esc_html__( 'Deductible row label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Uw eigen risico',
			'label_block' => true,
		] );

		$this->add_control( 'result_deductible_text', [
			'label'   => esc_html__( 'Deductible row description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Betaalt u aan uw zorgverzekeraar, niet aan ons.',
			'label_block' => true,
		] );

		$this->add_control( 'result_total_label', [
			'label'   => esc_html__( 'Total row label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Totaal dat u zelf betaalt',
			'label_block' => true,
		] );

		$this->add_control( 'result_summary', [
			'label'       => esc_html__( 'Summary paragraph', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {invoice}, {reimbursed}, {waived}, {contribution}, {deductible}, {total}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => 'Wij rekenen in dit voorbeeld met een gemiddeld factuurbedrag van ongeveer {invoice}; uw werkelijke factuur kan hoger of lager zijn. Uw zorgverzekeraar vergoedt hiervan naar verwachting {reimbursed}. Het overige niet-vergoede bedrag van ongeveer {waived} scheldt ADHD Medisch Centrum kwijt via de coulanceregeling. U betaalt een eenmalige persoonlijke bijdrage van {contribution}. Daarnaast kan uw zorgverzekeraar nog {deductible} aan eigen risico bij u in rekening brengen. Uw totale verwachte eigen kosten bedragen daarmee ongeveer {total}.',
		] );

		$this->add_control( 'result_summary_unknown', [
			'label'       => esc_html__( 'Summary paragraph (used deductible unknown)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Placeholders: {invoice}, {reimbursed}, {waived}, {contribution}, {totalMin}, {totalMax}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => 'Wij rekenen in dit voorbeeld met een gemiddeld factuurbedrag van ongeveer {invoice}; uw werkelijke factuur kan hoger of lager zijn. Uw zorgverzekeraar vergoedt hiervan naar verwachting {reimbursed}. Het overige niet-vergoede bedrag van ongeveer {waived} scheldt ADHD Medisch Centrum kwijt via de coulanceregeling. U betaalt een eenmalige persoonlijke bijdrage van {contribution}. Omdat u niet weet hoeveel eigen risico u al heeft gebruikt, liggen uw totale verwachte eigen kosten tussen {totalMin} en {totalMax}.',
		] );

		$this->add_control( 'result_note_policy_unknown', [
			'label'       => esc_html__( 'Warning: policy unknown', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => 'Omdat u niet weet welk type basisverzekering u heeft, is deze berekening minder nauwkeurig.',
		] );

		$this->add_control( 'result_note_deductible_unknown', [
			'label'       => esc_html__( 'Warning: used deductible unknown', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => 'Omdat u niet weet hoeveel eigen risico u al heeft gebruikt, tonen wij een minimum- en maximumbedrag.',
		] );

		$this->add_control( 'result_disclaimer', [
			'label'   => esc_html__( 'Disclaimer', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => 'Deze berekening is een persoonlijke inschatting en geen garantie. De uiteindelijke kosten zijn afhankelijk van de daadwerkelijk geleverde zorg, de verwerking van de declaratie, uw polisvoorwaarden, uw resterende eigen risico en de definitieve vergoeding van uw zorgverzekeraar. Aan deze berekening kunnen geen rechten worden ontleend.',
		] );

		$this->add_control( 'result_restart_label', [
			'label'   => esc_html__( 'Restart button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Berekening opnieuw maken',
			'label_block' => true,
		] );

		$this->add_control( 'signup_label', [
			'label'   => esc_html__( 'Sign-up button text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Aanmelden',
		] );

		$this->add_control( 'signup_link', [
			'label'   => esc_html__( 'Sign-up button link', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::URL,
			'default' => [ 'url' => '#' ],
		] );

		$this->end_controls_section();
	}

	private function register_style_controls() {

		$this->start_controls_section( 'section_style_colors', [
			'label' => esc_html__( 'Colors', 'zorgkosten-calculator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'color_primary', [
			'label'     => esc_html__( 'Primary (accent)', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#E8756B',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-primary: {{VALUE}};' ],
		] );

		$this->add_control( 'color_dark', [
			'label'     => esc_html__( 'Dark (secondary)', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#333C4E',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-dark: {{VALUE}};' ],
		] );

		$this->add_control( 'color_heading', [
			'label'     => esc_html__( 'Headings', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#1E2430',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-heading: {{VALUE}};' ],
		] );

		$this->add_control( 'color_text', [
			'label'     => esc_html__( 'Body text', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#4A5261',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-text: {{VALUE}};' ],
		] );

		$this->add_control( 'color_muted', [
			'label'     => esc_html__( 'Muted text', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#8A8F99',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-muted: {{VALUE}};' ],
		] );

		$this->add_control( 'color_page_bg', [
			'label'     => esc_html__( 'Page background', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#F6F7F9',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-page-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'color_card_bg', [
			'label'     => esc_html__( 'Card background', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#FFFFFF',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-card-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'color_border', [
			'label'     => esc_html__( 'Borders', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#E5E7EB',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-border: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_layout', [
			'label' => esc_html__( 'Layout', 'zorgkosten-calculator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'card_radius', [
			'label'      => esc_html__( 'Card border radius', 'zorgkosten-calculator' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'default'    => [ 'size' => 24, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .zkc' => '--zkc-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'max_width', [
			'label'      => esc_html__( 'Max width', 'zorgkosten-calculator' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [ 'px' => [ 'min' => 600, 'max' => 1600 ] ],
			'default'    => [ 'size' => 1140, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .zkc-inner' => 'max-width: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_typo', [
			'label' => esc_html__( 'Typography', 'zorgkosten-calculator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'typo_title',
			'label'    => esc_html__( 'Step titles', 'zorgkosten-calculator' ),
			'selector' => '{{WRAPPER}} .zkc .zkc-title',
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'typo_body',
			'label'    => esc_html__( 'Body', 'zorgkosten-calculator' ),
			'selector' => '{{WRAPPER}} .zkc',
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_buttons', [
			'label' => esc_html__( 'Buttons', 'zorgkosten-calculator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'btn_text_color', [
			'label'     => esc_html__( 'Primary button text', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#FFFFFF',
			'selectors' => [ '{{WRAPPER}} .zkc' => '--zkc-btn-text: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'btn_radius', [
			'label'      => esc_html__( 'Button radius', 'zorgkosten-calculator' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'default'    => [ 'size' => 999, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .zkc' => '--zkc-btn-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	/* -------------------------------------------------------------------------
	 * Render
	 * ---------------------------------------------------------------------- */

	protected function render() {
		$s = $this->get_settings_for_display();

		// Build insurer data.
		$insurers = [];
		foreach ( (array) ( $s['insurers'] ?? [] ) as $row ) {
			$logo = '';
			if ( ! empty( $row['ins_logo']['url'] ) ) {
				$logo = $row['ins_logo']['url'];
			} elseif ( ! empty( $row['ins_logo_url'] ) ) {
				$logo = $row['ins_logo_url'];
			}
			$insurers[] = [
				'group'      => (string) ( $row['ins_group'] ?? '' ),
				'name'       => (string) ( $row['ins_name'] ?? '' ),
				'logo'       => $logo,
				'agreement'  => ! empty( $row['ins_agreement'] ),
				'machtiging' => ! empty( $row['ins_machtiging'] ),
			];
		}

		// Build policy data.
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

		$signup_url    = ! empty( $s['signup_link']['url'] ) ? $s['signup_link']['url'] : '#';
		$signup_target = ! empty( $s['signup_link']['is_external'] ) ? '_blank' : '';

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

		$config = [
			'general' => [
				'brand'       => (string) $s['brand_name'],
				'showHeader'  => ! empty( $s['show_header'] ),
				'restart'     => (string) $s['restart_label'],
				'stepCounter' => (string) $s['step_counter_text'],
				'back'        => (string) $s['back_label'],
				'next'        => (string) $s['next_label'],
				'helpPrefix'  => (string) $s['help_prefix'],
				'unknown'     => (string) $s['unknown_label'],
				'footer'      => (string) $s['footer_text'],
				'showFooter'  => ! empty( $s['show_footer'] ),
			],
			'intro' => [
				'kicker' => (string) $s['intro_kicker'],
				'title'  => (string) $s['intro_title'],
				'text'   => (string) $s['intro_text'],
				'button' => (string) $s['intro_button'],
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
					'heroKicker'        => (string) $s['step6_hero_kicker'],
					'heroTitle'         => (string) $s['step6_hero_title'],
					'heroTitleFallback' => (string) $s['step6_hero_title_fallback'],
					'heroBasis'         => (string) $s['step6_hero_basis'],
					'estimateKicker'    => (string) $s['step6_estimate_kicker'],
					'estimateNote'      => (string) $s['step6_estimate_note'],
					'message'           => (string) $s['step6_message'],
					'messageUnknown'    => (string) $s['step6_message_unknown'],
					'basisKicker'       => (string) $s['step6_basis_kicker'],
					'accordionLabel'    => (string) $s['step6_accordion_label'],
					'footnote'          => (string) $s['step6_footnote'],
				],
				's7' => [
					'title'      => (string) $s['step7_title'],
					'yesBadge'   => (string) $s['step7_yes_badge'],
					'yesIntro'   => (string) $s['step7_yes_intro'],
					'yesContent' => (string) $s['step7_yes_content'],
					'yesNote'    => (string) $s['step7_yes_note'],
					'noBadge'    => (string) $s['step7_no_badge'],
					'noIntro'    => (string) $s['step7_no_intro'],
					'noContent'  => (string) $s['step7_no_content'],
					'noNote'     => (string) $s['step7_no_note'],
				],
				'machtiging' => [
					'title'     => (string) $s['step7_machtiging_title'],
					'content'   => (string) $s['step7_machtiging_content'],
					'asksTitle' => (string) $s['machtiging_asks_title'],
					'asks'      => $lines( $s['machtiging_asks'] ),
					'footnote'  => (string) $s['machtiging_footnote'],
				],
				's8' => [
					'title'           => (string) $s['step8_title'],
					'intro'           => (string) $s['step8_intro'],
					'conditionsTitle' => (string) $s['step8_conditions_title'],
					'conditions'      => $lines( $s['step8_conditions'] ),
					'excludedTitle'   => (string) $s['step8_excluded_title'],
					'excluded'        => $lines( $s['step8_excluded'] ),
					'button'          => (string) $s['step8_button'],
				],
			],
			'result' => [
				'kicker'            => (string) $s['result_kicker'],
				'title'             => (string) $s['result_title'],
				'avgLabel'          => (string) $s['result_avg_label'],
				'avgNote'           => (string) $s['result_avg_note'],
				'reimbursedLabel'   => (string) $s['result_reimbursed_label'],
				'reimbursedText'    => (string) $s['result_reimbursed_text'],
				'waivedLabel'       => (string) $s['result_waived_label'],
				'waivedText'        => (string) $s['result_waived_text'],
				'ownKicker'         => (string) $s['result_own_kicker'],
				'ownTitle'          => (string) $s['result_own_title'],
				'contributionLabel' => (string) $s['result_contribution_label'],
				'contributionText'  => (string) $s['result_contribution_text'],
				'deductibleLabel'   => (string) $s['result_deductible_label'],
				'deductibleText'    => (string) $s['result_deductible_text'],
				'totalLabel'        => (string) $s['result_total_label'],
				'summary'           => (string) $s['result_summary'],
				'summaryUnknown'    => (string) $s['result_summary_unknown'],
				'notePolicyUnknown' => (string) $s['result_note_policy_unknown'],
				'noteDeductibleUnknown' => (string) $s['result_note_deductible_unknown'],
				'disclaimer'        => (string) $s['result_disclaimer'],
				'restart'           => (string) $s['result_restart_label'],
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
