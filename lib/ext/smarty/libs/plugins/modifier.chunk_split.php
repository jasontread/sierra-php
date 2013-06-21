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
 * Name:     chunk_split<br>
 * Purpose:  split it a string at certain intervals
 * @param string
 * @param integer
 * @param string
 * @return string
 */
function smarty_modifier_chunk_split($string,$length=76,$break="\r\n")
{
    return chunk_split($string,$length,$break);
}

?>
