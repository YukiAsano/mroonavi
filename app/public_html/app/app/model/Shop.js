Ext.define('MapApp.model.Shop', {
    extend: 'Ext.data.Model',

    requires: [
        'Ext.data.Field',
        'Ext.data.proxy.Ajax',
        'Ext.data.reader.Json'
    ],

    config: {
        fields: [
            {
                name: 'thumbnail'
            },
            {
                name: 'name'
            },
            {
                name: 'address'
            },
            {
                name: 'lat'
            },
            {
                name: 'lng'
            },
            {
                name: 'url'
            },
            {
                name: 'coupon'
            },
            {
                name: 'tel'
            }
        ],
        proxy: {
            type: 'ajax',
            url: '/data/index.php?type=shop',
            reader: {
                type: 'json',
                rootProperty: 'items'
            }
        }
    }
});
