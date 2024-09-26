import __ from 'orotranslation/js/translator';
import BaseView from 'oroui/js/app/views/base/view';
import {scrollToElementIntoView} from '../../utils/location-util';

const ConversationUnreadDivider = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'participant', 'conversation', 'collectionView', 'initRender'
    ]),

    initRender: false,

    className: 'conversation__unread-messages',

    attributes: {
        'data-role': 'unread-divider'
    },

    constructor: function ConversationUnreadDivider(...args) {
        ConversationUnreadDivider.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.listenTo(this.conversation.get('participants'), 'reset change sync', this.render);
        this.listenTo(this.collection, 'sync', this.render);
        ConversationUnreadDivider.__super__.initialize.call(this, options);
    },

    render() {
        const participant = this.conversation.getMineParticipant();
        if (participant?.hasUnreadMessages(this.conversation.get('selected'))) {
            this.$el.text(__('oro.conversation.unread_messages'));

            const lastReadMessageView = this.collectionView.getItemViewByIndex(participant.get('lastReadMessageIndex'));

            if (lastReadMessageView) {
                lastReadMessageView.$el.before(this.$el);
                this.collectionView.bodyScrollCompensationCallback({
                    offset: 12
                });
            }
        } else {
            this.$el.detach();
        }

        if (this.initRender) {
            scrollToElementIntoView(this.el);
        }

        this.initRender = false;

        return this;
    }
});

export default ConversationUnreadDivider;
