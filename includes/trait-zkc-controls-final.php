<?php
/**
 * Zorgkosten Cost Calculator – controls for the closing steps:
 * 9 authorization (machtiging, conditional), 10 paying for the care.
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

trait ZKC_Controls_Final {

	/** Repeater of numbered steps (title + text). */
	private function numbered_step_repeater() {
		$repeater = new Repeater();

		$repeater->add_control( 'num_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'label_block' => true,
		] );

		$repeater->add_control( 'num_text', [
			'label'       => esc_html__( 'Text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer} and {declareLink}. Basic HTML is allowed.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
		] );

		return $repeater;
	}

	private function register_machtiging_controls() {
		$this->start_controls_section( 'section_step9', [
			'label' => esc_html__( 'Step 9 – Authorization (machtiging)', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'machtiging_note', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Only shown for insurers with the authorization toggle on. For every other insurer the calculator has 9 steps instead of 10.', 'zorgkosten-calculator' ),
			'content_classes' => 'elementor-descriptor',
		] );

		$this->add_control( 'step9_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Toestemming vooraf regelen',
			'label_block' => true,
		] );

		$this->add_control( 'step9_subtitle', [
			'label'       => esc_html__( 'Subtitle', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => 'Voor de start van de behandeling is mogelijk toestemming nodig van {insurer}. Dit wordt ook wel een machtiging genoemd. Voor diagnostiek is geen toestemming of machtiging nodig.',
		] );

		$this->add_control( 'step9_means_title', [
			'label'       => esc_html__( '"What does that mean" title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat betekent dat voor u?',
			'label_block' => true,
		] );

		$this->add_control( 'step9_means_note', [
			'label'   => esc_html__( '"What does that mean" intro', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Zo weet u vóór de start van de behandeling waar u aan toe bent.',
		] );

		$this->add_control( 'step9_means', [
			'label'       => esc_html__( '"What does that mean" list (one per line)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => "De diagnostiek kan gewoon starten, daarvoor is geen machtiging nodig.\nVoor start van behandeling is mogelijk toestemming nodig van {insurer}.\n{insurer} beslist over de aanvraag. Een machtiging betekent niet automatisch dat alle kosten volledig worden vergoed.",
		] );

		$this->add_control( 'step9_asks_title', [
			'label'       => esc_html__( '"What we ask" heading', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat vragen wij van u?',
			'label_block' => true,
		] );

		$this->add_control( 'step9_asks', [
			'label'   => esc_html__( '"What we ask" list (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 6,
			'default' => "Documenten tijdig aanleveren.\nFormulieren invullen en ondertekenen.\nVragen van ons of uw zorgverzekeraar snel beantwoorden.\nWijzigingen in uw polis direct doorgeven.",
		] );

		$this->add_control( 'step9_check_title', [
			'label'       => esc_html__( '"Check with your insurer" heading', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Only shown when the insurer has an authorization or declaration URL.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Controleer dit bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'step9_check_text', [
			'label'       => esc_html__( '"Check with your insurer" text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Op de website van {insurer} leest u wanneer toestemming nodig is en hoe u die aanvraagt.',
		] );

		$this->add_control( 'step9_check_button', [
			'label'       => esc_html__( 'Button text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Naar {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'step9_warning', [
			'label'   => esc_html__( 'Closing warning', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => 'Zonder tijdige toestemming kan de behandeling niet starten. Kosten die daardoor niet worden vergoed, blijven voor uw rekening en vallen niet onder de coulanceregeling.',
		] );

		$this->end_controls_section();
	}

	private function register_payment_controls() {
		$this->start_controls_section( 'section_step10', [
			'label' => esc_html__( 'Step 10 – Paying for your care', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step10_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Hoe betaalt u uw zorg?',
			'label_block' => true,
		] );

		$this->add_control( 'step10_subtitle_direct', [
			'label'       => esc_html__( 'Subtitle – WITH payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Bij {insurer} regelen wij het declareren voor u.',
		] );

		$this->add_control( 'step10_subtitle_self', [
			'label'       => esc_html__( 'Subtitle – WITHOUT payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Bij {insurer} dient u de factuur zelf in. Zo werkt dat stap voor stap.',
		] );

		$this->add_control( 'step10_steps_direct', [
			'label'       => esc_html__( 'Numbered steps – WITH payment agreement', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $this->numbered_step_repeater()->get_controls(),
			'title_field' => '{{{ num_title }}}',
			'default'     => [
				[
					'num_title' => 'Wij dienen de zorg rechtstreeks in',
					'num_text'  => 'ADHD Medisch Centrum stuurt de declaratie direct naar {insurer}.',
				],
				[
					'num_title' => 'Uw zorgverzekeraar betaalt aan ons',
					'num_text'  => 'De vergoeding gaat rechtstreeks naar ADHD Medisch Centrum.',
				],
				[
					'num_title' => 'U hoeft zelf niets in te dienen',
					'num_text'  => 'U ontvangt van ons geen declaratiefactuur voor het verzekerde deel.',
				],
				[
					'num_title' => 'Eigen risico',
					'num_text'  => '{insurer} verrekent uw openstaande eigen risico zelf met u. U ontvangt daarvoor geen factuur van ons.',
				],
			],
		] );

		$this->add_control( 'step10_steps_self', [
			'label'       => esc_html__( 'Numbered steps – WITHOUT payment agreement', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $this->numbered_step_repeater()->get_controls(),
			'title_field' => '{{{ num_title }}}',
			'default'     => [
				[
					'num_title' => 'U ontvangt een declaratiefactuur van ons',
					'num_text'  => 'Digitaal of op papier, met de geleverde zorg en het bedrag volgens de NZa-tarieven.',
				],
				[
					'num_title' => 'U dient de factuur in bij {insurer}',
					'num_text'  => 'Dit kan meestal via de app of het online portaal van uw zorgverzekeraar.{declareLink}',
				],
				[
					'num_title' => 'U stuurt ons het vergoedingenoverzicht',
					'num_text'  => 'Mail het overzicht naar <a href="mailto:facturen@adhdmc.nl">facturen@adhdmc.nl</a>. Daarmee stellen wij vast wat is vergoed en wat wij kwijtschelden.',
				],
				[
					'num_title' => 'U betaalt de vergoeding aan ons door',
					'num_text'  => 'De vergoeding die op uw rekening wordt gestort, maakt u binnen 14 dagen aan ons over.',
				],
			],
		] );

		$this->add_control( 'step10_declare_link_label', [
			'label'       => esc_html__( '{declareLink} text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Rendered as a link to the insurer\'s declaration page.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Declareren bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'heading_step10_risk', [
			'label'     => esc_html__( 'Deductible warning', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step10_risk_label', [
			'label'       => esc_html__( 'Warning label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Let op: eigen risico',
			'label_block' => true,
		] );

		$this->add_control( 'step10_risk_direct', [
			'label'       => esc_html__( 'Warning – WITH payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => 'Heeft u nog eigen risico openstaan? Dan verrekent {insurer} dat zelf met u. U ontvangt hiervoor geen factuur van ADHD Medisch Centrum. Het eigen risico valt niet onder de coulanceregeling.',
		] );

		$this->add_control( 'step10_risk_self', [
			'label'       => esc_html__( 'Warning – WITHOUT payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => 'Is uw eigen risico nog niet volledig verbruikt? Dan zal {insurer} uw eigen risico verrekenen met onze factuur. {insurer} trekt dit bedrag af van de vergoeding en keert daardoor minder aan u uit. Het eigen risico betaalt u dan rechtstreeks aan ADHD Medisch Centrum. U hoeft het eigen risico dus niet óók nog aan {insurer} te betalen; u betaalt het maar één keer. Het eigen risico valt niet onder onze coulanceregeling.',
		] );

		$this->add_control( 'heading_step10_know', [
			'label'     => esc_html__( '"Good to know" (only without a payment agreement)', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step10_know_title', [
			'label'       => esc_html__( 'Heading', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Goed om te weten',
			'label_block' => true,
		] );

		$this->add_control( 'step10_know_text', [
			'label'   => esc_html__( 'Text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => 'Een declaratiefactuur is geen betaalverzoek voor het volledige bedrag. Uw zorgverzekeraar bepaalt de vergoeding op basis van uw polisvoorwaarden. Het deel dat niet wordt vergoed, schelden wij grotendeels kwijt via onze coulanceregeling.',
		] );

		$this->add_control( 'step10_next_label', [
			'label'       => esc_html__( 'Final button text', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Bekijk uw kosteninschatting',
			'label_block' => true,
		] );

		$this->end_controls_section();
	}
}
