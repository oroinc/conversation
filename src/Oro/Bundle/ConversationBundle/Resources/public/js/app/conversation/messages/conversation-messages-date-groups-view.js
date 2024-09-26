import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!oroconversation/templates/conversation/messages/conversation-messages-group-view.html';
import {scrollToElementIntoView} from '../../utils/location-util';

const ConversationMessagesDateGroupsView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat(['collectionView']),

    template,

    className: 'conversation__scroll',

    attributes() {
        return {
            'data-role': 'scroll'
        };
    },

    listen: {
        'reset collection': 'render',
        'sync collection': 'render',
        'add collection': 'render',
        'layout:reposition mediator': 'render'
    },

    events: {
        'click [data-go-to]': 'onGoToMessage',
        'keyup [data-go-to]': 'onKeyup'
    },

    constructor: function ConversationMessagesDateGroupsView(...args) {
        ConversationMessagesDateGroupsView.__super__.constructor.apply(this, args);
    },

    getTemplateData() {
        return {
            groups: this.getGroups()
        };
    },

    onGoToMessage(event) {
        event.preventDefault();
        scrollToElementIntoView(this.container.find(event.target.dataset.goTo).get(0));
    },

    onKeyup(event) {
        if (event.keyCode === 13) {
            scrollToElementIntoView(this.container.find(event.target.dataset.goTo).get(0));
        }
    },

    render() {
        this.bodyHeight = this.container.get(0).getBoundingClientRect().height;
        this.container.css('--message-body-height', `${this.bodyHeight}px`);

        Object.values(this.collectionView.getItemViews()).forEach(view => view.updateHeight());

        ConversationMessagesDateGroupsView.__super__.render.call(this);
        return this;
    },

    getGroups() {
        return Object.entries(
            this.collection.groupBy(model => model.getUpdatedAtFormatted())
        ).reduce((groups, [date, models], index, collection) => {
            const [first] = models.reverse();
            models.forEach((model, index) => model.set('headOfDateGroup', index === 0));
            const height = models.reduce((height, model) => height + model.get('height'), 0);

            groups.push({
                id: first.id,
                index,
                date,
                height
            });

            return groups;
        }, []).reverse();
    }
});

export default ConversationMessagesDateGroupsView;
