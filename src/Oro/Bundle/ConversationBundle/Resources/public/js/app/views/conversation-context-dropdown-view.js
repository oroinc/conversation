import __ from 'orotranslation/js/translator';
import BaseView from 'oroui/js/app/views/base/view';
import ConversationListView from '../conversation/list/conversation-list-view';
import conversationListTemplate from
    'tpl-loader!oroconversation/templates/conversation/list/conversation-list-dropdown-view.html';
import ConversationAddNewButtonView from './conversation-add-new-button-view';
import ConversationListItemLinkView from '../conversation/list/conversation-list-item-link-view';
import LoadingMaskView from 'oroui/js/app/views/loading-mask-view';

const ConversationContextDropdownView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'conversationName', 'hasConversations', 'sourceType', 'sourceId',
        'addConversation'
    ]),

    hasConversations: false,

    addConversation: true,

    conversationName: '',

    sourceType: '',

    sourceId: '',

    initialLoad: true,

    attributes() {
        const attrs = {};

        if (this.hasConversations) {
            attrs['data-toggle'] = 'dropdown';
        }

        return attrs;
    },

    events: {
        'shown.bs.dropdown': 'onOpenDropdown'
    },

    constructor: function ConversationContextDropdownView(...args) {
        ConversationContextDropdownView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.subview('conversationList', new ConversationListView({
            className: 'conversation__list--dropdown',
            template: conversationListTemplate,
            listSelector: '[data-role="conversation-list"]',
            itemView: ConversationListItemLinkView
        }));

        if (this.addConversation) {
            this.subview('addNewConversation', new ConversationAddNewButtonView({
                className: 'btn btn--full',
                conversationName: this.conversationName,
                sourceType: this.sourceType,
                sourceId: this.sourceId,
                label: __('oro.conversation.ask_new_question')
            }));
        }

        ConversationContextDropdownView.__super__.initialize.call(this, options);
    },

    render() {
        ConversationContextDropdownView.__super__.render.call(this);

        this.subview('conversationList').render().$el.appendTo(this.$('[data-role="conversations"]'));

        if (this.subview('addNewConversation')) {
            this.subview('addNewConversation').render().$el.appendTo(this.$('[data-role="conversation-actions"]'));
        }

        this.subview('loadingMask', new LoadingMaskView({
            container: this.$('[data-role="conversation-list"]')
        }));

        return this;
    },

    onOpenDropdown() {
        this.updateConversationList();
    },

    updateConversationList() {
        if (!this.subview('conversationList')) {
            return;
        }

        this.initialLoad && this.subview('loadingMask').show();
        this.subview('conversationList').collection.fetch({
            data: {
                page: {
                    size: 10
                },
                filter: {
                    source: {
                        [`${this.sourceType}`]: this.sourceId
                    }
                }
            }
        }).done(({data}) => {
            this.initialLoad = false;
            this.hasConversations = !!data.length;
        }).always(() => this.subview('loadingMask').hide());
    }
});

export default ConversationContextDropdownView;
