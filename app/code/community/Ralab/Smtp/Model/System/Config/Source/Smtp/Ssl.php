<?php

/**
 *
 * @package     Ralab_Smtp
 * @author      Kalpesh Balar <kalpeshbalar@gmail.com>
 * @copyright   Ralab (http://ralab.in)
 *
 */

class Ralab_Smtp_Model_System_Config_Source_Smtp_Ssl
{
    public function toOptionArray()
    {
    	// http://framework.zend.com/manual/current/en/modules/zend.mail.smtp.options.html
        return array(
            "none"   => Mage::helper('adminhtml')->__('None'),
            "ssl"   => Mage::helper('adminhtml')->__('SSL'),
            "tls"   => Mage::helper('adminhtml')->__('TLS')
        );
    }
}
