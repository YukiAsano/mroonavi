Ext.define('MapApp.view.input.Main', {
    extend: 'Ext.Container',
    alias: 'widget.input_main',

    requires: [
        'MapApp.view.input.Form',
        'Ext.Toolbar',
        'Ext.form.Panel'
    ],

    config: {
        layout: 'fit',
        items: [
            {
                xtype: 'toolbar',
                docked: 'top',
                title: '検索'
            },
            {
                xtype: 'input_form'
            }
        ]
    }

});
