import routing from 'routing';
import ConversationListItemView from './conversation-list-item-view';
import template from 'tpl-loader!oroconversation/templates/conversation/list/conversation-list-item-link-view.html';

const ConversationListItemLinkView = ConversationListItemView.extend({
    optionNames: ConversationListItemView.prototype.optionNames.concat(['routeName']),

    template,

    routeName: 'oro_conversation_frontend_conversation_index',

    attributes() {
        return {
            href: routing.generate(this.routeName, {
                conversation_id: this.model.id
            })
        };
    },

    constructor: function ConversationListItemLinkView(...args) {
        ConversationListItemLinkView.__super__.constructor.apply(this, args);
    },

    getTemplateData() {
        return {
            ...ConversationListItemLinkView.__super__.getTemplateData.call(this),
            href: routing.generate(this.routeName, {
                conversation_id: this.model.id
            })
        };
    }
});

export default ConversationListItemLinkView;
