<?php class Registry implements ArrayAccess
{
  private static $vars = array();

  public static function set($key, $var)
  {
    if ( !isset(self::$vars[$key]) == true)
    {
      self::$vars[$key] = $var;
      return true;
    } else {
      return false;
    }
  }

  public static function get($key)
  {
    if(isset(self::$vars[$key]) == false)
    {
      return null;
    }
    return self::$vars[$key];
  }

  function remove($key)
  {
    unset(self::$vars[$key]);
  }

  function offsetExists($offset)
  {
    return isset(self::$vars[$offset]);
  }

  function offsetGet($offset)
  {
    return self::get($offset);
  }

  function offsetSet($offset, $value)
  {
    self::set($offset, $value);
  }

  function offsetUnset($offset)
  {
    unset(self::$vars[$offset]);
  }
}