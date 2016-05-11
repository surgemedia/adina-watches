/**
 * Team23_OrderComment
 *
 * @category  Team23
 * @package   Team23_OrderComment
 * @version   1.0.0
 * @copyright 2014 Team23 GmbH & Co. KG (http://www.team23.de)
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 */


/**
 * Override default review save method to append customer order comment
 */
Review.addMethods({
    save: function() {
        if (checkout.loadWaiting!=false)
            return;

        var customerNoticeForm   = $('co-customer-notice');
        var customerNoticeParams = Form.serialize(customerNoticeForm);

        checkout.setLoadWaiting('review');

        var params = Form.serialize(payment.form);

        if (this.agreementsForm)
            params += '&'+Form.serialize(this.agreementsForm);

        if (customerNoticeParams)
            params += '&'+customerNoticeParams;

        params.save = true;

        var request = new Ajax.Request(
            this.saveUrl,
            {
                method:'post',
                parameters:params,
                onComplete: this.onComplete,
                onSuccess: this.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout)
            }
        );
    }
});