import entitySync from 'oroentity/js/app/models/entity-sync';
import BaseCollection from 'oroui/js/app/models/base/collection';
import ConversationParticipantModel from './conversation-participant-model';

const ConversationParticipantCollection = BaseCollection.extend({
    model: ConversationParticipantModel,

    url: '/api/conversationparticipants',

    constructor: function ConversationParticipantCollection(...args) {
        ConversationParticipantCollection.__super__.constructor.apply(this, args);
    },

    initialize(data, options) {
        ConversationParticipantCollection.__super__.initialize.call(this, data, options);

        this.listenTo(this, 'reset', this.handleCollection);
        this.handleCollection();
    },

    handleCollection() {
        if (!this.find(model => model.isMe())) {
            this.add({
                isMe: true,
                ghost: true
            });
        }
    },

    sync(method, model, options) {
        return entitySync.call(this, method, model, {
            ...options,
            global: false
        });
    },

    getMine() {
        return this.find(participant => participant.isMe());
    }
});

export default ConversationParticipantCollection;
