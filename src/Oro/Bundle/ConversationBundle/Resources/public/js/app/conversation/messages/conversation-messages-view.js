import BaseCollectionView from 'oroui/js/app/views/base/collection-view';
import ConversationMessageView from './conversation-message-view';
import ConversationMessagesCollection from './conversation-messages-collection';
import ConversationSendMessageView from './conversation-send-message-view';
import ConversationStatusView from '../../views/conversation-status-view';
import template from 'tpl-loader!oroconversation/templates/conversation/messages/conversation-messages-view.html';
import ConversationUnreadDivider from './conversation-unread-divider';
import ConversationMessagesDateGroupsView from './conversation-messages-date-groups-view';

const ConversationMessagesView = BaseCollectionView.extend({
    itemView: ConversationMessageView,

    template,

    className: 'conversation__main',

    listSelector: '[data-role="messages-body"]',

    loadingContainerSelector: '[data-role="loading"]',

    listen: {
        'change:selected model': 'onChangeSelectedConversation',
        'sync collection': 'toggleLoadingIndicator'
    },

    constructor: function ConversationMessagesView(...args) {
        ConversationMessagesView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.collection = new ConversationMessagesCollection([], {
            conversation: this.model
        });

        ConversationMessagesView.__super__.initialize.call(this, options);
    },

    getTemplateData() {
        return {
            ...ConversationMessagesView.__super__.getTemplateData.call(this),
            ...this.model.attributes
        };
    },

    render() {
        ConversationMessagesView.__super__.render.call(this);

        if (this.model.get('selected')) {
            this.subview('unreadDivider', new ConversationUnreadDivider({
                conversation: this.model,
                collection: this.collection,
                container: this.$list,
                collectionView: this,
                initRender: true
            }));

            this.subview('conversationStatus', new ConversationStatusView({
                autoRender: true,
                model: this.model.get('selected').status,
                container: this.$('.conversation__title-container')
            }));

            if (this.model.get('selected').status && this.model.get('selected').status.isActive()) {
                this.subview('sendMessage', new ConversationSendMessageView({
                    autoRender: true,
                    collection: this.collection,
                    model: this.model,
                    container: this.$('[data-role="conversation-main"]')
                }));
            }
        }

        this.subview('conversationDatesGroup', new ConversationMessagesDateGroupsView({
            autoRender: true,
            collectionView: this,
            collection: this.collection,
            container: this.$list,
            containerMethod: 'prepend'
        }));

        this.intersectionObserver = new IntersectionObserver(entries => {
            if (entries[0].intersectionRatio <= 0) {
                return;
            }

            if (!this.$list.hasClass('rendering')) {
                this.collection.length && this.collection.loadMessages({
                    beforeSend: () => this.subview('loading').show(),
                    always: () => this.subview('loading').hide()
                });
            }
        });

        this.intersectionObserver.observe(this.$list.find('[data-role="loading-sentinel"]').get(0));

        this.$('[data-role="messages-body"]').on(`scroll${this.eventNamespace()}`, event => {
            this.scrollTop = event.currentTarget.scrollTop;
            this.scrollHeight = event.currentTarget.scrollHeight;
        });

        return this;
    },

    renderAllItems() {
        this.$list.addClass('rendering');
        ConversationMessagesView.__super__.renderAllItems.call(this);

        this.subview('conversationDatesGroup') && this.subview('conversationDatesGroup').render();
        this.subview('unreadDivider') && this.subview('unreadDivider').render();

        this.$list.removeClass('rendering');
        return this;
    },

    itemAdded(item, collection, options) {
        const view = ConversationMessagesView.__super__.itemAdded.call(this, item, collection, options);
        this.bodyScrollCompensationCallback();
        return view;
    },

    bodyScrollCompensationCallback({offset = 0} = {}) {
        const body = this.el.querySelector('[data-role="messages-body"]');
        const scrollHeight = body.scrollHeight;

        if (this.scrollTop < 0 && this.scrollHeight < scrollHeight) {
            body.classList.add('rendering');
            const delta = scrollHeight - this.scrollHeight + offset;
            body.scrollTop = this.scrollTop - delta;
            this.scrollTop = body.scrollTop;
            this.scrollHeight = body.scrollHeight;
            body.classList.remove('rendering');
        }
    },

    getItemViewByIndex(index) {
        return Object.values(this.getItemViews()).find(view => view.model.get('index') === index);
    },

    onChangeSelectedConversation(conversation) {
        this.collection.setConversation(conversation);

        this.listenTo(this.model.get('selected').status, 'change', this.render);
        this.listenTo(this.model.get('selected').participants, 'change', this.renderAllItems);

        this.render();
    },

    dispose() {
        if (this.disposed) {
            return;
        }

        this.$('[data-role="messages-body"]').off(this.eventNamespace());
        this.intersectionObserver.unobserve(this.$list.find('.conversation__intersection-sentinel').get(0));

        ConversationMessagesView.__super__.dispose.call(this);
    }
});

export default ConversationMessagesView;
