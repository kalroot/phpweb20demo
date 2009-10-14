var messages = '#messages';
var delay = 500;
var yellow_color = { backgroundColor: '#FFFF00'};
var white_color = { backgroundColor: '#FFFFFF' };

var timer = null;
var input = null;
var container = null;
var suggestion_delay = 100;

var KEY_ESC = 27;
var KEY_RETURN = 13;
var KEY_UP = 38;
var KEY_DOWN = 40;


function message_write(message)
{
	var m = $(messages);
	if (!m)
		return;
	
	if (message.length == 0)
	{
		m.fadeOut(delay);
		return;
	}
	
	m.text(message).css('display', 'block').animate(yellow_color, delay).animate(white_color, delay);
}

function message_clear()
{
	message_write('');
}

function loadSuggestions()
{
	var query = $.trim(input.val());
	if (query.length == 0)
		clearSuggestions();

	// 用post发起ajax，返回的数据（即success函数的参数）被看成字符串
	//$.post('/search/suggestion', 'q=' + query, onSuggestionLoad);

	// 用ajax发起，并且指定dataType为json，返回的数据被看成数组
	$.ajax({
		url			: '/search/suggestion',
		type		: 'post',
		data		: 'q=' + query,
		dataType	: 'json',
		success		: showSuggestions
	});
}

function showSuggestions(data)
{
	clearSuggestions();

	if (data.length == 0)
		return;

	var html = '<ul>';
	for (var i = 0 ; i < data.length ; i++)
	{
		html += '<li>' + data[i] + '</li>';
	}
	html += '</ul>';
	$(html).children('li').mouseover(function(event){
		$(event.target).addClass('active')
	}).mouseout(function(event){
		$(event.target).removeClass('active')
	}).click(function(event){
		input.val($(event.target).html());
		clearSuggestions();
		input.parents('form').submit();
	}).end().appendTo(container);
	// end()函数弹出栈顶的包装集，如果之前的操作链返回的对象都一样，则不压栈，只有返回的对象不一样时才压栈
}

function clearSuggestions()
{
	container.children('ul').remove();
}

function getNumberOfSuggestions()
{
	return container.find('li').size();
}

function selectSuggestion(idx)
{
	var items = container.find('li');
	for (var i = 0 ; i < items.size() ; i++)
	{
		if (i == idx)
			$(items[i]).addClass('active');
		else
			$(items[i]).removeClass('active');
	}
}

function getSelectedSuggestionIndex()
{
	var items = container.find('li');
	for (var i = 0 ; i < items.size() ; i++)
	{
		if ($(items[i]).hasClass('active'))
			return i;
	}

	return -1;
}

function getSelectedSuggestion()
{
	var items = container.find('li');
	for (var i = 0 ; i < items.size() ; i++)
	{
		if ($(items[i]).hasClass('active'))
			return $.trim(items[i].innerHTML);
	}

	return '';
}

$(function(){
	var m = $(messages);
	if (m && m.css('display') != 'none')
		m.animate(yellow_color, delay).animate(white_color, delay);

	input = $('#search-query');
	container = $('#search');
	
	input.attr('autocomplete', 'off').keydown(function(event){
		clearTimeout(timer);

		switch (event.keyCode)
		{
			case KEY_RETURN:
				var term = getSelectedSuggestion();
				if (term.length > 0)
				{
					input.val(term);
					clearSuggestions();
				}
				return;

			case KEY_ESC:
				clearSuggestions();
				return;

			case KEY_DOWN:
				var total = getNumberOfSuggestions();
				var selected = getSelectedSuggestionIndex();

				if (selected == total - 1)
					selected = -1;
				else if (selected < 0)
					selected = 0;
				else
					selected = (selected + 1) % total;

				selectSuggestion(selected);
				event.preventDefault();
				return;

			case KEY_UP:
				var total = getNumberOfSuggestions();
				var selected = getSelectedSuggestionIndex();

				if (selected == 0)
					selected = -1;
				else if (selected < 0)
					selected = total - 1;
				else
					selected = (selected - 1) % total;

				selectSuggestion(selected);
				event.preventDefault();
				return;
		}

		timer = setTimeout(loadSuggestions, suggestion_delay);
	});
});