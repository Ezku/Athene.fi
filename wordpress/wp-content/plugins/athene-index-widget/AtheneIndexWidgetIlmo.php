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
          $entries[] = $this->findEntryDetails($row, $ilmomasiina_url, $timezone);
      }
      include __DIR__ . '/templates/widget-ilmo.php';
  	}
  	
  	private function findEntryDetails($row, $ilmomasiina_url, $timezone)
  	{
        $details = array();
        
        $name = $row->find('.signup-name a');
        if (count($name) > 0) {
          $details['url'] = $ilmomasiina_url . $this->utfToIso($name[0]->href);
          $details['name'] = $this->utfToIso($name[0]->innertext);
        }
        
        $state = $row->find('.open-close-state span');
        if (count($state) > 0) {
          $details['state_string'] = $this->utfToIso($state[0]->outertext);
          $details['is_open'] = strpos($state[0], 'signup-open') !== false;
          $details['is_closed'] = strpos($state[0], 'signup-closed') !== false;
          $details['is_not_yet_open'] = strpos($state[0], 'signup-not-yet-open') !== false; // for sake of completeness
          
          switch (true) {
              case $details['is_open']: $details['state'] = 'open'; break;
              case $details['is_closed']: $details['state'] = 'closed'; break;
              case $details['is_not_yet_open']: $details['state'] = 'not_yet_open'; break;
          }
        }
        
        $opens = $row->find('.signup-opens');
        if (count($opens) > 0) {
          $details['opens'] = $this->asDate($this->utfToIso($opens[0]->innertext), $timezone);
          //$details['opens'] = "03.09.11 00:15";
        }
        
        $closes = $row->find('.signup-closes');
        if (count($closes) > 0) {
          $details['closes'] = $this->asDate($this->utfToIso($closes[0]->innertext), $timezone);
        }
        
        $details['relevant_date'] = $details['is_not_yet_open'] ? $details['opens'] : $details['closes'];
        
        return $details;
  	}
  	
  	private function statusToString($entry)
  	{
  	    switch (true) {
            case $entry['is_open']: return 'sulkeutuu';
            case $entry['is_closed']: return 'sulkeutunut';
            case $entry['is_not_yet_open']: return 'aukeaa';
  	    }
  	}
  	
  	private function asDate($string, $timezone)
  	{
        $date = trim(str_replace(" klo ", " ", $string));
        return DateTime::createFromFormat("d.m.y G:i", $date, new DateTimeZone($timezone));
  	}
  	
  	private function utfToIso($string)
  	{
  	    return mb_convert_encoding($string,'UTF-8', 'ISO-8859-15');
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
  	
  	public function limit($items, $count)
  	{
  	    return new LimitIterator(new ArrayIterator($items), 0, $count);
  	}
}
  add_action('widgets_init', create_function('', 'return register_widget("AtheneIndexWidgetIlmo");'));

?>