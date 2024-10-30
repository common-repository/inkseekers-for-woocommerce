<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inkseekers_Admin {

	const MENU_TITLE_TOP = 'Inkseekers';
	const PAGE_TITLE_DASHBOARD = 'Dashboard';
	const MENU_TITLE_DASHBOARD = 'Dashboard';
	const MENU_SLUG_DASHBOARD = 'inkseekers-dashboard';
	const CAPABILITY = 'manage_options';

	public static function init() {
		$admin = new self;
		$admin->register_admin();
	}

    /**
     * Register admin scripts
     */
	public function register_admin() {

		add_action( 'admin_menu', array( $this, 'inkseekers_register_admin_menu_page' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'inkseekers_add_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'inkseekers_add_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'inkseekers_add_global_style' ) );
		add_action( 'admin_bar_menu', array( $this, 'inkseekers_add_status_toolbar' ), 999 );
    }

    /**
     * Loads stylesheets used in inkseekers admin pages
     * @param $hook
     */
    public function inkseekers_add_admin_styles($hook) {

	    wp_enqueue_style( 'inkseekers-global', plugins_url( '../assets/css/global.css', __FILE__ ) );

	    if ( strpos( $hook, 'inkseekers-dashboard' ) !== false ) {
		    wp_enqueue_style( 'wp-color-picker' );
		    wp_enqueue_style( 'inkseekers-dashboard', plugins_url( '../assets/css/dashboard.css', __FILE__ ) );
		    wp_enqueue_style( 'inkseekers-status', plugins_url( '../assets/css/status.css', __FILE__ ) );
		    wp_enqueue_style( 'inkseekers-support', plugins_url( '../assets/css/support.css', __FILE__ ) );
	   }
    }

	/**
	 * Loads stylesheet for inkseekers toolbar element
	 */
    public function inkseekers_add_global_style() {
	    if ( is_user_logged_in() ) {
		    wp_enqueue_style( 'inkseekers-global', plugins_url( '../assets/css/global.css', __FILE__ ) );
	    }
    }

	/**
	 * Loads scripts used in inkseekers admin pages
	 * @param $hook
	 */
	public function inkseekers_add_admin_scripts($hook) {
		if ( strpos( $hook, 'inkseekers-dashboard' ) !== false ) {
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'inkseekers-connect', plugins_url( '../assets/js/connect.js', __FILE__ ) );
			wp_enqueue_script( 'inkseekers-block-loader', plugins_url( '../assets/js/block-loader.js', __FILE__ ) );
			wp_enqueue_script( 'inkseekers-intercom', plugins_url( '../assets/js/intercom.min.js', __FILE__ ) );
		}
	}

    /**
     * Register admin menu pages
     */
	public function inkseekers_register_admin_menu_page() {

		add_menu_page(
			__( 'Dashboard', 'inkseekers' ),
			self::MENU_TITLE_TOP,
			self::CAPABILITY,
			self::MENU_SLUG_DASHBOARD,
			array( 'Inkseekers_Admin', 'route' ),
			Inkseekers_Base::inkseekers_get_asset_url() . 'images/inkseeker-menu-icon.png',
			58
		);
	}

	/**
	 * Route the tabs
	 */
	public static function route() {

		$tabs = array(
			'dashboard' => 'Inkseekers_Admin_Dashboard',
			'status'    => 'Inkseekers_Admin_Status',
			'support'   => 'Inkseekers_Admin_Support',
		);

		$tab = ( ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'dashboard' );
		if ( ! empty( $tabs[ $tab ] ) ) {
			call_user_func( array( $tabs[ $tab ], 'inkseekers_view' ) );
		}
	}

    /**
     * Get the tabs used in inkseekers admin pages
     * @return array
     * @throws InkseekersException
     */
	public static function inkseekers_get_tabs() {
		$tabs = array(
			array( 'name' => __( 'Status', 'inkseekers' ), 'tab_url' => 'status' ),
			array( 'name' => __( 'Support', 'inkseekers' ), 'tab_url' => 'support' ),
		);
                if ( Inkseekers_Integration::instance()->inkseekers_is_connected() ) {
                        array_unshift( $tabs, array( 'name' => __( 'Dashboard', 'inkseekers' ), 'tab_url' => false ) );
		} else {
			array_unshift( $tabs, array( 'name' => __( 'Connect', 'inkseekers' ), 'tab_url' => false ) );
		}
              
		return $tabs;
	}

	/**
	 * Create the inkseekers toolbar
	 * @param $wp_admin_bar
	 */
	public function inkseekers_add_status_toolbar( $wp_admin_bar ) {

		$issueCount = get_transient( Inkseekers_Admin_Status::INKR_STATUS_ISSUE_COUNT );

		if ( $issueCount ) {
			//Add top level menu item
			$args = array(
				'id'    => 'inkseekers_toolbar',
				'title' => 'Inkseekers Integration' . ( $issueCount > 0 ? ' <span class="inkseekers-toolbar-issues">' . esc_attr( $issueCount ) . '</span>' : '' ),
				'href'  => get_admin_url( null, 'admin.php?page=' . Inkseekers_Admin::MENU_SLUG_DASHBOARD ),
				'meta'  => array( 'class' => 'inkseekers-toolbar' ),
			);
			$wp_admin_bar->add_node( $args );

			//Add status
			$args = array(
				'id'     => 'inkseekers_toolbar_status',
				'parent' => 'inkseekers_toolbar',
				'title'  => 'Integration status' . ( $issueCount > 0 ? ' (' . esc_attr( $issueCount ) . _n( ' issue', ' issues', $issueCount ) . ')' : '' ),
				'href'   => get_admin_url( null, 'admin.php?page=' . Inkseekers_Admin::MENU_SLUG_DASHBOARD . '&tab=status' ),
				'meta'   => array( 'class' => 'inkseekers-toolbar-status' ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 * Load a template file. Extract any variables that are passed
	 * @param $name
	 * @param array $variables
	 */
	public static function inkseekers_load_template( $name, $variables = array() ) {

		if ( ! empty( $variables ) ) {
			extract( $variables );
		}

		$filename = plugin_dir_path( __FILE__ ) . 'templates/' . $name . '.php';
		if ( file_exists( $filename ) ) {
			include( $filename );
		}
	}

}