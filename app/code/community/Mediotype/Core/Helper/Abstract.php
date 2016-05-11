<?php
/**
 *
 * @author      Joel Hart
 */

class Mediotype_Core_Helper_Abstract extends Mage_Core_Helper_Abstract
{

    /**
     * @param $xmlNode xml path to config group
     *
     * @return array system config values ($key => $value)
     */
    public function getExtensionSystemConfig($xmlNode)
    {
        if (Mage::helper('mediotype_core/debugger')->getEnabled()) {
            Mediotype_Core_Helper_Debugger::log(array("xmlnode" => $xmlNode));
        }
        return Mage::getStoreConfig($xmlNode);
    }
}
