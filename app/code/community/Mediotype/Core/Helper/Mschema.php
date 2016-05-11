<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Mschema
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Mschema
{
    /**
     *
     */
    const EC_REQUIRED_NODE = 1001;
    /**
     *
     */
    const EC_STRUCTURE_MALFORMED = 1003;


    /**
     * @var
     */
    protected $_validationObject;
    /**
     * @var null
     */
    protected $_currentValidationNode;
    /**
     * @var
     */
    protected $_lastNode;

    /**
     * @var array
     */
    protected $_validators;

    /**
     * @var Mage_Core_Helper_Abstract
     */
    private $debug;

    /**
     *
     */
    public function __construct()
    {
        // ************* DEBUG SETTINGS *************
        $this->debug = Mage::helper('mediotype_core/debugger');
        $this->debug->setEnabled(false);

        // ************* LOAD CORE VALIDATORS *************
        $this->_validators = array(
            "mediotype_core/mschema_validator_strip",
            "mediotype_core/mschema_validator_trim",
            "mediotype_core/mschema_validator_ucwords",
            "mediotype_core/mschema_validator_regex",
            "mediotype_core/mschema_validator_datatype",
            "mediotype_core/mschema_validator_cast",
            "mediotype_core/mschema_validator_parseavp"
        );

        $this->_currentValidationNode = null;
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->_validators;
    }

    /**
     * @param $class
     * @return Mediotype_Core_Helper_Mschema
     */
    public function addValidator($class)
    {
        if (is_string($class)) {
            $this->_validators[$class] = $class;
        }
        return $this;
    }

    /**
     * @param $target
     * @return bool
     */
    public function removeValidator($target)
    {
        if(array_key_exists($target, $this->_validators)){
            unset($this->_validators[$target]);
            return true;
        }
        return false;
    }

    /**
     * @param $filePath
     * @return Mediotype_Core_Helper_Mschema|null
     * @throws Mediotype_Core_Helper_Mschema_Exception
     */
    public function LoadSchema($filePath)
    {
        // ************* LOAD/VALIDATE VALIDATION OBJECT *************
        if (is_string($filePath)) {
            $this->debug->log("Loading Validation Object");

            // LOAD JSON DOCUMENT
            if ($ioHandler = fopen($filePath, "r")) {
                $json = fread($ioHandler, filesize($filePath));
                fclose($ioHandler);

                //PARSE JSON
                $this->_validationObject = json_decode($json);

                // HANDLE ERRORS
                if (is_null($this->_validationObject)) {
                    throw new Mediotype_Core_Helper_Mschema_Exception("Failed to parse json,", 1000);
                } else {
                    $this->_currentValidationNode = $this->_validationObject;
                    return $this;
                }
            } else {
                throw new Mediotype_Core_Helper_Mschema_Exception("Failed to open file,", 1000);
            }

        }

        return NULL;
    }

    /**
     * @param $data
     * @param int $depth
     * @return Mediotype_Core_Model_Response
     */
    public function Validate($data, $depth = 0) //$validationObject, $data, $depth = 0)
    {
        // ************* SET STARTING VARIABLES *************
        $returnObject = new Mediotype_Core_Model_Response(__METHOD__, array(), Mediotype_Core_Model_Response::OK); // OBJECT USED FOR COLLECTING NODES THAT FAILED VALIDATION
        $depth += 1; // TRACK DEPTH OF RECURSION

        // ************* MAIN PARSING LOOP *************
        foreach ($this->_currentValidationNode as $index => $value) {
            $errorMessages = array(); // USED TO HOLD DETAILS AS TO WHY A NODE FAILED VALIDATION


            $this->debug->logRecursion($depth, " foreach index of the validationSchema ($index)");

            // ************* DEFAULT DATA MERGE ****************
            if (!isset($data->$index) && isset($this->_currentValidationNode->$index->default)) {
                $this->debug->logRecursion($depth, "MERGING DEFAULT DATA ON ($index)");
                $data->$index = $this->_currentValidationNode->$index->default;
            }

            if (isset($data->$index)) { // If the index is found in the data
                $this->debug->logRecursion($depth, "($index) WAS FOUND IN THE DATA");
                // ************* OBJECT VALIDATION *************
                if (is_object($this->_currentValidationNode->$index)) { // CHECK IF THE NODE IS AN OBJECT
                    $this->debug->logRecursion($depth, "OBJECT VALIDATION ($index)");
                    // ************* END NODE DETECTION *************
                    if ($this->_isEndNode($this->_currentValidationNode->$index)) {
                        // *************  END NODE ****************
                        $this->debug->logRecursion($depth, "NODE ($index) IS A END NODE");

                        // *************  LOOP THROUGH VALIDATION OBJECTS ****************
                        foreach ($this->getValidators() as $className) {

                            $validator = NULL;
                            if (strstr($className, "/")) {
                                $validator = Mage::helper($className);
                            }

                            if (is_null($validator)) {
                                $this->debug->logRecursion($depth, "FAILED TO LOAD VALIDATOR ($className)");
                                continue;
                            }

                            if ($validator->canRead($this->_currentValidationNode->$index)) {
                                $this->debug->logRecursion($depth, "VALIDATING NODE ($index) USING $className");

                                $response = $validator->Validate($this->_currentValidationNode->$index, $data->$index);

                                $this->debug->logRecursion($depth, "$className RESULTS : " . print_r($response, true));

                                // *************  CATCH ANY ERROR MSGS ****************
                                if ($response->disposition !== Mediotype_Core_Model_Response::OK) {
                                    $this->debug->logRecursion($depth, "($index) HAS FAILED VALIDATION");
                                    $returnObject->data[$index] = $response;
                                    $returnObject->disposition = Mediotype_Core_Model_Response::FATAL;
                                }
                            }
                        }

                        if ($returnObject->disposition == Mediotype_Core_Model_Response::OK) {
                            $this->debug->logRecursion($depth, "($index) HAS PASSED VALIDATION");
                        }

                    } else {
                        // ************* NOT END NODE ****************
                        $this->debug->logRecursion($depth, "NODE ($index) *IS NOT* A END NODE");
                        $this->debug->logRecursion($depth, "NODE ($index) FOUND IN SUPPLIED DATA");

                        $this->_lastNode = $this->cloneObject($this->_currentValidationNode);
                        $this->_currentValidationNode = $this->cloneObject($this->_currentValidationNode->$index);

                        // ************* RECURSION *************
                        $this->debug->logRecursion($depth, "+START Recursion on index ( $index )");
                        $objectRecursionResponse = $this->Validate($data->$index, $depth); //
                        $this->_currentValidationNode = $this->cloneObject($this->_lastNode);
                        $this->debug->logRecursion($depth, "-END Recursion on index ( $index )");

                        if ($objectRecursionResponse->disposition !== Mediotype_Core_Model_Response::OK) {
                            $returnObject->data[$index] = $objectRecursionResponse;
                            $returnObject->disposition = Mediotype_Core_Model_Response::FATAL;

                            $this->debug->logRecursion($depth, "FAILURE IN CHILD NODE");
                        }
                    }
                }

                // ************* ARRAY VALIDATION *************
                if (is_array($this->_currentValidationNode->$index)) {
                    $this->debug->logRecursion($depth, "ARRAY VALIDATION");

                    $failedArrayResponseCollection = array();
                    // CHECK IF SUPPLIED DATA IS AN ARRAY
                    if (is_array($data->$index)) {

                        $this->_lastNode = $this->cloneObject($this->_currentValidationNode);
                        $this->_currentValidationNode = $this->cloneObject(array_shift($this->_currentValidationNode->$index));

                        // TODO: REVIEW THIS
                        // REMOVE KEYWORDS FROM DATA
                        if (isset($this->_currentValidationNode->$index) && is_bool(($this->_currentValidationNode->$index))) {
                            unset($this->_currentValidationNode->$index);
                        }

                        foreach ($data->$index as $key => $node) {
                            $this->debug->logRecursion($depth, "+START Recursion on array ( $index )");
                            $arrayRecursionResults = $this->Validate($node, $depth);
                            $this->debug->logRecursion($depth, "-END Recursion on array ( $index )");

                            if ($arrayRecursionResults->disposition !== Mediotype_Core_Model_Response::OK) {
                                $this->debug->logRecursion($depth, "FAILURE IN CHILD NODE");
                                $failedArrayResponseCollection[$key] = $arrayRecursionResults;
                            }
                        }

                        if (count($failedArrayResponseCollection) > 0) {
                            $returnObject->data[$index] = $failedArrayResponseCollection;
                            $returnObject->disposition = Mediotype_Core_Model_Response::FATAL;
                        }

                        $this->_currentValidationNode = $this->cloneObject($this->_lastNode);

                    } else {
                        $this->debug->logRecursion($depth, "NODE IN SUPPLIED DATA SHOULD BE AN ARRAY ($index)");

                        $returnObject->disposition = Mediotype_Core_Model_Response::FATAL;
                        $returnObject->data[$index] = new Mediotype_Core_Model_Response(
                            __METHOD__,
                            $index,
                            Mediotype_Core_Model_Response::FATAL,
                            "NODE IN SUPPLIED DATA SHOULD BE AN ARRAY",
                            Mediotype_Core_Helper_Mschema::EC_STRUCTURE_MALFORMED);

                    }

                }

            } else {
                $this->debug->logRecursion($depth, "NODE ($index) *WAS NOT* FOUND IN SUPPLIED DATA");
                $isNodeRequired = FALSE;
                if (is_array($this->_currentValidationNode->$index)) {
                    if (isset($this->_currentValidationNode->$index[0]->required)) {
                        $isNodeRequired = $this->_currentValidationNode->$index[0]->required;
                    } else {
                        $isNodeRequired = FALSE;
                    }

                }
                if (is_object($this->_currentValidationNode->$index)) {
                    if (isset($this->_currentValidationNode->$index->required)) {
                        $isNodeRequired = $this->_currentValidationNode->$index->required;
                    } else {
                        $isNodeRequired = FALSE;
                    }
                }
                if ($isNodeRequired) {
                    $this->debug->logRecursion($depth, "NODE ($index) IS REQUIRED");
                    $returnObject->disposition = Mediotype_Core_Model_Response::FATAL;
                    $returnObject->data[$index] = new Mediotype_Core_Model_Response(
                        __METHOD__,
                        $index,
                        Mediotype_Core_Model_Response::FATAL,
                        "MISSING REQUIRED NODE $index",
                        Mediotype_Core_Helper_Mschema::EC_REQUIRED_NODE
                    );
                } else {
                    $this->debug->logRecursion($depth, "NODE ($index) *IS NOT* REQUIRED");
                }
            }

        }
        //</editor-fold>

        // FORMAT RETURN DATA

        //NOTE: THE ORDER IN WHICH THE RETURN OBJECT IS BUILT WILL BE THE ORDER IN WHICH IT IS RETURNED
        if ($returnObject->disposition !== Mediotype_Core_Model_Response::OK) {
            $returnObject->description = "Failed mSchema Validation";
        } else {
            $returnObject->description = "Passed mSchema Validation";
        }
        return $returnObject;
    }

    /**
     * @param $object
     * @return bool
     */
    protected function _hasRequiredFields($object)
    {
        $requiredFieldsDetected = false;
        foreach ($object as $index => $node) {
            if (is_object($object->$index)) { // IF KEY IS AN OBJECT THEN
                if ($this->_isEndNode($object->$index)) { // If the key is a end node
                    if (ucwords($object->$index->required) == TRUE) {
                        $requiredFieldsDetected = true;
                    }
                } else { // IF NOT END NODE
                    $requiredFieldsDetected = $this->_hasRequiredFields($object->$index);
                }
            }
        }
        return $requiredFieldsDetected;
    }

    /**
     * @param $object
     * @return bool
     */
    protected function _isEndNode($object)
    {
        $isEndNode = true;

        foreach ($object as $index => $node) {
            if ($isEndNode && (is_object($node) || is_array($node))) {
                $isEndNode = false;
            }
        }
        return $isEndNode;
    }

    /**
     * @param object $obj
     * @return mixed
     */
    protected function cloneObject($obj)
    {
        return unserialize(serialize($obj));
    }
}