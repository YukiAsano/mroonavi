Ext.define('MapApp.store.Shops', {
    extend: 'Ext.data.Store',

    requires: [
        'MapApp.model.Shop'
    ],

    config: {
        model: 'MapApp.model.Shop',
        storeId: 'Shops'
    }
});
