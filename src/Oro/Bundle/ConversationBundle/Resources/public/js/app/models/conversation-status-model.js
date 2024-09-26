import BaseModel from 'oroui/js/app/models/base/model';
import {patchReceivedData} from '../utils/sync-utils';

export const STATUS_LABEL = {
    active: 'success',
    inactive: 'warning',
    closed: 'destructive'
};

const ConversationStatusModel = BaseModel.extend({
    defaults: {
        type: 'conversationstatuses',
        name: ''
    },

    constructor: function ConversationStatusModel(...args) {
        ConversationStatusModel.__super__.constructor.apply(this, args);
    },

    preinitialize(data = {}) {
        Object.assign(data, data.attributes);
        delete data.attributes;
    },

    parse(data = {}) {
        return patchReceivedData(data);
    },

    set(data, ...rest) {
        if (typeof data === 'object') {
            data = this.parse(data);
        }

        return ConversationStatusModel.__super__.set.apply(this, [data, ...rest]);
    },

    getLabel() {
        return STATUS_LABEL[this.get('id')];
    },

    isActive() {
        return this.get('id') === 'active';
    }
}, {
    type: 'conversationstatuses',
    getFetchFields() {
        return ConversationStatusModel.fields.join();
    },
    fields: ['name']
});

export default ConversationStatusModel;
