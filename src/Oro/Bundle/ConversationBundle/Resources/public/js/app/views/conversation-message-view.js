import $ from 'jquery';
import _ from 'underscore';
import __ from 'orotranslation/js/translator';
import messenger from 'oroui/js/messenger';
import tinyMCE from 'tinymce/tinymce';
import mediator from 'oroui/js/mediator';
import LoadingMask from 'oroui/js/app/views/loading-mask-view';
import BaseView from 'oroui/js/app/views/base/view';

const ConversationBackendMessageView = BaseView.extend({
    events: {
        'submit form': 'onSubmit'
    },

    options: {
        formSelector: null,
        bodySelector: null,
        responseMessage: null
    },

    requiredOptions: ['formSelector', 'bodySelector', 'reloadGridName', 'reloadWidgetAlias'],

    reloadGridName: null,
    reloadWidgetAlias: null,
    loadingMask: null,
    $form: null,
    $bodyElement: null,
    $submitBtn: null,

    constructor: function ConversationBackendMessageView(options) {
        ConversationBackendMessageView.__super__.constructor.call(this, options);
    },

    initialize: function(options) {
        this.options = _.defaults(options || {}, this.options);
        const missingRequiredOptions = this.requiredOptions.filter(function(option) {
            return _.isUndefined(options[option]);
        });
        if (missingRequiredOptions.length) {
            throw new TypeError('Missing required option(s): ' + missingRequiredOptions.join(','));
        }
        this.reloadGridName = this.options.reloadGridName;
        this.reloadWidgetAlias = this.options.reloadWidgetAlias;
        this.$form = $(this.options.formSelector);
        this.$bodyElement = $(this.options.bodySelector);
        this.$submitBtn = this.$form.find('[type=submit]');
        if (this.$submitBtn === undefined) {
            throw new Error('Submit button element is missing!');
        }
    },

    onSubmit: function(event) {
        if (this.$bodyElement.val() === '') {
            event.stopPropagation();
            event.preventDefault();

            return;
        }

        const data = this.$form.serializeArray();
        const url = this.$form.attr('action');

        const options = {
            type: 'POST',
            data: data,
            dataType: 'json'
        };

        this.getLoadingMask().show(__('Saving...'));
        this.$submitBtn.attr('disabled', true);
        $.ajax(url, options)
            .done(this.onAjaxSuccess.bind(this))
            .always(this.onAjaxComplete.bind(this));

        event.stopPropagation();
        event.preventDefault();
    },

    onAjaxSuccess: function(response) {
        tinyMCE.activeEditor.setContent('');
        mediator.trigger('datagrid:doRefresh:' + this.reloadGridName);
        mediator.execute('widgets:getByAliasAsync', this.reloadWidgetAlias, function(widget) {
            widget.loadContent();
        });

        messenger.notificationMessage(
            'success',
            response.widget.message
        );
    },

    onAjaxComplete: function() {
        this.$submitBtn.removeAttr('disabled');
        if (this.loadingMask) {
            this.loadingMask.hide();
        }
    },

    getLoadingMask: function() {
        if (!this.loadingMask) {
            this.loadingMask = new LoadingMask({
                container: $('.conversation-message-form')
            });
        }
        return this.loadingMask;
    }
});

export default ConversationBackendMessageView;
