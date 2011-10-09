<?php
define(LAYOUT_TOIMIJAT, "Toimijat");
define(LAYOUT_PHUKSIT, "Phuksit");
define(LAYOUT_VALMISTUNEET, "Valmistuneet");

$layouts = array(
    LAYOUT_TOIMIJAT => "toimijat",
    LAYOUT_PHUKSIT => "phuksit",
    LAYOUT_VALMISTUNEET => "valmistuneet"
);

function custom_field_found($field) { // yep, bubble gum found
  return get_custom_field($field) != "The ".$field." field is not defined as a custom field.";
}

function toimijalista_get_template($layouts, $layout, $section = NULL) {
    return 'toimijalista/'.$layouts[$layout].($section == NULL ? '' : '-'.$section).'.php';
}

$layout = get_custom_field('layout');
$perma_url = get_permalink();

get_header(); ?>

<div id="primary" class="container_16">
	<div id="content" class="grid_10 alpha" role="main">
        <?php while ( have_posts() ) : the_post(); ?>
	
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  	            <header class="entry-header">
  		            <h1 class="entry-title"><?php the_title(); ?></h1>
  	            </header><!-- .entry-header -->
  	            <div class="entry-content">
                    <?php 
                    $Q = new GetPostsQuery();
                    $Q->set_output_type(ARRAY_A);
                    $Q->limit = 10000;
                    $args = array(
                      "post_type" => get_custom_field('tyyppi')
                    );
                    if (array_key_exists($layout, $layouts)) {
                        //include('toimijalista/'.$layout.'.php');
                        include(toimijalista_get_template($layouts, $layout));
                    }
                    ?>
                    
                    <div class="clearfix" />
                </div>
            </article>

        <?php endwhile; // end of the loop. ?>
    </div> <!-- /#content -->
    <div id="extra" class="grid_6 omega">
        <?php 
        if (array_key_exists($layout, $layouts)) {
            //include('toimijalista/'.$layout.'.php');
            
            include(toimijalista_get_template($layouts, $layout, 'extra'));
        }
        ?>
    </div><!-- #extra -->
</div> <!-- /#primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>