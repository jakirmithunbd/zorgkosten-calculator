<?php
/**
 * Zorgkosten Cost Calculator – step content controls (steps 1-9)
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

trait ZKC_Controls_Steps {

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
			'default'     => ZKC_Defaults::bases(),
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
}
