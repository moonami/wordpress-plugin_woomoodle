<?php
/**
 * Plugin Name: WooMoodle
 * Description: Plugin allows to enter Moodle course details.
 * Version: 0.1
 * Author: Tõnis Tartes
 * Author URI: http://t6nis.com
 * License: GPL2
 */

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    add_action( 'admin_enqueue_scripts', 'woomoodle_add_stylesheet' );
    /**
     * Add stylesheet to the page
     */
    function woomoodle_add_stylesheet() {
        wp_register_style('styles.css', plugin_dir_url( __FILE__ ) . 'styles.css');
        wp_enqueue_style('styles.css');
    }
    /**
     * Custom Tabs for Product display
     * 
     * Outputs an extra tab to the default set of info tabs on the single product page.
     */
    function course_tab_options_tab() {
    ?>
            <li class="course_tab"><a href="#course_tab_data"><img src="<?php echo plugins_url( 'images/icon.png' , __FILE__ ); ?>" alt="moodle_icon"></img><?php _e('Course details'); ?></a></li>
    <?php
    }
    add_action('woocommerce_product_write_panel_tabs', 'course_tab_options_tab'); 


    /**
     * Custom Tab Options
     * 
     * Provides the input fields and add/remove buttons for custom tabs on the single product page.
     */
    function course_tab_options() {
        global $post;

        $course_tab_options = array(
            'course_link_active' => get_post_meta($post->ID, 'course_link_active', true),
            'course_id' => get_post_meta($post->ID, 'course_id', true),
            'cohort_id' => get_post_meta($post->ID, 'course_cohort_id', true),
        );
    ?>
        <div id="course_tab_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <p class="form-field">
                    <?php woocommerce_wp_checkbox( array( 'id' => 'course_link_active', 'label' => __('Course link active?', 'woothemes'), 'description' => __('Enable this option to show Moodle course link after purchase.') ) ); ?>
                </p>
            </div>
            <div class="options_group course_tab_options">
                <p class="form-field">
                    <label><?php _e('Course ID:'); ?></label>
                    <input type="text" size="5" name="course_id" value="<?php echo @$course_tab_options['course_id']; ?>" placeholder="<?php _e('Enter course id'); ?>" />
                </p>
            </div>
            <div class="options_group course_tab_options">
                <p class="form-field">
                    <label><?php _e('Cohort ID:'); ?></label>
                    <input type="text" size="5" name="course_cohort_id" value="<?php echo @$course_tab_options['cohort_id']; ?>" placeholder="<?php _e('Enter cohort id'); ?>" />
                </p>
            </div>	
        </div>
    <?php
    }
    add_action('woocommerce_product_write_panels', 'course_tab_options');


    /**
     * Process meta
     * 
     * Processes the custom tab options when a post is saved
     */
    function process_product_meta_course_tab( $post_id ) {
        update_post_meta( $post_id, 'course_link_active', ( isset($_POST['course_link_active']) && $_POST['course_link_active'] ) ? 'yes' : 'no' );
        update_post_meta( $post_id, 'course_id', $_POST['course_id']);
        update_post_meta( $post_id, 'course_cohort_id', $_POST['course_cohort_id']);
    }
    add_action('woocommerce_process_product_meta', 'process_product_meta_course_tab');

    /*
     * Add Course details to order details
     */
    function course_details_order_details($order) {
        global $post, $wpdb;
        ?>
        <?php
        if ($order->status == 'completed') {
            $order_meta = new WC_Order();
            $order_item = $wpdb->get_row($wpdb->prepare("SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %s", $order->id));
            $product_id = $order_meta->get_item_meta($order_item->order_item_id, '_product_id', true);
            $course_link_active = get_post_meta($product_id, 'course_link_active', true);            
            if ($course_link_active == 'yes') {
                $cohort_id = get_post_meta($product_id, 'course_cohort_id', true);
                $course_id = get_post_meta($product_id, 'course_id', true);
                $attributes = "";
                if (!empty($cohort_id)) {
                    $attributes .= "cohort='$product_id'"; 
                }
                if (!empty($course_id)) {
                    $attributes .= "courseid='$course_id'";
                }
                ?>
                <header>
                    <h2><?php _e( 'Course details' ); ?></h2>
                </header>
                <dl class="course_details">
                    <?php 
                    $content = 'Enter your course: ';
                    $content .= wpmdl_handler("", "[wpmdl $attributes]Click Here![/wpmdl]");
                    echo $content;
                    ?>
                </dl>
                <?php
            }
        }            
        ?>
        </dl>
        <?php
    }
    add_action('woocommerce_order_details_after_order_table', 'course_details_order_details');
    
}

?>
