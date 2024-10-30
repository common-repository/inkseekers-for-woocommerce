<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inkseekers_Admin_Settings {

	public static $_instance;
	const CARRIER_TYPE_STANDARD = 'standard';
	const CARRIER_TYPE_EXPEDITED = 'expedited';
	const CARRIER_TYPE_DOMESTIC = 'domestic';
	const CARRIER_TYPE_INTERNATIONAL = 'international';
    const CARRIER_REGION_US = 'US';
    const CARRIER_REGION_EU = 'LV';
    const DEFAULT_PERSONALIZE_BUTTON_TEXT = 'Personalize Design';
    const DEFAULT_PERSONALIZE_BUTTON_COLOR = '#eee';
    const DEFAULT_PERSONALIZE_MODAL_TITLE = 'Create a personalized design';

    // Size guide modal settings
    const DEFAULT_SIZE_GUIDE_BUTTON_TEXT = 'Size Guide';
    const DEFAULT_SIZE_GUIDE_BUTTON_COLOR = '#1164A9';
    const DEFAULT_SIZE_GUIDE_MODAL_TITLE = 'Size guide';
    const DEFAULT_SIZE_GUIDE_MODAL_TEXT_COLOR = '#000';
    const DEFAULT_SIZE_GUIDE_MODAL_BACKGROUND_COLOR = '#fff';
    const DEFAULT_SIZE_GUIDE_TAB_BACKGROUND_COLOR = '#fff';
    const DEFAULT_SIZE_GUIDE_ACTIVE_TAB_BACKGROUND_COLOR = '#fff';
    const DEFAULT_SIZE_GUIDE_UNIT = 'inch';

    /**
     * @return array
     */
	public static function inkseekers_getIntegrationFields()
    {
        $sales_tax_link = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( 'https://app.inkseekers.com' ),
            esc_html__( 'states where Inkseekers applies sales tax', 'inkseekers' )
        );

        return array(
            'inkseekers_key' => array(
                'title' => __( 'Inkseekers store API key', 'inkseekers' ),
                'type' => 'text',
                'desc_tip' => true,
                'description' => __( 'Your store\'s Inkseekers API key. Create it in the Prinful dashboard', 'inkseekers' ),
                'default' => false,
            ),
        );
    }


	/**
	 * @return array
	 */
	public static function inkseekers_getAllFields() {
		return array_merge(self::inkseekers_getIntegrationFields());
    }

	/**
	 * @return Inkseekers_Admin_Settings
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Setup the inkseekers_view
	 */
	public static function inkseekers_view() {

		$settings = self::instance();
		$settings->inkseekers_render();
	}

}