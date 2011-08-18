<script type="text/javascript" src="jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="./fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="./fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="flickrbrowser.js"></script>
<script type="text/javascript" charset="utf-8">
  <?php $options = get_option('flickr_photosets_options'); ?>
  flickrbrowser.api_key: "<?php echo $options['apikey']; ?>";
  flickrbrowser.user_id: "<?php echo $options['username']; ?>";
</script>
<link rel="stylesheet" type="text/css" href="./fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<style type="text/css" media="screen">
  #flickrphotos {
  }
  .photoset {
    background-color: #6c6;
    clear: both;
  }
  .photosettitle:hover {
    background-color: #7d7;
    cursor: pointer;
  }
  .photoset.active {
    background-color: #7d7;
  }
  .photo {
    margin: 5px;
    float: left;
  }
  #spinner {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 100px;
    height: 100px;
    margin: -50px -50px 0 0;
  }
  
  .hide {
    display: none;
  }
</style>
<div id="flickrphotos">
</div>
<div class="flickrlink">
  <p>Kaikki kuvat löytyvät myös <a href="http://www.flickr.com/photos/<?php echo $options['username'] ?>/sets/">Flickr-palvelusta</a>.</p>
</div>
<div id="spinner">
  loading....
</div>