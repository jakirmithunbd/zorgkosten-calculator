<?php
/**
 * Zorgkosten Cost Calculator – controls for the money steps:
 * 6 reimbursement, 7 goodwill scheme, 8 personal contribution.
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

trait ZKC_Controls_Money {

	private function register_reimbursement_controls() {
		$this->start_controls_section( 'section_step6', [
			'label' => esc_html__( 'Step 6 – Reimbursement', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step6_hero_title', [
			'label'       => esc_html__( 'Hero title', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
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
			'description' => esc_html__( 'Use {basis}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'van het {basis}',
			'label_block' => true,
		] );

		$this->add_control( 'heading_step6_example', [
			'label'     => esc_html__( 'Worked example', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step6_example_title', [
			'label'       => esc_html__( 'Box title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Rekenvoorbeeld voor een heel traject',
			'label_block' => true,
		] );

		$this->add_control( 'step6_label_diagnostiek', [
			'label'   => esc_html__( 'Row 1 label (diagnostics)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Diagnostiek',
		] );

		$this->add_control( 'step6_label_behandeling', [
			'label'   => esc_html__( 'Row 2 label (treatment)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Behandeling',
		] );

		$this->add_control( 'step6_label_total', [
			'label'   => esc_html__( 'Row 3 label (whole trajectory)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Heel traject',
		] );

		$this->add_control( 'step6_invoice_label', [
			'label'       => esc_html__( 'Invoice line under each row', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {amount}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'factuur ± {amount}',
			'label_block' => true,
		] );

		$this->add_control( 'step6_reimbursed_suffix', [
			'label'   => esc_html__( 'Suffix after the reimbursed amount', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'vergoed',
		] );

		$this->add_control( 'step6_not_reimbursed_suffix', [
			'label'   => esc_html__( 'Suffix after the non-reimbursed amount', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'niet vergoed',
		] );

		$this->add_control( 'step6_example_note', [
			'label'   => esc_html__( 'Note under the example', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Dit is een rekenvoorbeeld met gemiddelde bedragen. Uw werkelijke factuur kan hoger of lager uitvallen.',
		] );

		$this->add_control( 'heading_step6_uncovered', [
			'label'     => esc_html__( '"Not reimbursed" panel', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step6_uncovered_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'En het deel dat niet wordt vergoed?',
			'label_block' => true,
		] );

		$this->add_control( 'step6_uncovered_text', [
			'label'       => esc_html__( 'Text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {amount} for the highlighted non-reimbursed amount. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => 'Dat betaalt u niet zelf. Van de {amount} die niet wordt vergoed, schelden wij het grootste deel kwijt. In de volgende stap leest u hoe onze coulanceregeling werkt.',
		] );

		$this->end_controls_section();
	}

	private function register_coulance_controls() {
		$this->start_controls_section( 'section_step7', [
			'label' => esc_html__( 'Step 7 – Goodwill scheme (coulance)', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step7_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Coulanceregeling',
			'label_block' => true,
		] );

		$this->add_control( 'step7_subtitle', [
			'label'   => esc_html__( 'Subtitle', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => 'Om de zorg toegankelijk te houden, hanteren wij een coulanceregeling. Dat houdt in dat het grootste deel van het niet-vergoede bedrag wordt kwijtgescholden zolang u zich aan de afspraken hieronder houdt.',
		] );

		$this->add_control( 'step7_hero_kicker', [
			'label'       => esc_html__( 'Hero kicker', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Het deel dat niet wordt vergoed, schelden wij grotendeels kwijt',
			'label_block' => true,
		] );

		$this->add_control( 'step7_hero_note', [
			'label'       => esc_html__( 'Line under the amount', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {total} for the average trajectory amount. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'niet vergoed bij een traject van ± {total}, dit bedrag betaalt u dus niet zelf',
		] );

		$this->add_control( 'step7_means_title', [
			'label'       => esc_html__( '"What does that mean" title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat betekent dat voor u?',
			'label_block' => true,
		] );

		$this->add_control( 'step7_means', [
			'label'   => esc_html__( '"What does that mean" list (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 6,
			'default' => "U krijgt geen rekening voor het niet-vergoede deel van de zorg die onder de regeling valt.\nZo blijft de zorg toegankelijk, ook als uw polis niet alles vergoedt.\nU betaalt zelf alleen uw eigen risico en de eenmalige persoonlijke bijdrage, daarover leest u meer in de volgende stap.",
		] );

		$this->add_control( 'heading_step7_asks', [
			'label'     => esc_html__( 'Conditions', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step7_asks_title', [
			'label'       => esc_html__( 'Conditions heading', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat vragen wij van u?',
			'label_block' => true,
		] );

		$this->add_control( 'step7_asks_direct', [
			'label'       => esc_html__( 'Conditions – WITH payment agreement (one per line)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {contribution} and {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => "De eenmalige bijdrage van {contribution} binnen de betalingstermijn betalen.\nUw verplichte en eventuele vrijwillige eigen risico betalen.\nOverige facturen binnen 14 dagen betalen.\nMeewerken aan machtigingsaanvragen en gevraagde gegevens tijdig aanleveren.",
		] );

		$this->add_control( 'step7_asks_self', [
			'label'       => esc_html__( 'Conditions – WITHOUT payment agreement (one per line)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {contribution} and {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 9,
			'default'     => "De eenmalige bijdrage van {contribution} binnen de betalingstermijn betalen.\nDeclaratiefacturen tijdig bij {insurer} indienen.\nVergoedingenoverzichten tijdig en volledig aan ons sturen.\nVergoedingen die op uw rekening worden gestort binnen 14 dagen doorbetalen.\nUw verplichte en eventuele vrijwillige eigen risico betalen.\nOverige facturen binnen 14 dagen betalen.\nMeewerken aan declaraties en machtigingsaanvragen en gevraagde gegevens tijdig aanleveren.",
		] );

		$this->add_control( 'heading_step7_excl', [
			'label'     => esc_html__( 'Exclusions', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'step7_excl_title', [
			'label'       => esc_html__( 'Exclusions heading', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat valt niet onder de coulanceregeling?',
			'label_block' => true,
		] );

		$this->add_control( 'step7_excl_note', [
			'label'   => esc_html__( 'Exclusions intro', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Onderstaande onderdelen vallen niet onder de coulanceregeling en worden dus niet kwijtgescholden:',
		] );

		$this->add_control( 'step7_excl_direct', [
			'label'       => esc_html__( 'Exclusions – WITH payment agreement (one per line)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {contribution}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => "De eenmalige bijdrage van {contribution}.\nHet verplichte en eventuele vrijwillige eigen risico.\nNo-showfacturen.\nZorg die niet onder de verzekerde zorg valt en waarover u vooraf bent geïnformeerd.\nKosten die niet worden vergoed doordat gevraagde informatie of medewerking ontbreekt.",
		] );

		$this->add_control( 'step7_excl_self', [
			'label'       => esc_html__( 'Exclusions – WITHOUT payment agreement (one per line)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {contribution}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 7,
			'default'     => "De eenmalige bijdrage van {contribution}.\nHet verplichte en eventuele vrijwillige eigen risico.\nNo-showfacturen.\nBedragen die uw zorgverzekeraar rechtstreeks aan u heeft uitbetaald.\nZorg die niet onder de verzekerde zorg valt en waarover u vooraf bent geïnformeerd.\nKosten die niet worden vergoed doordat gevraagde informatie of medewerking ontbreekt.",
		] );

		$this->end_controls_section();
	}

	private function register_contribution_controls() {
		$this->start_controls_section( 'section_step8', [
			'label' => esc_html__( 'Step 8 – Personal contribution', 'zorgkosten-calculator' ),
		] );

		$this->add_control( 'step8_title', [
			'label'       => esc_html__( 'Title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Eigen bijdrage',
			'label_block' => true,
		] );

		$this->add_control( 'step8_subtitle', [
			'label'       => esc_html__( 'Subtitle', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {contribution}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 6,
			'default'     => 'Om onze coulanceregeling mogelijk te maken, vragen wij een persoonlijke bijdrage van {contribution} voor het volledige diagnostiek- en behandeltraject. Deze bijdrage dekt slechts een deel van de kosten die niet door uw zorgverzekeraar worden vergoed. Het resterende bedrag nemen wij voor onze rekening. De persoonlijke bijdrage staat los van het eigen risico.',
		] );

		$this->add_control( 'step8_hero_kicker', [
			'label'       => esc_html__( 'Hero kicker', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Uw eenmalige persoonlijke bijdrage',
			'label_block' => true,
		] );

		$this->add_control( 'step8_hero_note', [
			'label'       => esc_html__( 'Line under the amount', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'voor het volledige diagnostiek- en behandeltraject',
			'label_block' => true,
		] );

		$this->add_control( 'step8_means_title', [
			'label'       => esc_html__( '"What does that mean" title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat betekent dat voor u?',
			'label_block' => true,
		] );

		$this->add_control( 'step8_means', [
			'label'   => esc_html__( '"What does that mean" list (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 5,
			'default' => "U betaalt deze bijdrage één keer, ook als er alleen diagnostiek plaatsvindt.\nGaat u na de diagnostiek verder met een behandeling, dan komt er geen tweede bijdrage bij.",
		] );

		$this->add_control( 'step8_cards', [
			'label'       => esc_html__( 'Icon cards', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $this->icon_card_repeater()->get_controls(),
			'title_field' => '{{{ card_title }}}',
			'default'     => [
				[
					'card_icon'  => 'coins',
					'card_title' => 'Waarom deze bijdrage?',
					'card_text'  => 'Zij dekt een klein deel van de kosten die niet door uw verzekeraar worden vergoed, zodat wij het resterende bedrag kunnen kwijtschelden.',
				],
				[
					'card_icon'  => 'users',
					'card_title' => 'Eenmalig',
					'card_text'  => 'Deze bijdrage wordt tijdens het diagnostiektraject in rekening gebracht. Wordt de diagnose gesteld en gaat u verder met behandeling, dan hoeft u de bijdrage niet nog een keer te betalen.',
				],
				[
					'card_icon'  => 'shield-check',
					'card_title' => 'Los van uw eigen risico',
					'card_text'  => 'Uw eigen risico staat hier los van en wordt door uw zorgverzekeraar bepaald.',
				],
			],
		] );

		$this->add_control( 'step8_know_title', [
			'label'       => esc_html__( '"Good to know" heading', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Goed om te weten',
			'label_block' => true,
		] );

		$this->add_control( 'step8_know', [
			'label'   => esc_html__( '"Good to know" list (one per line)', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 5,
			'default' => "U ontvangt hiervoor een aparte factuur van ADHD Medisch Centrum.\nDeze bijdrage valt niet onder de coulanceregeling en kunt u niet declareren.\nBetaalt u binnen de betalingstermijn, dan blijft de coulanceregeling gelden.",
		] );

		$this->end_controls_section();
	}
}
