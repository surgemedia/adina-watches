<?php  
    class Tallpaul_CategoryShipping_Model_Carrier_Categoryshipping     
		extends Mage_Shipping_Model_Carrier_Abstract
		implements Mage_Shipping_Model_Carrier_Interface
	{  
        protected $_code = 'categoryshipping';  
		
		
		private function countryMatches($rule_country,$destination_country){
			$eu_countries = Mage::getModel('directory/country')->getResourceCollection()->loadByStore();

			$eu_countries_array = explode(',',$eu_countries);			
			if ($rule_country == "UK" && $destination_country == "GB")
				return true;		
			if ($rule_country == $destination_country)
				return true;		
			if ($rule_country == "EUR" && in_array($destination_country,$eu_countries_array))
				return true;
			if ($rule_country == "WORLD" && $rule_country != "AU")
				return true;
			if ($rule_country == "AU")
				return true;
			return false;
		}
      
        /** 
        * Collect rates for this shipping method based on information in $request 
        * 
        * @param Mage_Shipping_Model_Rate_Request $data 
        * @return Mage_Shipping_Model_Rate_Result 
        */  
        public function collectRates(Mage_Shipping_Model_Rate_Request $request){
        	$rules_result = false;
        	//get request destination country
        	$destination_country = $request->getDestCountryId();
        	foreach ($request->getAllItems() as $item) {
        		$categories = $item->getProduct()->getCategoryCollection()->addAttributeToSelect("shipping");
				foreach ($categories as $category){
					$rules = $category->getData('shipping');	
					$rule_lines = explode("\r\n",$rules);
					$rule_matched = false;
					foreach ($rule_lines as $lines){
						if (!$rule_matched){
							list($rule_country,$price) = explode("=",$lines);
							if ($this->countryMatches($rule_country,$destination_country) && $price == "-1")
								return false;														
							if ($this->countryMatches($rule_country,$destination_country) && ($rules_result == false || (float)$price > $rules_result)){
								$rules_result = (float)$price;
								$rule_matched = true;
							} 
						}						
					}						
				}				
			}
			
			if ($rules_result !== false) {       	
            	$result = Mage::getModel('shipping/rate_result');  
            	$method = Mage::getModel('shipping/rate_result_method');  
            	$method->setCarrier($this->_code);  
            	$method->setCarrierTitle($this->getConfigData('title'));
            	$method->setMethod($this->_code);  
            	$method->setMethodTitle($this->getConfigData('name'));
		    	$method->setPrice($rules_result);
				$method->setCost($rules_result);
            	$result->append($method);  
            	return $result;  
			} else {
				return false;
			}
        }  

		/**
		 * Get allowed shipping methods
		 *
		 * @return array
		 */
		public function getAllowedMethods()
		{
			return array($this->_code=>$this->getConfigData('name'));
		}
    }  
