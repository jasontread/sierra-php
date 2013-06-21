<?php
/**
 * phpJSO - The Javascript Obfuscator written in Javascript. Although
 * it effectively obfuscates Javascript code, it is meant to compress
 * code to save disk space rather than hide code from end-users.
 *
 * 5/24/2006: This file has been heavliy modified due to bugs. do not swap out 
 * for newer version without testing
 *
 * @started: Mon, May 23, 2005
 * @copyright: Copyright (c) 2004, 2005 JPortal, All Rights Reserved
 * @website: www.jportalhome.com
 * @license: Free, zlib/libpng license - see LICENSE
 * @version: 0.6
 * @subversion: $Id: phpJSO.php 56 2005-12-17 18:33:10Z josh $
 */

 /**
 * Main phpJSO compression function. Pass Javascript code to it, and it will
 * return compressed code.
 */
function phpJSO_compress ($code)
{
	// Array of tokens - alphanumeric
	$tokens = array();
	
	// Array of only numeric tokens, that are only inserted to prevent being
	// wrongly replaced with another token. For example: the integer 0 will
	// be replaced with whatever is at token index 0.
	$numeric_tokens = array();
	
	// Save original code length
	$original_code_length = strlen($code);
	
	// Remove strings and multi-line comments from code before performing operations
	$str_array = array();
	phpJSO_strip_strings_and_comments($code, $str_array, substr(md5(time()), 10, 2));
	
	// Strip junk from JS code
  
	phpJSO_strip_junk($code, true);
	phpJSO_strip_junk($code);
	
	// Add strings back into code
  phpJSO_restore_strings($code, $str_array);
  return $code;
}

/**
 * Strip strings and comments from code
 */
function phpJSO_strip_strings_and_comments (&$str, &$strings, $comment_delim)
{
	// Find all occurances of comments and quotes. Then loop through them and parse.
	$quotes_and_comments = &phpJSO_sort_occurances($str, array('//', '/*', '*/', '"', "'"));

	// Loop through occurances of quotes and comments
	$in_string = $last_quote_pos = $in_comment = false;
	$removed = 0;
	$num_strings = count($strings);
	foreach ($quotes_and_comments as $location => $token)
	{
		if ($in_string !== false)
		{
			if ($token == $in_string && $str[$location - $removed - 1] != '\\')
			{
				// First, we'll pull out the string and save it, and replace it with a number.
				$replacement = '`' . $num_strings . '`';
				$string_start_index = $last_quote_pos - $removed;
				$string_length = ($location - $last_quote_pos) + 1;
				$strings[$num_strings] = &substr($str, $string_start_index, $string_length);
				++$num_strings;

				// Remove the string completely
				$str = substr_replace($str, $replacement, $string_start_index, $string_length);

				// Clean up time...
				$removed += $string_length - strlen($replacement);
				$in_string = $last_quote_pos = false;
			}
		}
		else if ($in_comment !== false)
		{
			// If it's the end of a comment, replace it with a single space
			// We replace it with a space in case a comment is between two tokens: test/**/test
			if ($token == '*/')
			{
				$comment_start_index = $in_comment - $removed;
				$comment_length = ($location - $in_comment) + 2;
				$str = substr_replace($str, ' ', $comment_start_index, $comment_length);
				$removed += $comment_length - 1;
				$in_comment = false;
			}
		}
		else
		{
			// Make sure string hasn't been extracted by another operation...
			if (substr($str, $location - $removed, strlen($token)) != $token)
			{
				continue;
			}
			
			// This string shouldn't have been escaped...
			if ($location && $str[$location - $removed - 1] == '\\')
			{
				continue;
			}
			
			// See what this token is ...
			// Start of multi-line comment?
			if ($token == '/*')
			{
				$in_comment = $location;
			}
			// Start of a string?
			else if ($token == '"' || $token == "'")
			{
				$in_string = $token;
				$last_quote_pos = $location;
			}
			// A single-line comment?
			else if ($token == '//')
			{
				$comment_start_position = $location - $removed;
				$newline_pos = strpos($str, "\n", $comment_start_position);
				$comment_length = ($newline_pos !== false ? $newline_pos - $comment_start_position : $comment_start_position);
				$str = substr_replace($str, '', $comment_start_position, $comment_length);
				$removed += $comment_length;
			}
		}
	}
}

/**
 * Strips junk from code
 */
function phpJSO_strip_junk (&$str, $whitespace_only = false)
{
	// Remove unneeded spaces and semicolons
	$find = array
	(
		'/([^a-zA-Z0-9\s]|^)\s+([^a-zA-Z0-9\s]|$)/s', // Unneeded spaces between tokens
		'/([^a-zA-Z0-9\s]|^)\s+([a-zA-Z0-9]|$)/s', // Unneeded spaces between tokens
		'/([a-zA-Z0-9]|^)\s+([^a-zA-Z0-9\s]|$)/s', // Unneeded spaces between tokens
		'/([^a-zA-Z0-9]|^)\s+([^a-zA-Z0-9]|$)/s', // Unneeded spaces between tokens
		'/([^a-zA-Z0-9]|^)\s+([a-zA-Z0-9]|$)/s', // Unneeded spaces between tokens
		'/([a-zA-Z0-9]|^)\s+([^a-zA-Z0-9]|$)/s', // Unneeded spaces between tokens
		'/[\r\n]/s', // Unneeded newlines
		"/\t+/" // replace tabs with spaces
	);
  /*
	// Unneeded semicolons
	if (!$whitespace_only)
	{
		$find[] = '/;(\}|$)/si';
	}
  */
	$replace = array
	(
		'$1$2',
		'$1$2',
		'$1$2',
		'$1$2',
		'$1$2',
		'$1$2',
		'',
		' ',
		'$1',
	);
	$str = preg_replace($find, $replace, $str);
}


/**
 * Place stripped strings back into code
 */
function phpJSO_restore_strings (&$str, &$strings)
{
	do
	{
		$str = preg_replace('#`([0-9]+)`#e', 'isset($strings["$1"]) ? $strings["$1"] : "`$1`"', $str);
	}
	while (preg_match('#`([0-9]+)`#', $str));
}

/**
 * Finds all occurances of different strings in the first passed string and sorts
 * them by location. Returns array of locations. The key of each array element is the string
 * index (location) where the string was found; the value is the actual string, as seen below.
 *
 * [18] => "
 * [34] => "
 * [56] => /*
 * [100] => '
 */
function phpJSO_sort_occurances (&$haystack, $needles)
{
	$locations = array();
	
	foreach ($needles as $needle)
	{
		$pos = -1;
		while (($pos = @strpos($haystack, $needle, $pos+1)) !== false)
		{
			$locations[$pos] = $needle;
		}
	}
	
	ksort($locations);
	
	return $locations;
}

?>
