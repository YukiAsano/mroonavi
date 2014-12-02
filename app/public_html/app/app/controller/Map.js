Ext.define('MapApp.controller.Map', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
            main: 'map_main',
            map: 'map_map'
        },

        control: {
            ".map_main #return": {
                tap: 'onReturnSearch'
            },
            "map": {
                show: 'onShow',
                maprender: 'onMapRender',
                centerchange: 'onCenterChange',
                zoomchange: 'onZoomChange'
            },
            "main": {
                search: 'onSearch'
            }
        }
    },

    onReturnSearch: function(target) {
        var me = this,
            main = me.getMain();
        main.getParent().fireEvent('returnSearch');
    },

    onShow: function(target) {
        var me = this;
        me.doUpdate(map.getBounds());
    },

    onMapRender: function(target, map) {
        var me = this,
            geo = Ext.create('Ext.util.Geolocation', {
            autoUpdate: false,
            listeners: {
                locationupdate: function(geo) {
                    var latlng = new google.maps.LatLng(geo.getLatitude(), geo.getLongitude());
                    map.setCenter(latlng);
                    me.doUpdate(map.getBounds());
                },
                locationerror: function(geo, bTimeout, bPermissionDenied, bLocationUnavailable, message) {
                    if (bTimeout) {
                        Ext.Msg.alert('Timeout occurred',"Could not get current position");
                    } else {
                        alert('Error occurred.');
                    }
                }
            }
        });
        geo.updateLocation();
    },

    onCenterChange: function(target, map, latlng) {
        var me = this;

        // ストアの更新
        me.doUpdate(map.getBounds());
    },

    onZoomChange: function(target, map, zoom) {
        var me = this;

        // ストアの更新
        me.doUpdate(map.getBounds());
    },

    onSearch: function(values) {
        var me = this;
        me.searchValues = values;
        me.doUpdate(me.lastBounds);
    },

    init: function(application) {
        var me = this,
            markers = [];

        // ストアの更新が頻繁に発生するので
        me.updateFn = null;

        // 初期値
        me.searchValues = {
            'search1': '',
            'search2': '',
            'search3': ''
        };

        me.lastBounds = null;

        me.isPingClick = false;

        // ストア更新
        me.doUpdate = function (bounds) {

            var neLat, neLng, swLat, swLng, i;

            if (!bounds || me.isPingClick) {
                me.isPingClick = false;
                return;
            }
            me.lastBounds = bounds;
            clearTimeout(me.updateFn);

            // 表示領域の緯度経度の取得
            neLat = bounds.getNorthEast().lat();
            neLng = bounds.getNorthEast().lng();
            swLat = bounds.getSouthWest().lat();
            swLng = bounds.getSouthWest().lng();

            me.updateFn = setTimeout(function(){
                Ext.getStore('Shops').load({
                    params: {
                        search1: me.searchValues.search1,
                        search2: me.searchValues.search2,
                        search3: me.searchValues.search3,
                        swlat: swLat,
                        swlng: swLng,
                        nelng: neLng,
                        nelat: neLat
                    },
                    callback: function(records) {

                        var len = records.length,
                            googleMap = me.getMap().getMap(),
                            infowindow = new google.maps.InfoWindow(),
                            marker, i, pos, record,
                            bounds = new google.maps.LatLngBounds();

                        // マーカー削除
                        for (i = 0; i <  markers.length; i++) {
                            markers[i].setMap();
                        }

                        // 配列削除
                        for (i = 0; i <=  markers.length; i++) {
                            markers.shift();
                        }

                        for (i = 0; i < len; i++) {

                            record = records[i];
                            pos = new google.maps.LatLng(record.get('lat'), record.get('lng'));

                            marker = new google.maps.Marker({
                                position: pos,
                                map: googleMap,
                                title: 'Click Me ' + i
                            });

                            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                                var shopName = record.get('name'),
                                    shopUrl = record.get('url'),
                                    shopAddress = record.get('address'),
                                    coupon = record.get('coupon'),
                                    tel = record.get('tel');
                                return function () {
                                    infowindow.setContent(''.concat(
                                        '<div class="infowindow">',
                                            '<a href="'+shopUrl+'" target="_blank">',
                                                shopName,
                                            '</a>',
                                            (coupon !== "0" ? '&nbsp(クーポンあり)' : ''),
                                            '<br/>',
                                            shopAddress,
                                            (tel ? '<br/>tel:<a href="tel:'+tel+'">'+tel+'</a>' : ''),
                                        '</div>'
                                    ));
                                    infowindow.open(googleMap, marker);
                                    me.isPingClick = true;
                                    setTimeout(function() {
                                        me.isPingClick = false;
                                    }, 1500);
                                    return false;
                                };
                            })(marker, i));

                            // 後で削除するためマーカーを保持
                            markers.push(marker);
                        }
                        clearTimeout(me.updateFn);
                    }
                });
            }, 1200);
        };

    }

});
