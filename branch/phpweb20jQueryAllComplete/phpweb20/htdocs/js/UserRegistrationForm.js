function resetErrors()
{
	$('.error').hide();
}

function showError(key, val)
{
	var errorElement = $('input[name='+key+']').next('.error');
	if (errorElement)
	{
		errorElement.text(val).show();
	}
}

function onSuccess(response)
{
	if (response.errors != '')
	{
		$('#registration-form .error:first').show();
		$.each(response.errors, function(index, data){
			showError(index, data);
		});
	}
	else
	{
		$('#registration-form').ajaxFormUnbind().submit();
	}
}

$(function(){
	resetErrors();
	$('#registration-form').ajaxForm({
		dataType		: 'json',
		beforeSubmit	: resetErrors,
		success			: onSuccess
	});
});