google.load('maps', '2');

var container	= null;
var map			= null;
var geocoder	= null;
var markers		= new Array();

$(function(){
	container = $('#map')[0];
	geocoder = new google.maps.ClientGeocoder();
	loadMap();
});

function loadMap()
{
	if (!google.maps.BrowserIsCompatible())
		return;

	$(window).unload(unloadMap);

	map = new google.maps.Map2(container);
	map.addControl(new google.maps.MapTypeControl());
	map.addControl(new google.maps.ScaleControl());
	map.addControl(new google.maps.LargeMapControl());

	var overviewMap = new google.maps.OverviewMapControl();
	map.addControl(overviewMap);

	map.enableDoubleClickZoom();
	map.enableContinuousZoom();

	addMarkerToMap(0, 28.139878, 120.289431, 'Qing Tian');
	addMarkerToMap(1, 30.139878, 122.289431, 'Zhou Shan');

	zoomAndCenterMap();
}

function unloadMap()
{
	google.maps.Unload();
}

function zoomAndCenterMap()
{
	map.setCenter(new google.maps.LatLng(0, 0), 1, G_HYBRID_MAP);
}

function addMarkerToMap(id, lat, lng, desc)
{
	markers[id] = new google.maps.Marker(
		new google.maps.LatLng(lat, lng),
		{'title' : desc, draggable : true}
	);
	markers[id].location_id = id;
	markers[id].title = desc;

	map.addOverlay(markers[id]);

	return this.markers[id];
}