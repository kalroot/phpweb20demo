$(function(){
	$('#post_images form').ajaxForm({
		dataType		: 'json',
		beforeSubmit	: function(){ message_write('Deleting image...'); },
		success			: onDeleteSuccess,
		error			: onDeleteFailure
	});

	$('#post_images').sortable({
			opacity: 0.6,
			revert: true,
			update: onSortUpdate
	});
});

function onDeleteSuccess(data)
{
	if (data.deleted)
	{
		var image_id = data.image_id;
		$('#image_' + image_id).fadeOut('slow', message_clear);
	}
	else
	{
		onDeleteFailure();
	}
}

function onDeleteFailure()
{
	message_write('Error deleting image');
}

function onSortUpdate()
{
	var post_id = $('#post_images input[name=id]').val();
	var form = $('#post_images form')[0];

	var options =
	{
		url		: form.action,
		type	: form.method,
		data	: 'reorder=1&id=' + post_id + '&' +
			$('#post_images').sortable('serialize', { key: 'post_images[]' }),
		beforeSend : function() { message_write('Updating image order...'); },
		success	: message_clear,
		error	: function(){ message_write('Error updating order'); }
	}

	$.ajax(options);
}