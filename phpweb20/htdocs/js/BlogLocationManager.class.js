google.load('maps', '2');

BlogLocationManager = Class.create();

BlogLocationManager.prototype =
{
	url			: null,

	post_id		: null,
	container	: null,
	map			: null,
	geocoder	: null,

	markers		: $H({}),

	markerTemplate : new Template(
		'<div>' + '#{desc}<br />' + '<input type="button" value="Remove Location" />'
		+ '</div>'
	),

	initialize : function(container, form)
	{
		form			= $(form);
		this.url		= form.action;
		this.post_id	= $F(form.post_id);
		this.container	= $(container);

		this.geocoder	= new google.maps.ClientGeocoder();

		Event.observe(window, 'load', this.loadMap.bind(this));
		form.observe('submit', this.onFormSubmit.bindAsEventListener(this));
	},

	loadMap : function()
	{
		if (!google.maps.BrowserIsCompatible())
			return;

		Event.observe(window, 'unload', this.unloadMap.bind(this));

		this.map = new google.maps.Map2(this.container);
		this.zoomAndCenterMap();
		
		this.map.addControl(new google.maps.MapTypeControl());
		this.map.addControl(new google.maps.ScaleControl());
		this.map.addControl(new google.maps.LargeMapControl());

		var overviewMap = new google.maps.OverviewMapControl();
		this.map.addControl(overviewMap);
		overviewMap.hide(true);

		this.map.enableDoubleClickZoom();
		this.map.enableContinuousZoom();

		var options = {
			parameters	: 'action=get&post_id=' + this.post_id,
			onSuccess	: this.loadLocationsSuccess.bind(this)
		}

		new Ajax.Request(this.url, options);
	},

	zoomAndCenterMap : function()
	{
		var bounds = new google.maps.LatLngBounds();
		this.markers.each(function(pair){
			bounds.extend(pair.value.getPoint());
		});

		if (bounds.isEmpty())
			this.map.setCenter(new google.maps.LatLng(0, 0), 1, G_HYBRID_MAP);
		else
		{
			var zoom = Math.max(1, this.map.getBoundsZoomLevel(bounds) - 1);
			this.map.setCenter(bounds.getCenter(), zoom);
		}
	},

	loadLocationsSuccess : function(transport)
	{
		var json = transport.responseText.evalJSON(true);
		if (json.locations == null)
			return;

		json.locations.each(function(location){
			this.addMarkerToMap(
				location.location_id,
				location.latitude,
				location.longitude,
				location.description
			);
		}.bind(this));

		this.zoomAndCenterMap();
	},

	unloadMap : function()
	{
		google.maps.Unload();
	}
}