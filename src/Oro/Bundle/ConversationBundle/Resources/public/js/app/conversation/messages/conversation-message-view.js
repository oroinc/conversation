import _ from 'underscore';
import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!oroconversation/templates/conversation/messages/conversation-message-view.html';

const ConversationMessageView = BaseView.extend({
    template,

    className: 'conversation__message',

    userActivityTimeout: 500,

    attributes() {
        return {
            tabindex: 0,
            id: `message-${this.model.get('id')}`
        };
    },

    events: {
        mouseover: 'onUserActivity'
    },

    listen: {
        'change model': 'render'
    },

    constructor: function ConversationMessageView(...args) {
        this.onUserActivity = _.debounce(this.onUserActivity.bind(this), this.userActivityTimeout);
        ConversationMessageView.__super__.constructor.apply(this, args);
    },

    render() {
        ConversationMessageView.__super__.render.call(this);

        if (this.model.get('participant').isMe()) {
            this.$el.addClass('conversation__message--end conversation__message--own');
        } else {
            this.$el.addClass('conversation__message--start');
            this.$el.toggleClass('conversation__message--unread', !this.model.get('read'));
        }

        if (this.model.get('headOfDateGroup')) {
            this.$el.addClass('conversation__message--first-in-date-group');
            this.updateHeight();
        } else {
            this.$el.removeClass('conversation__message--first-in-date-group');
        }

        if (!this.model.get('read')) {
            this.intersectionObserver = new IntersectionObserver(entries => {
                if (entries[0].intersectionRatio > 0) {
                    return this.onUserActivity();
                }
            });

            this.intersectionObserver.observe(this.el);
        }

        return this;
    },

    updateHeight() {
        if (this.disposed) {
            return;
        }

        const styles = getComputedStyle(this.el);
        const stylesParent = this.el.parentNode ? getComputedStyle(this.el.parentNode) : 8;
        this.model.set('height',
            this.el.getBoundingClientRect().height + parseInt(styles.marginTop) + parseInt(stylesParent.gap));
    },

    onUserActivity() {
        if (!this.disposed && !this.model.get('read') && !this.model.get('participant').isMe()) {
            this.model.readMessage();
        }
    },

    dispose() {
        if (this.disposed) {
            return;
        }

        if (this.intersectionObserver) {
            this.intersectionObserver.unobserve(this.el);
            this.intersectionObserver.disconnect();
            delete this.intersectionObserver;
        }

        this.onUserActivity.cancel();

        ConversationMessageView.__super__.dispose.call(this);
    }
});

export default ConversationMessageView;
