<?php
if (is_admin()) { // admin actions
    add_action('admin_menu', 'smartfaq_create_menu');
    
    function smartfaq_create_menu()
    {
        //create custom top-level menu
        add_options_page('Smart FAQ Settings', 'Smart FAQ Settings', 'manage_options', 'smartfaq-admin', 'smartfaq_settings_page');
        
    }
    
    add_action('admin_init', 'register_smartfaq_settings');
    function register_smartfaq_settings()
    {
        register_setting('smartfaq_options', 'smartfaq_options', 'smartfaq_validate_options');
        add_settings_section('smartfaq_settings', 'Smart FAQ Settings', 'smartfaq_section_text', 'smartfaq-admin');
        add_settings_field('smartfaq_ordering', 'Order by', 'smartfaq_order_field', 'smartfaq-admin', 'smartfaq_settings');
        add_settings_field('smartfaq_order_type', 'FAQ Order Type', 'smartfaq_order_type_field', 'smartfaq-admin', 'smartfaq_settings');
        //add_settings_field('smartfaq_posts_no', 'FAQ\'s Per Page', 'smartfaq_postsno_field', 'smartfaq-admin', 'smartfaq_settings');
        
    }
    function smartfaq_validate_options($input)
    {
        $valid                        = array();
        //$valid['smartfaq_posts_no']   = preg_replace("/[^0-9]/", "", $input['smartfaq_posts_no']);
        $valid['smartfaq_ordering']   = $input['smartfaq_ordering'];
        $valid['smartfaq_order_type'] = $input['smartfaq_order_type'];
        return $valid;
    }
    function smartfaq_section_text()
    {
        echo "<p>How should your FAQ's be ordered?</p>";
    }
    
    function smartfaq_order_field()
    {
        // get option 'ordering_type’ value from the database
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
<option value="<?php
        echo $sf_ordering[0];
?>" <?php
        selected($ordering_by, $sf_ordering[0]);
?> > No order </option>
<option value="<?php
        echo $sf_ordering[1];
?>" <?php
        selected($ordering_by, $sf_ordering[1]);
?> > Order by ID </option>
<option value="<?php
        echo $sf_ordering[2];
?>" <?php
        selected($ordering_by, $sf_ordering[2]);
?> > Order by Title </option>
<option value="<?php
        echo $sf_ordering[3];
?>" <?php
        selected($ordering_by, $sf_ordering[3]);
?> > Random by FAQ slug </option>
<option value="<?php
        echo $sf_ordering[4];
?>" <?php
        selected($ordering_by, $sf_ordering[4]);
?> > Random Order </option>
<option value="<?php
        echo $sf_ordering[5];
?>" <?php
        selected($ordering_by, $sf_ordering[5]);
?> > Order by Custom value (On FAQ page) </option>
</select>
<?php
    }
    
    function smartfaq_order_type_field()
    {
        $options       = get_option('smartfaq_options');
        $ordering_type = $options['smartfaq_order_type'];
?>
<span>ASC </span><input type="radio" name="smartfaq_options[smartfaq_order_type]" value="1" <?php
        checked($ordering_type, 1);
?> />
&nbsp;&nbsp;
<span>DSC </span><input type="radio" name="smartfaq_options[smartfaq_order_type]" value="0" <?php
        checked($ordering_type, 0);
?> />
<?php
    }
    
    function smartfaq_postsno_field()
    {
        // get option 'no. of FAQ's to show’ value from the database
        $options           = get_option('smartfaq_options');
        $smartfaq_posts_no = $options['smartfaq_posts_no'];
        echo "<input id='smartfaq_posts_no' name='smartfaq_options[smartfaq_posts_no]'
type='text' value='$smartfaq_posts_no' /> ";
        
    }
    
    function smartfaq_settings_page()
    {
?>
<div>
<h2>Smart FAQ Options </h2>
Configure the Smart FAQ plugin parameters from here.
<form id="smartfaq_options" action="options.php" method="post">
<?php
        settings_fields('smartfaq_options');
?>
<?php
        do_settings_sections('smartfaq-admin');
?>
<?php
        submit_button('Save options', 'primary', 'smartfaq_options_submit');
?>
</form></div>
 
<?php
    }
}

else {
    
}
?>