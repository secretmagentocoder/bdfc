/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

tinyMCE.addI18n({en:{
    magentovariable:
    {
        insert_variable : "Insert Variable"
    }
}});

(function() {
    tinymce4.create('tinymce.plugins.MagentovariablePlugin', {
        /**
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            ed.addCommand('mceMagentovariable', function() {
                var pluginSettings = ed.settings.magentoPluginsOptions.get('magentovariable');
                MagentovariablePlugin.setEditor(ed);
                MagentovariablePlugin.loadChooser(pluginSettings.url, null);
            });

            // Register Widget plugin button
            ed.addButton('magentovariable', {
                title : 'magentovariable.insert_variable',
                cmd : 'mceMagentovariable',
                image : url + '/img/icon.gif'
            });
        },

        getInfo : function() {
            return {
                longname : 'Magento Variable Manager Plugin for TinyMCE 3.x',
                author : 'Magento Core Team',
                authorurl : 'http://magentocommerce.com',
                infourl : 'http://magentocommerce.com',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce4.PluginManager.add('magentovariable', tinymce4.plugins.MagentovariablePlugin);
})();
