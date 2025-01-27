define(function(require) {
    'use strict';

    const _ = require('underscore');
    const routing = require('routing');
    const ConversationNotificationView = require('./conversation-notification-item-view');
    const BaseCollectionView = require('oroui/js/app/views/base/collection-view');
    const LoadingMask = require('oroui/js/app/views/loading-mask-view');

    const ConversationNotificationCollectionView = BaseCollectionView.extend({
        template: require(
            'tpl-loader!oroconversation/templates/conversation-notification' +
            '/conversation-notification-collection-view.html'
        ),
        itemView: ConversationNotificationView,
        animationDuration: 0,
        listSelector: '.items',
        countNewMessages: 0,
        hasMarkVisibleButton: false,
        loadingMask: null,

        listen: {
            'change:seen collection': 'onSeenStatusChange',
            'reset collection': 'onResetCollection',
            'request collection': 'onCollectionRequest',
            'sync collection': 'onCollectionSync',
            'layout:reposition mediator': 'adjustMaxHeight'
        },

        events: {click: 'onClickIconEnvelope'},

        /**
         * @inheritdoc
         */
        constructor: function ConversationNotificationCollectionView(options) {
            ConversationNotificationCollectionView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ConversationNotificationCollectionView.__super__.initialize.call(this, options);
            _.extend(this, _.pick(options, ['hasMarkVisibleButton']));
            this.countNewMessages = parseInt(options.countNewMessages);
        },

        render: function() {
            ConversationNotificationCollectionView.__super__.render.call(this);
            this.updateViewMode();
            _.defer(this.adjustMaxHeight.bind(this));
        },

        getTemplateData: function() {
            const data = ConversationNotificationCollectionView.__super__.getTemplateData.call(this);
            const visibleUnreadMessages = this.collection.filter(function(item) {
                return item.get('seen') === false;
            }).length;
            _.extend(data, _.pick(this, [
                'countNewMessages',
                'hasMarkVisibleButton']));

            data.userConversationsUrl = routing.generate(
                'oro_conversation_index',
                {
                    'conversations-grid': {
                        _parameters: {view: 'my_conversations'},
                        _filter: {
                            is_my_conversation: {value: 1},
                            status: {
                                type: 1,
                                value: ['active']
                            }
                        }
                    }
                }
            );
            data.moreUnreadMessages = Math.max(this.countNewMessages - visibleUnreadMessages, 0);
            return data;
        },

        updateViewMode: function() {
            if (!this.isActiveTypeDropDown('notification')) {
                const $iconEnvelope = this.$el.find('.dropdown-toggle .fa-envelope');
                if (this.collection.models.length === 0) {
                    this.setModeDropDownMenu('empty');
                    $iconEnvelope.removeClass('highlight');
                } else {
                    this.setModeDropDownMenu('content');
                    if (this.countNewMessages > 0) {
                        $iconEnvelope.addClass('highlight');
                    } else {
                        $iconEnvelope.removeClass('highlight');
                    }
                }
            }
        },

        adjustMaxHeight: function() {
            let rect;
            let contentRect;
            let maxHeight;
            let $list;
            if (this.el) {
                maxHeight = parseInt(this.$el.css('max-height'));
                $list = this.$list;
                if ($list.length === 1 && !isNaN(maxHeight)) {
                    rect = this.$el[0].getBoundingClientRect();
                    contentRect = $list.parent()[0].getBoundingClientRect();
                    $list.css('max-height', rect.top + maxHeight + $list.height() - contentRect.bottom + 'px');
                }
            }
        },

        onSeenStatusChange: function(model, isSeen) {
            if (isSeen && this.countNewMessages > 0) {
                this.countNewMessages--;
            } else {
                this.countNewMessages++;
            }
        },

        resetModeDropDownMenu: function() {
            this.$el.find('.dropdown-menu').removeClass('content empty notification');

            return this;
        },

        setModeDropDownMenu: function(type) {
            this.resetModeDropDownMenu();
            this.$el.find('.dropdown-menu').addClass(type);
        },

        isActiveTypeDropDown: function(type) {
            return this.$el.find('.dropdown-menu').hasClass(type);
        },

        onResetCollection: function() {
            this.collection.unreadMessagesCount = 0;
        },

        onClickIconEnvelope: function() {
            if (this.isActiveTypeDropDown('notification')) {
                this.open();
                this.setModeDropDownMenu('content');
            }
            this.updateViewMode();
        },

        isOpen: function() {
            this.$el.hasClass('show');
        },

        close: function() {
            this.$el.removeClass('show');
        },

        open: function() {
            this.$el.addClass('show');
        },

        onCollectionRequest: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
                this.loadingMask.dispose();
            }
            this.loadingMask = new LoadingMask({
                container: this.$('.content')
            });
            this.loadingMask.show();
        },

        onCollectionSync: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
                this.loadingMask.dispose();
                this.loadingMask = null;
            }
            this.render();
        },

        dispose: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
                this.loadingMask.dispose();
                this.loadingMask = null;
            }
            ConversationNotificationCollectionView.__super__.dispose.call(this);
        }
    });

    return ConversationNotificationCollectionView;
});
