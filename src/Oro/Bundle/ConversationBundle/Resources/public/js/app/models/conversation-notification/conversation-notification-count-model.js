import BaseModel from 'oroui/js/app/models/base/model';

/**
 * @export  oroconversation/js/app/models/conversation-notification-count-model
 */
const ConversationNotificationCountModel = BaseModel.extend({
    defaults: {
        unreadMessagesCount: 0
    },

    /**
     * @inheritdoc
     */
    constructor: function ConversationNotificationCountModel(...args) {
        ConversationNotificationCountModel.__super__.constructor.apply(this, args);
    }
});

export default ConversationNotificationCountModel;
