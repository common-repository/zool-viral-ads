<?php
/*
Plugin Name: Zool Viral Ads
Plugin URI: http://www.zoolley.com/
Description: Zool Viral Ads enables blog owners to display ads or related blog posts. The ads are dynamically sourced from the Zoolley.com system and it can be a good source of revenue.
Author: Manoj Kumar Mahto
Version: 1.1.1.13
Author URI: http://twitter.com/Zoolley
Copyright: 2016 Zoolley Technologies India (info@zoolley.com)
*/

$zool_ads_options = get_option('zool_ads_settings');


function zool_ads_add_content ($content) {
	
	global $zool_ads_options;

	if(is_single() && $zool_ads_options['enable'] == true ){
		
		$dir = plugins_url();
		wp_enqueue_style( 'style', $dir.'/zool-viral-ads/css/zoolplugin.css');
		
		// create curl resource
		$ch = curl_init();

        //Get country based on IP
		$ad_url = get_url_on_ip();

        // set url
		curl_setopt($ch, CURLOPT_URL, $ad_url);

		//Set Post Fields for Sending Widget ID
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query(array('widgetID'=>$zool_ads_options['widget_id'],'ip'=>$_SERVER['REMOTE_ADDR'],'browser_agent'=>$_SERVER['HTTP_USER_AGENT'])));

        //return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
		$output = curl_exec($ch);

        // close curl resource to free up system resources
		curl_close($ch);
		
		if($output){
			$myJSON = json_decode($output, true);
		$vals_Output = '<div class="container-fluid"><div class="row">';
		$count = 1;
		//Get grid options
		$mod_condition = 4;
		$container_class = 'col-md-3';
		if($zool_ads_options['grid_option'] == 1){
			$container_class = 'col-md-3';
		}else if($zool_ads_options['grid_option'] == 2){
			$container_class = 'col-md-12';
			$mod_condition = 1;
		}else if($zool_ads_options['grid_option'] == 3){
			$container_class = 'col-md-4';
			$mod_condition = 3;
		}
		
		foreach($myJSON as $mJ){
			$vals_Output .= '<div class="'.$container_class.'" style="padding-right:2px; padding-left:2px;">';
			$vals_Output .= '<a href="'.$mJ['link'].'">';
			$vals_Output .= '<img src="'.$mJ['flink'].'" alt="image" class="img-responsive" style="width:100%;">';
			
			$vals_Output .= '<div style="color:#000;padding:8px 0;height:auto;font-weight:normal;font-size:15px;line-height:17px;">'.substr($mJ['title'],0,80).'...</div>';		$vals_Output .= '</a>';
			$vals_Output .= '</div>';
			if($count%$mod_condition == 0){
				$vals_Output .= '</div><div class="row">';
			}
			$count++;

		}
		$vals_Output .= '</div></div>';

		$title_may = count($myJSON);
		if($title_may == 0){
			$heading = "";
		}else{
			$heading = "You May Also Like";
		}
		
		$extra_content = '<div class="main">
		<b><p style="font-family: helvetica; font-size: 14px">'.$heading.'</p></b>
		<p style="text-align:right;font-size:11px;"><a href="http://zoolley.com/native/" target="_blank">Ads by Zoolley</a></p>
		<div style="clear:both;"></div>
		<div id="zolley_ad_content">'.$vals_Output.'</div>
	</div>
	<div class="clear"></div>';
	$content .= $extra_content;
		}else{
			$content .= '<center>Please set a valid Widget ID to activate ads.</center>';
		}
	
}
return $content;

}


add_action('wp_enqueue_scripts', 'zool_ads_add_content');
add_filter ('the_content', 'zool_ads_add_content');


function zool_ads_options_page(){
	
	global $zool_ads_options;
	
	ob_start(); ?>
	
	<div class="wrap">
		<h2>Zool Viral Ads - Settings</h2>
		<form method="post" action="options.php">
			<?php settings_fields('zool_ads_settings_group'); ?>
			
			
			
			<h4><?php _e('Enable', 'zool_ads_domain'); ?></h4>
			<p>
				<input id="zool_ads_settings[enable]" name="zool_ads_settings[enable]" type="checkbox" value="1" <?php checked('1', $zool_ads_options['enable']);?>/>
				<label class="description" for="zool_ads_settings[enable]"><?php _e('Display the Ads', 'zool_ads_domain'); ?> </label>
				
			</p>

			<p>
				<label class="description" for="zool_ads_settings[grid_option]"><?php _e('Select Grid Option', 'zool_ads_domain'); ?> </label>
				<select id="zool_ads_settings[grid_option]" name="zool_ads_settings[grid_option]">
					<option value="1" <?php selected('1', $zool_ads_options['grid_option']);?>>4x3</option>
					<option value="2" <?php selected('2', $zool_ads_options['grid_option']);?>>1x6</option>
					<option value="3" <?php selected('3', $zool_ads_options['grid_option']);?>>3x4</option>
				</select>
			</p>
			
			
			
			<h4><?php _e('Account Info', 'zool_ads_domain'); ?></h4>
			<p>
				<label class="description" for="zool_ads_settings[widget_id]"><?php _e('Enter your Zool Viral Widget ID', 'zool_ads_domain'); ?> </label>
				<input id="zool_ads_settings[widget_id]" name="zool_ads_settings[widget_id]" type="text" value="<?php echo $zool_ads_options['widget_id']; ?>"/>
			</p>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Options', 'zool_ads_domain'); ?>" />
			</p>
			
		</form>
		<p>Do you have Zool Viral Ads account? If not, <a href="http://www.zoolley.com/native">create one now</a> - it's 100% free.</p>
		
	</div>
	<?php
	echo ob_get_clean();
}






function zool_ads_add_options_link(){
	
	add_options_page('Zool Viral Ads Plugin Page', 'Zool Viral Ads', 'manage_options', 'zool-viral-ads', 'zool_ads_options_page' );
}

add_action('admin_menu', 'zool_ads_add_options_link');

function zool_ads_register_settings(){
	register_setting('zool_ads_settings_group','zool_ads_settings');
}

add_action('admin_init','zool_ads_register_settings');

//IP to Country Detect Code
function get_url_on_ip(){
	$user_ip = getenv('REMOTE_ADDR');
// $user_ip = '72.229.28.185';
	$geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
	$countryCode = $geo["geoplugin_countryCode"];
	// $url_nw = 'http://zoolley.com/api/test/json.php?country='.$countryCode;
	$url_nw = 'http://zoolley.com/native/api/get_ad_contents/'. $countryCode;
	//$url_nw = 'http://localhost/native/api/get_ad_contents/'.$countryCode;
	return $url_nw;
}

//Widget Code

//Action for Widget Register
add_action('widgets_init','zool_widget_function');

function zool_widget_function(){
	register_widget('zool_widget');
}

class zool_widget extends WP_Widget{
	function zool_widget(){
		//Process the widget
		$widget_opts = array(
			'classsname' => 'zool_widget_class',
			'description' => 'Zool Viral Ads Widget'
			);
		$this->WP_Widget('zool_widget','Zool Viral Ads',$widget_opts);
	}

	function form($instance){
		//Widget Form in Admin Dashboard
	}

	function update($new_instance, $old_instance){
		//Save widget option
	}

	function widget($args, $instance){
		global $zool_ads_options;
		//Display the widget
		extract($args);
		echo $before_widget;
		
		// create curl resource
		$ch = curl_init();

        //Get country based on IP
		$ad_url = get_url_on_ip();

        // set url
		curl_setopt($ch, CURLOPT_URL, $ad_url);

		//Set Post Fields for Sending Widget ID
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query(array('widgetID'=>$zool_ads_options['widget_id'],'ip'=>$_SERVER['REMOTE_ADDR'],'browser_agent'=>$_SERVER['HTTP_USER_AGENT'])));

        //return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
		$output = curl_exec($ch);

        // close curl resource to free up system resources
		curl_close($ch);
		
		if($output){
			$myJSON = json_decode($output, true);
		$vals_Output = '<div class="container-fluid"><div class="row">';
		$count = 1;

		$title_may = count($myJSON);
		if($title_may == 0){
			$heading = "";
		}else{
			$heading = 'You May Also Like<p style="text-align:right;font-size:11px;"><a href="http://zoolley.com/native/" target="_blank">Ads by Zoolley</a></p>';
		}

		echo $before_title.$heading.$after_title;

		//Get grid options
		$container_class = 'col-md-12';
		foreach($myJSON as $mJ){
			if($count > 6){
				continue;
			}
			$vals_Output .= '<div class="'.$container_class.'" style="padding-right:2px; padding-left:2px;">';
			$vals_Output .= '<a href="'.$mJ['link'].'">';
			$vals_Output .= '<img src="'.$mJ['flink'].'" alt="image" class="img-responsive" style="width:100%;">';
			$vals_Output .= '<div style="color:#000;padding:8px 0;height:auto;font-weight:normal;font-size:15px;line-height:17px;">'.substr($mJ['title'],0,80).'...</div>';
			$vals_Output .= '</a>';
			$vals_Output .= '</div>';
			$count++;		}
			$vals_Output .= '</div></div>';
			echo $vals_Output;
			echo $after_widget;
		}else{
			echo '<center>Please set a valid Widget ID to activate ads.</center>';
		}
		}
	}
	?>
