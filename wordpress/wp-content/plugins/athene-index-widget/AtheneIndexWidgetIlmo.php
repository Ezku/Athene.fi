<?php
include('simple_html_dom.php');
define('DEFAULT_ILMOMASIINA_URL', 'http://www.athene.fi/ilmomasiina');
define('DEFAULT_ILMOMASIINA_TITLE', 'Ilmomasiina');

class AtheneIndexWidgetIlmo extends AtheneIndexWidget {
  	function __construct() {
  		// widget actual processes
  		parent::__construct(false, $name = 'Athene Index Widget for Ilmomasiina');
  	}

  	function form($instance) {
  	  //$link_target = $instance['link_target'];
  	  $title = $instance['title'];
  	  if (!isset($title) || empty($title)) {
  	    $title = DEFAULT_ILMOMASIINA_TITLE;
  	  }
  	  $source_url = $this->getURL($instance);
  	  $timezone = $instance['timezone'];
  	  if (empty($timezone)) {
  	    $timezone = "Europe/London";
  	  }
  	  $items = $instance['items'];
  	  if (empty($items)) {
  	    $items = 2;
  	  }
  	  ?>
  	  <p>
	      <label for="<?php echo $this->get_field_id('title'); ?>">Title</label><br/>
  	    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>">
  	  </p>
  	  <p>
	      <label for="<?php echo $this->get_field_id('source_url'); ?>">Source URL</label><br/>
  	    <input type="text" id="<?php echo $this->get_field_id('source_url'); ?>" name="<?php echo $this->get_field_name('source_url'); ?>" value="<?php echo $source_url; ?>">
  	  </p>
  	  <p>
	      <label for="<?php echo $this->get_field_id('items'); ?>">Items to show</label><br/>
  	    <input type="number" id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" value="<?php echo $items; ?>">
  	  </p>
  	  <p>
	      <label for="<?php echo $this->get_field_id('timezone'); ?>">Timezone for parsing the event dates</label><br/>
  	    <input type="text" id="<?php echo $this->get_field_id('timezone'); ?>" name="<?php echo $this->get_field_name('timezone'); ?>" value="<?php echo $timezone; ?>">
  	  </p>
  	  <?php
  	}

  	function update($new_instance, $old_instance) {
  		// processes widget options to be saved
  		return $new_instance;
  	}

  	function widget($args, $instance) {
      $ilmomasiina_url = $this->getURL($instance);
      $title = $instance['title'];
      $now = new DateTime();
      $timezone = $instance['timezone'];
      if (empty($timezone)) {
  	    $timezone = "Europe/London";
  	  }
      if (empty($title)) {
        $title = DEFAULT_ILMOMASIINA_TITLE;
      }
      $items = $instance['items'];
  	  if (empty($items)) {
  	    $items = 2;
  	  }
      $entries = array();
      $ilmo = file_get_html($ilmomasiina_url);
      foreach($ilmo->find('tr.answer-row') as $row) {
        $details = array();
        $name = $row->find('.signup-name a');
        if (count($name) > 0) {
          $details['url'] = $ilmomasiina_url . mb_convert_encoding($name[0]->href,'UTF-8', 'ISO-8859-15');
          $details['name'] = mb_convert_encoding($name[0]->innertext,'UTF-8', 'ISO-8859-15');
        }
        
        $state = $row->find('.open-close-state span');
        if (count($state) > 0) {
          $details['state'] = mb_convert_encoding($state[0]->outertext,'UTF-8', 'ISO-8859-15');
          //$details['state'] = "signup-not-yet-open";
        }
        
        $opens = $row->find('.signup-opens');
        if (count($opens) > 0) {
          $details['opens'] = mb_convert_encoding($opens[0]->innertext,'UTF-8', 'ISO-8859-15');
          //$details['opens'] = "03.09.11 00:15";
          $details['opens'] = trim(str_replace(" klo ", " ", $details['opens']));
          $details['opens'] = DateTime::createFromFormat("d.m.y G:i", $details['opens'], new DateTimeZone($timezone));
        }
        
        $closes = $row->find('.signup-closes');
        if (count($closes) > 0) {
          $details['closes'] = mb_convert_encoding($closes[0]->innertext,'UTF-8', 'ISO-8859-15');
          $details['closes'] = trim(str_replace(" klo ", " ", $details['closes']));
          $details['closes'] = DateTime::createFromFormat("d.m.y G:i", $details['closes'], new DateTimeZone($timezone));
        }
        
        $entries[] = $details;
      }
      ?>
      <h1><a href="<?php echo $ilmomasiina_url; ?>"><?php echo $title; ?></a></h1>
      <ul class="ilmo">
      <?php for($i=0; $i<min($items,count($entries)); $i++) { ?>
        <?php $entry = $entries[$i]; ?>
        <li class="ilmo-entry">
          <p class="title"><a href="<?php echo $entry['url'] ?>"><?php echo $entry['name'] ?></a></p>
          <p class="state">
            <?php echo $entry['state'] ?>
            <?php if (strstr($entry['state'], 'signup-open')) { ?>
              - sulkeutuu <?php echo $this->getTimeFormat($entry['closes'], $timezone); ?>
            <?php } ?>
            <?php if (strstr($entry['state'], 'signup-not-yet-open')) { ?>
              - aukeaa <?php echo $this->getTimeFormat($entry['opens'], $timezone); ?>
            <?php } ?>
          </p>
        </li>
      <?php } ?> 
      </ul>
      <?php
  	}
  	
  	private function getURL($instance) {
  	  $url = $instance['source_url'];
  	  if (empty($url)) {
  	    $url = DEFAULT_ILMOMASIINA_URL;
  	  }
  	  if ($url[strlen($url)-1] != '/') { // add trailing /
  	    $url .= '/';
  	  }
  	  return $url;
  	}
}
  add_action('widgets_init', create_function('', 'return register_widget("AtheneIndexWidgetIlmo");'));

?>