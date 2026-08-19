<?php
/**
 * Zorgkosten Cost Calculator – controls for the input steps (1-5).
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

trait ZKC_Controls_Steps {

	/** Icon choices for the icon cards, matching the reference app's set. */
	private function icon_options() {
		return [
			'landmark'     => esc_html__( 'Landmark (government / tariffs)', 'zorgkosten-calculator' ),
			'clock'        => esc_html__( 'Clock (time)', 'zorgkosten-calculator' ),
			'users'        => esc_html__( 'Users (people)', 'zorgkosten-calculator' ),
			'coins'        => esc_html__( 'Coins (money)', 'zorgkosten-calculator' ),
			'shield-check' => esc_html__( 'Shield with check (protection)', 'zorgkosten-calculator' ),
		];
	}

	/** Repeater of icon + title + text cards. */
	private function icon_card_repeater() {
		$repeater = new Repeater();

		$repeater->add_control( 'card_icon', [
			'label'   => esc_html__( 'Icon', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::SELECT,
			'options' => $this->icon_options(),
			'default' => 'landmark',
		] );

		$repeater->add_control( 'card_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$repeater->add_control( 'card_text', [
			'label' => esc_html__( 'Text', 'zorgkosten-calculator' ),
			'type'  => Controls_Manager::TEXTAREA,
			'rows'  => 4,
		] );

		return $repeater;
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
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
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

		$this->add_control( 'step3_subtitle', [
			'label'   => esc_html__( 'Subtitle', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
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

		/* ---- Step 5: how the costs are determined ---- */
		$this->start_controls_section( 'section_step5', [
			'label' => esc_html__( 'Step 5 – How costs are determined', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step5_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Hoe worden de kosten van uw traject bepaald?',
			'label_block' => true,
		] );

		$this->add_control( 'step5_cards', [
			'label'       => esc_html__( 'Icon cards', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $this->icon_card_repeater()->get_controls(),
			'title_field' => '{{{ card_title }}}',
			'default'     => [
				[
					'card_icon'  => 'landmark',
					'card_title' => 'De tarieven worden landelijk vastgesteld',
					'card_text'  => 'De Nederlandse Zorgautoriteit (NZa) bepaalt de tarieven voor diagnostiek en behandeling in de specialistische GGZ. Deze tarieven gelden voor alle GGZ-instellingen. ADHD Medisch Centrum stelt de tarieven dus niet zelf vast en kan er niets aan veranderen.',
				],
				[
					'card_icon'  => 'clock',
					'card_title' => 'Zorgprestatiemodel: tijd en type zorg bepalen de kosten',
					'card_text'  => 'Het bedrag hangt af van het type zorg, de duur en het type contact en de betrokken zorgverleners.',
				],
				[
					'card_icon'  => 'users',
					'card_title' => 'Daarom verschilt het totaalbedrag per patiënt',
					'card_text'  => 'Uw factuur kan hoger of lager zijn dan een gemiddeld traject.',
				],
			],
		] );

		$this->add_control( 'step5_link_label', [
			'label'       => esc_html__( 'Link text', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Meer over de NZa-tarieven',
			'label_block' => true,
		] );

		$this->add_control( 'step5_link_url', [
			'label'       => esc_html__( 'Link URL', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'https://www.nza.nl/',
			'label_block' => true,
		] );

		$this->end_controls_section();
	}
}
