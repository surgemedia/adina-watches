<?php
/**
 * Product View Block For Google Enhanced Ecommerce Analytics
 *
 * @author  Joel Hart @mediotype
 */
class Mediotype_EnhancedEcommerce_Block_Catalog_Product_View extends Mage_Core_Block_Template{

    /**
     * Used to return the current product being viewed for aggregating enhanced ecommerce data
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getCurrentProduct()
    {
        return Mage::registry('current_product');
    }

}
