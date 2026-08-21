<?php
/**
 * Zorgkosten Cost Calculator – general content controls: intro, insurers, policies,
 * navigation/sidebar texts, calculation amounts and illustrations
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

trait ZKC_Controls_General {

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
			'label'   => esc_html__( 'Group (concern)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => '',
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

		$repeater->add_control( 'ins_agreement', [
			'label'   => esc_html__( 'Payment agreement (betaalovereenkomst)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$repeater->add_control( 'ins_machtiging', [
			'label'   => esc_html__( 'Authorization step (machtiging)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$repeater->add_control( 'ins_declare_url', [
			'label'       => esc_html__( 'Declaration page URL', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$repeater->add_control( 'ins_machtiging_url', [
			'label'       => esc_html__( 'Authorization URL (optional)', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$this->add_control( 'insurers', [
			'label'       => esc_html__( 'Insurers', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => ZKC_Defaults::insurers(),
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
			'options' => ZKC_Defaults::basis_options(),
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
			'default'     => ZKC_Defaults::policies(),
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
			'options' => ZKC_Defaults::basis_options(),
			'default' => 'gemiddeld_gecontracteerd',
		] );

		$this->add_control( 'heading_bases', [
			'label'     => esc_html__( 'Tariff basis wording', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'bases_note', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'How each tariff basis is written out in sentences, e.g. "van het gemiddeld gecontracteerd tarief".', 'zorgkosten-calculator' ),
			'content_classes' => 'elementor-descriptor',
		] );

		$basis_repeater = new Repeater();

		$basis_repeater->add_control( 'basis_key', [
			'label'   => esc_html__( 'Basis', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SELECT,
			'options' => ZKC_Defaults::basis_options(),
			'default' => 'gemiddeld_gecontracteerd',
		] );

		$basis_repeater->add_control( 'basis_label', [
			'label'       => esc_html__( 'Wording used in sentences', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$this->add_control( 'bases', [
			'label'       => esc_html__( 'Tariff bases', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $basis_repeater->get_controls(),
			'default'     => ZKC_Defaults::bases(),
			'title_field' => '{{{ basis_label }}}',
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

		$this->add_control( 'seg_jump', [
			'label'       => esc_html__( 'Progress segment tooltip (visited)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {step}. Visited segments jump back to that step.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Naar stap {step}',
			'label_block' => true,
		] );

		$this->add_control( 'seg_later', [
			'label'       => esc_html__( 'Progress segment tooltip (not reached)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {step}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Stap {step} volgt later',
			'label_block' => true,
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

	private function register_calculation_controls() {
		$this->start_controls_section( 'section_calculation', [
			'label' => esc_html__( 'Calculation', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'diagnostiek_amount', [
			'label'       => esc_html__( 'Average invoice – diagnostics (€)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'The whole trajectory is diagnostics + treatment.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'default'     => 2000,
		] );

		$this->add_control( 'behandeling_amount', [
			'label'   => esc_html__( 'Average invoice – treatment (€)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 0,
			'default' => 4000,
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
}
