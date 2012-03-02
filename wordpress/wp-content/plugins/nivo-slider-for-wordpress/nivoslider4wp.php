<?php
	/*
	Plugin Name: Nivo Slider for WordPress
	Description: Nivo Slider for WordPress plugin is based on S3Slider developed by Vinicius Massuchetto, adapted for their use JQuery plugin NivoSlider.
	Version: 0.3.3
	Author: Marcelo Torres
	Author URI: http://www.marcelotorresweb.com/
	*/

	if ( function_exists('plugins_url') )
		$url = plugins_url(plugin_basename(dirname(__FILE__)));
	else
		$url = get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));
	add_action('admin_menu', 'nivoslider4wp_install');
	global $ns4wp_plugindir;
	$ns4wp_plugindir = ABSPATH.'wp-content/plugins/nivo-slider-for-wordpress/';
	load_plugin_textdomain ( 'nivoslider4wp' , false, 'nivo-slider-for-wordpress/lang'  );
	
	function nivoslider4wp_install() {
	$nivoslider4wp_files = ABSPATH."wp-content/uploads/nivoslider4wp_files";
	$nivoslider4wp_files_old = ABSPATH."wp-content/nivoslider4wp_files";

	/* Cria a pasta (nivoslider4wp_files), onde ficarão as images do upload*/
	if (!file_exists($nivoslider4wp_files)) {
		umask(0); 
		mkdir($nivoslider4wp_files, 0777, true) or die("error creating the folder" . $nivoslider4wp_files . "check folder permissions");
	}
	
	/* função para copiar o diretório inteiro */
	function copyr($source, $dest)
	{
	   if (is_file($source)) {
		  return copy($source, $dest);
	   }	 
	   if (!is_dir($dest)) {
		  mkdir($dest);
	   }	 
	   $dir = dir($source);
	   while (false !== $entry = $dir->read()) {
		  if ($entry == '.' || $entry == '..') {
			 continue;
		  }	 
		  if ($dest !== "$source/$entry") {
			 copyr("$source/$entry", "$dest/$entry");
		  }
	   }
	   $dir->close();
	   return true;
	}
	
	if (file_exists($nivoslider4wp_files_old)) {
		umask(0); 
		copyr($nivoslider4wp_files_old, $nivoslider4wp_files) or die("error moving the folder" . $nivoslider4wp_files . "check folder permissions");
	}
	
	/* função para apagar o diretório inteiro */
	function rmdir_r($path)
	{
		if (!is_dir($path))
		{
			return false;
		}
		if (!preg_match("/\\/$/", $path))
		{
			$path .= '/';
		}
		$dh = opendir($path);

		while (($file = readdir($dh)) !== false)
		{
			if ($file == '.'  ||  $file == '..')
			{
				continue;
			}
			if (is_dir($path . $file))
			{
				rmdir_r($path . $file);
			}
			else if (is_file($path . $file))
			{
				unlink($path . $file);
			}
		}
		closedir($dh);
		rmdir($path);
		return true;
	}
	
	if (file_exists($nivoslider4wp_files_old)) {
		rmdir_r($nivoslider4wp_files_old) or die("error when deleting a folder" . $nivoslider4wp_files_old . "check folder permissions");
	}
	
		global $wpdb;
		/*adiciona menu e submenus*/
		add_menu_page('Nivo Slider for WordPress', __('Nivo Slider For WordPress'), 'read', __FILE__, 'nivoslider4wp_panel', get_option('siteurl') . '/wp-content/plugins/nivo-slider-for-wordpress/img/menu.png');
		$plugin_addimages = add_submenu_page(__FILE__ , __('Add/Edit image', 'nivoslider4wp'), __('Add/Edit image', 'nivoslider4wp'), 'read', 'nivo-slider-for-wordpress/nivoslider4wp.php');
		$plugin_options = add_submenu_page(__FILE__ , __('Options', 'nivoslider4wp'), __('Options', 'nivoslider4wp'), 'read', 'nivoslider4wp-options', 'nivoslider4wp_option');
	
		/*cria tabela no banco de dados*/
		$query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nivoslider4wp` (
			`nivoslider4wp_id` INT NOT NULL AUTO_INCREMENT,
			`nivoslider4wp_type` TEXT,
			`nivoslider4wp_order` INT,
			`nivoslider4wp_text_headline` TEXT,
			`nivoslider4wp_x` INT,
			`nivoslider4wp_y` INT,
			`nivoslider4wp_x2` INT,
			`nivoslider4wp_y2` INT,
			`nivoslider4wp_w` INT,
			`nivoslider4wp_h` INT,
			`nivoslider4wp_image_link` TEXT,
			`nivoslider4wp_image_status` CHAR(1),
		PRIMARY KEY ( `nivoslider4wp_id` ));";
		$wpdb->query($query);

		/* cria as opçoes no banco de dados e guarda um valor padr&atilde;o para campo da pagina de op&ccedil;&otilde;es*/
		add_option('nivoslider4wp_width', 640);
		add_option('nivoslider4wp_height', 219);
		
		add_option('nivoslider4wp_colsBox', 4);
		add_option('nivoslider4wp_rowsBox', 2);
		add_option('nivoslider4wp_animSpeed', 500);
		/*add_option('nivoslider4wp_themes', 'notheme');*/
		add_option('nivoslider4wp_effect', 'random');
		add_option('nivoslider4wp_pauseTime', 3000);
		add_option('nivoslider4wp_directionNav', 'true');
		add_option('nivoslider4wp_directionNavHide', 'true');
		add_option('nivoslider4wp_controlNav', 'false');
		add_option('nivoslider4wp_keyboardNav', 'true');
		add_option('nivoslider4wp_pauseOnHover', 'true');
		add_option('nivoslider4wp_manualAdvance', 'false');
		
		add_option('nivoslider4wp_backgroundCaption', '000000');
		add_option('nivoslider4wp_colorCaption', 'ffffff');
		add_option('nivoslider4wp_captionOpacity', '0.8');
		
		add_option('nivoslider4wp_nivoslider4wp_js', 'footer');
		add_option('nivoslider4wp_imageQuality', '80');
		
		/* Add contextual Help $plugin_addimages */
		if (function_exists('add_contextual_help')) {
			add_contextual_help( $plugin_manageslider, '<h2 style="font-weight:lighter;">'. __('Nivo Slider for WordPress - Help > Add image', 'nivoslider4wp') .'</h2>'.
				'<dl><dt><strong>'. __('Adding images:', 'nivoslider4wp') .'</strong></dt>'.
				'<dd>'. __('To add a new image on your slider, click in buttom <em>Add new image</em>, then select the desired image(are supported JPG, PNG and GIF) and then click the button <em>send and edit image', 'nivoslider4wp') .'</em>.</dd>' .	
				'<dt><strong>'. __('Image setting:', 'nivoslider4wp') .'</strong></dt>' .
				'<dd>'. __('Once the image was sent, set the crop area and write caption below and the image link if you wish.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Image editing:', 'nivoslider4wp') .'</strong></dt>' .
				'<dd>'. __('To edit the image after you have set your settings, click in the link <em>edit</em> aside the column\'s caption.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Excluding image:', 'nivoslider4wp') .'</strong></dt>' .
				'<dd>'. __('To delete the image, click in the link <em>Remove</em> aside the column\'s caption.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Changing the display order of images:', 'nivoslider4wp') .'</strong></dt>' .
				'<dd>'. __('To set the display order of images, click the line of the desired image and drag up or down until the order of images as you want.', 'nivoslider4wp') .'</dd>'.
				'</dl>'
			);
		}
		
		/* Add contextual Help $plugin_options */
		if (function_exists('add_contextual_help')) {
			add_contextual_help( $plugin_options ,'<h2 style="font-weight:lighter;">'. __('Nivo Slider for WordPress - Help > Options', 'nivoslider4wp') .'</h2>'.
				'<h3>'. __('Size of the cut (it will also be the size of the slider):', 'nivoslider4wp') .'</h3>' .
				'<p>'. __('This option sets the width and height in pixels in the corresponding fields. This will be the dimensions of the slider and the clipping of the image after uploading the same.', 'nivoslider4wp') .'</p>' .	
				'<em style="color:red;">'. __('Important: if you change these dimensions after you have already inserted some image, will have to cut out the images again, so that they fit the dimensions defined.', 'nivoslider4wp') .'</em>' .
				'<h3>'. __('Nivo Slider settings:', 'nivoslider4wp') .'</h3>'.
				'<dl><dt><strong>'. __('Transition effects:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Select one of the effects to change the transition between each image.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Speed the transition from slide:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Speed that will show the selected transition.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Pause time between transitions:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Time that an image of the slider will be shown, happen until the transition to the next.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Navigation arrows:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('If this option is enabled, the use of arrows to advance or move back to the images on the slider will appear.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Show navigation arrows only when the mouse is on the slide:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('If this option is enabled, the navigation arrows will only appear when the mouse cursor is on the slider.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Show the navigation bullets:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('If this option is enabled, the user can move forward or backward through the images also utilize the bullets below the slider', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Use left and right on the keyboard:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('If this option is enabled, the user can move forward or backward through the images of the slider left navigation arrows (&larr;) and right (&rarr;) on the keyboard.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Stop the animation while the mouse is on the slide:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('If this option is enabled, animation (caption and transition) will not happen while the mouse is on the slider.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Force transition manual:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('If this option is enabled, the transition will not happen automatically.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Background color of the caption:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Changes the background color of the caption of every image of the slider. To change the color click the button next to the text field, and select the desired color in the color picker.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Text color of the caption:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Changes the color of the caption text of all the image of the slider. To change the color click the button next to the text field, and select the desired color in the color picker.', 'nivoslider4wp') .'</dd>'.
				'<dt><strong>'. __('Opacity of the background caption:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Changes the background transparency of the caption of every image of the slider. Enter the desired value within a range from 0.0 to 10.0, eg 0.3.', 'nivoslider4wp') .'</dd>'.
				'</dl>'.
				'<h3>'. __('Advanced Options:', 'nivoslider4wp') .'</h3>'.
				'<dl><dt><strong>'. __('Image quality:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Set value between 0 at 100 for define the quality of cut the images. Quality ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file). The default is 80.', 'nivoslider4wp') .'</dd>'.
					'<dt><strong>'. __('Insert JavaScript:', 'nivoslider4wp') .'</strong></dt>'.
					'<dd>'. __('Select the options head or footer for define where will loaded the scripts Jquery Plugin Nivo Slider. It is recommended to load the JavaScript in the <strong>footer</strong> because it allows a faster loading page, but the slider is loaded last.', 'nivoslider4wp') .'</dd>'.
				'</dl>'
			);
		}

	}

	function nivoslider4wp_panel() {
		include 'nivoslider4wp-panel.php';
	}
	
	function nivoslider4wp_option() {
		include 'nivoslider4wp-option.php';
	}
	
	require_once($ns4wp_plugindir . 'nivoslider4wp-show.php');
?>