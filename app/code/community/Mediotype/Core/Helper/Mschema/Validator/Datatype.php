<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Mschema_Validator_Datatype
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Mschema_Validator_Datatype extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /*
    "boolean"
    "integer"
    "double" (for historical reasons "double" is returned in case of a float, and not simply "float")
    "string"
    "array"
    "object"
    "resource"
    "NULL"
    "unknown type"

     */

    /**
     * @param $validationObject
     * @param $data
     * @return bool|Mediotype_Core_Model_Response
     */
    public function Validate($validationObject, &$data)
    {
        $response = new Mediotype_Core_Model_Response(__METHOD__, NULL, Mediotype_Core_Model_Response::OK);
        if ($this->CanRead($validationObject)) {
            if(gettype($data) != $validationObject->datatype){
                $response->disposition = Mediotype_Core_Model_Response::FATAL;
                $response->description = "FAILED DATATYPE VALIDATION. PARAMETER PROVIDED IS A " . gettype($data);
            }
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getKeyword(){
        return "datatype";
    }
}