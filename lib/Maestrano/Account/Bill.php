<?php

class Maestrano_Account_Bill extends Maestrano_Api_Resource
{
  
  /**
   * @param string $class
   *
   * @returns string The endpoint URL for the Bill class
   */
  public static function classUrl($class)
  {
    return "/api/v1/account/bills";
  }
  
  /**
   * @param string $id The ID of the bill to retrieve.
   * @param string|null $apiKey
   *
   * @return Maestrano_Billing_Bill
   */
  public static function retrieve($id, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedRetrieve($class, $id, $apiKey);
  }

  /**
   * @param array|null $params
   * @param string|null $apiKey
   *
   * @return array An array of Maestrano_Billing_Bills.
   */
  public static function all($params=null, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $apiKey);
  }

  /**
   * @param array|null $params
   * @param string|null $apiKey
   *
   * @return Maestrano_Billing_Bill The created bill.
   */
  public static function create($params=null, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedCreate($class, $params, $apiKey);
  }

  /**
   * @return Maestrano_Billing_Bill The saved bill.
   */
  public function save()
  {
    $class = get_class();
    return self::_scopedSave($class);
  }
  
  /**
   * @return Maestrano_Billing_Bill The cancelled bill.
   */
  public function cancel($params=null)
  {
    $class = get_class();
    return self::_scopedDelete($class, $params);
  }
}
