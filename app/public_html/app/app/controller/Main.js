Ext.define('MapApp.controller.Main', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
            main: 'main'
        },

        control: {
            "main": {
                show: 'onShow',
                returnSearch: 'onShowSearch'
            },
            ".viewport": {
                submitSearch: 'onShowMap'
            }
        }
    },

    onShow: function(target) {
        var me = this;
        me.getMain().setActiveItem(0);
    },

    onShowMap: function(values) {
        var me = this,
            main = me.getMain();
        main.setActiveItem(1);
        main.down('map_main').fireEvent('search', values);
    },

    onShowSearch: function() {
        var me = this;
        me.getMain().setActiveItem(0);
    }

});
