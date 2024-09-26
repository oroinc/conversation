import entitySync from 'oroentity/js/app/models/entity-sync';
import BaseModel from 'oroui/js/app/models/base/model';
import {patchReceivedData} from '../../utils/sync-utils';

const ConversationParticipantModel = BaseModel.extend({
    defaults: {
        author: '',
        authorAcronym: '',
        authorType: 'User',
        isMe: false,
        lastReadMessageIndex: null,
        lastReadDate: '',
        createdAt: '',
        updatedAt: '',
        ghost: false,
        type: 'conversationparticipants'
    },

    constructor: function ConversationParticipantModel(...args) {
        ConversationParticipantModel.__super__.constructor.apply(this, args);
    },

    parse(data = {}) {
        return patchReceivedData(data);
    },

    preinitialize(data) {
        patchReceivedData(data);
    },

    sync(method, model, options) {
        this.xhr && this.xhr.abort();
        this.xhr = entitySync.call(this, method, model, {
            ...options,
            global: false,
            always: () => {
                delete this.xhr;
            }
        });

        return this.xhr;
    },

    isMe() {
        return this.get('isMe');
    },

    isGhost() {
        return this.get('ghost');
    },

    isRead(message) {
        return message.get('index') <= this.get('lastReadMessageIndex');
    },

    hasUnreadMessages(conversation) {
        return this.get('lastReadMessageIndex') < conversation.get('messagesNumber');
    },

    unreadMessagesCount(conversation) {
        return conversation.get('messagesNumber') - this.get('lastReadMessageIndex');
    },

    getData({relationships = {}, ...params} = {}) {
        return {
            data: {
                id: this.get('id'),
                type: this.get('type'),
                ...params,
                relationships
            }
        };
    },

    toJSON(options) {
        if (options.patch && options.readMessage) {
            return this.getData({
                relationships: {
                    lastReadMessage: options.readMessage.getData()
                }
            });
        }

        return ConversationParticipantModel.__super__.toJSON.call(this, options);
    },

    participantReadMessage(readMessage) {
        this.save(null, {
            patch: true,
            readMessage
        });
    }
}, {
    type: 'conversationparticipants',
    getFetchFields() {
        return ConversationParticipantModel.fields.join();
    },
    fields: ['lastReadMessageIndex', 'lastReadDate', 'isMe', 'author', 'authorAcronym', 'authorType', 'lastReadMessage']
});

export default ConversationParticipantModel;
