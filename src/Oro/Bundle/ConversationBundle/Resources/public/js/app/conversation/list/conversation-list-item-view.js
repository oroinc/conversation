import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!oroconversation/templates/conversation/list/conversation-list-item-view.html';
import {scrollToElementIntoView} from '../../utils/location-util';

const ConversationListItemView = BaseView.extend({
    template,

    className: 'conversation__list-item',

    attributes: {
        tabindex: 0
    },

    events: {
        click: 'onClick',
        keyup: 'onKeyup'
    },

    listen: {
        'change model': 'render'
    },

    constructor: function ConversationListItemView(...args) {
        ConversationListItemView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.collection = this.model.collection;
        this.listenTo(this.model.status, 'change', this.render);
        this.listenTo(this.model.participants, 'change sync', this.render);
        ConversationListItemView.__super__.initialize.call(this, options);
    },

    render() {
        ConversationListItemView.__super__.render.call(this);

        this.$el.toggleClass('conversation__list-item--selected', this.model.get('selected'));
        if (this.model.get('selected')) {
            setTimeout(() => scrollToElementIntoView(this.el));
        }

        this.$el.toggleClass('conversation__list-item--has-unread',
            this.model.participants.getMine()?.hasUnreadMessages(this.model)
        );
        if (this.model.status.previous('id')) {
            this.$el.removeClass(`conversation__list-item--${this.model.status.previous('id')}`);
        }
        this.$el.addClass(`conversation__list-item--${this.model.status.id}`);

        return this;
    },

    onClick() {
        this.collection.setSelected(this.model);
    },

    onKeyup(event) {
        if (event.keyCode === 13) {
            this.collection.setSelected(this.model);
        }
    }
});

export default ConversationListItemView;
