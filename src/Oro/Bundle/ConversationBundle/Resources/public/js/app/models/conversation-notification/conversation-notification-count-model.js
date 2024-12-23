define(function(require) {
    'use strict';

    const BaseModel = require('oroui/js/app/models/base/model');

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

    return ConversationNotificationCountModel;
});
