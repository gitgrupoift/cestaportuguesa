<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://meuppt.pt
 * @since      1.0.0
 *
 * @package    Wc_Gdpr_Aan
 * @subpackage Wc_Gdpr_Aan/admin/partials
 */
?>

<!-- Removes WPFooter -->

<style>

#wpfooter {display: none;}

</style>


<div class="wrap container-fluid">

<?php 
	include_once('wc-gdpr-aan-admin-header.php');
?>
	
	
<hr>
<form method="post" name="wc-gdpr-aan-settings" action="options.php">
	    
	    
	    
	    
<?php
       
        //Capture and save all settings and sanitize them in another file
         
        $options = get_option($this->plugin_name);
        
        $wc_gdpr_aan_cb1 = ( isset( $options['wc_gdpr_aan_cb1'] ) && ! empty( $options['wc_gdpr_aan_cb1'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb2 = ( isset( $options['wc_gdpr_aan_cb2'] ) && ! empty( $options['wc_gdpr_aan_cb2'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb3 = ( isset( $options['wc_gdpr_aan_cb3'] ) && ! empty( $options['wc_gdpr_aan_cb3'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb4 = ( isset( $options['wc_gdpr_aan_cb4'] ) && ! empty( $options['wc_gdpr_aan_cb4'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb5 = ( isset( $options['wc_gdpr_aan_cb5'] ) && ! empty( $options['wc_gdpr_aan_cb5'] ) ) ? 1 : 0;        
        $wc_gdpr_aan_cb6 = ( isset( $options['wc_gdpr_aan_cb6'] ) && ! empty( $options['wc_gdpr_aan_cb6'] ) ) ? 1 : 0;
        
        $wc_gdpr_aan_cb7 = ( isset( $options['wc_gdpr_aan_cb7'] ) && ! empty( $options['wc_gdpr_aan_cb7'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb8 = ( isset( $options['wc_gdpr_aan_cb8'] ) && ! empty( $options['wc_gdpr_aan_cb8'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb9 = ( isset( $options['wc_gdpr_aan_cb9'] ) && ! empty( $options['wc_gdpr_aan_cb9'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb10 = ( isset( $options['wc_gdpr_aan_cb10'] ) && ! empty( $options['wc_gdpr_aan_cb10'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb11 = ( isset( $options['wc_gdpr_aan_cb11'] ) && ! empty( $options['wc_gdpr_aan_cb11'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb12 = ( isset( $options['wc_gdpr_aan_cb12'] ) && ! empty( $options['wc_gdpr_aan_cb12'] ) ) ? 1 : 0;
        
        $wc_gdpr_aan_cb13 = ( isset( $options['wc_gdpr_aan_cb13'] ) && ! empty( $options['wc_gdpr_aan_cb13'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb14 = ( isset( $options['wc_gdpr_aan_cb14'] ) && ! empty( $options['wc_gdpr_aan_cb14'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb15 = ( isset( $options['wc_gdpr_aan_cb15'] ) && ! empty( $options['wc_gdpr_aan_cb15'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb16 = ( isset( $options['wc_gdpr_aan_cb16'] ) && ! empty( $options['wc_gdpr_aan_cb16'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb17 = ( isset( $options['wc_gdpr_aan_cb17'] ) && ! empty( $options['wc_gdpr_aan_cb17'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb18 = ( isset( $options['wc_gdpr_aan_cb18'] ) && ! empty( $options['wc_gdpr_aan_cb18'] ) ) ? 1 : 0;
        
        $wc_gdpr_aan_cb19 = ( isset( $options['wc_gdpr_aan_cb19'] ) && ! empty( $options['wc_gdpr_aan_cb19'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb20 = ( isset( $options['wc_gdpr_aan_cb20'] ) && ! empty( $options['wc_gdpr_aan_cb20'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb21 = ( isset( $options['wc_gdpr_aan_cb21'] ) && ! empty( $options['wc_gdpr_aan_cb21'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb22 = ( isset( $options['wc_gdpr_aan_cb22'] ) && ! empty( $options['wc_gdpr_aan_cb22'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb23 = ( isset( $options['wc_gdpr_aan_cb23'] ) && ! empty( $options['wc_gdpr_aan_cb23'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cb24 = ( isset( $options['wc_gdpr_aan_cb24'] ) && ! empty( $options['wc_gdpr_aan_cb24'] ) ) ? 1 : 0;
       
        
        $wc_gdpr_aan_message = ( isset( $options['wc_gdpr_aan_message'] ) && ! empty( $options['wc_gdpr_aan_message'] ) ) ? $options['wc_gdpr_aan_message'] : false ;
        
        $wc_gdpr_aan_cba1 = ( isset( $options['wc_gdpr_aan_cba1'] ) && ! empty( $options['wc_gdpr_aan_cba1'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba2 = ( isset( $options['wc_gdpr_aan_cba2'] ) && ! empty( $options['wc_gdpr_aan_cba2'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba3 = ( isset( $options['wc_gdpr_aan_cba3'] ) && ! empty( $options['wc_gdpr_aan_cba3'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba4 = ( isset( $options['wc_gdpr_aan_cba4'] ) && ! empty( $options['wc_gdpr_aan_cba4'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba5 = ( isset( $options['wc_gdpr_aan_cba5'] ) && ! empty( $options['wc_gdpr_aan_cba5'] ) ) ? 1 : 0;        
        $wc_gdpr_aan_cba6 = ( isset( $options['wc_gdpr_aan_cba6'] ) && ! empty( $options['wc_gdpr_aan_cba6'] ) ) ? 1 : 0;
        
        $wc_gdpr_aan_cba7 = ( isset( $options['wc_gdpr_aan_cba7'] ) && ! empty( $options['wc_gdpr_aan_cba7'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba8 = ( isset( $options['wc_gdpr_aan_cba8'] ) && ! empty( $options['wc_gdpr_aan_cba8'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba9 = ( isset( $options['wc_gdpr_aan_cba9'] ) && ! empty( $options['wc_gdpr_aan_cba9'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba10 = ( isset( $options['wc_gdpr_aan_cba10'] ) && ! empty( $options['wc_gdpr_aan_cba10'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba11 = ( isset( $options['wc_gdpr_aan_cba11'] ) && ! empty( $options['wc_gdpr_aan_cba11'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba12 = ( isset( $options['wc_gdpr_aan_cba12'] ) && ! empty( $options['wc_gdpr_aan_cba12'] ) ) ? 1 : 0;
        
        $wc_gdpr_aan_cba13 = ( isset( $options['wc_gdpr_aan_cba13'] ) && ! empty( $options['wc_gdpr_aan_cba13'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba14 = ( isset( $options['wc_gdpr_aan_cba14'] ) && ! empty( $options['wc_gdpr_aan_cba14'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba15 = ( isset( $options['wc_gdpr_aan_cba15'] ) && ! empty( $options['wc_gdpr_aan_cba15'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba16 = ( isset( $options['wc_gdpr_aan_cba16'] ) && ! empty( $options['wc_gdpr_aan_cba16'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba17 = ( isset( $options['wc_gdpr_aan_cba17'] ) && ! empty( $options['wc_gdpr_aan_cba17'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba18 = ( isset( $options['wc_gdpr_aan_cba18'] ) && ! empty( $options['wc_gdpr_aan_cba18'] ) ) ? 1 : 0;
        
        $wc_gdpr_aan_cba19 = ( isset( $options['wc_gdpr_aan_cba19'] ) && ! empty( $options['wc_gdpr_aan_cba19'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba20 = ( isset( $options['wc_gdpr_aan_cba20'] ) && ! empty( $options['wc_gdpr_aan_cba20'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba21 = ( isset( $options['wc_gdpr_aan_cba21'] ) && ! empty( $options['wc_gdpr_aan_cba21'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba22 = ( isset( $options['wc_gdpr_aan_cba22'] ) && ! empty( $options['wc_gdpr_aan_cba22'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba23 = ( isset( $options['wc_gdpr_aan_cba23'] ) && ! empty( $options['wc_gdpr_aan_cba23'] ) ) ? 1 : 0;
        $wc_gdpr_aan_cba24 = ( isset( $options['wc_gdpr_aan_cba24'] ) && ! empty( $options['wc_gdpr_aan_cba24'] ) ) ? 1 : 0;
       
        
        $wc_gdpr_aan_messagea = ( isset( $options['wc_gdpr_aan_messagea'] ) && ! empty( $options['wc_gdpr_aan_messagea'] ) ) ? $options['wc_gdpr_aan_messagea'] : false ;
        
        
        
        settings_fields($this->plugin_name);
        do_settings_sections($this->plugin_name);      
        
?>

<?php
	include_once('wc-gdpr-aan-admin-first.php');
?>



	
	<nav class="navbar fixed-bottom navbar-light bg-light">
		<ul class="navbar-nav ml-auto">
            		<li class="nav-item">
                		<?php submit_button( __( 'Guardar configurações', $this->plugin_name ), 'primary','submit', TRUE ); ?>
            		</li>
  		</ul>
  
	</nav>
	
	</form>
	


</div>



</div>
</div>




<script>

// Wordpress button style override
var element = document.getElementById("submit"); 
element.classList.remove("button","button-primary");
element.classList.add("btn","btn-success");



</script>

<?php



?>

	
	
	
	
	
	
	
	