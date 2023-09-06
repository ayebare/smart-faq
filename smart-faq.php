<?php
/**
 * Plugin Name: Smart FAQ
 * Plugin URI:  http://en.gravatar.com/brooksx
 * Author:      brooksX
 * Author URI:  http://en.gravatar.com/brooksx
 * Description: This plugin does wonders
 * Version:     1.5.0
 * License:     GPL-2.0+
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: smart-faq
 *
 * @package SmartFaq
 */

// For your eyes only.
define( 'PLUGIN_DIR', dirname(__FILE__) . '/' );
define( 'SCRIPT_VER','1.4' );

register_activation_hook( __FILE__, 'smartfaq_plugin_install' );

function smartfaq_plugin_install() {
	add_option(
		'smartfaq_options',
		array(
			'smartfaq_order_type' => 1,
			'smartfaq_ordering'   => 'title',
			'smartfaq_posts_no'   => -1
		)
	);

	add_action( 'init', 'load_textdomain' );

	register_uninstall_hook( __FILE__, 'smartfaq_uninstall' );
	flush_rewrite_rules();
}

function load_textdomain() {
	load_plugin_textdomain( 'smart-faq', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

function smartfaq_uninstall() {
	delete_option('smartfaq_options'); // Delete the database field.
}

if ( is_admin() ) {
	include( PLUGIN_DIR . 'includes/admin.php' );
}

if ( ! defined( 'DONT_LOAD_CSS' ) ) {
	add_action( 'wp_enqueue_scripts', 'smartfaq_add_CSS' );
}

function smartfaq_add_JS() {
	wp_enqueue_script(
		'smartfaq',
		plugins_url( 'dist/js/smartfaq.js', __FILE__ ),
		array( 'jquery' ),
		SCRIPT_VER
	);
}

function smartfaq_add_CSS() {
	wp_register_style(
		'smartfaq-style',
		plugins_url( 'dist/css/skin.css', __FILE__ ),
		'',
		SCRIPT_VER
	);
	wp_enqueue_style('smartfaq-style');
}

add_action( 'init', 'smartfaq_function' );
add_action( 'wp_enqueue_scripts', 'smartfaq_add_JS');

function smartfaq_function() {
	register_post_type(
		'smart_faq',
		array(
			'labels'             => array(
				'name'          => __( 'FAQ List', 'smart-faq' ),
				'add_new_item'  => __( 'Add New FAQ', 'smart-faq'),
				'singular_name' => __( 'FAQ', 'smart-faq'),
				'edit_item'     => __( 'Edit FAQ', 'smart-faq'),
				'view_item'     => __( 'View FAQ', 'smart-faq'),
				'search_items'  => __( 'Search Frequently Asked Questions', 'smart-faq' ),
				'not_found'     => __( 'No Items Found', 'smart-faq' ),
				'add_new'       => __( 'Add New FAQ', 'smart-faq' )
			),
			'description'        => __('Add a Frequently Asked Question'),
			'public'             => true,
			'show_ui'            => true,
			'supports'           => array('title','editor','custom-fields','revisions'),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
		)
	);

	register_taxonomy_for_object_type( 'category', 'smart_faq' );
}

add_action( 'init', 'smartfaq_display_shortcode' );
function smartfaq_display_shortcode() {
	// Register the [smart_faq cat=x] shortcode.
	add_shortcode( 'smart_faq', 'smartfaq_shortcode_function' );
}

function smartfaq_shortcode_function( $cat_attr ) {
	$options           = get_option('smartfaq_options');
	$ordering_type     = $options['smartfaq_order_type'] ? 'ASC' : 'DSC';
	$ordering_by       = $options['smartfaq_ordering'];
	$smartfaq_posts_no = empty($options['smartfaq_posts_no']) ? 10 : $options['smartfaq_posts_no'];
	$paged             = (get_query_var('paged')) ? get_query_var('paged') : 1;

	if ( isset( $cat_attr['cat'] ) ) {
		$args = array(
			'post_type'      => 'smart_faq',
			'orderby'        => $ordering_by,
			'order'          => $ordering_type,
			'category_name'  => sanitize_text_field($cat_attr['cat']),
			'posts_per_page' => -1,
			'paged'          => $paged
		);
	} else {
		$args = array(
			'post_type'      => 'smart_faq',
			'orderby'        => $ordering_by,
			'order'          => $ordering_type,
			'posts_per_page' => -1,
			'paged'          => $paged
		);
	}

	if ( 'meta_value_num' == $ordering_by ) {
		$args['meta_key'] = '_smartfaq_order';
	}

	$return_string = '';
	$smartfaq_loop = new WP_Query( $args );

	// Check if any faq's were returned.
	if ( $smartfaq_loop->have_posts() ) {
		while ( $smartfaq_loop->have_posts() ) :
			$smartfaq_loop->the_post();
			$formated_content = get_the_content();
			$formated_content = apply_filters( 'the_content', $formated_content );
			$formated_content = str_replace( ']]>', ']]&gt;', $formated_content );
			$editLink         = ( current_user_can( 'edit_posts' ) ) ? ' <p><a class="edit" href="' . admin_url('post.php?post=' . get_the_ID() . '&action=edit') . '">[Edit Faq]</a></p>' : '';
			$formated_content = $formated_content . $editLink;
			$return_string    .= '<div class="faq-body"> <h2><a class="faq-link" href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
			$return_string    .= '<div class="answer">' . $formated_content . '</div></div>';
		endwhile;
	} else {
		$return_string .= sprintf(
			'<p>%s</p>',
			esc_attr__( "Sorry, no FAQ's matched your criteria.", 'smart-faq' ),
		);
	}

	wp_reset_postdata();

	return $return_string;
}

add_action( 'add_meta_boxes', 'smartfaq_order' );

function smartfaq_order() {
	add_meta_box(
		'smartfaq-order',
		esc_attr__( 'Order of FAQ', 'smart-faq'),
		'smartfaq_order_function',
		'smart_faq',
		'normal',
		'high'
	);
}

function smartfaq_order_function( $post ) {
	// Retrieve the metadata values if they exist.
	$custom_ordering = get_option( 'smartfaq_options' );
	if ( 'meta_value_num' == $custom_ordering['smartfaq_ordering'] ) {
		$smartfaq_current_order = get_post_meta( $post->ID, '_smartfaq_order', true );

		printf(
			'<p>%s</p>',
			'<p>%s <input type="text" name="smartfaq_order" value="%s" /></p>',
			esc_attr__( 'Order:', 'smart-faq' ),
			esc_attr__( 'Please fill in a non negative number to determine order of this FAQ', 'smart-faq' ),
			esc_attr( $smartfaq_current_order ),
		);
	} else {
		echo wp_kses_post(
			__( 'For Custom Order, enable <b>Order by custom value</b> in Settings->Smart FAQ Settings', 'smart-faq' )
		);

		// printf(
		// 	// TODO: Translate sting with HTML inset.
		// 	'<p>For Custom Order, enable <b>Order by custom value</b> in Settings->Smart FAQ Settings</p>',
		// 	esc_attr__( 'Order by custom value', 'smart-faq' ),
		// );
	}
}

// Hook to save the meta box data.
add_action( 'save_post', 'smartfaq_save_order_meta' );

function smartfaq_save_order_meta( $post_id ) {
	if ( isset( $_POST['post_type'] ) && 'smart_faq' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	}
	else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	// Verify the metadata is set smartfaq_order name attribute.
	if ( isset( $_POST['smartfaq_order'] ) ) {
		// Save the metadata.
		update_post_meta( $post_id, '_smartfaq_order', preg_replace( '/[^0-9]/', '', $_POST['smartfaq_order'] ) );
	}
}
