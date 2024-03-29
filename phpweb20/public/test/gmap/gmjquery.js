google.load('maps', '2');

var container	= null;
var map			= null;
var geocoder	= null;
var markers		= new Array();
var i			= 0;

// Javascript也支持关联数组：
/*

1、直接定义数组：
myhash = { "key1" : "val1", "key2" : "val2" };

2、用Array()定义：

var myhash = new Array();
myhash["key1"] = "val1";
myhash["key2"] = "val2";

3、向关联数组添加新值：

myhash["newkey"] = "newval";

4、删除关联数组键值：

delete myhash["newkey"]; // 整个键与值都消失了

5、遍历整个关联数组：

for (key in myhash)
{
	val = myhash[key];
}

6、如果使用关联数组，就不能再用length属性来获取数组长度了。

7、获取整个关联数组的键数组：

function array_keys(hash)
{
	keys = [];
	for (key in hash)
		keys.push(key);

	return keys;
}

*/

$(function(){
	container = $('#map')[0];
	geocoder = new google.maps.ClientGeocoder();
	$('#lform').submit(onFormSubmit);
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

	//addMarkerToMap(0, 28.139878, 120.289431, 'Qing Tian');
	//addMarkerToMap(1, 30.139878, 122.289431, 'Zhou Shan');

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

	// 注意此处的技巧，直接用html生成包装集。
	var infoWindow = $(generateHtml(id, lat, lng, desc));
	markers[id].bindInfoWindow(infoWindow[0]);
	infoWindow.children('input').attr('id', id).click(onRemoveMarker);

	// 不规范方法
	i = i + 1;
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

function onFormSubmit(event)
{
	event.preventDefault();

	var address = $.trim($('#lform input[name=location]').val());
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

		alert(msg);
		return;
	}

	var placemark = locations.Placemark[0];

	var detail = 'address = ' + placemark.address + '\n'
				+ 'latitude = ' + placemark.Point.coordinates[1] + '\n'
				+ 'longitude = ' + placemark.Point.coordinates[0] + '\n\n'
				+ '在Google地图上标记这个地址吗？';

	if (confirm(detail))
		addMarkerToMap(i, 
				placemark.Point.coordinates[1],
				placemark.Point.coordinates[0],
				placemark.address);
}