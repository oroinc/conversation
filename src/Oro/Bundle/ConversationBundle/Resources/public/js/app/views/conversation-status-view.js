import BaseView from 'oroui/js/app/views/base/view';
import ConversationStatusModel from '../models/conversation-status-model';

const ConversationStatusView = BaseView.extend({
    constructor: function ConversationStatusView(...args) {
        ConversationStatusView.__super__.constructor.apply(this, args);
    },

    listen: {
        'change model': 'render'
    },

    className() {
        let className = `conversation__status`;

        if (this.model) {
            className += ` status-label status-label--${this.model.getLabel()}`;
        }

        return className;
    },

    initialize(options) {
        if (!options.model) {
            this.model = new ConversationStatusModel(options);
        }
    },

    render() {
        ConversationStatusView.__super__.render.apply(this);

        this.$el.text(this.model.get('name'));
        this.$el.attr('class', this.className());

        return this;
    }
});

export default ConversationStatusView;
