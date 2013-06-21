<?php
// {{{ Header
/*
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | SIERRA : PHP Application Framework  http://code.google.com/p/sierra-php |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 | Copyright 2005 Jason Read                                               |
 |                                                                         |
 | Licensed under the Apache License, Version 2.0 (the "License");         |
 | you may not use this file except in compliance with the License.        |
 | You may obtain a copy of the License at                                 |
 |                                                                         |
 |     http://www.apache.org/licenses/LICENSE-2.0                          |
 |                                                                         |
 | Unless required by applicable law or agreed to in writing, software     |
 | distributed under the License is distributed on an "AS IS" BASIS,       |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.|
 | See the License for the specific language governing permissions and     |
 | limitations under the License.                                          |
 +~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~+
 */
// }}}

// {{{ Constants
// None.
// }}}

// {{{ Includes
// None
// }}}

// {{{ SRA_LogEntry
/**
* The SRA_LogEntry class manages the creation and storage of log entries into log
* files. It is used by other classes such as the SRA_Error class to make log entries.
*
* @author:  Jason Read <jason@idir.org>
* @package sierra.util
*/
class SRA_LogEntry {
    // {{{ Properties
    /**
     * The file where the error occurred.
     * @type   String
     * @access private
     */
    var $_file;

    /**
     * The line number on the file where the error occurred.
     * @type   int
     * @access private
     */
    var $_lineNumber;

    /**
     * The log message.
     * @type   String
     * @access private
     */
    var $_message;

    /**
     * Unix timestamp particular log entry.
     * @type   int
     * @access private
     */
    var $_unixTimeStamp;
    // }}}

    // {{{ getFile()
    /**
     * Returns the file name where the error occurred.
     *
     * @access	public
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function getFile( )
    {
        if (isset($this->_file))
        {
            return($this->_file);
        }

    }
    // }}}

    // {{{ getLineNumber()
    /**
     * Returns the line number in the file where the error occurred.
     *
     * @access	public
     * @return	int
     * @author	Jason Read <jason@idir.org>
     */
    function getLineNumber( )
    {
        if (isset($this->_lineNumber))
        {
            return($this->_lineNumber);
        }

    }
    // }}}

    // {{{ getMessage()
    /**
     * Returns the log entry message.
     *
     * @access	public
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function getMessage( )
    {
        if (isset($this->_message))
        {
            return($this->_message);
        }

    }
    // }}}

    // {{{ getUnixTimeStamp()
    /**
     * Returns the unix timestamp when the log entry occured.
     *
     * @access	public
     * @return	int
     * @author	Jason Read <jason@idir.org>
     */
    function getUnixTimeStamp()
    {
        if (isset($this->_unixTimeStamp))
        {
            return($this->_unixTimeStamp);
        }

    }
    // }}}
	
    // {{{ log()
    /**
     * This static method is used to quickly log a message to a file.
     *
	 * @param   msg. String. The message to write to the file. 
	 * 			This value is mandatory.
	 * 
	 * @param   file. String. The log file to write the message to. 
	 * 			This value is mandatory.
	 * 
	 * @param   timestamp. boolean. Whether or not the log should be timestamped. 
	 * 			By default this parameter is true.
	 * 
	 * @param   source. String. The source file to record in the log. 
	 * 			This parameter is optional.
	 *
	 * @param	line. int. The line number to include in the log. This 
	 * 			parameter is optional.
	 * 
     * @access	public - static
     * @return	void
     * @author	Jason Read <jason@idir.org>
     */
    function log($msg, $file, $timestamp=TRUE, $source=FALSE, $line=FALSE)
    {
        if ($msg && $file)
		{
			$entry = "";
			if ($timestamp)
			{
        if (is_object($this)) {
          $this->_unixTimeStamp = mktime();
          $unixTimeStamp = $this->_unixTimeStamp;
        }
        else {
          $unixTimeStamp = mktime();
        }
				$entry = date ("Y-m-d", $unixTimeStamp) . "T" . date ("H:i:s", $unixTimeStamp);
			}
			
			if ($source)
			{
				if ($entry)
				{
					$entry .= " ";
				}
				$entry .= $source;
			}
			
			if ($line)
			{
				if ($entry)
				{
					$entry .= " ";
				}
				$entry .= $line;
			}
			if ($entry)
			{
				$entry .= " ";
			}
			$entry .= "MSG: ". $msg;
			$entry .= "\n";
			if (!file_exists($file)) {
				SRA_File::touch($file);
			}
			return (error_log($entry, 3, $file));
		}
    }
    // }}}

    // {{{ setFile()
    /**
     * Sets the file where the error occurred.
     *
     * @param   origination String. Origination to set.
     * @access	public
     * @return	Void
     * @author	Jason Read <jason@idir.org>
     */
    function setFile( $origination )
    {
        $this->_file = $origination;
    }
    // }}}

    // {{{ setLineNumber()
    /**
     * Sets the _lineNumber instance variable and returns true if the line number
     * parameter is valid.
     *
     * @param 	lineNumber Line number to be set.
     * @access	public
     * @return	boolean
     * @author	Jason Read <jason@idir.org>
     */
    function setLineNumber( $lineNumber )
    {
        $this->_lineNumber = $lineNumber;
    }
    // }}}

    // {{{ setMessage()
    /**
     * Sets the log entry message.
     *
     * @param message The message to set.
     * @access	public
     * @return	boolean
     * @author	Jason Read <jason@idir.org>
     */
    function setMessage( $message )
    {
        $this->_message = $message;
    }
    // }}}

    // {{{ writeToFile()
    /**
     * Method used to write the log entry to a log file.
     *
     * @param fileName The file to write this log entry to.
     * @access	public
     * @return	boolean
     * @author	Jason Read <jason@idir.org>
     */
    function writeToFile( $fileName )
    {
        // Set the _unixTimeStamp.
        if (empty($this->getUnixTimeStamp))
        {
            $this->_unixTimeStamp = mktime();
        }

        // Write logEntry.
        $entry = date ("Y-m-d", $this->_unixTimeStamp) . "T" . date ("H:i:s", $this->_unixTimeStamp);
        $entry .= " ". $this->getFile();
        $entry .= " ". $this->getLineNumber();
        $entry .= " MSG: ". $this->getMessage();
        $entry .= "\n";
        if (!file_exists($fileName)) {
          SRA_File::touch($fileName);
          SRA_File::chmod($fileName, 0666);
        }
        if (!@error_log($entry, 3, $fileName))
		{
			print_r($entry);
			return false;
		}
		else
		{
			return true;
		}
    }
    // }}}

}
// }}}

?>