<?php
class AtheneIndexWidgetEvents extends AtheneIndexWidget {
  	function __construct() {
  		// widget actual processes
  		parent::__construct(false, $name = 'Athenize Index Widget for Events');
  	}

  	function form($instance) {
  		// outputs the options form on admin
  		$pageId = $instance['page'];
  		?>
  		<p>
  	  <label for="<?php echo $this->get_field_id('page'); ?>">Target page for links</label>
  	  <?php wp_dropdown_pages(array('name' => $this->get_field_name('page'), 'selected' => $pageId)) ?>
  	  </p>
  	  <?php
  	}

  	function update($new_instance, $old_instance) {
  		// processes widget options to be saved
  		return $new_instance;
  	}

  	function widget($args, $instance) {
      ?>
      <script type="text/javascript">
        jQuery(function() {
          var widget = jQuery('.widget_gce_widget');
          widget.prepend('<header class="widget-header"><h2><a href="'+
            '<?php echo get_permalink($instance['page']) ?>">'+
            widget.children('h2').text()+'</a></h2></header>');  
          widget.children('h2').remove();
          widget.children('.gce-widget-list').addClass('widget-content');
          
        });
      </script>
      <?php
  	}

}
  add_action('widgets_init', create_function('', 'return register_widget("AtheneIndexWidgetEvents");'));

?>