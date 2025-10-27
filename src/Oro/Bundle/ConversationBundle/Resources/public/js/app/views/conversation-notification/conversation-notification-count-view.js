import BaseView from 'oroui/js/app/views/base/view';
import template
    from 'tpl-loader!oroconversation/templates/conversation-notification/conversation-notification-icon-view.html';

const ConversationNotificationCountView = BaseView.extend({
    autoRender: true,

    listen: {
        'change:unreadMessagesCount model': 'render'
    },

    template,

    /**
     * @inheritdoc
     */
    constructor: function ConversationNotificationCountView(options) {
        ConversationNotificationCountView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    getTemplateData: function() {
        const data = ConversationNotificationCountView.__super__.getTemplateData.call(this);

        if (data.unreadMessagesCount === void 0) {
            data.unreadMessagesCount = 0;
        }

        return data;
    }
});

export default ConversationNotificationCountView;
