<?php
	/*mostra o slide no site*/
	function nivoslider4wp_show() {
		if ( function_exists('plugins_url') )
			$url = plugins_url(plugin_basename(dirname(__FILE__)));
		else
			$url = get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));
		global $wpdb;
		$ns4wp_plugindir = ABSPATH.'wp-content/plugins/nivo-slider-for-wordpress/';
		$ns4wp_pluginurl = $url;
		$ns4wp_filesdir = ABSPATH.'/wp-content/uploads/nivoslider4wp_files/';
		$ns4wp_filesurl = get_option('siteurl').'/wp-content/uploads/nivoslider4wp_files/';

	?>
	<div id="slider">
				<?php $items = $wpdb->get_results("SELECT nivoslider4wp_id,nivoslider4wp_type,nivoslider4wp_text_headline,nivoslider4wp_image_link,nivoslider4wp_image_status FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_image_status = 1 OR nivoslider4wp_image_status IS NULL ORDER BY nivoslider4wp_order,nivoslider4wp_id"); ?>
				<?php foreach($items as $item) : ?>
						<?php
						if(!$item->nivoslider4wp_image_link){ ?>
						<img src="<?php echo $ns4wp_filesurl.$item->nivoslider4wp_id.'_s.'.$item->nivoslider4wp_type; ?>" alt="<?php echo stripslashes($item->nivoslider4wp_text_headline); ?>" title="<?php echo stripslashes($item->nivoslider4wp_text_headline); ?>"/>
						<?php } else { ?>
						<a href="<?php echo $item->nivoslider4wp_image_link;?>"><img src="<?php echo $ns4wp_filesurl.$item->nivoslider4wp_id.'_s.'.$item->nivoslider4wp_type; ?>" alt="<?php echo stripslashes($item->nivoslider4wp_text_headline); ?>" title="<?php echo stripslashes($item->nivoslider4wp_text_headline); ?>"/></a>
						<?php } ?>
				<?php endforeach; ?>
		</div>
	<?php
	}

	/*conteudo que ora para dentro do <head>*/
	function js_NivoSlider(){
	?>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo get_option('siteurl') . '/wp-content/plugins/nivo-slider-for-wordpress/js/jquery.nivo.slider.pack.js';?>"></script>
		<script type="text/javascript">
		var $nv4wp = jQuery.noConflict();
		$nv4wp(window).load(function() {
			$nv4wp('#slider').nivoSlider({
				effect:'<?php echo get_option('nivoslider4wp_effect'); ?>',
				slices:15, // For slice animations
				boxCols: <?php echo get_option('nivoslider4wp_colsBox'); ?>, // For box animations
				boxRows: <?php echo get_option('nivoslider4wp_rowsBox'); ?>, // For box animations
				animSpeed:<?php echo get_option('nivoslider4wp_animSpeed'); ?>, // Slide transition speed
				pauseTime:<?php echo get_option('nivoslider4wp_pauseTime'); ?>, // How long each slide will show
				startSlide:0, // Set starting Slide (0 index)
				directionNav:<?php echo get_option('nivoslider4wp_directionNav'); ?>, //Next & Prev
				directionNavHide:<?php echo get_option('nivoslider4wp_directionNavHide'); ?>, //Only show on hover
				controlNav:true, // 1,2,3... navigation
				controlNavThumbs:false, // Use thumbnails for Control Nav
				controlNavThumbsFromRel:false, // Use image rel for thumbs
				controlNavThumbsSearch: '.jpg', // Replace this with...
				controlNavThumbsReplace: '_thumb.jpg', // ...this in thumb Image src
				keyboardNav:<?php echo get_option('nivoslider4wp_keyboardNav'); ?>, //Use left & right arrows
				pauseOnHover:<?php echo get_option('nivoslider4wp_pauseOnHover'); ?>, //Stop animation while hovering
				manualAdvance:<?php echo get_option('nivoslider4wp_manualAdvance'); ?>, //Force manual transitions
				captionOpacity:<?php echo get_option('nivoslider4wp_captionOpacity'); ?>, //Universal caption opacity
				prevText: 'Prev', // Prev directionNav text
				nextText: 'Next', // Next directionNav text
				beforeChange: function(){}, // Triggers before a slide transition
				afterChange: function(){}, // Triggers after a slide transition
				slideshowEnd: function(){}, // Triggers after all slides have been shown
				lastSlide: function(){}, // Triggers when last slide is shown
				afterLoad: function(){} // Triggers when slider has loaded
			});
		});
		</script>
		<?php
		}
		function css_NivoSlider(){
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo get_option('siteurl') . '/wp-content/plugins/nivo-slider-for-wordpress/css/nivoslider4wp.css'?>" />
		<style>
		#slider{
			width:<?php echo get_option('nivoslider4wp_width'); ?>px;
			height:<?php echo get_option('nivoslider4wp_height'); ?>px;
			background:transparent url(<?php echo plugins_url(plugin_basename(dirname(__FILE__))); ?>/css/images/loading.gif) no-repeat 50% 50%;
		}
		.nivo-caption {
			background:#<?php echo get_option('nivoslider4wp_backgroundCaption'); ?>;
			color:#<?php echo get_option('nivoslider4wp_colorCaption'); ?>;
		}
		</style>
	<?php
	}
	add_action('wp_head', 'css_NivoSlider');
	if(get_option('nivoslider4wp_js') == 'head'){
		add_action('wp_head', 'js_NivoSlider');
	}
		else
	{
		add_action('wp_footer', 'js_NivoSlider');
	}
?>