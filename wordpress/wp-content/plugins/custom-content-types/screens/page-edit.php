<?php

if(isset($_GET["f"])){ 

	switch($_GET["f"]){
	
		case "delete" :
			$content_types->delete_content_type($_GET["name"]);
			$message = "Content type deleted.";
			break;			
			
		
	}
		
}

if(isset($_POST["action"]) or isset($_POST["action2"])){
	
	$_POST["action"] = (!empty($_POST["action2"])) ? $_POST["action2"] : $_POST["action"];
	
	switch($_POST["action"]){
	
		case "delete" :
		
			if(!empty($_POST["cb_cct"])){
				foreach($_POST["cb_cct"] as $cct_name){
					$content_types->delete_content_type($cct_name);
				}
			}
			$message = "Content types deleted.";
			break;
			
		case "add" :
				
			if(!empty($_POST["label"]) and !empty($_POST["label-plural"])){
				$content_types->add_content_type($_POST["label"], $_POST["label"], $_POST["label-plural"], $_POST["description"]);
			} else {
			
				$error = true;
				$message = "You must set both the singular and plural names.";
				
			}
			break;
			
		case "edit" :
				
			if(!empty($_POST["label"]) and !empty($_POST["label-plural"])){
				$content_types->update_content_type($_POST["name"], $_POST["label"], $_POST["label-plural"], $_POST["description"]);
				unset($_POST);
				unset($_GET);
			} else {
			
				$error = true;
				$message = "You must set both the singular and plural names.";
				
			}
			break;
	
	}
	
}

$all_cct = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."content_types");


if(!empty($all_cct)){
	$total_cct = count($all_cct);
}

$_REQUEST["paged"] = (!empty($_REQUEST["paged"]))?$_REQUEST["paged"]:1;
$numberposts = (!empty($_REQUEST["n"]))?$_REQUEST["n"]:20;
$pages = ceil($total_cct/$numberposts);
$currentpage = (!empty($_REQUEST["paged"]))?$_REQUEST["paged"]-1:0;
$offset = $numberposts*$currentpage;
$showing_from = $offset+1;
$showing_to = ($showing_from+$numberposts <= $total_cct)?$showing_from+$numberposts-1:$total_cct;
$max_pages = 4;

if($_REQUEST["paged"]-floor($max_pages/2) < 1){
	$start_pagination = 1;
} else {
	$start_pagination = $_REQUEST["paged"]-floor($max_pages/2);
}

if($start_pagination+$max_pages > $pages){
	$end_pagination = $pages;
} else {
	$end_pagination = $start_pagination+$max_pages-1;
}



$ccts = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."content_types LIMIT $offset,$numberposts");


if($message){ 
	echo "<div class=\"updated below-h2\" id=\"message\"><p>$message</p></div>";
}

?>
      
      
      
<div id="col-container">
  <div id="col-right">
    <div class="col-wrap">
        <div class="tablenav">
        
        
            <div class="tablenav-pages"><span class="displaying-num">Displaying <?=$showing_from;?>&ndash;<?=$showing_to;?> of <?=$total_cct;?></span>
            
            <? if($pages > 1){ ?>
            <? if($_REQUEST["paged"] != 1){ ?>
            <a href="admin.php?page=<?=$content_types->directory;?>/cct&paged=<?=$_REQUEST["paged"]-1;?>&n=<?=$numberposts?>&n=<?=$numberposts?>" class="previous page-numbers">&laquo;</a>
            <? } ?>
            
            <? for($i = $start_pagination; $i <= $end_pagination; $i++){ ?>
            
            <? if($i-1 == $currentpage){ ?>
            <span class="page-numbers current"><?=$i;?></span>
            <? } else { ?>
            <a href="admin.php?page=<?=$content_types->directory;?>/cct&paged=<?=$i;?>&n=<?=$numberposts?>" class="page-numbers"><?=$i?></a>
            <? } ?>
            
            <? } ?>
            
            <? if($_REQUEST["paged"] != $pages){ ?>
            <a href="admin.php?page=<?=$content_types->directory;?>/cct&paged=<?=$_REQUEST["paged"]+1;?>&n=<?=$numberposts?>" class="next page-numbers">&raquo;</a>
            <? } ?>
            <? } ?>
            
            </div>
        
        
        
          <div class="alignleft actions">
            <select name="action">
              <option selected="selected" value="">Bulk Actions</option>
              <option value="delete">Delete</option>
            </select>
            <input type="submit" class="button-secondary action" id="doaction" name="doaction" value="Apply">
          </div>
          <br class="clear">
        </div>
                
        
        <div class="clear"></div>
        <table cellspacing="0" class="widefat tag fixed">
          <thead>
            <tr>
              <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
              <th style="" class="manage-column column-name" id="name" scope="col">Name</th>
              <th style="" class="manage-column column-description" id="description" scope="col">Description</th>
              <th style="" class="manage-column column-slug" id="slug" scope="col">Slug</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>
              <th style="" class="manage-column column-name" scope="col">Name</th>
              <th style="" class="manage-column column-description" scope="col">Description</th>
              <th style="" class="manage-column column-slug" scope="col">Slug</th>
            </tr>
          </tfoot>
          <tbody class="list:tag" id="the-list">
          
          <?
		  
			if(!empty($ccts)){
				foreach($ccts as $cct){
				
				?><tr class="alternate">
			   <th class="check-column" scope="row"><input type="checkbox" value="<?=$cct->name;?>" name="cb_cct[]"></th>
				<td class="name column-name"><strong><a href="admin.php?page=<?=$content_types->directory;?>/cct&f=single&name=<?=$cct->name;?>" class="row-name"><?=$cct->label_plural;?></a></strong>
				<div class="row-actions"><span class="edit"><a href="admin.php?page=<?=$content_types->directory;?>/cct&f=single&name=<?=$cct->name;?>">Edit</a> | </span><span class="trash"><a href="javascript:delete_cct('<?=$cct->name;?>','<?=$cct->label_plural;?>')">Delete</a></span></div></td>
                <td class="description column-description"><?=$cct->description;?></td>
                <td class="slug column-slug"><?=$cct->name;?></td>
				</tr><?
				
				}
			}
			
			?>
          
          </tbody>
        </table>
        <div class="tablenav">
        
        
          <div class="tablenav-pages"><span class="displaying-num">Displaying <?=$showing_from;?>&ndash;<?=$showing_to;?> of <?=$total_cct;?></span>
            
            <? if($pages > 1){ ?>
            <? if($_REQUEST["paged"] != 1){ ?>
            <a href="admin.php?page=<?=$content_types->directory;?>/cct&paged=<?=$_REQUEST["paged"]-1;?>&n=<?=$numberposts?>&n=<?=$numberposts?>" class="previous page-numbers">&laquo;</a>
            <? } ?>
            
            <? for($i = $start_pagination; $i <= $end_pagination; $i++){ ?>
            
            <? if($i-1 == $currentpage){ ?>
            <span class="page-numbers current"><?=$i;?></span>
            <? } else { ?>
            <a href="admin.php?page=<?=$content_types->directory;?>/cct&paged=<?=$i;?>&n=<?=$numberposts?>" class="page-numbers"><?=$i?></a>
            <? } ?>
            
            <? } ?>
            
            <? if($_REQUEST["paged"] != $pages){ ?>
            <a href="admin.php?page=<?=$content_types->directory;?>/cct&paged=<?=$_REQUEST["paged"]+1;?>&n=<?=$numberposts?>" class="next page-numbers">&raquo;</a>
            <? } ?>
            <? } ?>
            
            </div>
        
        
          <div class="alignleft actions">
            <select name="action2">
              <option selected="selected" value="">Bulk Actions</option>
              <option value="delete">Delete</option>
            </select>
            <input type="submit" class="button-secondary action" id="doaction2" name="doaction2" value="Apply">
          </div>
          <br class="clear">
        </div>
        <br class="clear">
      <div class="form-wrap">
        <p><strong>Note:</strong><br>
          Deleting a content type does not delete posts of that content type.</p>
      </div>
    </div>
  </div>
  
  </form><form method="post">
  
  <?php wp_nonce_field($this->parent.'-'.$this->slug); ?>
  
  <? switch($_GET["f"]) {
  
  	case "single" :
		$cct_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."content_types WHERE name = '".$_GET["name"]."'");
		if(!empty($cct_data)){
			$action = "edit";
			$title = "Edit Content Type";
			$submit = "Update";
			$field["label"] = $cct_data->label;
			$field["label-plural"] = $cct_data->label_plural;
			$field["description"] = $cct_data->description;
		} else {
			$action = "add";
			$title = "Add New Content Type";
		}
		break;
	default :
		$action = "add";
		$title = "Add New Content Type";
		$submit = "Add New Content Type";
		$field["label"] = $_POST["label"];
		$field["label-plural"] = $_POST["label-plural"];
		$field["description"] = $_POST["description"];
		break;
  
  } ?>
  <input type="hidden" name="action" value="<?=$action;?>" />
  
  <div id="col-left">
    <div class="col-wrap">
      <div class="form-wrap">
        <h3><?=$title;?></h3>
          <div class="form-field form-required">
            <label for="label">Singular Name</label>
            <input type="text" aria-required="true" size="40" id="label" name="label" value="<?=$field["label"];?>">
            <p>The singular name of the content type how you want it to appear on your admin screens (For example &quot;Book&quot;)</p>
          </div>
          <div class="form-field">
            <label for="label-plural">Plural Name</label>
            <input type="text" size="40" id="label-plural" name="label-plural" value="<?=$field["label-plural"];?>">
            <p>The plural name of the content type how you want it to appear on your admin screens (For example &quot;Books&quot;)</p>
          </div>
          <div class="form-field">
            <label for="description">Description</label>
            <textarea cols="40" rows="5" id="description" name="description"><?=$field["description"];?></textarea>
          </div>
          <p class="submit">
            <input type="submit" value="<?=$submit;?>" id="submit" name="submit" class="button<?=($action == "edit")?"-primary":null;?>">
            <? if($action == "edit"){ ?>
            <input type="button" value="Cancel" id="submit" name="submit" class="button" onclick="window.location='admin.php?page=<?=$content_types->directory;?>/cct'">
            <input type="hidden" name="name" value="<?=$_GET["name"];?>" />
            <? } ?>
          </p>
      </div>
    </div>
  </div>
</div>