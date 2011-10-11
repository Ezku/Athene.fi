<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="description" content="<?php bloginfo('description'); ?>" />
	<title><?php bloginfo('name'); ?> &rsaquo; <?php echo $this->g_opt['mamo_pagetitle']; ?></title>
	
<?php
	wp_admin_css( 'login', true );
	wp_admin_css( 'colors-fresh', true );
	global $is_iphone;
	if ( $is_iphone ) { ?>
	<meta name="viewport" content="width=320; initial-scale=0.9; maximum-scale=1.0; user-scalable=0;" />
	<style type="text/css" media="screen">
	form { margin-left: 0px; }
	#login { margin-top: 20px; }
	</style>
<?php
	} elseif ( isset($interim_login) && $interim_login ) { ?>
	<style type="text/css" media="all">
	.login #login { margin: 20px auto; }
	</style>

<?php
	}
 ?>
	<style type="text/css" media="all">
	#loginform h1 {padding: 0 0 .8em 0; text-align: center; }
	#loginform p { padding: .4em 0; line-height: 1.5em }
	#menu { text-align: center; width: 100%; position: absolute; bottom: 2em; }
	</style>
<?php

	do_action('login_head'); ?>

</head>

<body class="login">

	<div id="login">
	
		<h1><a href="<?php echo apply_filters('login_headerurl', 'http://wordpress.org/'); ?>" title="<?php echo apply_filters('login_headertitle', __('Powered by WordPress')); ?>"><?php bloginfo('name'); ?></a></h1>
	
		<form name="loginform" id="loginform" action="<?php echo site_url('wp-login.php', 'login_post') ?>" method="post">
			<?php echo $this->mamo_template_tag_message(); ?>
		</form>
 
		<p id="nav">
			<?php echo $this->mamo_template_tag_login_logout(); ?>
		</p>

	</div>

	<p id="menu">
		Maintenance Mode plugin by <a title="Software Guide" href="http://sw-guide.de/">Software Guide</a>.
	</p>

	<p id="backtoblog"><a href="<?php bloginfo('url'); ?>/" title="<?php _e('Home Page') ?>"><?php printf('%s', get_bloginfo('title', 'display' )); ?></a></p>

</body>
</html>