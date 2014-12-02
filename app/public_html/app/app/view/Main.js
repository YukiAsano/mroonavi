Ext.define('MapApp.view.Main', {
    extend: 'Ext.Container',
    alias: 'widget.main',

    requires: [
        'MapApp.view.input.Main',
        'MapApp.view.map.Main',
        'Ext.Container'
    ],

    config: {
        layout: 'card',
        items: [
            {
                xtype: 'input_main'
            },
            {
                xtype: 'map_main'
            }
        ]
    }

});
