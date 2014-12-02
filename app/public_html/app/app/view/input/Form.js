Ext.define('MapApp.view.input.Form', {
    extend: 'Ext.form.Panel',
    alias: 'widget.input_form',

    requires: [
        'Ext.form.FieldSet',
        'Ext.field.Text',
        'Ext.Button'
    ],

    config: {
        items: [
            {
                xtype: 'fieldset',
                title: '検索ワード1',
                items: [
                    {
                        xtype: 'textfield',
                        name: 'search1'
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: '検索ワード2',
                items: [
                    {
                        xtype: 'textfield',
                        name: 'search2'
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: '検索ワード3',
                items: [
                    {
                        xtype: 'textfield',
                        name: 'search3'
                    }
                ]
            },
            {
                xtype: 'button',
                itemId: 'submit',
                margin: '10 100 10',
                text: '検索'
            }
        ]
    }

});
