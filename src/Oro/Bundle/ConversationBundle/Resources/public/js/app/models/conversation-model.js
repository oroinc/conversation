import {SyncMachine} from 'chaplin';
import {updateLocationWithParams} from '../utils/location-util';
import BaseModel from 'oroui/js/app/models/base/model';

/**
 * @class ConversationModel
 * @export 'oroconversation/models/conversation-model'
 */
const ConversationModel = BaseModel.extend({
    ...SyncMachine,

    defaults: {
        id: null,
        name: '',
        sourceTitle: '',
        sourceUrl: '',
        selected: null,
        participants: null,
        hasConversations: true,
        type: '',
        disabledSync: false,
        syncMessages: true
    },

    syncTimeout: 10000,

    constructor: function ConversationModel(...args) {
        ConversationModel.__super__.constructor.apply(this, args);
    },

    initialize() {
        this.syncStateChange(this.onSyncStateChange);
    },

    /**
     * Set selected conversation from list
     * @param {ConversationListModel} selected
     */
    setSelected(selected) {
        if (selected === null || selected === void 0) {
            return this.unsetSelected();
        }

        if (selected.id === this.get('id')) {
            return;
        }

        this.update(selected);

        selected.set('selected', true);

        this.set('syncMessages', true);

        updateLocationWithParams({
            conversation_id: selected.id
        });
    },

    unsetSelected() {
        if (this.get('id')) {
            this.set('id', null);
            this.unset('name');
            this.unset('sourceUrl');
            this.unset('sourceTitle');
            this.set('syncMessages', false);
            this.get('selected').collection.setSelected();

            updateLocationWithParams({
                conversation_id: null
            });

            this.unsync();
        }
    },

    update(selected) {
        if (!selected) {
            return;
        }

        this.set({
            id: selected.id,
            name: selected.get('name'),
            sourceTitle: selected.get('sourceTitle'),
            sourceUrl: selected.get('sourceUrl'),
            selected
        });
    },

    getParticipant(id) {
        return this.get('selected').participants.get(id);
    },

    getMineParticipant() {
        return this.get('selected')?.participants.find(participant => participant.isMe());
    },

    getData(params = {}, relationships = {}) {
        return {
            data: {
                type: 'conversations',
                id: this.get('id'),
                ...params,
                relationships
            }
        };
    },

    getMessagesNumber() {
        return this.get('selected')?.get('messagesNumber') || 0;
    },

    onSyncStateChange(model, state) {
        switch (state) {
            case 'synced':
                if (this.syncTimeoutId) {
                    clearTimeout(this.syncTimeoutId);
                }
                this.syncTimeoutId = setTimeout(() => {
                    this.unsync();
                }, this.syncTimeout);
                break;
            case 'syncing':
                if (this.syncTimeoutId) {
                    clearTimeout(this.syncTimeoutId);
                }
                break;
        }
    },

    stopSync() {
        this.set('disabledSync', true);
        if (this.syncTimeoutId) {
            clearTimeout(this.syncTimeoutId);
        }
    },

    startSync() {
        this.set('disabledSync', false);
        this.unsync();
    },

    dispose() {
        if (this.disposed) {
            return;
        }

        if (this.syncTimeoutId) {
            clearTimeout(this.syncTimeoutId);
        }

        ConversationModel.__super__.dispose.call(this);
    }
});

export default ConversationModel;
