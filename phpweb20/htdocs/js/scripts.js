var messages = '#messages';
var delay = 500;
var yellow_color = { backgroundColor: '#FFFF00'};
var white_color = { backgroundColor: '#FFFFFF' };

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

$(function(){
	var m = $(messages);
	if (m && m.css('display') != 'none')
		m.animate(yellow_color, delay).animate(white_color, delay);

	// a SearchSuggestor
});