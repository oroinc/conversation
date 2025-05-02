import $ from 'jquery';
import mediator from 'oroui/js/mediator';
import FrontendDialogWidget from 'oro/dialog-widget';
import template from 'tpl-loader!oroconversation/templates/conversation-add-new-dialog-widget.html';

const ConversationAddNewDialogWidget = FrontendDialogWidget.extend({
    options: {
        ...FrontendDialogWidget.prototype.options,
        preventModelRemoval: true,
        url: false,
        defaultSubject: null,
        dialogOptions: {
            modal: true,
            resizable: false,
            maxWidth: 500,
            autoResize: true
        }
    },

    events: {
        'submit form': 'onSubmit',
        'change input, textarea': 'onChangeInputs'
    },

    constructor: function ConversationAddNewDialogWidget(...args) {
        ConversationAddNewDialogWidget.__super__.constructor.apply(this, args);
    },

    render() {
        const {conversationName} = this.options;

        this.$el.append(template({
            conversationName
        }));

        ConversationAddNewDialogWidget.__super__.render.call(this);

        this.$('form').validate();

        return this;
    },

    getRelationships() {
        const relationships = {
            messages: {
                data: [{
                    type: 'conversationmessages',
                    id: 'message-1'
                }]
            }
        };

        if (this.options.sourceType && this.options.sourceId) {
            Object.assign(relationships, {
                source: {
                    data: {
                        type: this.options.sourceType,
                        id: this.options.sourceId
                    }
                }
            });
        }

        return relationships;
    },

    getJsonData() {
        return {
            data: {
                type: 'conversations',
                attributes: {
                    name: this.$('[name="conversation-name"]').val().trim()
                },
                relationships: this.getRelationships()
            },
            included: [{
                type: 'conversationmessages',
                id: 'message-1',
                attributes: {
                    body: this.$('[name="conversation-message"]').val().trim()
                }
            }]
        };
    },

    blockActions(status) {
        Object.values(this.actions.adopted).forEach(action => action.prop('disabled', status));
    },

    onChangeInputs(event) {
        if (!event.target.value.trim()) {
            event.target.value = '';
        }
    },

    onSubmit(event) {
        event.preventDefault();

        if (!this.$('form').valid()) {
            return;
        }

        this.blockActions(true);

        $.ajax({
            method: 'post',
            headers: {
                contentType: 'application/vnd.api+json',
                Accept: 'application/vnd.api+json'
            },
            url: '/api/conversations',
            data: this.getJsonData()
        }).done(({data}) => {
            if (this.options.mediatorEvents) {
                this.options.mediatorEvents.forEach(event => mediator.trigger(event));
            }
            this.model?.unsync();
            this.model?.once('synced', model => model.get('selected').collection.setSelectedById(data.id));
            this.trigger('conversation:created');
            this.widget && this.widget.dialog('close');
        }).always(() => {
            this.blockActions(false);
        });
    }
});

export default ConversationAddNewDialogWidget;
