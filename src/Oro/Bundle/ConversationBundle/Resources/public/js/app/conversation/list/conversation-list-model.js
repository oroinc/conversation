import BaseModel from 'oroui/js/app/models/base/model';
import ConversationParticipantCollection from '../participants/conversation-participant-collection';
import ConversationStatusModel from '../../models/conversation-status-model';

const ConversationListModel = BaseModel.extend({
    defaults: {
        id: null,
        updatedAt: '',
        name: '',
        sourceTitle: '',
        sourceUrl: '',
        selected: false,
        lastMassage: null,
        unreadMessagesCount: null
    },

    constructor: function ConversationListModel(...args) {
        ConversationListModel.__super__.constructor.apply(this, args);
    },

    initialize(data = {}, options) {
        this.participants = new ConversationParticipantCollection(data.participants || []);
        this.status = new ConversationStatusModel(data.status || {});

        ConversationListModel.__super__.initialize.apply(this, data, options);
    },

    parse(data) {
        if (this.status && data.status) {
            this.status.set(data.status);
        }

        if (this.participants && data.participants) {
            this.participants.reset(data.participants);
        }

        return data;
    },

    serialize() {
        return this.getAttributes();
    },

    getAttributes() {
        return {
            ...ConversationListModel.__super__.getAttributes.call(this),
            getUnreadMessagesCount: () => this.participants.getMine()?.unreadMessagesCount(this)
        };
    },

    setSelected(status) {
        this.set('selected', status);
    }
}, {
    type: 'conversations',
    getFetchFields() {
        return ConversationListModel.fields.join();
    },
    fields: [
        'name', 'messagesNumber', 'lastMessage', 'participants', 'status', 'sourceTitle', 'sourceUrl', 'updatedAt'
    ]
});

export default ConversationListModel;
