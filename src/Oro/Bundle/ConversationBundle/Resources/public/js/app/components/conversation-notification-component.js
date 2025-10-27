import _ from 'underscore';
import Backbone from 'backbone';
import tools from 'oroui/js/tools';
import BaseComponent from 'oroui/js/app/components/base/component';
import DesktopConversationNotificationView
    from 'oroconversation/js/app/views/conversation-notification/conversation-notification-collection-view';
import MobileConversationNotificationView
    from 'oroconversation/js/app/views/conversation-notification/mobile-conversation-notification-view';
import ConversationNotificationCountView
    from 'oroconversation/js/app/views/conversation-notification/conversation-notification-count-view';

const ConversationNotificationComponent = BaseComponent.extend({
    view: null,

    collection: null,

    countModel: null,

    /**
     * @inheritdoc
     */
    constructor: function ConversationNotificationComponent(options) {
        ConversationNotificationComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        _.extend(this, _.pick(options, ['countModel']));
        if (this.countModel instanceof Backbone.Model === false) {
            throw new TypeError('Invalid "countModel" option of ConversationNotificationComponent');
        }
        this.initViews(options);
    },

    initViews: function(options) {
        const ConversationNotificationView = tools.isMobile()
            ? MobileConversationNotificationView
            : DesktopConversationNotificationView;
        this.view = new ConversationNotificationView({
            el: options.listSelector ? options._sourceElement.find(options.listSelector) : options._sourceElement,
            collection: this.collection,
            countNewMessages: this.countModel.get('unreadMessagesCount'),
            folderId: options.folderId,
            hasMarkVisibleButton: Boolean(options.hasMarkVisibleButton)
        });
        let iconElement;
        if (options.iconSelector && (iconElement = options._sourceElement.find(options.iconSelector)).length) {
            this.countView = new ConversationNotificationCountView({
                el: iconElement,
                model: this.countModel
            });
        }
    },

    dispose: function() {
        delete this.collection;
        delete this.countModel;
        ConversationNotificationComponent.__super__.dispose.call(this);
    }
});

export default ConversationNotificationComponent;
