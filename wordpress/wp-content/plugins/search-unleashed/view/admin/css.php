<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<style type="text/css" media="screen">
<?php for ($x = 0; $x < 5; $x++) : ?>
.searchterm<?php echo $x + 1 ?>, .searchterm<?php echo $x + 1; ?> a { background-color: #<?php echo $highlight[$x] ?>; padding: 2px 0px; font-weight: bold;}
<?php endfor; ?>

.incoming {
overflow: hidden;
border: 3px solid #DCEEEE;
padding: 0.5em;
margin: 2em 0em;
color: #2530A8;
background-color: #F1FAF9;
}

.incoming h3
{
width: 100%;
margin: 0;
padding: 5px;
font-weight: bold;
font-size: 16px;
}

.incoming a {	color: #405D77; }
.incoming a:hover {	color: red; }
.incoming div { padding: 5px; }
.incoming p { margin: 0; padding: 0; }
.incoming ul li, .incoming ul { background: none; margin: 0; padding: 0; }
.incoming ul li { margin-left: 40px; }
.incoming .hide { float: right; margin: 5px; padding: 0; }
</style>