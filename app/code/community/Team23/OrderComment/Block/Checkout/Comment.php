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


class Team23_OrderComment_Block_Checkout_Comment extends Mage_Core_Block_Template
{

    /**
     * Get label text for comment field
     *
     * @return string
     */
    public function getLabelText()
    {
        return $this->_getHelper()->__('Customer notice');
    }

    /**
     * Get field id
     *
     * @return string
     */
    public function getCommentFieldId()
    {
        return Team23_OrderComment_Model_Observer::COMMENT_FIELD_ID;
    }

    /**
     * Get module helper class
     *
     * @return Team23_OrderComment_Helper_Data
     */
    protected function _getHelper()
    {
        $helper = Mage::helper('ordercomment');

        return $helper;
    }
}