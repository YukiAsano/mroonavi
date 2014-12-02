// @require @packageOverrides
Ext.Loader.setConfig({

});


Ext.application({
    models: [
        'Shop'
    ],
    stores: [
        'Shops'
    ],
    views: [
        'Main',
        'input.Main',
        'map.Main',
        'input.Form',
        'map.Map'
    ],
    controllers: [
        'Main',
        'Input',
        'Map'
    ],
    name: 'MapApp',

    launch: function() {

        Ext.create('MapApp.view.Main', {fullscreen: true});
    }

});
