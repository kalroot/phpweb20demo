$(function(){

	var publishButton   = $('#status-publish');
    var unpublishButton = $('#status-unpublish');
    var deleteButton    = $('#status-delete');

    if (publishButton)
    {
    	publishButton.click(function(event){
    		if (!confirm('Click OK to publish this post'))
    			event.preventDefault();
    	});
    }

    if (unpublishButton)
    {
    	unpublishButton.click(function(event){
    		if (!confirm('Click OK to unpublish this post'))
    			event.preventDefault();
    	});
    }

    if (deleteButton)
    {
    	deleteButton.click(function(event){
    		if (!confirm('Click OK to permanently delete this post'))
    			event.preventDefault();
    	});
    }

	// 图像排序Javascript
});