import entitySync from 'oroentity/js/app/models/entity-sync';
import BaseCollection from 'oroui/js/app/models/base/collection';
import ConversationListModel from './conversation-list-model';
import ConversationParticipantModel from '../participants/conversation-participant-model';
import ConversationStatusModel from '../../models/conversation-status-model';
import ConversationMessageModel from '../messages/conversation-message-model';

const ConversationListCollection = BaseCollection.extend({
    url: '/api/conversations',

    model: ConversationListModel,

    constructor: function ConversationListCollection(...args) {
        ConversationListCollection.__super__.constructor.apply(this, args);
    },

    comparator(prevModel, nextModel) {
        return new Date(nextModel.get('updatedAt')) - new Date(prevModel.get('updatedAt'));
    },

    initialize(data, options) {
        if (options.conversation) {
            this.conversation = options.conversation;
        }

        ConversationListCollection.__super__.initialize.call(this, data, options);

        this.conversation?.synced(this.onSynced.bind(this));
        this.conversation?.unsynced(this.onUnSynced.bind(this));
    },

    onUnSynced() {
        this.fetch();
    },

    parse(response) {
        if (response.included) {
            const {data} = response;

            data.forEach(item => {
                item.lastMassage = response.included
                    .filter(inc => inc.type === ConversationMessageModel.type)
                    .find(inc => item.relationships.lastMessage?.data?.id === inc.id);

                item.participants = response.included
                    .filter(inc => inc.type === ConversationParticipantModel.type)
                    .filter(inc =>
                        item.relationships.participants.data.find(participant => participant.id === inc.id)
                    );

                item.status = response.included
                    .filter(inc =>
                        inc.type === ConversationStatusModel.type && inc.id === item.relationships.status.data.id
                    ).pop();

                Object.assign(item, item.attributes);
                delete item.attributes;
            });
        }

        return response.data;
    },

    sync(method, model, options) {
        this.conversation?.beginSync();

        return entitySync.call(this, method, model, {
            ...options,
            url: this.url,
            global: false,
            data: {
                ...this.getFetchDataOptions(),
                fields: this.getRequestFields(),
                include: this.getRequestIncludes(),
                ...options?.data
            },
            complete: () => this.conversation?.finishSync()
        });
    },

    getFetchDataOptions() {
        return {
            sort: '-id',
            page: {
                size: 999
            }
        };
    },

    getRequestIncludes() {
        return 'lastMessage,participants,status';
    },

    getRequestFields() {
        return {
            conversationmessages: 'plainBody',
            conversationparticipants: ConversationParticipantModel.getFetchFields(),
            conversations: ConversationListModel.getFetchFields(),
            conversationstatuses: ConversationStatusModel.getFetchFields()
        };
    },

    onSynced() {
        this.conversation?.set('hasConversations', !!this.length);
    },

    getSelected() {
        return this.find(model => model.get('selected'));
    },

    setSelected(selected) {
        this.invoke('setSelected', false);

        if (!selected) {
            return this.conversation?.setSelected(null);
        }

        selected.setSelected(true);
        this.conversation?.setSelected(selected);
    },

    setSelectedById(id) {
        if (this.get(id)) {
            this.setSelected(this.get(id));
        }
    }
});

export default ConversationListCollection;
