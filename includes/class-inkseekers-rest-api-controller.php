<?php

/**
 * API class
 */
class Inkseekers_REST_API_Controller extends WC_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'wc/v2';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'inkseekers';

    /**
     * Register the REST API routes.
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/access', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'inkseekers_set_access'),
                'permission_callback' => array($this, 'inkseekers_get_items_permissions_check'),
                'show_in_index' => false,
                'args' => array(
                    'accessKey' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => __('Inkseekers access key', 'inkseekers'),
                    ),
                    'storeId' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => __('Store Identifier', 'inkseekers'),
                    ),
                ),
            )
        ));
        register_rest_route($this->namespace, '/' . $this->rest_base . '/version', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'permission_callback' => array($this, 'inkseekers_get_items_permissions_check'),
                'callback' => array($this, 'inkseekers_get_version'),
                'show_in_index' => false,
            )
        ));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/store_data', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'permission_callback' => array($this, 'inkseekers_get_items_permissions_check'),
                'callback' => array($this, 'inkseekers_get_store_data'),
                'show_in_index' => true,
            )
        ));
    }

    /**
     * @param WP_REST_Request $request
     * @return array
     */
    public static function inkseekers_set_access($request) {
        $error = false;

        $options = get_option('woocommerce_inkseekers_settings', array());

        $api_key = $request->get_param('accessKey');
        $store_id = $request->get_param('storeId');
        $store_id = intval($store_id);

        if (!is_string($api_key) || strlen($api_key) == 0 || $store_id == 0) {
            $error = 'Failed to update access data';
        }

        $options['inkseekers_key'] = $api_key;
        $options['inkseekers_store_id'] = $store_id;

        Inkseekers_Integration::instance()->update_settings($options);

        return array(
            'error' => $error,
        );
    }

   

    /**
     * Allow remotely get plugin version for debug purposes
     */
    public static function inkseekers_get_version() {
        $error = false;

        try {
            $client = Inkseekers_Integration::instance()->inkseekers_get_client();
            $store_data = $client->get('store');
        } catch (Exception $exception) {
            $error = $exception->getMessage();
        }

        $checklist = Inkseekers_Admin_Status::inkseekers_get_checklist();
        $checklist['overall_status'] = ( $checklist['overall_status'] ? 'OK' : 'FAIL' );

        foreach ($checklist['items'] as $checklist_key => $checklist_item) {

            if ($checklist_item['status'] == Inkseekers_Admin_Status::INKR_STATUS_OK) {
                $checklist_item['status'] = 'OK';
            } elseif ($checklist_item['status'] == Inkseekers_Admin_Status::INKR_STATUS_WARNING) {
                $checklist_item['status'] = 'WARNING';
            } elseif ($checklist_item['status'] == Inkseekers_Admin_Status::INKR_STATUS_NOT_CONNECTED) {
                $checklist_item['status'] = 'NOT CONNECTED';
            } else {
                $checklist_item['status'] = 'FAIL';
            }

            $checklist['items'][$checklist_key] = $checklist_item;
        }

        return array(
            'version' => Inkseekers_Base::VERSION,
            'store_id' => !empty($store_data['id']) ? $store_data['id'] : false,
            'error' => $error,
            'status_checklist' => $checklist,
        );
    }

    /**
     * Get necessary store data
     * @return array
     */
    public static function inkseekers_get_store_data() {
        return array(
            'website' => get_site_url(),
            'version' => WC()->version,
            'name' => get_bloginfo('title', 'display')
        );
    }

    /**
     * Check whether a given request has permission to read inkseekers endpoints.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function inkseekers_get_items_permissions_check($request) {
        if (!wc_rest_check_user_permissions('read')) {
            return new WP_Error('woocommerce_rest_cannot_inkseekers_view', __('Sorry, you cannot list resources.', 'woocommerce'), array('status' => rest_authorization_required_code()));
        }

        return true;
    }

    /**
     * Check if a given request has access to update a product.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function inkseekers_update_item_permissions_check($request) {
        $params = $request->get_url_params();
        $product = wc_get_product((int) $params['product_id']);

        if (empty($product) && !wc_rest_check_post_permissions('product', 'edit', $product->get_id())) {
            return new WP_Error('woocommerce_rest_cannot_edit', __('Sorry, you are not allowed to edit this resource.', 'woocommerce'), array('status' => rest_authorization_required_code()));
        }

        return true;
    }

}
