define(function(require) {
    'use strict';

    const _ = require('underscore');
    const sync = require('orosync/js/sync');
    const mediator = require('oroui/js/mediator');
    const ConversationNotificationCollection =
        require('oroconversation/js/app/models/conversation-notification/conversation-notification-collection');
    const ConversationNotificationCountModel =
        require('oroconversation/js/app/models/conversation-notification/conversation-notification-count-model');
    const ConversationNotificationComponent = require('oroconversation/js/app/components/conversation-notification-component');

    const UserMenuConversationNotificationComponent = ConversationNotificationComponent.extend({
        collection: null,

        countModel: null,

        /**
         * @type {Function}
         */
        notificationHandler: null,

        wsChannel: '',

        dropdownContainer: null,

        listen: {
            'sync collection': 'updateCountModel',
            'widget_dialog:open mediator': 'onWidgetDialogOpen'
        },

        /**
         * @inheritdoc
         */
        constructor: function UserMenuConversationNotificationComponent(options) {
            UserMenuConversationNotificationComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            let messages = options.messages || [];
            _.extend(this, _.pick(options, ['wsChannel']));
            if (typeof messages === 'string') {
                messages = JSON.parse(messages);
            }
            this.collection = new ConversationNotificationCollection(messages);
            this.countModel = new ConversationNotificationCountModel({unreadMessagesCount: options.count});
            this.dropdownContainer = options._sourceElement;

            this.notificationHandler = _.debounce(this._notificationHandler.bind(this), 1000);
            sync.subscribe(this.wsChannel, this.notificationHandler);

            UserMenuConversationNotificationComponent.__super__.initialize.call(this, options);
        },

        _notificationHandler: function() {
            this.collection.fetch();
            mediator.trigger('datagrid:doRefresh:user-conversation-grid');
        },

        updateCountModel: function(collection) {
            this.countModel.set('unreadMessagesCount', collection.unreadMessagesCount);
        },

        onWidgetDialogOpen: function() {
            this.dropdownContainer.trigger('tohide.bs.dropdown');
        },

        dispose: function() {
            sync.unsubscribe(this.wsChannel, this.notificationHandler);
            UserMenuConversationNotificationComponent.__super__.dispose.call(this);
        }
    });

    return UserMenuConversationNotificationComponent;
});
