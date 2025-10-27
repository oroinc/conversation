import BaseModel from 'oroui/js/app/models/base/model';

/**
 * @export  oroconversation/js/app/models/conversation-notification-model
 */
const ConversationNotificationModel = BaseModel.extend({
    id: '',
    conversationId: '',
    seen: '',
    conversationName: '',
    message: '',
    fromName: '',
    messageTime: '',

    /**
     * @inheritdoc
     */
    constructor: function ConversationNotificationModel(...args) {
        ConversationNotificationModel.__super__.constructor.apply(this, args);
    }
});

export default ConversationNotificationModel;
