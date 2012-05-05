/**
 * phpwcms Backend
 */

/*
var phpwcms = {
	
	modal_status: false,
	okClick: function() {
		this.modal_status = true;
	},
	confirm_modal: function(title, html) {
		
		this.modal_status = false;
			
		$('<div class="modal hide fade"><div class="modal-header">'+
		  '<button class="close" data-dismiss="modal">&times;</button>'+
		  '<h3>'+title+'</h3></div><div class="modal-body"><p>'+html+'</p></div>'+
		  '<div class="modal-footer"><a href="#" class="btn" data-dismiss="modal">Cancel</a>'+
		  '<a href="#" class="btn btn-primary" onclick="phpwcms.okClick();">OK</a></div></div>').modal({
			backdrop: false
		}).on('hide', function() {
			$(this).remove();
		});
		
	}
	
}
*/

/* jQuery ready */
$(function(){
	
	$('.target-blank').attr('target', '_blank');
	
	$('.action-confirm').click(function(event) {
		var rel = $(this).attr('rel');
		rel = rel.replace(/\\n/gm, " ").replace(/\s+/gm, ' ');
		
		//phpwcms.confirm_modal('test', rel);
		if(confirm(rel)) {
			return true;
		}
		event.preventDefault();
	})
	
});