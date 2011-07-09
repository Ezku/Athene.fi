// Used for various iterations, e.g. id'ing css elements
var i = 0;

/*------------------------------------------------------------------------------
Add a dropdown option
@param	string	target_id: target CSS id
@param	string	delete_label: the translated label for the delete button
@param	integer	local_i: a number used to generate unique ids (used along with i)
------------------------------------------------------------------------------*/
function append_dropdown_option( target_id, delete_label, set_as_default_label, local_i )
{
	if (!i) {
		i = local_i;
	}
	my_html = '<div id="cctm_dropdown_option'+i+'"><input type="text" name="options[]" id="option_'+i+'" value=""/> <span class="button" onclick="javascript:remove_html(\'cctm_dropdown_option'+i+'\');">'+delete_label+'</span> <span class="button" onclick="javascript:set_as_default(\'option_'+i+'\');">'+set_as_default_label+'</span></div>';
	jQuery('#'+target_id).append(my_html);
	i++;
}

/*------------------------------------------------------------------------------
Sets the one of the options in a dropdown to be the default value by copying its
value to the default_value field.
@param	string	source_id identifies the dropdown option whose value we want to
				copy as the default value.
------------------------------------------------------------------------------*/
function set_as_default(source_id)
{
	new_default_value = jQuery('#'+source_id).val();
	jQuery('#default_value').val(new_default_value);
}

/*------------------------------------------------------------------------------
Remove the HTML identified by the target_id
------------------------------------------------------------------------------*/
function remove_html( target_id )
{
	jQuery('#'+target_id).remove();	
}
	
/*------------------------------------------------------------------------------
Remove the associated image, media, or relation item.  This means the hidden 
field that stores the actual value must be set to null and the preview hmtl
must be cleared.
@param 	string	target_id is the hidden field id that needs to be nulled
@param	string	target_html is the id of the div whose html needs to be cleared
------------------------------------------------------------------------------*/
function remove_relation( target_id, target_html )
{
	jQuery('#'+target_id).val('');
	jQuery('#'+target_html).html('');	
}