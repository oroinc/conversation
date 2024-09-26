import {escape} from 'underscore';
import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!oroconversation/templates/conversation/messages/conversation-send-message-view.html';

const ConversationSendMessageView = BaseView.extend({
    template,

    className: 'conversation__send-message',

    events: {
        'submit form': 'onSubmitMessage',
        'keyup': 'onPressEnter'
    },

    constructor: function ConversationSendMessageView(...args) {
        ConversationSendMessageView.__super__.constructor.apply(this, args);
    },

    onPressEnter(event) {
        if (event.keyCode === 13 && !event.shiftKey) {
            this.$('form').submit();
        }
    },

    onSubmitMessage(event) {
        event.preventDefault();

        const body = this.getMessageBody();
        if (body) {
            const model = this.collection.sendMessage({
                body,
                updatedAt: new Date()
            });

            model.set('body', body.split('\n').map(line => `<p>${line}</p>`).join(''));
            this.listenToOnce(model, 'destroy', () => this.$('[role="textbox"]').html(body));

            this.$('[role="textbox"]').empty();
        }
    },

    getMessageBody() {
        return escape(this.$('[role="textbox"]').text());
    }
});

export default ConversationSendMessageView;
