<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty wordwrap modifier plugin
 *
 * Type:     modifier<br>
 * Name:     wordwrap<br>
 * Purpose:  wrap a string of text at a given length
 * @link http://smarty.php.net/manual/en/language.modifier.wordwrap.php
 *          wordwrap (Smarty online manual)
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_layout_wrap($string,$length=80,$seperator='<wbr />')
{
  $j = $length;
  while ($length < strlen($string)) {
      if (strpos($string, ' ', $length-$j+1) > $length+$j || strpos($string, ' ', $length-$j+1) === false) {
          $string = substr($string, 0, $length) . $seperator . substr($string, $length);
      }
      $length += $j;
  }
  return $string;
}

?>
