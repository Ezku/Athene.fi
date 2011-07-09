function delete_cct(id, name){

	conf = window.confirm("Are you sure you want to delete this content type?\n\n"+name);
	
	if(conf){
		window.location='admin.php?page='+cct_directory+'/cct&name='+id+'&f=delete';	
	}

}