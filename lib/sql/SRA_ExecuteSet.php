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
// }}}

// {{{ Includes
// }}}

// {{{ SRA_ExecuteSet
/**
 * This class manages the results of performing an execute query. There are
 * two types of results that may be needed from an execute query. The
 * first is to know the number of records that were affected by the query,
 * and the second is to know the sequenced id that was used when dealing
 * with tables with incremented sequences or auto-increment fields
 *
 * @author    Jason Read <jason@idir.org>
 * @package sierra.sql
 */
class SRA_ExecuteSet {
    // {{{ Properties
    /**
     * This attribute stores the number of rows that were affected by an
     * execute query
     * @type   int
     * @access private
     */
    var $_numRowsAffected;
    /**
     * This attribute holds the incremental value that was used by the
     * sequenced or incremental column specified in the query parameter of
     * the execute method (see SRA_Database::execute api for more info)
     * @type   int
     * @access private
     */
    var $_sequenceValue;
    // }}}

    // {{{ SRA_ExecuteSet()
    /**
     * Execute set constructor. This class is essentially just a read-only
     * container for two values. This method requires the numRowsAffected
     * parameter to be specified. The sequenceValue parameter is optional
     *
     * @param int $numRowsAffected the number of rows that were
     * affected by the execute query. This parameter is mandatory
     * (although it may be zero).
     * @param int $sequenceValue the value of the sequenced column.
     * This value is optional.
     * @access  public
     * @return  void
     */
    function SRA_ExecuteSet($numRowsAffected, $sequenceValue=FALSE)
    {
        $this->_numRowsAffected = $numRowsAffected;

        if ($sequenceValue !== FALSE)
        {
            $this->_sequenceValue = $sequenceValue;
        }
    }
    // }}}

    // {{{ getNumRowsAffected()
    /**
     * This method returns the number of rows that were affected by the
     * execute query
     *
     * @access  public
     * @return  int
     */
    function getNumRowsAffected()
    {
        return $this->_numRowsAffected;
    }
    // }}}

    // {{{ getSequenceValue()
    /**
     * This method returns the sequence value that was used for an insert
     * statement on a table with an incremental field
     *
     * @access  public
     * @return  int
     */
    function getSequenceValue()
    {
        if (isset($this->_sequenceValue))
        {
            return (int) $this->_sequenceValue;
        }
    }
    // }}}

    // {{{ isValid()
    /**
     * Static method that returns true if the object parameter references a
     * valid SRA_ExecuteSet object
     *
     * @param object $object the object to evaluate.
     * @access  public
     * @return  boolean
     */
    function isValid($object)
    {
        return (is_object($object) && (!isset($object->err) || !SRA_Error::isError($object->err)) && strtolower(get_class($object)) == 'sra_executeset');
    }
    // }}}

    // {{{ setSequenceValue()
    /**
     * Sets the value of the _sequenceValue
     *
     * @access  public
     * @return  void
     */
    function setSequenceValue($sequenceValue)
    {

        $this->_sequenceValue = $sequenceValue;
    }
    // }}}

}
// }}}

?>
