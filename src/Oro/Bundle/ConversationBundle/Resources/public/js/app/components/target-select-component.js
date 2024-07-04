define(function(require) {
    'use strict';

    const mediator = require('oroui/js/mediator');
    const BaseComponent = require('oroui/js/app/components/base/component');
    const TargetSelectComponent = BaseComponent.extend({
        $el: null,
        inputSelector: null,
        requiredOptions: [
            'inputSelector'
        ],

        /**
         * @inheritdoc
         */
        constructor: function TargetSelectComponent(options) {
            TargetSelectComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.inputSelector = options.inputSelector;
            this.$el = options._sourceElement;

            mediator.on('source-dialog:select', this.onTargetDialogSelect, this);
        },

        onTargetDialogSelect: function(id) {
            const $input = this.$el.find(this.inputSelector);
            $input.inputWidget('val', id, true);
            $input.inputWidget('focus');
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('source-dialog:select', this.onTargetDialogSelect, this);
            mediator.off('widget_registration:source-dialog', this.onTargetDialogInit, this);

            TargetSelectComponent.__super__.dispose.call(this);
        }
    });

    return TargetSelectComponent;
});
