import $ from 'jquery';
import mediator from 'oroui/js/mediator';
import routing from 'routing';
import BaseView from 'oroui/js/app/views/base/view';

const ConversationNotificationView = BaseView.extend({
    tagName: 'li',

    attributes: {
        'data-layout': 'separate'
    },

    templateSelector: '#conversation-notification-item-template',

    events: {
        'click .title': 'onClickOpenMessage'
    },

    listen: {
        'change model': 'render',
        'addedToParent': 'delegateEvents'
    },

    /**
     * @inheritdoc
     */
    constructor: function ConversationNotificationView(options) {
        ConversationNotificationView.__super__.constructor.call(this, options);
    },

    render: function() {
        ConversationNotificationView.__super__.render.call(this);
        this.$el.toggleClass('highlight', !this.model.get('seen'));
        this.initLayout();
    },

    getTemplateFunction: function() {
        if (!this.template) {
            this.template = $(this.templateSelector).html();
        }

        return ConversationNotificationView.__super__.getTemplateFunction.call(this);
    },

    onClickOpenMessage: function() {
        const url = routing.generate('oro_conversation_view', {id: this.model.get('conversationId')});
        this.model.set({seen: true});
        mediator.execute('redirectTo', {url: url});
    }
});

export default ConversationNotificationView;
