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
	var bounds = new google.maps.LatLngBounds();
	$.each(markers, function(index, value){
		bounds.extend(value.getPoint());
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

function addMarkerToMap(id, lat, lng, desc)
{
	removeMarkerFromMap(id);
	
	markers[id] = new google.maps.Marker(
		new google.maps.LatLng(lat, lng),
		{'title' : desc, draggable : true}
	);
	markers[id].location_id = id;
	markers[id].title = desc;

	google.maps.Event.addListener(markers[id], 'dragend', function(){
		dragEnd(this);
	});
	google.maps.Event.addListener(markers[id], 'dragstart', function(){
		this.closeInfoWindow();
	});

	map.addOverlay(markers[id]);

	var infoWindow = $(generateHtml(id, lat, lng, desc));
	markers[id].bindInfoWindow(infoWindow[0]);
	infoWindow.children('input').attr('id', id).click(onRemoveMarker);

	return this.markers[id];
}

function generateHtml(id, lat, lng, desc)
{
	return '<div>' +
				'location_id = ' + id + '<br />' +
				'latitude = ' + lat + '<br />' +
				'longitude = ' + lng + '<br />' +
				'description = ' + desc + '<br /><br />' +
				'<input type="button" value="Remove Location" /></div>';
}

function hasMarker(location_id)
{
	return findMarkerIndex(location_id) >= 0;
}

function findMarkerIndex(location_id)
{
	for (i in markers)
		if (i == location_id)
			return i;
	return -1;
}

function removeMarkerFromMap(location_id)
{
	if (!hasMarker(location_id))
		return;

	map.removeOverlay(markers[location_id]);
	delete markers[location_id];
}

function dragEnd(marker)
{
	var point = marker.getPoint();

	addMarkerToMap(marker.location_id, point.lat(), point.lng(), marker.title);

	alert('marker.location_id = ' + marker.location_id + '\n'
				+ 'marker.latitude = ' + point.lat() + '\n'
				+ 'marker.longitude = ' + point.lng() + '\n'
				+ 'marker.description = ' + marker.title);
}

function onRemoveMarker(event)
{
	var location_id = $(event.target).attr('id');
	removeMarkerFromMap(location_id);
}