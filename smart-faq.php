<?php
/*
Plugin Name: Smart FAQ
Plugin URI: #
Description: Provides A nice Frequently asked Questions Page with answers hidden untill the question is clicked then the desired answer fades smoothly into view. To show the FAQ's, place [smart_faq] short Code in a default wordpress Page or post where you want to display the Frequently asked questions you created. use [smart_faq cat=your-category-slug] to show only FAQ's from a single category
Version: 1.4
License: GPLv2
Author: brooksX
Author URI: http://en.gravatar.com/brooksx
*/
?>
<?php
//for your eyes only 
define('PLUGIN_DIR', dirname(__FILE__) . '/');
define('SCRIPT_VER','1.4');
register_activation_hook(__FILE__, 'smartfaq_plugin_install');
function smartfaq_plugin_install()
{
    add_option('smartfaq_options', array(
        'smartfaq_order_type' => 1,
        'smartfaq_ordering' => 'title',
        'smartfaq_posts_no' => -1
    ));
    register_uninstall_hook(__FILE__, 'smartfaq_uninstall');
	flush_rewrite_rules();
}


function smartfaq_uninstall()
{
    /* Delete the database field */
    delete_option('smartfaq_options');
}
if (is_admin())
    include(PLUGIN_DIR . 'includes/admin.php');

if(!defined('DONT_LOAD_CSS')){
    add_action( 'wp_enqueue_scripts', 'smartfaq_add_CSS');
}

function smartfaq_add_JS()
{
    wp_enqueue_script('smartfaq', plugins_url('js/smartfaq.min.js', __FILE__), array('jquery'),SCRIPT_VER);
}

function smartfaq_add_CSS()
{
    wp_register_style('smartfaq-style', plugins_url('/css/skin1.css', __FILE__),'',SCRIPT_VER);
    wp_enqueue_style('smartfaq-style');
}


add_action('init', 'smartfaq_function');
function smartfaq_function()
{
    add_action( 'wp_enqueue_scripts', 'smartfaq_add_JS');
    register_post_type('smart_faq', array(
        'labels' => array(
            'name' => __('FAQ List'),
            'add_new_item' => __('Add New FAQ'),
            'singular_name' => __('FAQ'),
            'edit_item' => __('Edit FAQ'),
            'view_item' => __('View FAQ'),
            'search_items' => __('Search Frequently Asked Questions'),
            'not_found' => __('No Items Found'),
            'add_new' => __('Add New FAQ')
        ),
        'description' => __('Add a Frequently Asked Question'),
        'public' => true,
        'show_ui' => true,
        'supports' => array('title','editor','custom-fields','revisions'),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
    ));
    register_taxonomy_for_object_type('category', 'smart_faq');
}
add_action('init', 'smartfaq_display_shortcode');
function smartfaq_display_shortcode()
{
    /* Register the [smart_faq cat=x] shortcode. */
    add_shortcode('smart_faq', 'smartfaq_shortcode_function');
}
function smartfaq_shortcode_function($cat_attr)
{
    $options           = get_option('smartfaq_options');
    $ordering_type     = $options['smartfaq_order_type'] ? 'ASC' : 'DSC';
    $ordering_by       = $options['smartfaq_ordering'];
    $smartfaq_posts_no = empty($options['smartfaq_posts_no']) ? 10 : $options['smartfaq_posts_no'];
    $paged         = (get_query_var('paged')) ? get_query_var('paged') : 1;
    if (isset($cat_attr['cat']))
        $args = array(
            'post_type' => 'smart_faq',
            'orderby' => $ordering_by,
            'order' => $ordering_type,
            'category_name' => sanitize_text_field($cat_attr['cat']),
            'posts_per_page' => -1,
            'paged' => $paged
        );
    else
        $args = array(
            'post_type' => 'smart_faq',
            'orderby' => $ordering_by,
            'order' => $ordering_type,
            'posts_per_page' => -1,
            'paged' => $paged
        );
    if ('meta_value_num' == $ordering_by)
        $args['meta_key'] = '_smartfaq_order';
    
    $return_string = '';

    $smartfaq_loop = new WP_Query($args);
    
    /* Check if any faq's were returned. */
    if ($smartfaq_loop->have_posts()):
        while ($smartfaq_loop->have_posts()):
            $smartfaq_loop->the_post();
            $formated_content=get_the_content();
            $formated_content = apply_filters('the_content', $formated_content);
			$formated_content = str_replace(']]>', ']]&gt;', $formated_content);
			$editLink = (current_user_can('edit_posts')) ? ' <p><a class="edit" href="' . admin_url('post.php?post=' . get_the_ID() . '&action=edit') . '">[Edit Faq]</a></p>' : '';
			$formated_content= $formated_content.$editLink;
            $return_string .= '<div class="faq-body"> <h2><a class="faq-link" href="'.get_permalink().'">' . get_the_title() .'</a></h2>';
            $return_string .= '<div class="answer">' . $formated_content.'</div></div>';
        endwhile;
    else:
        $return_string .= '<p>Sorry, no FAQ\'s matched your criteria.</p>';
    endif;
	wp_reset_postdata();
    return $return_string;
    
}

add_action('add_meta_boxes', 'smartfaq_order');
function smartfaq_order()
{
    add_meta_box('smartfaq-order', 'Order of FAQ', 'smartfaq_order_function', 'smart_faq', 'normal', 'high');
}
function smartfaq_order_function($post)
{
    //retrieve the metadata values if they exist
    $custom_ordering = get_option('smartfaq_options');
    if ('meta_value_num' == $custom_ordering['smartfaq_ordering']) {
        $smartfaq_current_order = get_post_meta($post->ID, '_smartfaq_order', true);
        echo 'Please fill in a non negative number to determine order of this FAQ';
?>
<p> Order: <input type="text" name="smartfaq_order" value="<?php
        echo esc_attr($smartfaq_current_order);
?>" /> </p>
<?php
    } else
        echo 'For Custom Order, enable <b>Order by custom value</b> in Settings->Smart FAQ Settings';
}
//hook to save the meta box data
add_action('save_post', 'smartfaq_save_order_meta');
function smartfaq_save_order_meta($post_id)
{
    if (isset($_POST['post_type'])&&'smart_faq' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return;
    } else {
        if (!current_user_can('edit_post', $post_id))
            return;
    }
    
    
    //verify the metadata is set smartfaq_order name attribute
    if (isset($_POST['smartfaq_order'])) {
        //save the metadata
        update_post_meta($post_id, '_smartfaq_order', preg_replace("/[^0-9]/", "", $_POST['smartfaq_order']));
    }
}
?>