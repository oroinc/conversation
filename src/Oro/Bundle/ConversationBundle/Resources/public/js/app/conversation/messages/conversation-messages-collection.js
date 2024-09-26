import entitySync from 'oroentity/js/app/models/entity-sync';
import BaseCollection from 'oroui/js/app/models/base/collection';
import ConversationMessageModel from './conversation-message-model';
import {patchReceivedData, patchSyncOptions} from '../../utils/sync-utils';

const ConversationMessagesCollection = BaseCollection.extend({
    model: ConversationMessageModel,

    PAGE_DEFAULT: {
        number: 1,
        size: 40
    },

    comparator(model) {
        return -model.get('index');
    },

    url(method) {
        if (method === 'create') {
            return '/api/conversationmessages';
        }

        return `/api/conversations/${this.id}/messages`;
    },

    constructor: function ConversationMessagesCollection(...args) {
        this.page = {...this.PAGE_DEFAULT};
        ConversationMessagesCollection.__super__.constructor.apply(this, args);
    },

    initialize(data, options) {
        if (options.conversation) {
            this.setConversation(options.conversation);
        }

        ConversationMessagesCollection.__super__.initialize.call(this, data, options);

        this.conversation.synced(this.onUnSynced.bind(this));
    },

    isSyncing() {
        return this.conversation?.isSyncing();
    },

    onUnSynced() {
        if (!this.id || !this.conversation.get('syncMessages')) {
            return this.reset([]);
        }

        this.xhr = this.fetch({
            merge: false,
            remove: false,
            data: {
                page: {
                    number: 1,
                    size: this.page.size
                }
            }
        });
    },

    setId(id) {
        this.id = id;

        if (!id) {
            return;
        }

        this.conversation?.unsync();
    },

    setConversation(conversation) {
        this.reset();
        this.conversation = conversation;
        this.setId(conversation.id);
    },

    parse(data) {
        return patchReceivedData(data);
    },

    reset(...args) {
        this.xhr && this.xhr.abort();
        this.page = {...this.PAGE_DEFAULT};
        return ConversationMessagesCollection.__super__.reset.apply(this, args);
    },

    getParams(method, options) {
        if (method === 'read') {
            options.data.sort = '-id';
        }

        return options;
    },

    loadMessages({beforeSend, always} = {}) {
        if (this.conversation && this.length < this.conversation.getMessagesNumber()) {
            this.page.number += 1;
            if (typeof beforeSend === 'function') {
                beforeSend();
            }

            return this.fetch({
                merge: false,
                remove: false,
                data: {
                    page: this.page
                }
            }).always(() => {
                if (typeof always === 'function') {
                    always();
                }
            });
        }
    },

    sync(method, model, options) {
        if (this.conversation.isSyncing()) {
            this.xhr && this.xhr.abort();
        }

        return entitySync.call(this, method, model, {
            ...options,
            global: false,
            url: this.url(method),
            ...this.getParams(method, options)
        });
    },

    sendMessage(message) {
        return this.create(message, patchSyncOptions({
            at: 0,
            url: this.url('create'),
            error: model => model.destroy()
        }));
    },

    readMessage(model) {
        model.set('read', true);

        this.sync('update', model);
    }
});

export default ConversationMessagesCollection;
