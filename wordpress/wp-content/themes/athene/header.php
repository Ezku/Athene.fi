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
<link rel="shortcut icon" href="<?php bloginfo( 'stylesheet_directory' ) ?>/images/athene.ico" type="image/x-icon" />

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page" class="hfeed">

	<header id="branding" role="banner">
	    <div class="container_16 clearfix">
    		<hgroup class="grid_4 alpha">
    			<h1 id="site-title"><span><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></span></h1>
    		</hgroup>

    		<nav class="grid_12 omega" id="access" role="navigation">
    			<h1 class="section-heading"><?php _e( 'Main menu', 'toolbox' ); ?></h1>
    			<div class="skip-link screen-reader-text"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'toolbox' ); ?>"><?php _e( 'Skip to content', 'toolbox' ); ?></a></div>

    			<?php wp_nav_menu( array(
    			    'theme_location' => 'primary',
    			    'depth' => 1,
    			    'walker' => SubMenuWalker::create(array(
		                'levels_shown' => array(0),
		                'only_current_branch' => false
		            ))/* Looks like ass on everything except content pages.
		            ->setDepthClasses(array(
		                0 => cycle(array('grid_3', 'alpha'), 'grid_3', 'grid_3', array('grid_3', 'omega'))
		            )) */

    			) ); ?>
    		</nav><!-- #access -->
		</div>
	</header><!-- #branding -->

    <?php if (!$no_small_submenu) { ?>
    <header id="subnavi-small">
        <?php include 'subnavi-header.php' ?>
    </header>
    <?php } ?>

	<div id="main" class="clearfix">
