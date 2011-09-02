<?php
abstract class AtheneIndexWidget extends WP_Widget {
  protected function getTimeFormat($time, $timezone) {
	  $now = new DateTime("now", new DateTimeZone($timezone));
	  $timeDiff = $now->diff($time);
	  if ($timeDiff->d == 0 && $timeDiff->h == 0) {
	    return $timeDiff->i . "min ". ($now < $time ? "päästä" : "sitten");
	  }
	  if ($timeDiff->d == 0) {
	    return $timeDiff->h . "h ". ($now < $time ? "päästä" : "sitten");;
	  }
	  return $time->format(get_option('date_format') . ' ' . get_option('time_format'));
	}
	
	protected function excerpt($text, $chars,$more = "...") {
	  if (substr($text, 0, $chars+strlen($more)) == $text) {
	    return $text;
	  }
	  // strip chars after $chars+1 (+1: in case char no $chars is whitespace),
	  // then strip potential incomplete word in the end
	  return preg_replace('/\s[^\s]*$/i','',substr($text, 0, $chars+1)).$more;
	}
}

?>