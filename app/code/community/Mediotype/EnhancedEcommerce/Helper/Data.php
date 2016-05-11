<?php
/**
 * @author  Joel Hart @mediotype
 */
class Mediotype_EnhancedEcommerce_Helper_Data extends Mage_Core_Helper_Abstract{

    /**
     * Get Google Analytics UA Code
     *
     * @return string
     */
    public function getGoogleAnalyticsUA(){
        return Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
    }

}
