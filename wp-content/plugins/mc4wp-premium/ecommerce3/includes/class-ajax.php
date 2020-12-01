<?php

class MC4WP_Ecommerce_Admin_Ajax
{
    public function hook()
    {
        add_action('wp_ajax_mc4wp_ecommerce_process_queue', array( $this, 'process_queue' ));
        add_action('wp_ajax_mc4wp_ecommerce_reset_queue', array( $this, 'reset_queue' ));
    }

    /**
     * Checks if current user has `manage_options` capability or kills the request.
     */
    private function authorize()
    {
        if (! current_user_can('manage_options')) {
            status_header(401);
            exit;
        }
    }

    /**
     * Process the background queue.
     */
    public function process_queue()
    {
        $this->authorize();
        do_action('mc4wp_ecommerce_process_queue');
        wp_send_json(true);
        exit;
    }

    /**
    * Process the background queue.
    */
    public function reset_queue()
    {
        $this->authorize();
        $queue = mc4wp('ecommerce.queue');
        $queue->reset();
        $queue->save();
        wp_send_json(true);
        exit;
    }

    /**
     * @return MC4WP_Ecommerce
     */
    public function get_ecommerce()
    {
        return mc4wp('ecommerce');
    }
}
