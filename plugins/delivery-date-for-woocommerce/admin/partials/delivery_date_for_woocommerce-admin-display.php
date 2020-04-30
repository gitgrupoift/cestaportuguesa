<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.pixlogix.com
 * @since      1.0.0
 *
 * @package    Delivery_date_for_woocommerce
 * @subpackage Delivery_date_for_woocommerce/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->


<?php if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){ ?>

<h2>Delivery Date for Woocommerce Settings</h2>
<div class="SettingDetails" >
	<div class="PluginSettings">
	<form method="post" action="options.php">
	<?php settings_fields( 'ddfw_delivery_options_group' ); ?>
		<table class="form-table">
		  <tbody>
				<tr>
				<th scope="row" class="pxl-option"><label for="calendar on of">Enable Delivery Date?</label></th>
				<td>
					<div class="onoffswitch-1"> 
						<input  class="onoffswitch-checkbox" id="myonoffswitch_calendar_on_of" name="ddfw_enable_delivery" type="checkbox" <?php if(get_option('ddfw_enable_delivery')=="1"){ ?> checked <?php }?> value="1">
						<label class="onoffswitch-label" for="myonoffswitch_calendar_on_of">
							<span class="onoffswitch-inner-1"></span>
							<span class="onoffswitch-switch-1"></span>
						</label>
					</div>
				</td>
				</tr>
				<tr>
				<th scope="row" class="pxl-option"><label for="calendar on of">Disable delivery for Virtual products</label></th>
				<td>
					<div class="onoffswitch-1"> 
						<input  class="onoffswitch-checkbox" id="myonoffswitch_VF_disable" name="ddfw_disable_virtual" type="checkbox" <?php if(get_option('ddfw_disable_virtual')=="1"){ ?> checked <?php }?> value="1">
						<label class="onoffswitch-label" for="myonoffswitch_VF_disable">
							<span class="onoffswitch-inner-1"></span>
							<span class="onoffswitch-switch-1"></span>
						</label>
					</div>
				</td>
				</tr>
				<tr>
					<th><label for="Day_Delivery">Required delivery data?</label></th>
					<td>
						<div class="onoffswitch-1"> 
							<input  class="onoffswitch-checkbox" id="myonoffswitch_option_calendar_required" name="ddfw_required_delivery" type="checkbox" <?php if(get_option('ddfw_required_delivery')=="1"){ ?> checked <?php }?> value="1">
							<label class="onoffswitch-label" for="myonoffswitch_option_calendar_required">
								<span class="onoffswitch-inner-1"></span>
								<span class="onoffswitch-switch-1"></span>
							</label>
						</div>    
					</td>
				</tr>
				<tr>
				<th><label for="Day_Delivery">Field Label Title</label></th> 
				<td>
					<div class="onoffswitch"> 
						<input name="ddfw_delivery_date_title" type="text" class="input_text" value="<?php $title = get_option('ddfw_delivery_date_title'); if(empty($title)){ echo "Delivery Date"; }else{echo $title; }?>">
					</div>  
					<p class="description"><b>Note :</b>Add Your label that is to be displayed for the field on checkout page.</p>				
				</td>
				</tr>
				<tr>
				<th><label for="Day_Delivery">Field Placeholder Text</label></th> 
				<td>
					<div class="onoffswitch"> 
						<input name="ddfw_delivery_date_option_title" type="text" class="input_text" value="<?php $title_option = get_option('ddfw_delivery_date_option_title'); if(empty($title_option)){ echo "Select Your Delivery Date"; }else{echo $title_option; }?>">
					</div> 
					<p class="description"><b>Note :</b>Add Your placeholder text that is to be displayed for the field on checkout page.</p>					
				</td>
				</tr>
				<tr>
				<th><label for="Day_Delivery">Delivery Date Error Message</label></th>
				<td>
					<div class="onoffswitch"> 
						<input name="ddfw_delivery_error_msg" type="text" class="input_text" value="<?php $msg = get_option('ddfw_delivery_error_msg'); if(empty($msg)){ echo "Plz Select Your Delivery Date"; }else{echo $msg; }?>">
					</div>
					<p class="description"><b>Note :</b>Add Your  the placeholder text that is to be displayed for the field on checkout page.</p>	
				</td>
			</tr>
			<tr>
				<th><label for="Day_Delivery">Field Label For Email</label></th> 
				<td>
					<div class="onoffswitch"> 
						<input name="ddfw_delivery_date_email_title" type="text" class="input_text" value="<?php $emailTitle = get_option('ddfw_delivery_date_email_title'); if(empty($emailTitle)){ echo "Delivery Date"; }else{echo $emailTitle; }?>">
					</div>  
					<p class="description"><b>Note :</b>Add Your label that is to be displayed for the customer email.</p>				
				</td>
				</tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Disable X Days From Current Days?</label></th>
				<td>
					<div class="center-on-page">
						<div class="select">
							<select id="disable_x_days" name="ddfw_disable_x_days">
								<?php for($index = 0; $index <= 7; $index++){  ?>
									<option value="<?php echo $index ?>" <?php echo (get_option('ddfw_disable_x_days')== $index) ? "selected" : ""; ?>><?php echo $index; ?></option>
								<?php }; ?>
							</select>
						</div>
						<p class="description"><b>Note :</b>Option 0 is for today date (if you want to give deliver same day to your customer )</p>	
						<p class="description"><i><b>Example : </b></i> 1 for today,2 for tomorrow</p>
				  </div>
				</td>
			</tr>
			<?php 
			if(get_option('ddfw_disable_x_days') == 0 && get_option('ddfw_specific_day') == date("d-m-Y")):
				$dateVal = '';
			elseif (get_option('ddfw_disable_x_days') !=0  && empty(get_option('ddfw_specific_day'))):
				$dateVal = date("d-m-Y");
			else:
				$dateVal = get_option('ddfw_specific_day');
			endif;
			?>
			<tr>
				<th scope="row"><label for="Day_Delivery">Select Specific Day's Off</label></th>
				<td>
					<div class="calendar_icon"> 
						<input type="text" name="ddfw_specific_day" id="from-input" value="<?php echo $dateVal;  ?>">
					</div>
			   </td>
			</tr>
			<tr><td class="ddfd"><h2>Disable Days for Delivery</h2></td></tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Monday</label></th>
				<td>
					<div class="onoffswitch"> 
						<input class="onoffswitch-checkbox" id="myonoffswitch" name="ddfw_disable_monday" type="checkbox" <?php if(get_option('ddfw_disable_monday')=="1"){ ?> checked <?php }?> value="1">
						<label class="onoffswitch-label" for="myonoffswitch">
							<span class="onoffswitch-inner"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</div>
			   </td>
			</tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Tuesday</label></th>
				<td>
					<div class="onoffswitch"> 
						<input class="onoffswitch-checkbox" id="myonoffswitch_tu" name="ddfw_disable_tuesday" type="checkbox" <?php if(get_option('ddfw_disable_tuesday')=="2"){ ?> checked <?php }?> value="2">
						<label class="onoffswitch-label" for="myonoffswitch_tu">
							<span class="onoffswitch-inner"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Wednesday</label></th>
				<td>
					<div class="onoffswitch"> 
						<input class="onoffswitch-checkbox" id="myonoffswitch_w"  name="ddfw_disable_wednesday" type="checkbox" <?php if(get_option('ddfw_disable_wednesday')=="3"){ ?> checked <?php }?> value="3">
						<label class="onoffswitch-label" for="myonoffswitch_w">
							<span class="onoffswitch-inner"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Thursday</label></th>
				<td>
					<div class="onoffswitch"> 
						<input class="onoffswitch-checkbox" id="myonoffswitch_th" name="ddfw_disable_thursday" type="checkbox" <?php if(get_option('ddfw_disable_thursday')=="4"){ ?> checked <?php }?> value="4">
						<label class="onoffswitch-label" for="myonoffswitch_th">
							<span class="onoffswitch-inner"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Friday</label></th>
				<td>
					<div class="onoffswitch"> 
						<input class="onoffswitch-checkbox" id="myonoffswitch_f" name="ddfw_disable_friday" type="checkbox" <?php if(get_option('ddfw_disable_friday')=="5"){ ?> checked <?php }?> value="5">
						<label class="onoffswitch-label" for="myonoffswitch_f">
							<span class="onoffswitch-inner"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Saturday</label></th>
				<td>
					<div class="onoffswitch"> 
						<input class="onoffswitch-checkbox" id="myonoffswitch_sa" name="ddfw_disable_saturday" type="checkbox" <?php if(get_option('ddfw_disable_saturday')=="6"){ ?> checked <?php }?> value="6">
						<label class="onoffswitch-label" for="myonoffswitch_sa">
							<span class="onoffswitch-inner"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="Day_Delivery">Sunday</label></th>
				<td>
					<div class="onoffswitch"> 
						<input class="onoffswitch-checkbox" id="myonoffswitch_sun" name="ddfw_disable_sunday" type="checkbox" <?php if(get_option('ddfw_disable_sunday')=="7"){ ?> checked <?php }?> value="7">
						<label class="onoffswitch-label" for="myonoffswitch_sun">
							<span class="onoffswitch-inner"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</div>
				</td>
			</tr>
		  </tbody>
		</table>
	  <?php  submit_button(); ?>
	</form>
	</div>
	<div class="sideBar"> 
		<div class="widget plugin-donate">
			<a target="_blank" href="https://www.paypal.com/paypalme2/pixlogix/">
			<img src="<?php echo plugin_dir_url( __FILE__ ).'/Donate.png'; ?>">
			</a>
			<a  href="mailto:support@pixlogix.com">
			<img src="<?php echo plugin_dir_url( __FILE__ ).'/customization.png'; ?>">
			</a>
			<a target="_blank" href="https://www.pixlogix.com/request-quote/">
			<img src="<?php echo plugin_dir_url( __FILE__ ).'/pixlogix.png'; ?>">
			</a>
		</div>
	</div>
</div>

<?php } else{
echo '<div class="error"><h1> This plugin is dependent on the Woocommerce plugin, so kindly activate the  Woocommerce plugin to use this plugin!  </h1></div>';
} ?> 