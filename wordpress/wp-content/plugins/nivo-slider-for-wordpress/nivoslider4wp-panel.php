<?php global $wpdb; ?>
<?php
	if ( function_exists('plugins_url') )
		$url = plugins_url(plugin_basename(dirname(__FILE__)));
	else
		$url = get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));
		$ns4wp_plugindir = ABSPATH.'wp-content/plugins/nivo-slider-for-wordpress/';
		$ns4wp_pluginurl = $url;
		$ns4wp_filesdir = ABSPATH.'/wp-content/uploads/nivoslider4wp_files/';
		$ns4wp_filesurl = get_option('siteurl').'/wp-content/uploads/nivoslider4wp_files/';
?>
<?php 
		   $ns4wp_x = "empty"; 
		   $ns4wp_y= "empty"; 
		   $ns4wp_x2= "empty"; 
		   $ns4wp_y2= "empty";
?>
<link rel="stylesheet" type="text/css" href="<?php echo $ns4wp_pluginurl; ?>/css/nivoslider4wp-painel.css" />
<script type="text/javascript" src="<?php echo $ns4wp_pluginurl; ?>/js/functions.js"></script>
<div class="wrap">
<h2 id="all-schemes"><?php _e('Nivo Slider For WordPress - Add Image','nivoslider4wp'); ?></h2>			
  <?php

		if (isset($_GET['remove'])) {
			$ns4wp_file_type = $wpdb->get_var("SELECT nivoslider4wp_type FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_id = '$_GET[remove]'");
			$wpdb->query("DELETE FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_id = $_GET[remove]");
			if (is_file($ns4wp_filesdir.$_GET['remove'].'_o.'.$ns4wp_file_type)) { unlink($ns4wp_filesdir.$_GET['remove'].'_o.'.$ns4wp_file_type); }
			if (is_file($ns4wp_filesdir.$_GET['remove'].'_s.'.$ns4wp_file_type)) { unlink($ns4wp_filesdir.$_GET['remove'].'_s.'.$ns4wp_file_type); }
			unset($_GET);
		}
		
		if (isset($_GET['disable'])) {
			//$ns4wp_file_type = $wpdb->get_var("SELECT nivoslider4wp_type FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_id = '$_GET[disable]'");
			$wpdb->query("UPDATE {$wpdb->prefix}nivoslider4wp SET nivoslider4wp_image_status=0 WHERE nivoslider4wp_id = $_GET[disable]");
			//if (is_file($ns4wp_filesdir.$_GET['disable'].'_o.'.$ns4wp_file_type)) { unlink($ns4wp_filesdir.$_GET['disable'].'_o.'.$ns4wp_file_type); }
			//if (is_file($ns4wp_filesdir.$_GET['disable'].'_s.'.$ns4wp_file_type)) { unlink($ns4wp_filesdir.$_GET['disable'].'_s.'.$ns4wp_file_type); }
			unset($_GET);
		}
		
		if (isset($_GET['enable'])) {
			//$ns4wp_file_type = $wpdb->get_var("SELECT nivoslider4wp_type FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_id = '$_GET[disable]'");
			$wpdb->query("UPDATE {$wpdb->prefix}nivoslider4wp SET nivoslider4wp_image_status=1 WHERE nivoslider4wp_id = $_GET[enable]");
			//if (is_file($ns4wp_filesdir.$_GET['disable'].'_o.'.$ns4wp_file_type)) { unlink($ns4wp_filesdir.$_GET['disable'].'_o.'.$ns4wp_file_type); }
			//if (is_file($ns4wp_filesdir.$_GET['disable'].'_s.'.$ns4wp_file_type)) { unlink($ns4wp_filesdir.$_GET['disable'].'_s.'.$ns4wp_file_type); }
			unset($_GET);
		}

		if (isset($_POST['order_id'])) {
			$values = array( 'nivoslider4wp_order' => $_POST['order_value'] );
			$conditions = array( 'nivoslider4wp_id' => $_POST['order_id']);
			$values_types = array('%d');
			$conditions_types = array('%d');
			$wpdb->update($wpdb->prefix.'nivoslider4wp', $values, $conditions, $values_types, $conditions_types);
			unset($_GET);
		}

		if (isset($_POST['x'])) {
			if($_POST['nivoslider4wp_file_type'] == 'jpeg')
			{
				$ns4wp_image_src = imagecreatefromjpeg($ns4wp_filesdir.$_POST['nivoslider4wp_file_id'].'_o.'.$_POST['nivoslider4wp_file_type']);
				$ns4wp_image_crop = imagecreatetruecolor(get_option('nivoslider4wp_width'), get_option('nivoslider4wp_height'));
				imagecopyresampled($ns4wp_image_crop, $ns4wp_image_src, 0, 0, $_POST['x'],$_POST['y'], get_option('nivoslider4wp_width'), get_option('nivoslider4wp_height'), $_POST['w'], $_POST['h']);
				imagejpeg($ns4wp_image_crop,$ns4wp_filesdir.$_POST['nivoslider4wp_file_id'].'_s.'.$_POST['nivoslider4wp_file_type'], get_option('nivoslider4wp_imageQuality'));
			}
			else if($_POST['nivoslider4wp_file_type'] == 'png')
			{
				$ns4wp_image_src = imagecreatefrompng($ns4wp_filesdir.$_POST['nivoslider4wp_file_id'].'_o.'.$_POST['nivoslider4wp_file_type']);
				$ns4wp_image_crop = imagecreatetruecolor(get_option('nivoslider4wp_width'), get_option('nivoslider4wp_height'));
				imagecopyresampled($ns4wp_image_crop, $ns4wp_image_src, 0, 0, $_POST['x'],$_POST['y'], get_option('nivoslider4wp_width'), get_option('nivoslider4wp_height'), $_POST['w'], $_POST['h']);
				if(get_option('nivoslider4wp_imageQuality') > 90)
				{
					// qualidade do PNG Varia entre 0 - 9, diferente do Jpeg que é entre 0 - 100
					imagepng($ns4wp_image_crop,$ns4wp_filesdir.$_POST['nivoslider4wp_file_id'].'_s.'.$_POST['nivoslider4wp_file_type'], 9);
				}
				else
				{
				imagepng($ns4wp_image_crop,$ns4wp_filesdir.$_POST['nivoslider4wp_file_id'].'_s.'.$_POST['nivoslider4wp_file_type'], get_option('nivoslider4wp_imageQuality') / 10);
				}
			}
			else if($_POST['nivoslider4wp_file_type'] == 'gif')
			{
				$ns4wp_image_src = imagecreatefromgif($ns4wp_filesdir.$_POST['nivoslider4wp_file_id'].'_o.'.$_POST['nivoslider4wp_file_type']);
				$ns4wp_image_crop = imagecreatetruecolor(get_option('nivoslider4wp_width'), get_option('nivoslider4wp_height'));
				imagecopyresampled($ns4wp_image_crop, $ns4wp_image_src, 0, 0, $_POST['x'],$_POST['y'], get_option('nivoslider4wp_width'), get_option('nivoslider4wp_height'), $_POST['w'], $_POST['h']);
				imagegif($ns4wp_image_crop,$ns4wp_filesdir.$_POST['nivoslider4wp_file_id'].'_s.'.$_POST['nivoslider4wp_file_type']);
			}

			$values = array(
				'nivoslider4wp_x' => $_POST['x'],
				'nivoslider4wp_y' => $_POST['y'],
				'nivoslider4wp_x2' => $_POST['x2'],
				'nivoslider4wp_y2' => $_POST['y2'],
				'nivoslider4wp_w' => $_POST['w'],
				'nivoslider4wp_h' => $_POST['h'],
				'nivoslider4wp_text_headline' => $_POST['nivoslider4wp_file_text_headline'],
				'nivoslider4wp_image_link' => $_POST['nivoslider4wp_image_link'],
				'nivoslider4wp_image_status' => 1
			);
			
			$conditions = array( 'nivoslider4wp_id' => $_POST['nivoslider4wp_file_id']);
			
			$values_types = array('%d','%d','%d','%d','%d','%d','%s','%s','%s','%s');
			$conditions_types = array('%d');
			$wpdb->update($wpdb->prefix.'nivoslider4wp', $values, $conditions, $values_types, $conditions_types);
			unset($_GET);
		}

		if ((isset($_FILES['file'])) || (isset($_GET['edit']))) {
			if (isset($_FILES['file'])) {
				$ns4wp_file_type = explode('/',$_FILES['file']['type']);
				if ($ns4wp_file_type[1] != 'jpeg' && $ns4wp_file_type[1] != 'png'  && $ns4wp_file_type[1] != 'gif') die(_e('Sorry. Only JPG, GIF and PNG formats are supported','nivoslider4wp'));
				$values = array('nivoslider4wp_order' => 0, 'nivoslider4wp_type' => $ns4wp_file_type[1]);
				$types = array('%d','%s');
				$wpdb->insert($wpdb->prefix.'nivoslider4wp',$values, $types);
				$ns4wp_file_id = $wpdb->get_var("SELECT nivoslider4wp_id FROM {$wpdb->prefix}nivoslider4wp ORDER BY nivoslider4wp_id DESC LIMIT 1");
				$ns4wp_original_image_dir = $ns4wp_filesdir.$ns4wp_file_id.'_o.'.$ns4wp_file_type[1];
				$ns4wp_original_image_url = $ns4wp_filesurl.$ns4wp_file_id.'_o.'.$ns4wp_file_type[1];
				move_uploaded_file($_FILES['file']['tmp_name'], $ns4wp_original_image_dir);
				list($ns4wp_file_width,$ns4wp_file_height) = getimagesize($ns4wp_original_image_dir);

				if ($ns4wp_file_width > 1000) {
					$ns4wp_image_res_w = 1000;
					$ns4wp_image_res_h = $ns4wp_file_height*1000/$ns4wp_file_width;
					$ns4wp_image_res = imagecreatetruecolor($ns4wp_image_res_w, $ns4wp_image_res_h);
					if($ns4wp_file_type[1] == 'jpeg')
					{
						$ns4wp_image_src = imagecreatefromjpeg($ns4wp_original_image_dir);
						imagecopyresized($ns4wp_image_res, $ns4wp_image_src, 0, 0, 0, 0, $ns4wp_image_res_w, $ns4wp_image_res_h, $ns4wp_file_width, $ns4wp_file_height);
						imagedestroy($ns4wp_image_src);
						imagejpeg($ns4wp_image_res, $ns4wp_original_image_dir);
					}
					else if($ns4wp_file_type[1] == 'png')
					{
						$ns4wp_image_src = imagecreatefrompng($ns4wp_original_image_dir);
						imagecopyresized($ns4wp_image_res, $ns4wp_image_src, 0, 0, 0, 0, $ns4wp_image_res_w, $ns4wp_image_res_h, $ns4wp_file_width, $ns4wp_file_height);
						imagedestroy($ns4wp_image_src);
						imagepng($ns4wp_image_res, $ns4wp_original_image_dir);
					}
					else if($ns4wp_file_type[1] == 'gif')
					{
						$ns4wp_image_src = imagecreatefromgif($ns4wp_original_image_dir);
						imagecopyresized($ns4wp_image_res, $ns4wp_image_src, 0, 0, 0, 0, $ns4wp_image_res_w, $ns4wp_image_res_h, $ns4wp_file_width, $ns4wp_file_height);
						imagedestroy($ns4wp_image_src);
						imagegif($ns4wp_image_res, $ns4wp_original_image_dir);
					}
				}
			} elseif (isset($_GET['edit'])) {
				$item = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_id = '$_GET[edit]'");
				$ns4wp_file_type[1] = $item->nivoslider4wp_type;
				$ns4wp_x = $item->nivoslider4wp_x;
				$ns4wp_y = $item->nivoslider4wp_y;
				$ns4wp_x2 = $item->nivoslider4wp_x2;
				$ns4wp_y2 = $item->nivoslider4wp_y2;
				$ns4wp_w = $item->nivoslider4wp_w;
				$ns4wp_h = $item->nivoslider4wp_h;
				
				$ns4wp_image_link = $item->nivoslider4wp_image_link;
				$ns4wp_file_text_headline = $item->nivoslider4wp_text_headline;
				$ns4wp_file_id = $_GET['edit'];
				$ns4wp_original_image_dir = $ns4wp_filesdir.$ns4wp_file_id.'_o.'.$ns4wp_file_type[1];
				$ns4wp_original_image_url = $ns4wp_filesurl.$ns4wp_file_id.'_o.'.$ns4wp_file_type[1];
			}

			list($ns4wp_file_width,$ns4wp_file_height) = getimagesize($ns4wp_original_image_dir);
	?>
										                                   
  <script src="<?php echo $ns4wp_pluginurl; ?>/js/jquery.min.js"></script>
  <script src="<?php echo $ns4wp_pluginurl; ?>/js/jquery.Jcrop.js"></script>
  <link rel="stylesheet" href="<?php echo $ns4wp_pluginurl; ?>/css/jquery.Jcrop.css" type="text/css" />
  <script language="Javascript">
				var $j = jQuery.noConflict();

				$j(window).load(function() {
					$j('#image').Jcrop({
						onChange: showPreview,
						onSelect: showPreview,
						onChange: showCoords,
						addClass: 'custom',
						setSelect:   [  <?php if ($ns4wp_x == "empty") {
												echo 100;
											} else {
												echo $ns4wp_x;
											}
                                          ?>,
										  <?php if ($ns4wp_y == "empty") {
												echo 100;
											} else {
												echo $ns4wp_y;
											}
                                          ?>,
										   <?php if ($ns4wp_x2 == "empty") {
												echo 50;
											} else {
												echo $ns4wp_x2;
											}
                                          ?>,    
										<?php if ($ns4wp_y2 == "empty") {
												echo 50;
											} else {
												echo $ns4wp_y2;
											}
                                          ?> ],
						aspectRatio: <?php echo get_option('nivoslider4wp_width') / get_option('nivoslider4wp_height'); ?>
					});
				});

				function showPreview(coords) {
					if (parseInt(coords.w) > 0) {
						var rx = <?php echo get_option('nivoslider4wp_width'); ?> / coords.w;
						var ry = <?php echo get_option('nivoslider4wp_height'); ?> / coords.h;
						$j('#preview').css({
							width: Math.round(rx * <?php echo $ns4wp_file_width; ?>) + 'px',
							height: Math.round(ry * <?php echo $ns4wp_file_height; ?>) + 'px',
							marginLeft: '-' + Math.round(rx * coords.x) + 'px',
							marginTop: '-' + Math.round(ry * coords.y) + 'px'
						});
					}
				}

				function showCoords(c) {
					$j('#x').val(c.x);
					$j('#y').val(c.y);
					$j('#x2').val(c.x2);
					$j('#y2').val(c.y2);
					$j('#w').val(c.w);
					$j('#h').val(c.h);
				};
				
				
					var $b = jQuery.noConflict();
						$b(document).ready(function() {
						// Initialise the table
						$b("#table-1").tableDnD();
					});
			
            </script>
  <h3>
    <?php _e('Original image', 'nivoslider4wp'); ?>
  </h3>
  <?php _e('Click and drag to select the crop area','nivoslider4wp'); ?>
  <br/>
  <img src="<?php echo $ns4wp_original_image_url.'?'.rand(1,1000); ?>" id="image" />
  <h3>
    <?php _e('Slide preview','nivoslider4wp'); ?>
  </h3>
  <div style="width:<?php echo get_option('nivoslider4wp_width'); ?>px;height:<?php echo get_option('nivoslider4wp_height'); ?>px;overflow:hidden;"> <img src="<?php echo $ns4wp_original_image_url.'?'.rand(1,1000); ?>" id="preview" /> </div>
  <form name="nivoslider4wp_coords" method="post" id="edit_form" action="">
    <label for="nivoslider4wp_file_text_headline"><?php _e('Image caption(optional)','nivoslider4wp'); ?></label>
		<textarea name="nivoslider4wp_file_text_headline" id="nivoslider4wp_file_text_headline" class="edit"><?php echo stripslashes(@$ns4wp_file_text_headline); ?></textarea>
	<label for="nivoslider4wp_image_link"><?php _e('Image link, please use <strong>http://</strong>(optional)', 'nivoslider4wp'); ?></label>
		<input type="text" name="nivoslider4wp_image_link" id="nivoslider4wp_image_link" value="<?php echo stripslashes(@$ns4wp_image_link); ?>" class="edit" />
	
	<input type="hidden" id="x" name="x" />
    <input type="hidden" id="y" name="y" />
    <input type="hidden" id="x2" name="x2" />
    <input type="hidden" id="y2" name="y2" />
    <input type="hidden" id="w" name="w" />
    <input type="hidden" id="h" name="h" />
    <input type="hidden" id="nivoslider4wp_file_id" name="nivoslider4wp_file_id" value="<?php echo $ns4wp_file_id; ?>" />
    <input type="hidden" id="nivoslider4wp_file_type" name="nivoslider4wp_file_type" value="<?php echo $ns4wp_file_type[1]; ?>" />
    <br />
    <input type="submit" id="crop_button" value="<?php _e('Save','nivoslider4wp'); ?>" class="button-primary action"/>
  </form>
  <?php
		} else {
	?>
	<div class="alert"><?php _e('The dimensions of the slider (and the clipping of the image) are <strong>', 'nivoslider4wp'); ?><?php echo get_option('nivoslider4wp_width'); ?>px <?php _e('</strong>width, per <strong>', 'nivoslider4wp'); ?><?php echo get_option('nivoslider4wp_height'); ?>px <?php _e('</strong>height, to modify them go to the ', 'nivoslider4wp'); ?><a href="<?php echo get_option('siteurl');?>/wp-admin/admin.php?page=nivoslider4wp-options"><?php _e('options page', 'nivoslider4wp'); ?></a>.</div>
  <div class="tablenav">
	<div class="alignleft actions">
			<a href="#add_new" class="button-secondary action" onClick="Show('nivoslider4wp_addnew'); return false;"><?php _e('Add New image','nivoslider4wp'); ?></a>
		</div>
  </div>
  <div id="nivoslider4wp_addnew" class="nivoslider4wp_box">
    <?php if (substr(decoct(fileperms($ns4wp_filesdir)),2) != '777') : ?>
    <p class="warning"><b>
      <?php _e('Warning','nivoslider4wp'); ?>
      :</b> <?php printf(__('The permissions to the directory <b>%s</b> are invalid. Set them to 777 to be able to upload files.','nivoslider4wp'),$ns4wp_filesdir); ?>
      <?php else : ?>
    <form name="nivoslider4wp_addnew" method="post" action="" enctype="multipart/form-data">
      <table>
        <tr>
          <td><?php _e('File','nivoslider4wp'); ?>
            :</td>
          <td><input type="file" name="file" />
            <input type="submit" value="<?php _e('Send picture and configure','nivoslider4wp'); ?>" /></td>
        </tr>
      </table>
    </form>
    <?php endif; ?>
  </div>
  <div id="nivoslider4wp_images">
    <table class="widefat" cellspacing="0">
      <thead>
        <tr>
          <th width="8%"  style=""><?php _e('Order','nivoslider4wp'); ?></th>
          <th width="30%" style=""><?php _e('Image','nivoslider4wp'); ?></th>
          <th width="50%" style=""><?php _e('Caption and Link','nivoslider4wp'); ?></th>
          <th width="8%" style=""><?php _e('Action','nivoslider4wp'); ?></td>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th scope="col"><?php _e('Order','nivoslider4wp'); ?></th>
          <th scope="col"><?php _e('Image','nivoslider4wp'); ?></th>
          <th scope="col"><?php _e('Caption and Link','nivoslider4wp'); ?></th>
          <th scope="col"></td>
        </tr>
      </tfoot>
      <script type="text/javascript" src="<?php echo $ns4wp_pluginurl; ?>/js/jquery.tablednd_0_5.js"></script>
		<script type="text/javascript">
            var $b = jQuery.noConflict();
            $b(document).ready(function() {
            $b("#table-1").tableDnD();
				$b('#table-1').tableDnD({
				onDrop: function(table, row) {
					update();
					}
				});
			});
			function update(){
				$b(".orderNumber").each(function(index){
					$b(this).html(index);
					var orderId=$b(this).parent().find("input[name='order_id']").attr("value");
					$b.ajax({
					   type: "POST",
					   url: '<?php bloginfo('url'); ?>/wp-admin/admin.php?page=nivo-slider-for-wordpress/nivoslider4wp.php',
					   data: "order_value="+index+"&order_id="+orderId,
					   success: function(msg){
					   }
					 });
				})
			}
        </script>
        <style>
            .tDnD_whileDrag{background:#ececec;}
        </style>		 
      <tbody id="table-1">
      
		<?php $items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_image_status=1 OR nivoslider4wp_image_status IS NULL ORDER BY nivoslider4wp_order,nivoslider4wp_id ASC"); ?>
        <?php if ($items) : ?>
        <?php foreach ($items as $item) : ?>
        <tr>
		<td class="manage-column column-numero orderNumber"><?php echo $item->nivoslider4wp_order; ?></td>
          <td><img width="80%" src="<?php echo $ns4wp_filesurl.$item->nivoslider4wp_id.'_s.'.$item->nivoslider4wp_type; ?>" /></td>
          <td><strong><?php echo stripslashes($item->nivoslider4wp_text_headline); ?></strong><br />
			<br />
			<?php if($item->nivoslider4wp_image_link != ''){ ?>
			<?php _e('Image Link to :','nivoslider4wp'); ?><a href="<?php echo stripslashes($item->nivoslider4wp_image_link); ?>"><?php echo stripslashes($item->nivoslider4wp_image_link); ?></a>
			<?php } ?>
			</td>
          <td><small> 
			<a href="admin.php?page=nivo-slider-for-wordpress/nivoslider4wp.php&edit=<?php echo $item->nivoslider4wp_id; ?>"><img src="<?php echo $ns4wp_pluginurl."/img/edit.png" ?>" alt="<?php _e('Edit','nivoslider4wp'); ?>" title="<?php _e('Edit','nivoslider4wp'); ?>" /></a>
			<a href="admin.php?page=nivo-slider-for-wordpress/nivoslider4wp.php&remove=<?php echo $item->nivoslider4wp_id; ?>"><img src="<?php echo $ns4wp_pluginurl."/img/remove.png" ?>" alt="<?php _e('Remove','nivoslider4wp'); ?>" title="<?php _e('Remove','nivoslider4wp'); ?>" /></a>
			<a href="admin.php?page=nivo-slider-for-wordpress/nivoslider4wp.php&disable=<?php echo $item->nivoslider4wp_id; ?>"><img src="<?php echo $ns4wp_pluginurl."/img/disable.png" ?>" alt="<?php _e('Disable','nivoslider4wp'); ?>" title="<?php _e('Disable','nivoslider4wp'); ?>" /></a>
            <form id="order_<?php echo $item->nivoslider4wp_id; ?>" name="order_<?php echo $item->nivoslider4wp_id; ?>" class="order" method="post">
              <input type="hidden" name="order_id" value="<?php echo $item->nivoslider4wp_id; ?>" />
            </form>
            </small></td>
        </tr>
        <?php endforeach; ?>
        
        <?php else : ?>
        <tr>
          <td colspan="3"><?php _e('No images uploaded yet.','nivoslider4wp'); ?></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
	<h3>Desabilitados</h3>
	<?php $items_desable = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nivoslider4wp WHERE nivoslider4wp_image_status=0 ORDER BY nivoslider4wp_order,nivoslider4wp_id ASC"); ?>
	<table class="widefat disabled" cellspacing="0">
      <thead>
        <tr>
          <th width="8%"  style=""><?php _e('Order','nivoslider4wp'); ?></th>
          <th width="30%" style=""><?php _e('Image','nivoslider4wp'); ?></th>
          <th width="50%" style=""><?php _e('Caption and Link','nivoslider4wp'); ?></th>
          <th width="8%" style=""><?php _e('Action','nivoslider4wp'); ?></td>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th scope="col"><?php _e('Order','nivoslider4wp'); ?></th>
          <th scope="col"><?php _e('Image','nivoslider4wp'); ?></th>
          <th scope="col"><?php _e('Caption and Link','nivoslider4wp'); ?></th>
          <th scope="col"></td>
        </tr>
      </tfoot>
        <style>
            .tDnD_whileDrag{background:#ececec;}
        </style>		 
      <tbody id="table-1">
        <?php if ($items_desable) : ?>
        <?php foreach ($items_desable as $item_desable) : ?>
        <tr>
		<td>x</td>
          <td><img width="80%" src="<?php echo $ns4wp_filesurl.$item_desable->nivoslider4wp_id.'_s.'.$item_desable->nivoslider4wp_type; ?>" /></td>
          <td><strong><?php echo stripslashes($item_desable->nivoslider4wp_text_headline); ?></strong><br />
			<br />
			<?php if($item_desable->nivoslider4wp_image_link != ''){ ?>
			<?php _e('Image Link to :','nivoslider4wp'); ?><a href="<?php echo stripslashes($item_desable->nivoslider4wp_image_link); ?>"><?php echo stripslashes($item_desable->nivoslider4wp_image_link); ?></a>
			<?php } ?>
			</td>
          <td><small> 
			<a href="admin.php?page=nivo-slider-for-wordpress/nivoslider4wp.php&edit=<?php echo $item_desable->nivoslider4wp_id; ?>"><img src="<?php echo $ns4wp_pluginurl."/img/edit.png" ?>" alt="<?php _e('Edit','nivoslider4wp'); ?>" title="<?php _e('Edit','nivoslider4wp'); ?>" /></a>
			<a href="admin.php?page=nivo-slider-for-wordpress/nivoslider4wp.php&remove=<?php echo $item_desable->nivoslider4wp_id; ?>"><img src="<?php echo $ns4wp_pluginurl."/img/remove.png" ?>" alt="<?php _e('Remove','nivoslider4wp'); ?>" title="<?php _e('Remove','nivoslider4wp'); ?>" /></a>
			<a href="admin.php?page=nivo-slider-for-wordpress/nivoslider4wp.php&enable=<?php echo $item_desable->nivoslider4wp_id; ?>"><img src="<?php echo $ns4wp_pluginurl."/img/enable.png" ?>" alt="<?php _e('Enable','nivoslider4wp'); ?>" title="<?php _e('Enable','nivoslider4wp'); ?>" /></a>
            <form id="order_<?php echo $item_desable->nivoslider4wp_id; ?>" name="order_<?php echo $item_desable->nivoslider4wp_id; ?>" class="order" method="post">
              <input type="hidden" name="order_id" value="<?php echo $item_desable->nivoslider4wp_id; ?>" />
            </form>
            </small></td>
        </tr>
        <?php endforeach; ?>
        
        <?php else : ?>
        <tr>
          <td colspan="3"><?php _e('No images uploaded yet.','nivoslider4wp'); ?></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php
		}
	?>
</div>
