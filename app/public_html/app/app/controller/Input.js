Ext.define('MapApp.controller.Input', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
            input: 'input_main',
            form: 'input_form'
        },

        control: {
            ".input_main #submit": {
                tap: 'onSubmit'
            }
        }
    },

    onSubmit: function() {
        var me = this,
            form = me.getForm();
        Ext.Viewport.fireEvent('submitSearch', form.getValues());
    }

});
