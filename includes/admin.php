<?php
/**
 * Admin related tasks.
 *
 * @package SmartFaq
 */

if ( ! is_admin() ) {
	return;
}

add_action( 'admin_menu', 'smartfaq_create_menu' );

function smartfaq_create_menu() {
	// Create custom top-level menu.
	add_options_page(
		__( 'Smart FAQ Settings', 'smart-faq' ),
		__( 'Smart FAQ Settings', 'smart-faq' ),
		'manage_options',
		'smartfaq-admin',
		'smartfaq_settings_page'
	);
}

add_action( 'admin_init', 'smartfaq_register_settings' );

function smartfaq_register_settings() {
	register_setting( 'smartfaq_options', 'smartfaq_options', 'smartfaq_validate_options' );
	add_settings_section( 'smartfaq_settings', 'Smart FAQ Settings', 'smartfaq_section_text', 'smartfaq-admin' );
	add_settings_field( 'smartfaq_ordering', 'Order by', 'smartfaq_order_field', 'smartfaq-admin', 'smartfaq_settings' );
	add_settings_field( 'smartfaq_order_type', 'FAQ Order Type', 'smartfaq_order_type_field', 'smartfaq-admin', 'smartfaq_settings' );
	// add_settings_field( 'smartfaq_posts_no', 'FAQ\'s Per Page', 'smartfaq_postsno_field', 'smartfaq-admin', 'smartfaq_settings' );
}

function smartfaq_validate_options( $input ) {
	$valid                        = array();
	//$valid['smartfaq_posts_no']   = preg_replace("/[^0-9]/", "", $input['smartfaq_posts_no']);
	$valid['smartfaq_ordering']   = $input['smartfaq_ordering'];
	$valid['smartfaq_order_type'] = $input['smartfaq_order_type'];

	return $valid;
}

function smartfaq_section_text() {
	printf(
		'<p>%s</p>',
		esc_attr__( "How should your FAQ's be ordered?", 'smart-faq' ),
	);
}

function smartfaq_order_field() {
	// Get option 'ordering_type value from the database.
	$options     = get_option('smartfaq_options');
	$ordering_by = $options['smartfaq_ordering'];
	$sf_ordering = array(
		"none",
		"ID",
		"title",
		"name",
		"rand",
		"meta_value_num"
	);
	// echo the field
	?>
	<select name='smartfaq_options[smartfaq_ordering]' id='smartfaq_ordering'>
		<option value="<?php echo $sf_ordering[0]; ?>"
			<?php selected($ordering_by, $sf_ordering[0]); ?> > <?php esc_attr_e( 'No order', 'smart-faq' ); ?> </option>
		<option value="<?php echo $sf_ordering[1]; ?>" <?php
			selected($ordering_by, $sf_ordering[1]); ?> > <?php esc_attr_e( 'Order by ID', 'smart-faq' ); ?> </option>
		<option value="<?php echo $sf_ordering[2]; ?>"
			<?php selected($ordering_by, $sf_ordering[2]); ?> > <?php esc_attr_e( 'Order by Title', 'smart-faq' ); ?> </option>
		<option value="<?php echo $sf_ordering[3]; ?>"
			<?php selected($ordering_by, $sf_ordering[3]); ?> > <?php esc_attr_e( 'Random by FAQ slug', 'smart-faq' ); ?> </option>
		<option value="<?php echo $sf_ordering[4]; ?>"
			<?php selected($ordering_by, $sf_ordering[4]); ?> > <?php esc_attr_e( 'Random Order', 'smart-faq' ); ?> </option>
		<option value="<?php echo $sf_ordering[5]; ?>"
			<?php selected($ordering_by, $sf_ordering[5]); ?> > <?php esc_attr_e( 'Order by Custom value (On FAQ page)', 'smart-faq' ); ?> </option>
	</select>
	<?php
}

function smartfaq_order_type_field() {
	$options       = get_option( 'smartfaq_options' );
	$ordering_type = $options['smartfaq_order_type'];
	?>
	<span><?php esc_attr_e( 'ASC', 'smart-faq' ); ?> </span>
	<input
		type="radio"
		name="smartfaq_options[smartfaq_order_type]"
		value="1" <?php checked( $ordering_type, 1 ); ?> />
	&nbsp;&nbsp;
	<span><?php esc_attr_e( 'DSC', 'smart-faq' ); ?> </span>
	<input
		type="radio"
		name="smartfaq_options[smartfaq_order_type]"
		value="0" <?php checked( $ordering_type, 0 ); ?> />
	<?php
}

/**
 * Get option 'no. of FAQ's to show� value from the database.
 *
 * @return void
 */
function smartfaq_postsno_field() {
	$options           = get_option('smartfaq_options');
	$smartfaq_posts_no = $options['smartfaq_posts_no'];

	printf(
		'<input id="smartfaq_posts_no" name="smartfaq_options[smartfaq_posts_no]" type="text" value="%s" />',
		$smartfaq_posts_no
	);
}

function smartfaq_settings_page() {
	?>
	<div>
		<h2><?php esc_attr_e( 'Smart FAQ Options', 'smart-faq' ); ?></h2>
		<p><?php esc_attr_e( 'Configure the Smart FAQ plugin parameters from here.', 'smart-faq' ); ?></p>
		<form id="smartfaq_options" action="options.php" method="post">
		<?php
			settings_fields( 'smartfaq_options' );
			do_settings_sections( 'smartfaq-admin' );
			submit_button( esc_attr__( 'Save options', 'smart-faq' ), 'primary', 'smartfaq_options_submit' );
		?>
		</form>
	</div>
	<?php
}
