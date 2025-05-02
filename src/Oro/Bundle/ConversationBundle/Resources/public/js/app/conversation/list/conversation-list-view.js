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
        if (!options.collection) {
            this.collection = new ConversationListCollection([], {
                conversation: this.model
            });
        }

        return ConversationListView.__super__.initialize.call(this, options);
    },

    selectFirst() {
        this.model.setSelected(this.collection.first());
    },

    selectConversation(id) {
        if (this.collection.get(id)) {
            this.model.setSelected(this.collection.get(id));
        } else {
            this.selectFirst();
        }
    },

    render() {
        ConversationListView.__super__.render.call(this);
        return this;
    }
});

export default ConversationListView;
