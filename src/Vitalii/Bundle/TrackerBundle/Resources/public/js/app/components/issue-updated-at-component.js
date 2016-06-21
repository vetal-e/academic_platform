define(function(require) {
    'use strict';

    var BaseComponent = require('oroui/js/app/components/base/component');
    var widgetManager = require('oroui/js/widget-manager');

    var IssueUpdatedAtComponent = BaseComponent.extend({
        initialize: function(options) {
            require(['oroui/js/widget-manager', 'oroui/js/mediator'],
            function(widgetManager, mediator) {
                widgetManager.getWidgetInstanceByAlias('tracker_issue_updated_at', function(widget) {
                    mediator.on('widget_success:note-dialog', function() {
                        widget.render();
                        mediator.trigger('datagrid:doRefresh:issue-collaborators-grid');
                    });
                });
            });
        }
    });

    return IssueUpdatedAtComponent;
});
