/* jQuery ready */
$(function(){
	
	$('a.blank').attr('target', '_blank');
	
	$('#move-deleted').click(function(event) {
		var rel = $(this).attr('rel');
		rel = rel.replace(/(\\n)/gm, "\n");
		if(confirm(rel)) {
			return true;
		}
		event.preventDefault();
	})
	
});