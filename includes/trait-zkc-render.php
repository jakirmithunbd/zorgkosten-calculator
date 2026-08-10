<?php
/**
 * Zorgkosten Cost Calculator – frontend render: builds the JSON config the JS stepper consumes
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait ZKC_Render {

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
