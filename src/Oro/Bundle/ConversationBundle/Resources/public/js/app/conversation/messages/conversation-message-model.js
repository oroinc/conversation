import BaseModel from 'oroui/js/app/models/base/model';
import dateTimeFormatter from 'orolocale/js/formatter/datetime';
import __ from 'orotranslation/js/translator';
import {patchReceivedData} from '../../utils/sync-utils';
import {isTodayDate} from '../../utils/location-util';

const ConversationMessageModel = BaseModel.extend({
    defaults: {
        body: '',
        updatedAt: '',
        createdAt: '',
        dateTimeFormatter,
        participant: {},
        index: 0,
        read: true,
        type: 'conversationmessages',
        headOfDateGroup: false
    },

    constructor: function ConversationMessageModel(...args) {
        ConversationMessageModel.__super__.constructor.apply(this, args);
    },

    initialize(data, options) {
        this.conversation = this.collection.conversation;
        this.updateParticipant();
        this.set('read', this.conversation.getMineParticipant()?.isRead(this));

        if (this.isNew()) {
            this.set('body', this.get('body'));

            if (this.get('participant').isGhost()) {
                this.listenTo(this.get('participant').collection, 'reset', this.updateParticipant);
            }
        }

        ConversationMessageModel.__super__.initialize.call(this, data, options);
    },

    parse(data = {}) {
        return patchReceivedData(data);
    },

    updateParticipant() {
        this.set('participant', this.getMessageParticipant());
    },

    getMessageParticipant() {
        if (this.isNew()) {
            return this.conversation.getMineParticipant();
        }

        return this.conversation.getParticipant(this.get('relationships').participant.data.id);
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

    toJSON() {
        const relationships = {
            conversation: this.conversation.getData()
        };

        if (!this.get('participant').get('ghost')) {
            relationships.participant = this.get('participant').getData();
        }

        return this.getData({
            attributes: {
                body: this.get('body')
            },
            relationships
        });
    },

    readMessage() {
        this.set('read', true);
        this.conversation.getMineParticipant()?.participantReadMessage(this);
    },

    getUpdatedAtFormatted() {
        if (isTodayDate(this.get('updatedAt'))) {
            return __('Today');
        }

        return dateTimeFormatter.formatSmartDateTime(this.get('updatedAt'));
    }
}, {
    type: 'conversationmessages'
});

export default ConversationMessageModel;
