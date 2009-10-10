google.load('maps', '2');

BlogLocationManager = Class.create();

BlogLocationManager.prototype =
{
	container : null,
	map		  : null,

	initialize : function(container)
	{
		this.container = $(container);
		Event.observe(window, 'load', this.loadMap.bind(this));
	},

	loadMap : function()
	{
		if (!google.maps.BrowserIsCompatible())
			return;

		Event.observe(window, 'unload', this.unloadMap.bind(this));

		this.map = new google.maps.Map2(this.container);
		this.map.setCenter(new google.maps.LatLng(37.423111, -122.081783), 16, G_HYBIRD_MAP);
		this.map.addControl(new google.maps.MapTypeControl());
		this.map.addControl(new google.maps.ScaleControl());
		this.map.addControl(new google.maps.LargeMapControl());
		this.map.addControl(new google.maps.OverviewMapControl());
	},

	unloadMap : function()
	{
		google.maps.Unload();
	}
}