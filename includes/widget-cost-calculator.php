<?php
/**
 * Zorgkosten Cost Calculator – Elementor Widget.
 *
 * Rebuilds the Kostenkompas GGZ app as an Elementor widget: two-column
 * layout with a "Uw gegevens" sidebar, a clickable segmented progress bar,
 * per-insurer declaration/authorization links and a full cost overview.
 * Every text, amount, image and link is editable from the Elementor editor.
 *
 * @package zorgkosten-calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;

require_once ZKC_PATH . 'includes/class-zkc-defaults.php';
require_once ZKC_PATH . 'includes/trait-zkc-controls-general.php';
require_once ZKC_PATH . 'includes/trait-zkc-controls-steps.php';
require_once ZKC_PATH . 'includes/trait-zkc-controls-money.php';
require_once ZKC_PATH . 'includes/trait-zkc-controls-final.php';
require_once ZKC_PATH . 'includes/trait-zkc-controls-result.php';
require_once ZKC_PATH . 'includes/trait-zkc-controls-style.php';
require_once ZKC_PATH . 'includes/trait-zkc-render.php';

class ZKC_Cost_Calculator_Widget extends Widget_Base {

	use ZKC_Controls_General;
	use ZKC_Controls_Steps;
	use ZKC_Controls_Money;
	use ZKC_Controls_Final;
	use ZKC_Controls_Result;
	use ZKC_Controls_Style;
	use ZKC_Render;

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

	/**
	 * Control sections, in the order they appear in the editor: the data and
	 * shared texts first, then one section per step, then the result.
	 */
	protected function register_controls() {
		$this->register_intro_controls();
		$this->register_insurer_controls();
		$this->register_policy_controls();
		$this->register_navigation_controls();

		$this->register_step_text_controls();      // steps 1-5
		$this->register_reimbursement_controls();  // step 6
		$this->register_coulance_controls();       // step 7
		$this->register_contribution_controls();   // step 8
		$this->register_machtiging_controls();     // step 9
		$this->register_payment_controls();        // step 10
		$this->register_result_controls();

		$this->register_calculation_controls();
		$this->register_illustration_controls();
		$this->register_style_controls();
	}
}
