import {getSelectedFromLocation} from '../../utils/location-util';
import BaseCollectionView from 'oroui/js/app/views/base/collection-view';
import ConversationListItemView from './conversation-list-item-view';
import ConversationListCollection from './conversation-list-collection';

const ConversationListView = BaseCollectionView.extend({
    optionNames: BaseCollectionView.prototype.optionNames.concat(['template', 'listSelector']),

    itemView: ConversationListItemView,

    className: 'conversation__list',

    constructor: function ConversationListView(...args) {
        ConversationListView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.collection = new ConversationListCollection([], {
            conversation: this.model
        });

        this.listenToOnce(this.collection, 'sync', this.onConversationInitSynced);

        return ConversationListView.__super__.initialize.call(this, options);
    },

    onConversationInitSynced(collection) {
        const selectedConversationId = getSelectedFromLocation();

        if (this.model && collection.length) {
            this.model.setSelected(
                collection.get(selectedConversationId) ? collection.get(selectedConversationId) : collection.first()
            );
        }
    },

    selectFirst() {
        this.model.setSelected(this.collection.first());
    },

    render() {
        ConversationListView.__super__.render.call(this);
        return this;
    }
});

export default ConversationListView;
