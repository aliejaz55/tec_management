<?php
/**
 * 11/21/2019 | 11:27 AM
 * @category    b2c
 * @author      Ejaz Alam
 * @email       ejaz.alam@evampsaanga.com
 */

namespace Tec\Management\Helper;
use \Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function validateMobileNumber($msisdn) {
        $regx_msisdn = preg_match("/^[0-9]{12}$/", $msisdn);
        if ($regx_msisdn == 1 && strlen($msisdn) == 12 && is_numeric($msisdn)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
    public function validateCnicNumber($cnicNUmber) {
        $cnic = str_replace('-','',"$cnicNUmber");
        if (strlen($cnic) == 13) {
            $result = $cnic;
        } else {
            $result = false;
        }
        return $result;
    }
}