<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap supporter">
	<?php screen_icon(); ?>
	
	<h2><?php _e ('Search Unleashed | Support', 'search-unleashed'); ?></h2>
	<?php $this->submenu (true); ?>
	
	<p style="clear: both">
		<?php _e( 'Search Unleashed is free to use - life is wonderful and lovely!  However, it has required a great deal of time and effort to develop and if it has been useful you can help support this development by <strong>making a small donation</strong>.', 'search-unleashed'); ?>
		<?php _e( 'This will act as an incentive for me to carry on developing, providing countless hours of support, and including new features and suggestions. You get some useful software and I get to carry on making it.  Everybody wins.', 'search-unleashed'); ?>
	</p>
	
	<p><?php _e( 'If you are using this plugin in a commercial setup, or feel that it\'s been particularly useful, then you may want to consider a <strong>commercial donation</strong>.  If you really really want to show your appreciation then there is the <strong>Super Smashing Great</strong> donation which, along with making my day, will earn you a badge of honour (125x125 image of your choosing + nofollow link) to be displayed on the Search Unleashed page for a period of two months.', 'search-unleashed' )?>
	
	<ul class="donations">
		<li>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="admin@urbangiraffe.com">
				<input type="hidden" name="item_name" value="Search Unleashed - Individual">
				<input type="hidden" name="amount" value="12.00">
				<input type="hidden" name="buyer_credit_promo_code" value="">
				<input type="hidden" name="buyer_credit_product_category" value="">
				<input type="hidden" name="buyer_credit_shipping_method" value="">
				<input type="hidden" name="buyer_credit_user_address_change" value="">
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="return" value="http://urbangiraffe.com/plugins/search-unleashed/">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-DonationsBF">
				<input type="image" style="border: none" src="<?php echo $this->url () ?>/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
			</form>
			
			<p><strong>$12</strong><br/><?php _e( 'Individual<br/>Donation', 'search-unleashed' ); ?></p>
		</li>
		<li>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="admin@urbangiraffe.com">
				<input type="hidden" name="item_name" value="Search Unleashed - Commercial">
				<input type="hidden" name="amount" value="42.00">
				<input type="hidden" name="buyer_credit_promo_code" value="">
				<input type="hidden" name="buyer_credit_product_category" value="">
				<input type="hidden" name="buyer_credit_shipping_method" value="">
				<input type="hidden" name="buyer_credit_user_address_change" value="">
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="return" value="http://urbangiraffe.com/plugins/search-unleashed/">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-DonationsBF">
				<input type="image" style="border: none" src="<?php echo $this->url () ?>/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
			</form>
			<p><strong>$42</strong><br/><?php _e( 'Commercial<br/>Donation', 'search-unleashed' ); ?></p>
		</li>
		<li>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="admin@urbangiraffe.com">
				<input type="hidden" name="item_name" value="Search Unleashed - Super Smashing Great">
				<input type="hidden" name="amount" value="142.00">
				<input type="hidden" name="buyer_credit_promo_code" value="">
				<input type="hidden" name="buyer_credit_product_category" value="">
				<input type="hidden" name="buyer_credit_shipping_method" value="">
				<input type="hidden" name="buyer_credit_user_address_change" value="">
				<input type="hidden" name="no_shipping" value="1">
				<input type="hidden" name="return" value="http://urbangiraffe.com/plugins/search-unleashed/">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="bn" value="PP-DonationsBF">
				<input type="image" style="border: none" src="<?php echo $this->url () ?>/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
			</form>
			<p><strong>$142</strong><br/><?php _e( 'Super Smashing<br/>Great Donation', 'search-unleashed' ); ?></p>
		</li>
	</ul>
	
	<h3 style="clear: both"><?php _e( 'Translations', 'search-unleashed' )?></h3>
	
	<p><?php _e( 'If you\'re multi-lingual then you may want to consider donating a translation:', 'search-unleashed' )?>
		
	<ul class="translators">
		<?php foreach( $this->locales() AS $language => $author ) : ?>
			<li><?php echo $language ?> &mdash; <?php echo $author; ?></li>
		<?php endforeach; ?>
	</ul>

	<p style="clear: both"><br/><?php _e( 'All translators will have a link to their website placed on the plugin homepage at <a href="http://urbangiraffe.com/plugins/search-unleashed/">UrbanGiraffe</a> and <a href="http://wordpress.org/extend/plugins/search-unleashed/">WordPress.org</a>, in addition to being an individual supporter.', 'search-unleashed' )?></p>
	<p><?php _e( 'Full details of producing a translation can be found in this <a href="http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/">guide to translating WordPress plugins</a>.', 'search-unleashed' )?>
	
	<?php echo $this->contextual_help( '', 'tools_page_search-unleashed' ); ?>
</div>