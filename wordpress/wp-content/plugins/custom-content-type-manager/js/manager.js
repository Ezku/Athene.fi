// Used for various iterations, e.g. id'ing css elements
var i = 0;

/*------------------------------------------------------------------------------
Add a dropdown option: used by the dropbox and multiselect fields.

@param	string	target_id: target CSS id
@param	string	delete_label: the translated label for the delete button
@param	integer	local_i: a number used to generate unique ids (used along with i)
------------------------------------------------------------------------------*/
function append_dropdown_option( target_id, delete_label, set_as_default_label, local_i )
{
	if (!i) {
		i = local_i;
	}
	
	readonly_str='';

	if( !jQuery('#use_key_values:checked').is(':checked') )
	{
        readonly_str=' readonly="readonly"';
    } 
	/*this string must match up with the string used in the dropdown and multiselect classes*/
	my_html = '<tr id="cctm_dropdown_option'+i+'"><td><input type="text" name="options[]" id="option_'+i+'" value=""/></td><td><input type="text" name="values[]" id="value_'+i+'" value="" class="possibly_gray"'+readonly_str+'/></td><td><span class="button" onclick="javascript:remove_html(\'cctm_dropdown_option'+i+'\');">'+delete_label+'</span> <span class="button" onclick="javascript:set_as_default(\''+i+'\');">'+set_as_default_label+'</span><td></tr>';
	jQuery('#'+target_id).append(my_html);
	i++;
}

/*------------------------------------------------------------------------------

------------------------------------------------------------------------------*/
function add_instance(){
	console.log('testing 123...');
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

/*------------------------------------------------------------------------------
Sets the one of the options in a dropdown to be the default value by copying its
value to the default_value field.
@param	integer	i identifies the integer of dropdown option whose value we want to
				copy as the default value.
------------------------------------------------------------------------------*/
function set_as_default(i)
{
	source_id= 'option_'+i;
	
	if( jQuery('#use_key_values:checked').is(':checked') )
	{
        source_id= 'value_'+i;
    }
    
	new_default_value = jQuery('#'+source_id).val();
	jQuery('#default_value').val(new_default_value);
}


/*------------------------------------------------------------------------------
TinyMCE make HTML view
------------------------------------------------------------------------------*/
function show_html_view(id)
{
	tinyMCE.execCommand('mceRemoveControl', false, id);
}

/*------------------------------------------------------------------------------
TinyMCE make Rich-Text-Formatted view
------------------------------------------------------------------------------*/
function show_rtf_view(id)
{
	tinyMCE.execCommand('mceAddControl', false, id);
}

/*------------------------------------------------------------------------------
Grey out form elements (i.e. make readonly)
------------------------------------------------------------------------------*/
function toggle_readonly() 
{
	// is checked: i.e. use both options and values (remove readonly)
	if( jQuery('#use_key_values:checked').is(':checked') )
	{
//        jQuery('.possibly_gray').attr('readonly','');
        jQuery('.possibly_gray').removeAttr('readonly');
    }
    // is not checked: use options only and make values readonly.
    else
    {
    	jQuery('.possibly_gray').attr('readonly','readonly');
    }
}