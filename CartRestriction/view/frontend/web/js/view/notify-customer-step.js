define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer
    ) {
        'use strict';
        /**
         * Just display notice
         */
        return Component.extend({
            defaults: {
                template: 'Practice_CartRestriction/notify-customer'
            },

            isVisible: ko.observable(true),
            notifyStepToCustomer: customer.isLoggedIn(),
            stepCode: 'notify',
            stepTitle: 'Notice',

            /**
             * @returns {*}
             */
            initialize: function () {
                this._super();
                stepNavigator.registerStep(
                    this.stepCode,
                    null,
                    this.stepTitle,
                    this.isVisible,

                    _.bind(this.navigate, this),

                    5
                );

                return this;
            },

            /**
             * The navigate() method is responsible for navigation between checkout step
             * during checkout. You can add custom logic, for example some conditions
             * for switching to your custom step
             */
            navigate: function () {

            },

            /**
             * @returns void
             */
            navigateToNextStep: function () {
                stepNavigator.next();
            }
        });
    }
);
