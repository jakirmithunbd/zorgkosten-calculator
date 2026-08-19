<?php
/**
 * Zorgkosten Cost Calculator – style tab controls (colors, layout, typography)
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

trait ZKC_Controls_Style {

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
			'color_eyebrow'      => [ 'Eyebrow labels (brand blue)', '#5B7491', '--zkc-eyebrow' ],
			'color_success'      => [ 'Reimbursed percentage (green)', '#439A67', '--zkc-success' ],
			'color_uncovered'    => [ 'Not-reimbursed amounts (red)', '#CF5E55', '--zkc-uncovered' ],
			'color_uncovered_soft' => [ 'Not-reimbursed panel background', '#FFEEEB', '--zkc-uncovered-soft' ],
			'color_secondary_fg' => [ 'Kicker text on cream panels', '#323845', '--zkc-secondary-fg' ],
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
}
