Ext.define('MapApp.view.map.Main', {
    extend: 'Ext.Container',
    alias: 'widget.map_main',

    requires: [
        'MapApp.view.map.Map',
        'Ext.Toolbar',
        'Ext.Button',
        'Ext.Map'
    ],

    config: {
        layout: 'fit',
        items: [
            {
                xtype: 'toolbar',
                docked: 'top',
                title: '結果',
                items: [
                    {
                        xtype: 'button',
                        itemId: 'return',
                        ui: 'back',
                        text: '戻る'
                    }
                ]
            },
            {
                xtype: 'map_map',
                height: '100%',
                width: '100%',
                useCurrentLocation: false
            }
        ]
    }

});
