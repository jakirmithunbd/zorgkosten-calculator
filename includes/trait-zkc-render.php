<?php
/**
 * Zorgkosten Cost Calculator – frontend render: builds the JSON config the
 * JS stepper consumes.
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait ZKC_Render {

	/** Split a textarea into trimmed, non-empty lines. */
	private function zkc_lines( $text ) {
		$out = [];
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$out[] = $line;
			}
		}
		return $out;
	}

	/**
	 * Saved widget settings can hold absolute URLs to a previous plugin folder
	 * name (e.g. a GitHub "-main" ZIP install). Re-base anything pointing at
	 * this plugin's own bundled assets onto the current folder. Media-library
	 * uploads and external URLs are left untouched.
	 */
	private function zkc_rebase( $url ) {
		if ( ! is_string( $url ) || '' === $url ) {
			return '';
		}
		if ( preg_match( '#/wp-content/plugins/[^/]+/(assets/(?:logos|img|fonts)/[^/?\#]+)$#', $url, $m ) ) {
			return ZKC_URL . $m[1];
		}
		return $url;
	}

	/** Icon + title + text cards from a repeater. */
	private function zkc_cards( $rows ) {
		$out = [];
		foreach ( (array) $rows as $row ) {
			$out[] = [
				'icon'  => (string) ( $row['card_icon'] ?? '' ),
				'title' => (string) ( $row['card_title'] ?? '' ),
				'text'  => (string) ( $row['card_text'] ?? '' ),
			];
		}
		return $out;
	}

	/** Numbered steps from a repeater. */
	private function zkc_numbered( $rows ) {
		$out = [];
		foreach ( (array) $rows as $row ) {
			$out[] = [
				'title' => (string) ( $row['num_title'] ?? '' ),
				'text'  => (string) ( $row['num_text'] ?? '' ),
			];
		}
		return $out;
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$media_or = function ( $media, $fallback ) {
			return ! empty( $media['url'] ) ? $this->zkc_rebase( $media['url'] ) : $fallback;
		};

		// Insurers.
		$insurers = [];
		foreach ( (array) ( $s['insurers'] ?? [] ) as $row ) {
			$logo = '';
			if ( ! empty( $row['ins_logo']['url'] ) ) {
				$logo = $this->zkc_rebase( $row['ins_logo']['url'] );
			} elseif ( ! empty( $row['ins_logo_url'] ) ) {
				// Legacy field from before the logo default moved into the media control.
				$logo = $this->zkc_rebase( $row['ins_logo_url'] );
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

		// Tariff bases (only the inline label is still used by the frontend).
		$bases = [];
		foreach ( (array) ( $s['bases'] ?? [] ) as $row ) {
			$bases[] = [
				'key'   => (string) ( $row['basis_key'] ?? '' ),
				'label' => (string) ( $row['basis_label'] ?? '' ),
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
				'back'           => (string) $s['back_label'],
				'next'           => (string) $s['next_label'],
				'restart'        => (string) $s['restart_label'],
				'adjust'         => (string) $s['adjust_label'],
				'stepCounter'    => (string) $s['step_counter_text'],
				'segJump'        => (string) $s['seg_jump'],
				'segLater'       => (string) $s['seg_later'],
				'helpLabel'      => (string) $s['help_label'],
				'warnLabel'      => (string) $s['warning_label'],
				'unknown'        => (string) $s['unknown_label'],
				'fallbackName'   => (string) $s['fallback_insurer'],
				'sidebarTitle'   => (string) $s['sidebar_title'],
				'sidebarEmpty'   => (string) $s['sidebar_empty'],
				'chipTitle'      => (string) $s['sidebar_chip_title'],
				'chipDeductible' => (string) $s['sidebar_chip_deductible'],
				'chipUsed'       => (string) $s['sidebar_chip_used'],
				'appFrame'       => ! empty( $s['app_frame'] ),
			],
			'intro' => [
				'kicker'   => (string) $s['intro_kicker'],
				'title'    => (string) $s['intro_title'],
				'text'     => (string) $s['intro_text'],
				'bullets'  => $this->zkc_lines( $s['intro_bullets'] ),
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
				'diagnostiek'       => is_numeric( $s['diagnostiek_amount'] ) ? (float) $s['diagnostiek_amount'] : 2000,
				'behandeling'       => is_numeric( $s['behandeling_amount'] ) ? (float) $s['behandeling_amount'] : 4000,
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
					'title'    => (string) $s['step3_title'],
					'subtitle' => (string) $s['step3_subtitle'],
					'help'     => (string) $s['step3_help'],
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
					'title'     => (string) $s['step5_title'],
					'cards'     => $this->zkc_cards( $s['step5_cards'] ?? [] ),
					'linkLabel' => (string) $s['step5_link_label'],
					'linkUrl'   => (string) $s['step5_link_url'],
				],
				's6' => [
					'heroTitle'           => (string) $s['step6_hero_title'],
					'heroTitleFallback'   => (string) $s['step6_hero_title_fallback'],
					'heroBasis'           => (string) $s['step6_hero_basis'],
					'exampleTitle'        => (string) $s['step6_example_title'],
					'labelDiagnostiek'    => (string) $s['step6_label_diagnostiek'],
					'labelBehandeling'    => (string) $s['step6_label_behandeling'],
					'labelTotal'          => (string) $s['step6_label_total'],
					'invoiceLabel'        => (string) $s['step6_invoice_label'],
					'reimbursedSuffix'    => (string) $s['step6_reimbursed_suffix'],
					'notReimbursedSuffix' => (string) $s['step6_not_reimbursed_suffix'],
					'exampleNote'         => (string) $s['step6_example_note'],
					'uncoveredTitle'      => (string) $s['step6_uncovered_title'],
					'uncoveredText'       => (string) $s['step6_uncovered_text'],
				],
				's7' => [
					'title'      => (string) $s['step7_title'],
					'subtitle'   => (string) $s['step7_subtitle'],
					'heroKicker' => (string) $s['step7_hero_kicker'],
					'heroNote'   => (string) $s['step7_hero_note'],
					'meansTitle' => (string) $s['step7_means_title'],
					'means'      => $this->zkc_lines( $s['step7_means'] ),
					'asksTitle'  => (string) $s['step7_asks_title'],
					'asksDirect' => $this->zkc_lines( $s['step7_asks_direct'] ),
					'asksSelf'   => $this->zkc_lines( $s['step7_asks_self'] ),
					'exclTitle'  => (string) $s['step7_excl_title'],
					'exclNote'   => (string) $s['step7_excl_note'],
					'exclDirect' => $this->zkc_lines( $s['step7_excl_direct'] ),
					'exclSelf'   => $this->zkc_lines( $s['step7_excl_self'] ),
				],
				's8' => [
					'title'      => (string) $s['step8_title'],
					'subtitle'   => (string) $s['step8_subtitle'],
					'heroKicker' => (string) $s['step8_hero_kicker'],
					'heroNote'   => (string) $s['step8_hero_note'],
					'meansTitle' => (string) $s['step8_means_title'],
					'means'      => $this->zkc_lines( $s['step8_means'] ),
					'cards'      => $this->zkc_cards( $s['step8_cards'] ?? [] ),
					'knowTitle'  => (string) $s['step8_know_title'],
					'know'       => $this->zkc_lines( $s['step8_know'] ),
				],
				's9' => [
					'title'       => (string) $s['step9_title'],
					'subtitle'    => (string) $s['step9_subtitle'],
					'meansTitle'  => (string) $s['step9_means_title'],
					'meansNote'   => (string) $s['step9_means_note'],
					'means'       => $this->zkc_lines( $s['step9_means'] ),
					'asksTitle'   => (string) $s['step9_asks_title'],
					'asks'        => $this->zkc_lines( $s['step9_asks'] ),
					'checkTitle'  => (string) $s['step9_check_title'],
					'checkText'   => (string) $s['step9_check_text'],
					'checkButton' => (string) $s['step9_check_button'],
					'warning'     => (string) $s['step9_warning'],
				],
				's10' => [
					'title'            => (string) $s['step10_title'],
					'subtitleDirect'   => (string) $s['step10_subtitle_direct'],
					'subtitleSelf'     => (string) $s['step10_subtitle_self'],
					'stepsDirect'      => $this->zkc_numbered( $s['step10_steps_direct'] ?? [] ),
					'stepsSelf'        => $this->zkc_numbered( $s['step10_steps_self'] ?? [] ),
					'declareLinkLabel' => (string) $s['step10_declare_link_label'],
					'riskLabel'        => (string) $s['step10_risk_label'],
					'riskDirect'       => (string) $s['step10_risk_direct'],
					'riskSelf'         => (string) $s['step10_risk_self'],
					'knowTitle'        => (string) $s['step10_know_title'],
					'knowText'         => (string) $s['step10_know_text'],
					'nextLabel'        => (string) $s['step10_next_label'],
				],
			],
			'result' => [
				'kicker'               => (string) $s['result_kicker'],
				'title'                => (string) $s['result_title'],
				'intro'                => (string) $s['result_intro'],
				'invoiceTitle'         => (string) $s['result_invoice_title'],
				'invoiceText'          => (string) $s['result_invoice_text'],
				'uncertainTooltip'     => (string) $s['result_uncertain_tooltip'],
				'reimbursedLabel'      => (string) $s['result_reimbursed_label'],
				'reimbursedText'       => (string) $s['result_reimbursed_text'],
				'waivedLabel'          => (string) $s['result_waived_label'],
				'waivedText'           => (string) $s['result_waived_text'],
				'colPart'              => (string) $s['result_col_part'],
				'colCost'              => (string) $s['result_col_cost'],
				'colReimbursed'        => (string) $s['result_col_reimbursed'],
				'colWaived'            => (string) $s['result_col_waived'],
				'rowDiagnostiek'       => (string) $s['result_row_diagnostiek'],
				'rowBehandeling'       => (string) $s['result_row_behandeling'],
				'rowTotal'             => (string) $s['result_row_total'],
				'rangeNotePolicy'      => (string) $s['result_range_note_policy'],
				'ownKicker'            => (string) $s['result_own_kicker'],
				'ownText'              => (string) $s['result_own_text'],
				'contributionLabel'    => (string) $s['result_contribution_label'],
				'contributionText'     => (string) $s['result_contribution_text'],
				'deductibleLabel'      => (string) $s['result_deductible_label'],
				'deductibleTextDirect' => (string) $s['result_deductible_text_direct'],
				'deductibleTextSelf'   => (string) $s['result_deductible_text_self'],
				'usedUnknownNote'      => (string) $s['result_used_unknown_note'],
				'selfKicker'           => (string) $s['result_self_kicker'],
				'selfTitleDirect'      => (string) $s['result_self_title_direct'],
				'selfTitleSelf'        => (string) $s['result_self_title_self'],
				'selfTextDirect'       => (string) $s['result_self_text_direct'],
				'selfSteps'            => $this->zkc_lines( $s['result_self_steps'] ),
				'declareButton'        => (string) $s['result_declare_button'],
				'machtigingLabel'      => (string) $s['result_machtiging_label'],
				'machtigingText'       => (string) $s['result_machtiging_text'],
				'machtigingLink'       => (string) $s['result_machtiging_link'],
				'notePolicyUnknown'    => (string) $s['result_note_policy_unknown'],
				'disclaimer'           => (string) $s['result_disclaimer'],
				'ctaTitle'             => (string) $s['result_cta_title'],
				'ctaText'              => (string) $s['result_cta_text'],
				'signupLabel'          => (string) $s['signup_label'],
				'signupUrl'            => $signup_url,
				'signupTarget'         => $signup_target,
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
