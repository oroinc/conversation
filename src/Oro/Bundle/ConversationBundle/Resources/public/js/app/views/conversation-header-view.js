import BaseView from 'oroui/js/app/views/base/view';
import viewportManager from 'oroui/js/viewport-manager';
import template from 'tpl-loader!oroconversation/templates/conversation-header-view.html';
import templateTabletSmall from 'tpl-loader!oroconversation/templates/conversation-header-view-tablet-small.html';
import ConversationAddNewButtonView from './conversation-add-new-button-view';

const ConversationHeaderView = BaseView.extend({
    template,

    templateTabletSmall,

    className: 'conversation__header',

    listen: {
        'change:id model': 'render',
        'viewport:tablet-small mediator': 'render'
    },

    events: {
        'click [data-role="back"]': 'onClickBackButton'
    },

    constructor: function ConversationHeaderView(...args) {
        ConversationHeaderView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.subview('conversationAddNewButtonView', new ConversationAddNewButtonView({
            model: this.model
        }));
        ConversationHeaderView.__super__.initialize.call(this, options);
    },

    getTemplateFunction(templateKey) {
        if (viewportManager.isApplicable('tablet-small')) {
            templateKey = 'templateTabletSmall';
        }

        return ConversationHeaderView.__super__.getTemplateFunction.call(this, templateKey);
    },

    onClickBackButton() {
        this.model.setSelected(null);
    },

    render() {
        this.undelegateEvents();
        ConversationHeaderView.__super__.render.call(this);

        this.subview('conversationAddNewButtonView').render().$el.appendTo(this.$('[data-role="actions"]'));
        this.delegateEvents();
        return this;
    }
});

export default ConversationHeaderView;
