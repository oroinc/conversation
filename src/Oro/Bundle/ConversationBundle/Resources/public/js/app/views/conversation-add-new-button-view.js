import __ from 'orotranslation/js/translator';
import BaseView from 'oroui/js/app/views/base/view';
import ConversationAddNewDialogWidget from './conversation-add-new-dialog-widget';
import template from 'tpl-loader!oroconversation/templates/conversation-add-new-button-view.html';

const ConversationAddNewButtonView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'label', 'conversationName', 'sourceId', 'sourceType', 'mediatorEvents',
        'hideLabelOnMobile'
    ]),

    tagName: 'button',

    template,

    className: 'btn',

    label: __('oro.conversation.new_conversation'),

    conversationName: null,

    hideLabelOnMobile: true,

    attributes: {
        'data-role': 'conversation-create'
    },

    events: {
        click: 'onClick'
    },

    constructor: function ConversationAddNewButtonView(...args) {
        ConversationAddNewButtonView.__super__.constructor.apply(this, args);
    },

    getTemplateData() {
        return {
            ...ConversationAddNewButtonView.__super__.getTemplateData.call(this),
            label: this.label,
            hideLabelOnMobile: this.hideLabelOnMobile
        };
    },

    render() {
        this.undelegateEvents();
        ConversationAddNewButtonView.__super__.render.call(this);
        this.delegateEvents();

        return this;
    },

    onClick() {
        this.subview('conversationAddNewDialogWidget', new ConversationAddNewDialogWidget({
            model: this.model,
            conversationName: this.conversationName,
            sourceType: this.sourceType,
            sourceId: this.sourceId,
            mediatorEvents: this.mediatorEvents,
            title: this.label
        }));
        this.listenTo(
            this.subview('conversationAddNewDialogWidget'), 'conversation:created',
            () => this.trigger('conversation:created')
        );
        this.subview('conversationAddNewDialogWidget').render();
    }
});

export default ConversationAddNewButtonView;
