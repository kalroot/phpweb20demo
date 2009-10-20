google.load('maps', '2');

var form		= null;
var post_id		= null;
var map			= null;
var geocoder	= null;
var markers		= {};


$(function(){
	form = $('#location-add');
	post_id = form.find('[name=post_id]').val();
	geocoder = new google.maps.ClientGeocoder();
	form.submit(onFormSubmit);
	loadMap();
});

function loadMap()
{
	if (!google.maps.BrowserIsCompatible())
		return;

	$(window).unload(unloadMap);

	map = new google.maps.Map2($('#location-manager')[0]);
	map.addControl(new google.maps.MapTypeControl());
	map.addControl(new google.maps.ScaleControl());
	map.addControl(new google.maps.LargeMapControl());

	var overviewMap = new google.maps.OverviewMapControl();
	map.addControl(overviewMap);

	map.enableDoubleClickZoom();
	map.enableContinuousZoom();

	$.ajax({
		url			: form[0].action,
		type		: 'post',
		data		: 'action=get&post_id=' + post_id,
		dataType	: 'json',
		success		: loadLocationsSuccess
	});
}

function loadLocationsSuccess(response)
{
	if (response.locations == '')
	{
		zoomAndCenterMap();
		return;
	}

	$.each(response.locations, function(index, data){
		addMarkerToMap(data.location_id, data.latitude, data.longitude, data.description);
	});

	zoomAndCenterMap();
}

function unloadMap()
{
	google.maps.Unload();
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

function addMarkerToMap(id, lat, lng, desc)
{
	removeMarkerFromMap(id);

	markers[id] = new google.maps.Marker(
		new google.maps.LatLng(lat, lng),
		{'title' : desc, draggable : true}
	);
	markers[id].location_id = id;

	google.maps.Event.addListener(markers[id], 'dragend', function(){
		dragEnd(this);
	});
	google.maps.Event.addListener(markers[id], 'dragstart', function(){
		this.closeInfoWindow();
	});

	map.addOverlay(markers[id]);

	var infoWindow = $(generateHtml(desc));
	markers[id].bindInfoWindow(infoWindow[0]);
	infoWindow.children('input').attr('location_id', id).click(onRemoveMarker);

	return markers[id];
}

function generateHtml(desc)
{
	return '<div>' + desc + '<br />' +
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

	$.ajax({
		url			: form[0].action,
		type		: 'post',
		data		: 'action=move&post_id=' + post_id
						+ '&location_id=' + marker.location_id
						+ '&latitude=' + point.lat()
						+ '&longitude=' + point.lng(),
		dataType	: 'json',
		success		: dragSuccess
	});
}

function dragSuccess(response)
{
	// var b = '', 则if(b)为假
	if (response.location_id && hasMarker(response.location_id))
	{
		var point = new google.maps.LatLng(response.latitude, response.longitude);
		var marker = addMarkerToMap(response.location_id,
									response.latitude,
									response.longitude,
									response.description);
		google.maps.Event.trigger(marker, 'click');
	}
}

function onRemoveMarker(event)
{
	var location_id = $(event.target).attr('location_id');
	$.ajax({
		url			: form[0].action,
		type		: 'post',
		data		: 'action=delete&post_id=' + post_id
						+ '&location_id=' + location_id,
		dataType	: 'json',
		success		: onRemoveMarkerSuccess
	});
}

function onRemoveMarkerSuccess(response)
{
	if (response.location_id)
		removeMarkerFromMap(response.location_id);
}

function onFormSubmit(event)
{
	event.preventDefault();

	var address = $.trim(form.find('input[name=location]').val());
	if (address == '')
		return;

	geocoder.getLocations(address, createPoint);
}

function createPoint(locations)
{
	if (locations.Status.code != G_GEO_SUCCESS)
	{
		var msg = '';
		switch (locations.Status.code)
		{
			case G_GEO_BAD_REQUEST:
				msg = 'Unable to parse request';
				break;
			case G_GEO_MISSING_QUERY:
				msg = 'Query not specified';
				break;
			case G_GEO_UNKNOWN_ADDRESS:
				msg = 'Unable to find address';
				break;
			case G_GEO_UNAVAILABLE_ADDRESS:
				msg = 'Forbidden address';
				break;
			case G_GEO_BAD_KEY:
				msg = 'Invalid API key';
				break;
			case G_GEO_TOO_MANY_QUERIES:
				msg = 'Too many geocoder queries';
				break;
			case G_GEO_SERVER_ERROR:
			default:
				msg = 'Unknown server error occurred';
		}

		message_write(msg);
		return;
	}

	var placemark = locations.Placemark[0];

	$.ajax({
		url			: form[0].action,
		type		: 'post',
		data		: 'action=add&post_id=' + post_id
						+ '&description=' + placemark.address
						+ '&latitude=' + placemark.Point.coordinates[1]
						+ '&longitude=' + placemark.Point.coordinates[0],
		dataType	: 'json',
		success		: createPointSuccess
	});
}

function createPointSuccess(response)
{
	if (response.location_id == 0)
	{
		message_write('Error adding location to blog post');
		return;
	}

	marker = addMarkerToMap(response.location_id, response.latitude, response.longitude, response.description);
	google.maps.Event.trigger(marker, 'click');
	zoomAndCenterMap();
}