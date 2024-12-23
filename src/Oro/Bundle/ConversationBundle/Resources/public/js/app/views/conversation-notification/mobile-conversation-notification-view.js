define(function(require) {
    'use strict';

    const $ = require('jquery');
    const BaseView = require('oroui/js/app/views/base/view');

    const MobileConversationNotificationView = BaseView.extend({
        autoRender: true,

        /**
         * @type {number}
         */
        countNewMessages: null,

        /**
         * @inheritdoc
         */
        constructor: function MobileConversationNotificationView(options) {
            MobileConversationNotificationView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            MobileConversationNotificationView.__super__.initialize.call(this, options);
            this.countNewMessages = parseInt(options.countNewMessages);
        },

        /**
         * @inheritdoc
         */
        render: function() {
            const $conversationsMenuItem = $('#user-menu .oro-conversation-user-conversations a');
            this.$counter = $('<span class="messages-count"/>').appendTo($conversationsMenuItem);
            this.setCount(this.countNewMessages);
        },

        remove: function() {
            this.$counter.remove();
            MobileConversationNotificationView.__super__.remove.call(this);
        },

        setCount: function(count) {
            this.countNewMessages = count = parseInt(count);
            if (count === 0) {
                count = '';
            } else {
                count = '(' + (count > 99 ? '99+' : count) + ')';
            }
            this.$counter.html(count);
            $('#user-menu .dropdown-toggle').toggleClass('has-new-messages', Boolean(this.countNewMessages));
        }
    });

    return MobileConversationNotificationView;
});
