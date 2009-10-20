google.load('maps', '2');

var map		= null;
var markers	= {};
var marker_number = 1;

$(function(){
	loadMap();
});

function loadMap()
{
	if (!google.maps.BrowserIsCompatible())
		return;

	$(window).unload(unloadMap);

	map = new google.maps.Map2($('#post-locations div.map')[0]);
	map.addControl(new google.maps.MapTypeControl());
	map.addControl(new google.maps.ScaleControl());
	map.addControl(new google.maps.LargeMapControl());

	map.enableDoubleClickZoom();
	map.enableContinuousZoom();

	$('#post-locations abbr.geo').each(function(){
		var coords = this.title.split(';');
		addMarkerToMap(coords[0], coords[1], this.innerHTML);
	});

	zoomAndCenterMap();
}

function zoomAndCenterMap()
{
	var bounds = new google.maps.LatLngBounds();
	$.each(markers, function(index, data){
		bounds.extend(data.getPoint());
	});

	if (bounds.isEmpty())
	{
		map.setCenter(new google.maps.LatLng(0, 0), 1, G_HYBRID_MAP);
	}
	else
	{
		var _zoom = map.getBoundsZoomLevel(bounds) - 1;
		var zoom = (_zoom > 1) ? _zoom : 1;
		map.setCenter(bounds.getCenter(), zoom, G_HYBRID_MAP);
	}
}

function addMarkerToMap(lat, lng, desc)
{
	var marker = new google.maps.Marker(
		new google.maps.LatLng(lat, lng),
		{ 'title' : desc }
	);

	var html = '<div>' + desc + '</div>';
	marker.bindInfoWindowHtml(html);

	map.addOverlay(marker);
	markers[marker_number++] = marker;
}

function unloadMap()
{
	google.maps.Unload();
}