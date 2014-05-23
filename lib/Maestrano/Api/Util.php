<?php

abstract class Maestrano_Api_Util
{
  /**
   * Whether the provided array (or other) is a list rather than a dictionary.
   *
   * @param array|mixed $array
   * @return boolean True if the given object is a list.
   */
  public static function isList($array)
  {
    if (!is_array($array))
      return false;

    // TODO: generally incorrect, but it's correct given Maestrano's response
    foreach (array_keys($array) as $k) {
      if (!is_numeric($k))
        return false;
    }
    return true;
  }

  /**
   * Recursively converts the PHP Maestrano object to an array.
   *
   * @param array $values The PHP Maestrano object to convert.
   * @return array
   */
  public static function convertMaestranoObjectToArray($values)
  {
    $results = array();
    foreach ($values as $k => $v) {
      // FIXME: this is an encapsulation violation
      if ($k[0] == '_') {
        continue;
      }
      if ($v instanceof Maestrano_Api_Object) {
        $results[$k] = $v->__toArray(true);
      } else if (is_array($v)) {
        $results[$k] = self::convertMaestranoObjectToArray($v);
      } else {
        $results[$k] = $v;
      }
    }
    return $results;
  }

  /**
   * Converts a response from the Maestrano API to the corresponding PHP object.
   *
   * @param array $resp The response from the Maestrano API.
   * @param string $apiKey
   * @return Maestrano_Api_Object|array
   */
  public static function convertToMaestranoObject($resp, $apiKey)
  {
    $types = array(
      'account_bill' => 'Maestrano_Account_Bill',
    );
    if (self::isList($resp)) {
      $mapped = array();
      foreach ($resp as $i)
        array_push($mapped, self::convertToMaestranoObject($i, $apiKey));
      return $mapped;
    } else if (is_array($resp)) {
      if (isset($resp['object']) 
          && is_string($resp['object'])
          && isset($types[$resp['object']])) {
        $class = $types[$resp['object']];
      } else {
        $class = 'Maestrano_Api_Object';
      }
      return Maestrano_Api_Object::scopedConstructFrom($class, $resp, $apiKey);
    } else {
      return $resp;
    }
  }
}
