<?php

/**
 * Team23_OrderComment
 *
 * @category  Team23
 * @package   Team23_OrderComment
 * @version   1.0.0
 * @copyright 2014 Team23 GmbH & Co. KG (http://www.team23.de)
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 */


class Team23_OrderComment_Model_Observer
{

    const COMMENT_FIELD_ID = 'customer_order_comment';

    /**
     * Check if customer notice should be added to order.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addCustomerCommentToOrder(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        /** @var Mage_Core_Controller_Request_Http $request */
        $request = Mage::app()->getRequest();

        if (!is_object($order) || !$request)
            return $this;

        $comment = strip_tags($request->getParam(self::COMMENT_FIELD_ID));

        if (!empty($comment))
            $this->_addCustomerCommentToOrder($order, $comment);

        return $this;
    }

    /**
     * Add customer notice to order.
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $comment
     * @return $this
     */
    protected function _addCustomerCommentToOrder($order, $comment)
    {
        $value = '<strong>' . Mage::helper('ordercomment')->__('Customer notice') . '</strong><br/>' . $comment;

        $order->setCustomerNote($value);

        return $this;
    }
}