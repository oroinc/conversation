define(function(require) {
    'use strict';

    const ConversationNotificationModel = require('./conversation-notification-model');
    const RoutingCollection = require('oroui/js/app/models/base/routing-collection');

    /**
     * @export oroconversation/js/app/models/conversation-notification-collection
     */
    const ConversationNotificationCollection = RoutingCollection.extend({
        model: ConversationNotificationModel,

        routeDefaults: {
            routeName: 'oro_conversation_last_user_conversations',
            routeQueryParameterNames: ['onlyData'],
            onlyData: 1
        },

        /**
         * @inheritdoc
         */
        constructor: function ConversationNotificationCollection(...args) {
            ConversationNotificationCollection.__super__.constructor.apply(this, args);
        },

        setRouteParams: function(params) {
            this._route.set({
                limit: params.limit
            });
        },

        parse: function(response, q) {
            if (this.disposed) {
                return;
            }

            this.unreadMessagesCount = response.unreadMessagesCount;
            // format response to regular backbone one
            response = {
                data: response.messages
            };

            return ConversationNotificationCollection.__super__.parse.call(this, response, q);
        }
    });

    return ConversationNotificationCollection;
});
