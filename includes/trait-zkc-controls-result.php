<?php
/**
 * Zorgkosten Cost Calculator – result screen controls.
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

trait ZKC_Controls_Result {

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
			'description' => esc_html__( 'Use {total} and {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => 'Een volledig traject van diagnostiek en behandeling kost gemiddeld ± {total}. Hieronder ziet u wat {insurer} vergoedt, wat wij kwijtschelden en welk deel u zelf betaalt.',
		] );

		/* ---- Invoice panel ---- */
		$this->add_control( 'heading_result_invoice', [
			'label'     => esc_html__( 'Invoice panel', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'result_invoice_title', [
			'label'       => esc_html__( 'Panel title', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {total}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wat gebeurt er met de factuur van ± {total}?',
			'label_block' => true,
		] );

		$this->add_control( 'result_invoice_text', [
			'label'   => esc_html__( 'Panel text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
			'default' => 'Uw werkelijke factuur kan hoger of lager zijn: dat hangt af van de zorg die u nodig heeft en de tijd die daaraan wordt besteed (NZa-tarieven, zorgprestatiemodel). De hele factuur wordt gedekt. U betaalt hier niets van zelf.',
		] );

		$this->add_control( 'result_uncertain_tooltip', [
			'label'       => esc_html__( 'Tooltip on the striped (uncertain) part', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Dit deel is nog onzeker: het hangt af van uw basisverzekering.',
			'label_block' => true,
		] );

		$this->add_control( 'result_reimbursed_label', [
			'label'       => esc_html__( 'Reimbursed label', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer} and {amount}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '{insurer} vergoedt {amount}',
			'label_block' => true,
		] );

		$this->add_control( 'result_reimbursed_text', [
			'label'       => esc_html__( 'Reimbursed description', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {percentage} and {basis}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => 'Dit deel wordt vergoed uit uw basisverzekering ({percentage} van het {basis}).',
		] );

		$this->add_control( 'result_waived_label', [
			'label'       => esc_html__( 'Waived label', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {amount}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Wij schelden {amount} kwijt',
			'label_block' => true,
		] );

		$this->add_control( 'result_waived_text', [
			'label'   => esc_html__( 'Waived description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Dit niet-vergoede deel vervalt via onze coulanceregeling. U krijgt hiervoor geen rekening.',
		] );

		/* ---- Breakdown table ---- */
		$this->add_control( 'heading_result_table', [
			'label'     => esc_html__( 'Breakdown table', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'result_col_part', [
			'label'   => esc_html__( 'Column 1 heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Onderdeel',
		] );

		$this->add_control( 'result_col_cost', [
			'label'   => esc_html__( 'Column 2 heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Gemiddelde kosten',
		] );

		$this->add_control( 'result_col_reimbursed', [
			'label'   => esc_html__( 'Column 3 heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Vergoeding door verzekeraar',
		] );

		$this->add_control( 'result_col_waived', [
			'label'   => esc_html__( 'Column 4 heading', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Wij schelden kwijt',
		] );

		$this->add_control( 'result_row_diagnostiek', [
			'label'   => esc_html__( 'Row 1 label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Diagnostiek',
		] );

		$this->add_control( 'result_row_behandeling', [
			'label'   => esc_html__( 'Row 2 label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Behandeling',
		] );

		$this->add_control( 'result_row_total', [
			'label'   => esc_html__( 'Row 3 label', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXT,
			'default' => 'Gehele traject',
		] );

		$this->add_control( 'result_range_note_policy', [
			'label'       => esc_html__( 'Note: policy unknown (range shown)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer} and {percentage}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => 'Omdat u niet weet welk type basisverzekering u heeft, rekenen wij met de laagste en hoogste vergoeding van {insurer} ({percentage}). Daarom ziet u hier een minimum- en maximumbedrag.',
		] );

		/* ---- Own costs card ---- */
		$this->add_control( 'heading_result_own', [
			'label'     => esc_html__( 'Own costs card', 'zorgkosten-calculator' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'result_own_kicker', [
			'label'       => esc_html__( 'Card kicker', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Uw eigen kosten voor het hele traject',
			'label_block' => true,
		] );

		$this->add_control( 'result_own_text', [
			'label'       => esc_html__( 'Card text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {total}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => 'Van een traject van ± {total} betaalt u alleen deze twee bedragen zelf. De rest wordt vergoed of door ons kwijtgescholden.',
		] );

		$this->add_control( 'result_contribution_label', [
			'label'       => esc_html__( 'Contribution row label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Eenmalige persoonlijke bijdrage',
			'label_block' => true,
		] );

		$this->add_control( 'result_contribution_text', [
			'label'   => esc_html__( 'Contribution row description', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'default' => 'betaalt u eenmalig aan ons, voor diagnostiek én behandeling samen. Bij de start van de behandeling betaalt u dit niet nog een keer.',
		] );

		$this->add_control( 'result_deductible_label', [
			'label'       => esc_html__( 'Deductible row label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Eigen risico',
			'label_block' => true,
		] );

		$this->add_control( 'result_deductible_text_direct', [
			'label'       => esc_html__( 'Deductible row – WITH payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => 'verrekent {insurer} zelf met u, voor alle zorg uit de basisverzekering',
		] );

		$this->add_control( 'result_deductible_text_self', [
			'label'       => esc_html__( 'Deductible row – WITHOUT payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => 'wordt door {insurer} ingehouden op de vergoeding en aan onze kant verrekend, dus u betaalt dit niet dubbel',
		] );

		$this->add_control( 'result_used_unknown_note', [
			'label'   => esc_html__( 'Note: used deductible unknown', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => 'Omdat u niet weet hoeveel eigen risico u al heeft gebruikt, tonen wij een minimum- en maximumbedrag.',
		] );

		/* ---- "What you arrange yourself" card ---- */
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

		$this->add_control( 'result_self_title_self', [
			'label'       => esc_html__( 'Title – WITHOUT payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'U dient de factuur zelf in bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'result_self_steps', [
			'label'       => esc_html__( 'Numbered steps (one per line)', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 5,
			'default'     => "U ontvangt van ons een declaratiefactuur.\nU dient deze in bij {insurer}, meestal via de app of het online portaal.\nU ontvangt de vergoeding van {insurer} en betaalt die binnen 14 dagen aan ons door.",
		] );

		$this->add_control( 'result_declare_button', [
			'label'       => esc_html__( 'Declare button text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Shown when the insurer has a declaration URL. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Declareren bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'result_self_title_direct', [
			'label'       => esc_html__( 'Title – WITH payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'U hoeft niets in te dienen bij {insurer}',
			'label_block' => true,
		] );

		$this->add_control( 'result_self_text_direct', [
			'label'       => esc_html__( 'Text – WITH payment agreement', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => 'Wij dienen de zorg rechtstreeks in bij {insurer}. U ontvangt van ons geen declaratiefactuur en hoeft zelf niets in te dienen. Alleen uw eigen risico verrekent {insurer} met u.',
		] );

		$this->add_control( 'result_machtiging_label', [
			'label'       => esc_html__( 'Authorization warning label', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Let op: machtiging',
			'label_block' => true,
		] );

		$this->add_control( 'result_machtiging_text', [
			'label'       => esc_html__( 'Authorization warning text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 4,
			'default'     => '{insurer} vraagt vaak een machtiging (toestemming) vóórdat de behandeling start. Regel dit op tijd, anders kan de vergoeding worden afgewezen.',
		] );

		$this->add_control( 'result_machtiging_link', [
			'label'       => esc_html__( 'Authorization warning link text', 'zorgkosten-calculator' ),
			'description' => esc_html__( 'Use {insurer}. Aliases: {invoice}/{factuur}/{traject} = {total}, {bijdrage} = {contribution}, {eigenrisico} = {deductible}, {verzekeraar} = {insurer}; {percentage}, {basis}, {vergoed}, {waived} are always available.', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Bekijk dit bij {insurer}',
			'label_block' => true,
		] );

		/* ---- Notes & CTA ---- */
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
			'rows'    => 6,
			'default' => '*Rekenvoorbeeld op basis van de door u ingevulde gegevens en gemiddelde bedragen voor diagnostiek en behandeling. De uiteindelijke kosten zijn afhankelijk van de daadwerkelijk geleverde zorg, de verwerking van de declaratie, uw polisvoorwaarden, uw resterende eigen risico en de definitieve vergoeding van uw zorgverzekeraar. Aan deze berekening kunnen geen rechten worden ontleend.',
		] );

		$this->add_control( 'result_cta_title', [
			'label'       => esc_html__( 'CTA title', 'zorgkosten-calculator' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Hoe gaat het verder?',
			'label_block' => true,
		] );

		$this->add_control( 'result_cta_text', [
			'label'   => esc_html__( 'CTA text', 'zorgkosten-calculator' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 4,
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
}
