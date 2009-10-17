$(function(){
	$('#preview-months a').click(function(event){
		// 先要阻止使用默认事件提交
		event.preventDefault();

		message_write('Loading blog posts...');

		// 以下是使用jQuery load函数发起Ajax请求
		$('#month-preview').load(
			event.target.href,
			message_clear
		);

		/* 以下使用$.get发起Ajax请求
		$.get(event.target.href, function(response){
			$('#month-preview').html(response);
		});
		*/
	});
});