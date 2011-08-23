<?php
/**
 * @package WordPress
 * @subpackage Athene
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'toolbox' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
<script src="<?php bloginfo( 'template_directory' ); ?>/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page" class="hfeed">

	<header class="container_16" id="branding" role="banner">
		<hgroup class="grid_6">
			<h1 id="site-title"><span><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></span></h1>
			<h2 id="site-description"><?php bloginfo( 'description' ); ?></h2>
		</hgroup>

		<nav class="grid_10" id="access" role="navigation">
			<h1 class="section-heading"><?php _e( 'Main menu', 'toolbox' ); ?></h1>
			<div class="skip-link screen-reader-text"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'toolbox' ); ?>"><?php _e( 'Skip to content', 'toolbox' ); ?></a></div>

			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'depth' => 1 ) ); ?>
		</nav><!-- #access -->
	</header><!-- #branding -->
	<div class="clear"></div>

	<nav class="container_16" id="subnavi-small" role="navigation">

		<?php wp_nav_menu( array( 'theme_location' => 'primary', 'depth' => 0, 'walker' => new SubMenuWalker(array(1,2)) ) ); ?>

		<?php
		/*
		// Only show subnavi for subpages,
		// we'll trust everyone uses the layout "subnavi-page" for parent pages.
		if($post->post_parent) {

			$subpages = get_pages(
				'child_of='.$post->post_parent.
				'&parent='.$post->post_parent.
				'&hierarcial=0&sort_column=menu_order&sort_order=desc'
			);

			foreach($subpages as $subpage) {
		?>

		<div class="grid_4 subnavi-box">
			<a href="<?php echo get_page_link($subpage->ID) ?>"><?php echo $subpage->post_title ?></a>
		</div>
		<?php
			} // end foreach
		} // end if
		*/
		?>

	</nav><!-- #subnavi-small -->
	<div class="clear"></div>

	<div id="main">