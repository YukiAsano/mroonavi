Ext.define('MapApp.view.map.Map', {
    extend: 'Ext.Map',
    alias: 'widget.map_map',

    config: {
        mapOptions: {
            center: new google.maps.LatLng(35.681382, 139.766084),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: false,
            maxZoom: 19,
            minZoom: 15,
            zoom: 17
        }
    }

});
