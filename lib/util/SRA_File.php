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
/**
 * identifies the tar compression format which uses the extension ".tar"
 * @type string
 */
define ('SRA_FILE_ARCHIVE_TYPE_TAR', 'tar');

/**
 * identifies the tar gzip compression format which uses the extension ".tar.gz"
 * @type string
 */
define ('SRA_FILE_ARCHIVE_TYPE_TAR_GZ', 'tar.gz');

/**
 * identifies the tar gzip compression format which uses the extension ".tgz"
 * @type string
 */
define ('SRA_FILE_ARCHIVE_TYPE_TAR_GZ1', 'tgz');

/**
 * identifies the zip compression format which uses the extension ".zip"
 * @type string
 */
define ('SRA_FILE_ARCHIVE_TYPE_ZIP', 'zip');

/**
 * Specifies the mime type file to use. This must be specified in order to use the getMimeType method
 * @type   String
 * @access public
 */
define('SRA_FILE_MIME_TYPE_FILE', "/etc/mime.types");

/**
 * Specifies whether or not the SRA_File class should operate in debug mode (outputs what it is doing to
 * the active console window).
 * @type   boolean
 * @access public
 */
define('SRA_FILE_DEBUG', false);

/**
 * The location where the cache csv arrays should be stored.
 * 
 * @type   String
 * @access public
 */
define('SRA_FILE_CACHE_DIR', SRA_DIR . "/tmp");
// }}}

// {{{ Includes
// }}}

// {{{ SRA_File

/**
 * SRA_File Class. This class encapsulates the basic file system function. It
 * wraps the functions suppressing normal PHP error reporting and insteads uses
 * SRA_Error.
 *
 * Note: For debugging turn track_errors on in the php.ini. The error messages
 * from this class will then be clearer because $php_errormsg is passed as part
 * of the message.
 *
 * Current Methods:
 * copy($src, $dest)
 * rename($src, $dest)
 * _mkdir($pathname, $mode)
 * mkdir($path, $mode=0777, $parents=TRUE)
 * chmod($pathname, $mode)
 * unlink($file)
 * symlink($target, $link)
 * touch($file)
 * _rmdir($dir)
 * rmdir($dir, $children=FALSE)
 * umask($mode)
 * read($file, &$rBuffer)
 * _write($file, &$rBuffer)
 * write($file, &$rBuffer, $parents=TRUE, $mode=0777)
 * fileMTime($file)
 * compareMTimes($file1, $file2)
 * parseIniFile($pathname, $processSections=FALSE)
 *
 * @author	Jason Read <jason@idir.org>
 * @package sierra.util
 */

class SRA_File {
    
  // {{{ copy
  /**
   * used to copy a file from one location to another
   * @param	string $src source path and name file to copy. this file may be 
   * remote (http, https, ftp), and if it is, it will be downloaded using wget
   * (wget must be installed)
   * @param	string $dest destination directory or directory + file name. if 
   * $dest is a directory, the name of the file in $dest will be the same name 
   * as $src. if the $dest directory does not exist, it will be created
   * @param boolean $recursive whether or not the copy should be recursive. does 
   * not apply to remote $src files
   * @return void
   */
  function copy($src, $dest, $recursive=FALSE) {
    $dir = is_dir($dest) ? $dest : dirname($dest);
    if (!is_dir($dir)) { SRA_File::mkdir($dir); }
    if (strpos($src, '://') && ($wget = SRA_File::findInPath('wget'))) {
      if (is_dir($dest)) {
        $dest = SRA_Util::endsWith($dest, '/') ? $dest : $dest . '/';
        $dest .= basename($src);
      }
      system("$wget -O $dest $src");
    }
    else {
      system(SRA_File::findInPath('cp') . ' -f' . ($recursive ? ' -r' : '') . " $src $dest");
    }
  }
  // }}}
  
  // {{{ findInPath
  /**
   * looks for the program $bin in the $PATH environment variable. returns the 
   * absolute path to the program. returns NULL if $bin does not exist in the 
   * path
   * @param	string $bin the name of the program to look for
   * @param mixed $addlPathDirs an optional array of additional directories to 
   * consider in the path search. this value can also be a single path or 
   * multiple paths represented as a single string each separated by :
   * @return string
   */
  function findInPath($bin, $addlPathDirs=NULL) {
    $path = explode(':', getenv('PATH'));
    if ($addlPathDirs) { $path = array_merge(is_array($addlPathDirs) ? $addlPathDirs : explode(':', $addlPathDirs), $path); }
    return SRA_File::findFile($bin, $path);
  }
  // }}}
    
  // {{{ move
  /**
   * move a file
   * @param	string $src source path and name file to copy
   * @param	string $dest destination path and name of new file
   * @return void
   */
  function move($src, $dest) {
    system(SRA_File::findInPath('mv') . " -f $src $dest");
  }
  // }}}
    
    // {{{ rename()

    /**
     * Rename a file or directory. Hint: Use absolute paths if possible to avoid
     * confustion of where src and dest are located.
     *
     * @param	src		String. Source file/directory to rename.
     * @param	dest	String. Destination file/directory name.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function rename($src, $dest)
    {

        if (FALSE === @rename($src, $dest)) {// Copy FAILED. Log and return err.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::rename: Failed - Cannot rename $src to $dest. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Worked. Log and return TRUE.

            return TRUE;
        }
    }
    // }}}
    // {{{ _mkdir()

    /**
     * Create (make) a directory.
     *
     * @param	pathname	String. Path and name of new directory. This method
     *						only creates a single directory. It's used by mkdir.
     * @param	mode		Int. The mode (permissions) of the new directory. If
     *						using octal add leading 0. eg. 0777. Mode is affect
     *						by the umask system setting.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     * @access	private.
     */

    function _mkdir($pathname, $mode)
    {

        // Throw a warning if mode is 0. PHP converts illegal octal numbers to
        // 0 so 0 might not be what the user intended.

        if ($mode == 0) {
            $msg = "SRA_File::_mkdir: Warning - Creating a directory with permissions of 0. Is this what you wanted? Possible out of range octal number for mode.";
            return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM);
        }

        $str_mode = decoct($mode); // Show octal in messages.

        mkdir($pathname);
        if (!is_dir($pathname)) {// Mkdir FAILED.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::_mkdir: Failed - Cannot mkdir $pathname. Mode $str_mode. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Mkdir worked. Log and return TRUE.
            chmod($pathname, $mode);
            return TRUE;
        }
    }
    // }}}
    // {{{ mkdir()

    /**
     * This method makes one directory or recursively make directories in path.
     *
     * @param	path	String. Path of directories.
     * @param	mode	Int. The mode (permissions) of the new directory. If
     *					using octal add leading 0. eg. 0777. Mode is affect
     *					by the umask system setting.
     * @param	parents	Boolean.	True: Make parent directories as needed.
     *								False: Do not make parent directories.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     * @access	public
     */

    function mkdir($path, $mode=0777, $parents=TRUE)
    {

        // If dir already exists return TRUE.
        if (is_dir($path)) {
            return TRUE;
        }

        // Throw a warning if mode is 0. PHP converts illegal octal numbers to
        // 0 so 0 might not be what the user intended.

        if ($mode == 0) {
            $msg = "SRA_File::mkdir: Warning - Creating a directory with permissions of 0. Is this what you wanted? Possible out of range octal number for mode.";
            return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM);
        }

        $str_mode = decoct($mode); // Show octal in messages.

        // Only make directory if parents=FALSE
        if (FALSE === $parents) {

            // Call _mkdir.
            $error = SRA_File::_mkdir($path, $mode);

                if (SRA_Error::isError($error)) { // error.

                    $msg = "SRA_File::mkdir: Failed - Cannot mkdir $path. Mode $str_mode. ". $error->getErrorMessage();

                    return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                }

        } else { // Make parents and directory.

            // Break up path.
            $path_parts = preg_split("/\//", $path);

            if ($path_parts[0] == '') { // start at root.

                $dir_to_make = '/';

                // Pop off [0]
                array_shift($path_parts);

            } else {
                $dir_to_make = '';
            }

            foreach ($path_parts as $dir) {

                $dir_to_make .= $dir.'/';

                if (!is_dir($dir_to_make)) { // mkdir if not existing.

                    $error = SRA_File::_mkdir($dir_to_make, $mode);

                    if (SRA_Error::isError($error)) { // error.

                        $msg = "SRA_File::mkdir: Failed - Cannot mkdir $path. Mode $str_mode. ". $error->getErrorMessage();

                        return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                    }

                }

            }
        }
            // Mkdir worked. Log and return TRUE.

            return TRUE;

    }
    // }}}
    
    
  // {{{ chgrp
  /**
   * changes the group ownership of $file to $group
   * @param	string $file the file to change the group ownership of
   * @param string $group the name of the group to change group ownership to
   * @param boolean $recursive whether or not to change the group permissions 
   * recursively if $file is a directory
   * @return boolean
   */
  function chgrp($file, $group, $recursive=FALSE) {
    if (($results = chgrp($file, $group)) && is_dir($file) &&$recursive) {
      foreach(SRA_File::getFileList($file, '*', TRUE, 3) as $file) {
        SRA_File::chgrp($file, $group, TRUE);
      }
    }
    return $results;
  }
  // }}}
  
  
  // {{{ chown
  /**
   * changes the ownership of $file to $user
   * @param	string $file the file to change the ownership of
   * @param string $user the name of the user to change ownership to
   * @param boolean $recursive whether or not to change the permissions 
   * recursively if $file is a directory
   * @return boolean
   */
  function chown($file, $user, $recursive=FALSE) {
    if (($results = chown($file, $user)) && is_dir($file) &&$recursive) {
      foreach(SRA_File::getFileList($file, '*', TRUE, 3) as $file) {
        SRA_File::chown($file, $user, TRUE);
      }
    }
    return $results;
  }
  // }}}
  
  
  // {{{ chmod
  /**
   * change the permissions of a file or directory
   * @param	string $file the file to change the permissions for
   * @param	int $mode the new mode (permissions) for $file. if using octal add 
   * leading 0: i.e. 0777
   * @param boolean $recursive whether or not to change the file permissions 
   * recursively if $file is a directory
   * @return boolean
   */
  function chmod($file, $mode, $recursive=FALSE) {
    if (($results = chmod($file, $mode)) && is_dir($file) &&$recursive) {
      foreach(SRA_File::getFileList($file, '*', TRUE, 3) as $file) {
        SRA_File::chmod($file, $mode, TRUE);
      }
    }
    return $results;
  }
  // }}}
	
  
  // {{{ unlink
  /**
   * Delete a file or files. returns TRUE on success, FALSE otherwise (when 
   * $files is an array, FALSE will be returned if ANY of the files in that 
   * array could not be deleted)
   * @param	string $file path and/or name of file to delete. if this is an 
   * array, all of the files in the array will be deleted.
   * @return	boolean
   */
  function unlink($file) {
    $results = TRUE;
    // Multiple files
    if (is_array($file)) {
      foreach ($file as $f) {
        if (!SRA_File::unlink($f)) { $results = FALSE; }
      }
    }
    // Single file
    else {
      $results = unlink($file);
    }
    return $results;
  }
  // }}}
	
    // {{{ symlink()
    /**
     * Symbolically link a file to another name. Currently symlink is not
     * implemented on Windows.
     * Don't use if the application is to be portable.
     *
     * @param	target	String. Path and/or name of file to link.
     * @param	link	String. Path and/or name of link to be created.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function symlink($target, $link)
    {

        // If Windows OS then symlink() will report it is not supported in
        // the build. Use this error instead of checking for Windows as the OS.

        if (FALSE === @symlink($target, $link)) { // FAILED.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::symlink: Failed - Cannot symlink $target to $link. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Worked. Log and return TRUE.

            return TRUE;
        }

    }
    // }}}
    // {{{ touch()

    /**
     * Set the modification and access time on a file to the present time.
     *
     * @param	file	String. Path and/or name of file to touch.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function touch($file, $time=FALSE)
    {
        $fp = fopen($file, 'w');
        fwrite($fp, '');
        fclose($fp);
        return file_exists($file) ? TRUE : SRA_Error::logError("SRA_File::touch: Failed - Cannot touch $file. $php_errormsg", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
    }
    // }}}
    // {{{ dirlist
    
    /**
     * Returns all file listings in a directory. This includes . and ..
     * Errors can occur if $dir is not a directory, or if the directory cannot be opened (read permissions, file handles, etc)
     * 
     * @param dir String. Full path of the directory to get a listing for
     * 
     * @return array on success. Err object on failure
     * @author Matthew Barlocker <mbarlocker@soleranetworks.com>
     */
     
     function dirlist($dir)
     {
     	$ret = null;
     	
     	if (is_dir($dir))
     	{
     		if ($handle = opendir($dir))
     		{
     			$file = '';
     			$ret = array();
     			while (($file = readdir($handle)) !== false)
     			{
     				$ret[] = $file;
     			}
     		}
     		else
     		{
     			$ret = SRA_Error::logError('Directory could not be opened: ' . $dir, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
     		}
     	}
     	else
     	{
     		$ret = SRA_Error::logError('Invalid directory: ' . $dir, __FILE__, __LINE__, SRA_ERROR_OPERATIONAL);
     	}
     	
     	return $ret;
     }
    // }}}
    // {{{ _rmdir()

    /**
     * Delete an empty directory.
     *
     * @param	dir	String. Path and/or name of empty directory to delete. Used
     * 						by rmdir.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function _rmdir($dir)
    {

        if (FALSE === @rmdir($dir)) { // FAILED.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::_rmdir: Failed - Cannot rmdir $dir. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Worked. Log and return TRUE.

            return TRUE;
        }

    }
    // }}}
    // {{{ rmdir()

    /**
     * Delete an empty directory OR a directory and all of its contents.
     *
     * @param	dir	String. Path and/or name of directory to delete.
     * @param	children	Boolean.	False: don't delete directory contents.
     *									True: delete directory contents.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function rmdir($dir, $children=FALSE)
    {

        // If children=FALSE only delete dir if empty.
        if (FALSE === $children) {

            // Call _rmdir.
            $error = SRA_File::_rmdir($dir);

            if (SRA_Error::isError($error)) { // error.

                $msg = "SRA_File::rmdir: Failed - Cannot rmdir $dir. ". $error->getErrorMessage();

                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

            }

        } else { // delete contents and dir.

            $handle = @opendir($dir);

            if (FALSE === $handle) { // SRA_Error.

                $msg = "SRA_File::rmdir: Failed - Cannot opendir() $dir. $php_errormsg";

                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

            } else { // Read from handle.

                // Don't error on readdir().
                while (false !== ($entry = @readdir($handle))) {

                    if ($entry != '.' &&$entry != '..') {

                        // Only add / if it isn't already the last char.
                        // This ONLY serves the purpose of making the
                        // output look nice:)

                        if (strpos(strrev($dir), '/') == 0) {// there is a /
                            $next_entry = $dir . $entry;
                        } else { // no /
                            $next_entry = $dir.'/'.$entry;
                        }

                        // NOTE: As of php 4.1.1 is_dir doesn't return FALSE it
                        // returns 0. So use == not ===.

                        // Don't error on is_dir()
                        if (is_link($next_entry) || !is_dir($next_entry)) { // Is file.

                            $error = SRA_File::unlink($next_entry); // Delete.

                            if (SRA_Error::isError($error)) { // error and return.

                                $msg = "SRA_File::rmdir: Failed - Cannot SRA_File::unlink() $next_entry. ". $error->getErrorMessage();

                                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                            }

                        } else { // Is directory.

                            $error = SRA_File::rmdir($next_entry, TRUE); // Delete

                            if (SRA_Error::isError($error)) { // error and return.

                                $msg = "SRA_File::rmdir: Failed - Cannot SRA_File::rmdir() $next_entry. ". $error->getErrorMessage();

                                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                            }

                        } // end is_dir else
                    } // end .. if
                } // end while
            } // end handle if

            // Don't error on closedir()
            @closedir($handle);

            $error = SRA_File::_rmdir($dir);

            if (SRA_Error::isError($error)) { // error and return.

                $msg = "SRA_File::rmdir: Failed - Cannot SRA_File::_rmdir() $dir. ". $error->getErrorMessage();

                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

            }

        }

        // Worked. Log and return TRUE.

        return TRUE;

    }
    // }}}
    // {{{ umask()

    /**
     * Set the umask for file and directory creation.
     *
     * @param	mode	Int. Permissions ususally in ocatal. Use leading 0 for
     *					octal. Number between 0 and 0777.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function umask($mode)
    {

        // Throw a warning if mode is 0. PHP converts illegal octal numbers to
        // 0 so 0 might not be what the user intended.

        if ($mode == 0) {
            trigger_error("SRA_File::umask: Warning - Creating a directory with permissions of 0. Is this what you wanted? Possible out of range octal number for mode.", SRA_ERROR_OPERATIONAL);
        }

        $str_mode = decoct($mode); // Show octal in messages.

        if (FALSE === @umask($mode)) { // FAILED.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::umask: Failed - Value $mode. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Worked. Log and return TRUE.

            return TRUE;
        }

    }
    // }}}
    // {{{ read()

    /**
     * Reads a file and stores the data in the variable passed by reference.
     *
     * @param	file	String. Path and/or name of file to read.
     * @param	rBuffer	Reference. Variable of where to put contents.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function read($file, &$rBuffer)
    {

        $fp = @fopen($file, "rb");  // b is for binary and used on Windows
                                    // ignored on *nix.

        if (FALSE === $fp) { // fopen FAILED.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::read: Failed - Cannot fopen $file. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // fopen worked. Log and try to lock it.

            if (FALSE) { // Locks don't seem to work on windows??? HELP!!!!!!!!!
            //if (FALSE === @flock($fp, LOCK_EX)) { // FAILED.
                // Add error from php to end of log message. $php_errormsg.
                $msg = "SRA_File::read: Failed - Cannot acquire flock on $file. $php_errormsg";

                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                return $resource;

            } else { // Try to get file size.

                $fs = @filesize($file);
                if (FALSE === $fs) { // FAILED.

                    // Add error from php to end of log message. $php_errormsg.
                    $msg = "SRA_File::read: Failed - Cannot get filesize of $file. $php_errormsg";

                    return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                } else { // Read file then close.

                    $rBuffer = @fread($fp, $fs);

                    if (FALSE === @fclose($fp)) { // FAILED.
                            // Add error from php to end of log message. $php_errormsg.
                            $msg = "SRA_File::read: Failed - Cannot fclose $file. $php_errormsg";

                            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                    } else {

                        return TRUE;

                    } // End fclose if

                } // End filesize if

            } // End flock if

        } // End fopen if
    }
    // }}}
    // {{{ _write()

    /**
     * _write the passed buffer to filename. Overwrites existing file if any.
     *
     * @param	file	Path and/or name of file to write.
     * @param	rBuffer	Reference. String to write.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function _write($file, &$rBuffer)
    {

        $fp = @fopen($file, "w");  // b is for binary and used on Windows
                                    // ignored on *nix.

		// fopen FAILED.
        if (FALSE === $fp) 
		{

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::_write: Failed - Cannot fopen $file. $php_errormsg";
            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        }
		// fopen worked. Log and try to lock it.
		else 
		{

			// FAILED
            if (FALSE === @flock($fp, LOCK_EX)) 
			{

                // Add error from php to end of log message. $php_errormsg.
                $msg = "SRA_File::_write: Failed - Cannot acquire flock on $file. $php_errormsg";

                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

            }
			// Write file.
			else 
			{
				// FAILED.
                if (-1 === @fwrite($fp, $rBuffer)) 
				{

                    // Add error from php to end of log message. $php_errormsg.
                    $msg = "SRA_File::_write: Failed - Cannot fwrite $file. $php_errormsg";

                    return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                }
				// Close.
				else 
				{
					// FAILED.
                    if (FALSE === @fclose($fp)) 
					{
                            // Add error from php to end of log message. $php_errormsg.
                            $msg = "SRA_File::_write: Failed - Cannot fclose $file. $php_errormsg";

                            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

                    } 
					else 
					{
                        return TRUE;
                    } // End fclose if

                } // End fwrite if

            } // End flock if

        } // End fopen if
    }
    // }}}
    // {{{ write()

    /**
     * Write() writes a file and makes directories in path if they don't
     * exist. additionally, this method handle synchronization issues using a 
     * temp lock file. it will wait up to 30 seconds before generating an error 
     * object when the file is locked at the time this method is invoked
     *
     * @param	file	String. Path and/or name of file to create.
     * @param	rBuffer	Reference. Contents to write.
     * @param	parents	Boolean. Create parent directories if they don't exist.
     * @param	mode	Int. The mode (permissions) of the new directories.
     *					If using octal add leading 0. eg. 0777. Mode is
     *					affect by the umask system setting.
     *
     * @return	TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function write($file, &$rBuffer, $parents=true, $mode=0777)
    {
      // try to get a lock on this file
      $lockFile = SRA_Controller::getSysTmpDir() . '/' . '.fwlock' . basename($file);
      $startTime = time();
      $curPid = file_exists($lockFile) ? SRA_File::toString($lockFile)*1 : NULL;
      while($curPid && SRA_Util::isProcessActive($curPid) && time() < ($startTime + (30))) {
        sleep(1);
      }
      if (file_exists($lockFile) && (!$curPid || SRA_Util::isProcessActive($curPid))) {
        $msg = "SRA_File::write: Failed - Cannot write to $file because a lock could not be obtained due to existing lock held by process " . $curPid;
        return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM);
      }
      $fp = fopen($lockFile, 'w');
      fwrite($fp, getmypid());
      fclose($fp);
      
        // If  already exists OR parents=FALSE. Write file and return.
        if (is_file($file) OR FALSE === $parents) 
		{
            $error = SRA_File::_write($file, $rBuffer);
			// error.
            if (SRA_Error::isError($error)) 
			{
                SRA_File::unlink($lockFile);
                $msg = "SRA_File::write: Failed - Cannot write() $file. ". $error->getErrorMessage();
                return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

            }
            SRA_File::unlink($lockFile);
            // Success.
            return TRUE;
        }

        // Throw a warning if mode is 0. PHP converts illegal octal numbers to
        // 0 so 0 might not be what the user intended.

        if ($mode == 0) 
		{
            trigger_error("SRA_File::write: Warning - Creating a directory with permissions of 0. " . 
						  "Is this what you wanted? Possible out of range octal number for mode.", SRA_ERROR_OPERATIONAL);
        }

        $str_mode = decoct($mode); // Show octal in messages.

        // Get path parts. Don't error.
        $path_parts = @pathinfo($file);

        // Make path.
        $error = SRA_File::mkdir($path_parts["dirname"], $mode, TRUE);
		
		// error.
        if (SRA_Error::isError($error)) 
		{

            SRA_File::unlink($lockFile);
            $msg = "SRA_File::write: Failed - Cannot write() $file. ". $error->getErrorMessage();
            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        }

        // Directory structure has been made write file.
        $error = SRA_File::_write($file, $rBuffer);
		
		// error.
        if (SRA_Error::isError($error)) 
		{
            SRA_File::unlink($lockFile);
            $msg = "SRA_File::write: Failed - Cannot write() $file. ". $error->getErrorMessage();
            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        }

        // Worked. Log and return TRUE.
        SRA_File::unlink($lockFile);
        return TRUE;

    }
    // }}}
    // {{{ fileMTime()

    /**
     * Get the modified time for a file.
     *
     * @param	file	String. Path and name of file.
     *
     * @return	Int. Unix timestamp on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function fileMTime($file)
    {

        $mtime = @fileMTime($file);

        if (FALSE === $mtime) { // FAILED. Log and return err.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::fileMTime: Failed - Cannot can not get modified time of $file. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Worked.
            return $mtime;
        }
    }
    // }}}
    // {{{ compareMTimes()

    /**
     * Compare the modified time of two files.
     *
     * @param	file1	String. Path and name of file1.
     * @param	file2	String. Path and name of file2.
     *
     * @return	Int. 	1 if file1 is newer.
                        -1 if file2 is newer.
                        0 if files have the same time.
                        Err object on failure.

     * @author  Charlie Killian, charlie@tizac.com
     */

    function compareMTimes($file1, $file2)
    {

        $mtime1 = SRA_File::fileMTime($file1);
        $mtime2 = SRA_File::fileMTime($file2);

        if (SRA_Error::isError($mtime1)) { // FAILED. Log and return err.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::compareMTimes: Failed - Cannot can not get modified time of $file1. $mtime1->getErrorMessage";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } elseif (SRA_Error::isError($mtime2)) { // FAILED. Log and return err.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::compareMTimes: Failed - Cannot can not get modified time of $file2. $mtime2->getErrorMessage";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Worked. Log and return compare.

            // Compare mtimes.
            if ($mtime1 == $mtime2) {
                return 0;
            } else {
                return ($mtime1 < $mtime2) ? -1 : 1;
            } // end compare
        }
    }
    // }}}
    // {{{ parseIniFile()

    /**
     * Proccess an ini file returning an array of values.
     *
     * @param	pathname		String. Path and name to ini file.
     * @param	processSections	Boolean. TRUE include sections as associative
     *							array keys. See PHP manual.
     *
     * @return	Associative array of values on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     */

    function parseIniFile($pathname, $processSections=FALSE)
    {

        $iniArray = @parseIni_file($pathname, $processSections);

        // parseIni_file() returns 0 not FALSE as of PHP 4.1.1 so don't use ===
        if (FALSE == $iniArray) { // Copy FAILED. Log and return err.

            // Add error from php to end of log message. $php_errormsg.
            $msg = "SRA_File::parseIniFile: Failed - Cannot parse $pathname. processSections=$processSections. $php_errormsg";

            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));

        } else { // Copy worked. Log and return TRUE.

            return $iniArray;
        }
    }
    // }}}

  // {{{ getFileList
  /**
   * returns an array of paths to files names that meet the specified criteria
   * @param	string $path	the base path to search
   * @param	string $pattern regular expression pattern or exact name to match. 
   * use '*' to match all. default is '*'
   * @param boolean $recursive whether or not the search should descend into 
   * child directories
   * @param int $type bitmask identifying the file types to return where 1 is 
   * files and 2 is directories (3=both files and directories). default is 1
   * @return string[]
   */
  function getFileList($path, $pattern='*', $recursive=FALSE, $type=1) {
    $files = array();
    $path = substr($path, -1) !== '/' ? $path .= '/' : $path;
    $pattern = $pattern == '*' ? '/^.*$/' : $pattern;
    if (is_dir($path) &&$pattern) {
      $d = dir($path);
      while(FALSE !== ($file = $d->read())) {
        $filePath = $path . $file;
        if ($file != '.' &&$file != '..' && is_dir($filePath) &&$recursive) { $files = array_merge($files, SRA_File::getFileList($filePath, $pattern, $recursive, $type)); }
        if ($file == '.' || $file == '..' || ($pattern != $file && (((substr($pattern, 0, 1) == '/') && !preg_match($pattern, $file)) || substr($pattern, 0, 1) != '/'))) { continue; }
        if ((is_dir($filePath) && ($type & 2)) || (!is_dir($filePath) && ($type & 1))) { $files[] = $filePath; }
      }
      $d->close();
    }
    return $files;
  }
  // }}}

  // {{{ createRandomFile
  /**
   * creates a temp file prefixed by $pre and postfixed by $post in $dir with 
   * the contents $base. if $base is a path to a local or remote file the 
   * SRA_File::copy method will be used to copy it into the created file and the 
   * created file extension will be the same as the original file. the absolute 
   * path to the random file created will be returned
   * @param	string $dir the directory where the file should be created. if not 
   * specified, the SRA_Controller::getAppTmpDir will be used
   * @param	string $pre a string to add to the beginning of the file. this 
   * attribute is optional. The default is ''
   * @param string $post a string to add to the end of the file. this attribute 
   * is optional. The default is ''.
   * @param string $base optional file contents or path to an existing file to 
   * copy into the newly created random file
   * @param boolean $delete whether or not to delete this file automatically 
   * upon termination of the current php process
   * @param boolean $mkdir whether or not the file should be created as a 
   * directory
   * @return string
   */
  function createRandomFile($dir=NULL, $pre='', $post='', $base='', $delete=FALSE, $mkdir=FALSE) {
    $dir = is_dir($dir) ? $dir : SRA_Controller::getAppTmpDir();
    $dir = SRA_Util::endsWith($dir, '/') ? $dir : $dir . '/';
    $file = uniqid($pre);
    $file = $dir . $file . $post;
    if (is_file($base) || strpos($base, '://') && !strpos($base, ' ')) {
      $ext = SRA_Util::getFileExtension($base);
      if (!SRA_Util::endsWith($file, $ext)) { $file .= '.' . $ext; }
      SRA_File::copy($base, $file);
      if (!file_exists($file)) {
        $fp = fopen($file, 'w');
        fclose($fp);
      }
    }
    else {
      if ($mkdir) {
        SRA_File::mkdir($file);
      }
      else {
        $fp = fopen($file, 'w');
        fwrite($fp, $base);
        fclose($fp);
      }
    }
    if ($delete && is_file($file)) {
      static $_sraFileDeleteRandomFiles;
      SRA_File::deleteRandomFiles($file);
      if (!$_sraFileDeleteRandomFiles) {
        SRA_Controller::registerShutdownMethod($className = 'SRA_File', 'deleteRandomFiles');
        $_sraFileDeleteRandomFiles = TRUE;
      }
    }
    return $file;
  }
  // }}}
  
  // {{{ deleteRandomFiles
  /**
   * deletes any random files created using SRA_File::createRandomFile when 
   * $delete is TRUE
   * @param	string $file the file to delete, if NULL, the files will be deleted
   * @return void
   */
  function deleteRandomFiles($file=NULL) {
    static $_sraFileRandomFiles = array();
    if ($file) {
      $_sraFileRandomFiles[] = $file;
    }
    else {
      foreach($_sraFileRandomFiles as $file) {
        if (file_exists($file)) { SRA_File::unlink($file); }
      }
    }
  }
  // }}}

  // {{{ toString()
  /**
   * this method converts a file to a string. it returns an SRA_Error object if 
   * it is unable to find or open the file
   * @param	string $file the absolute or relative path to the file
   * @param	int $err the level of the error that should be thrown if the file 
   * cannot be found or opened. this value will correspond to one of the 
   * SRA_Error class error level constants. By default this value is 
   * SRA_ERROR_PROBLEM
   * @return string
   * @author Jason Read <jason@idir.org>
   */
  function &toString($file, $err=SRA_ERROR_PROBLEM) {
    $ret = FALSE !== ($content = file(file_exists($file) ? $file : SRA_File::getRelativePath(NULL, $file))) ? ($ret = implode('', $content)) : SRA_Error::logError('SRA_File::toString - Failed: Could not open the file "' . $file . '"', __FILE__, __LINE__, $err);
    return $ret;
  }
  // }}}

    // {{{ getMimeType()
    /**
     * This method attempts to lookup the mime type for the file provided (based on the file extension)
     *
     * @param	fileName String. The name of the file to lookup the mime type for (this file must have an extension)
     *
     * @return	String
     * @author  Jason Read <jason@idir.org>
     */

    function getMimeType( $file )
    {
        if (isset($file))
        {
            /* build an array keyed on the file ext */
            if (is_readable(SRA_FILE_MIME_TYPE_FILE))
            {
                $mime_types=array();
                /* open our mime.types file for reading */
                $mt_fd=fopen(SRA_FILE_MIME_TYPE_FILE,"r");
                while (!feof($mt_fd))
                {
                    /* pull a line off the file */
                    $mt_buf=trim(fgets($mt_fd,1024));
                    /* discard if the line was blank or started with a comment */
                    if (strlen($mt_buf) > 0) if (substr($mt_buf,0,1) != "#")
                    {
                        /* make temp array of the mime.types line we just read */
                        $mt_tmp=preg_split("/[\s]+/", $mt_buf, -1, PREG_SPLIT_NO_EMPTY);
                        $mt_num=count($mt_tmp);
                        /* if $mt_num = 1 then we got no file extensions for the type */
                        if ($mt_num > 1)
                        {
                            for ($i=1;$i<$mt_num;$i++)
                            {
                                /* if we find a comment mid-line, stop processing */
                                if (strstr($mt_tmp[$i],"#"))
                                {
                                    break;
                                /* otherwise stick the type in an array keyed by extension */
                                }
                                else
                                {
                                    $mime_types[$mt_tmp[$i]]=$mt_tmp[0];
                                }
                            }
                        /* zero the temporary array */
                        unset($mt_tmp);
                        }
                    }
                }
                /* close the mime.types file we were reading */
                fclose($mt_fd);
                $filePieces=explode(".", $file);
                if (array_key_exists($filePieces[count($filePieces)-1], $mime_types))
                    return $mime_types[$filePieces[count($filePieces)-1]];
            }
            else
                return SRA_Error::logError("SRA_File::getMimeType() failed: Unable to open mime.types file.",
                                        __FILE__, __LINE__, SRA_ERROR_PROBLEM);
        }
        else
            return SRA_Error::logError("SRA_File::getMimeType() failed: Invalid file parameter.",
                                    __FILE__, __LINE__, SRA_ERROR_PROBLEM);
    }
    // }}}
    
  // {{{ getArchiveType
  /**
   * returns the archive type identifier for the $archive specified
   * @param	string $archive the name of the archive
   * @return string
   */
  function getArchiveType($archive) {
    return SRA_Util::endsWith($archive, SRA_FILE_ARCHIVE_TYPE_TAR) ? SRA_FILE_ARCHIVE_TYPE_TAR : (SRA_Util::endsWith($archive, SRA_FILE_ARCHIVE_TYPE_TAR_GZ) || SRA_Util::endsWith($archive, SRA_FILE_ARCHIVE_TYPE_TAR_GZ1) ? SRA_FILE_ARCHIVE_TYPE_TAR_GZ : (SRA_Util::endsWith($archive, SRA_FILE_ARCHIVE_TYPE_ZIP) ? SRA_FILE_ARCHIVE_TYPE_ZIP : NULL));
  }
  // }}}

  // {{{ uncompress
  /**
   * uncompresses an archive
   * @param	string $archive the name of the archive to uncompress
   * @param string $dest the destination path where $archive should be 
   * uncompressed into. if not specified, the archive will be uncompressed into 
   * the parent directory of $archive
   * @param boolean $delete whether or not $archive should be deleted after it 
   * is uncompressed
   * @param string $archiveType the archive type (if $archive does not end with 
   * one of the SRA_FILE_ARCHIVE_TYPE_* file extensions
   * @return boolean
   */
  function uncompress($archive, $dest=NULL, $delete=FALSE, $archiveType=NULL) {
    $archiveType = $archiveType ? $archiveType : SRA_File::getArchiveType($archive);
    $dest = $dest ? $dest : dirname($archive);
    SRA_Util::printDebug('SRA_File::uncompress: Attempting to uncompress ' . $archive . ' to ' . $dest, SRA_FILE_DEBUG, __FILE__, __LINE__);
    if ($archiveType && file_exists($archive) && is_dir($dest) && is_writable($dest)) {
      switch ($archiveType) {
        case SRA_FILE_ARCHIVE_TYPE_TAR:
        case SRA_FILE_ARCHIVE_TYPE_TAR_GZ:
          if ($bin = SRA_File::findInPath('tar')) {
            exec("cd $dest; " . $bin . ' -x' . ($archiveType == SRA_FILE_ARCHIVE_TYPE_TAR_GZ ? ' -z' : '') . ' -f ' . $archive);
            if ($delete) unlink($archive);
            return TRUE;
          }
          break;
        case SRA_FILE_ARCHIVE_TYPE_ZIP:
          SRA_Util::printDebug('SRA_File::uncompress: Using zip uncompress', SRA_FILE_DEBUG, __FILE__, __LINE__);
          if ($bin = SRA_File::findInPath('unzip')) {
            exec("cd $dest; " . $bin . ' ' . $archive);
            if ($delete) unlink($archive);
            return TRUE;
          }
          break;
      }
    }
    return FALSE;
  }
  // }}}

  // {{{ compress
  /**
   * this method creates a compressed archive of $file. it returns the name of 
   * the of the compressed archive on success, FALSE otherwise
   * @param	string $file the path to the file or directory to compress
   * @param	string $archiveType the archive type to create. should be one of 
   * the SRA_FILE_ARCHIVE_TYPE_* constants
   * @param string $archive the name of the archive to create. if not specified 
   * the archive name will be the name $file plus the correct file extension 
   * based on the $archiveType specified and placed in the same directory as 
   * $file
   * @param	string $directory is $file is a directory, this parameter may be 
   * used to specify the starting directory for the archive. if not specified, 
   * the archive will start at the root directory ('/')
   * @return mixed
   */
  function compress($file, $archiveType=SRA_FILE_ARCHIVE_TYPE_TAR_GZ, $archive=NULL, $directory=NULL) {
    if ($directory && !SRA_Util::endsWith($directory, '/')) $directory .= '/';
    $archive = $archive ? $archive : $file . '.' . $archiveType;
    SRA_Util::printDebug('SRA_File::compress: Attempting to compress ' . $file . ' using archive type ' . $archiveType . ' and archive name ' . $archive, SRA_FILE_DEBUG, __FILE__, __LINE__);
    if (file_exists($file)) {
      switch ($archiveType) {
        case SRA_FILE_ARCHIVE_TYPE_TAR:
        case SRA_FILE_ARCHIVE_TYPE_TAR_GZ:
        case SRA_FILE_ARCHIVE_TYPE_TAR_GZ1:
          if ($bin = SRA_File::findInPath('tar')) {
            exec($bin . ($directory ? ' -C ' . $directory : '') . ($archiveType == SRA_FILE_ARCHIVE_TYPE_TAR_GZ || $archiveType == SRA_FILE_ARCHIVE_TYPE_TAR_GZ1 ? ' -z' : '') . ' -c -f ' . $archive . ' ' . ($directory ? str_replace($directory, '', $file) : $file));
            return $archive;
          }
        case SRA_FILE_ARCHIVE_TYPE_ZIP:
          if ($bin = SRA_File::findInPath('zip')) {
            exec(($directory ? "cd $directory;" : '') . $bin . ' -q -r ' . $archive . ' ' . ($directory ? str_replace($directory, '', $file) : $file));
            return $archive;
          }
      }
    }
    return FALSE;
  }
  // }}}

  // {{{ findFile
  /**
   * looks for $file in $dirs and returns the absolute path if it is found or 
   * NULL if it is not
   * @param	string $file the name of the file to look for
   * @param	array $dirs the array of directories to look in
   * @return string
   */
  function findFile($file, $dirs) {
    foreach($dirs as $dir) {
      $dir = SRA_Util::endsWith($dir, '/') ? $dir : $dir . '/';
      if (is_file($dir . $file)) { return $dir . $file; }
    }
    return NULL;
  }
  // }}}

    // {{{ getContents()
    /**
     * This method returns the contents of a file as a string limited by the parameters specified.
     * It returns false if there are no more blocks in the file that meet the criteria.
     *
     * @param	fp - A reference to the file pointer for the file.
     * @param	options - A reference to a key based options array. The possible values for options are as follows:
     * 			'start_string' => 	String that should appear at the start of a block of text to be parsed. This value will be
     *								converted to an array if it contains a "&". In this case, all values in the array must exist in the line.
     *								If this value contains a ':', the end string criteria will be converted to an array of arrays each containing
     * 								one set of strings that must be present for the end to have been found.
     * 			'start_line_num' => String that should appear at the start of a block of text to be parsed.
     *					   			This option is overriden if 'start_string' is specified.
     * 			'end_string' => 	String that should appear at the end of a block of text to be parsed
     *				   				(either this text or an EOF should exist). This value will be
     *								converted to an array if it contains a "&". In this case, all values in the array must exist in the line.
     *								If this value contains a ':', the end string criteria will be converted to an array of arrays each containing
     * 								one set of strings that must be present for the end to have been found.
     * 			'header_lines' => 	The # of lines that make up the file header. These lines will be ignored.
     *								This value is only taken into consideration when at BOF.
     * 			'line_count' => 	The # of lines to be returned. This option is overriden if 'end_string' is specified.
     * 			'min_line_count' => This option specifies the minimum # of lines that should exist in a text block.
     *
     * @return	String
     * @author  Jason Read <jason@idir.org>
     */

    function &getContents( &$fp, &$options )
    {
        SRA_Util::printDebug("getContents - called with the following options:",
                        SRA_FILE_DEBUG, __FILE__, __LINE__);
        SRA_Util::printDebug($options, SRA_FILE_DEBUG, __FILE__, __LINE__);

        $string='';
        $buffer='';
        $start=false;
        $searchStartVals=false;
        $searchStopVals=false;
        $headerChecked=false;
        if (ftell($fp)!=0 || !array_key_exists("header_lines", $options))
            $headerChecked=true;

        $lineNum=0;
        $lineCount=0;
        while (!feof ($fp))
        {
            $prevPos=ftell($fp);
            $buffer=fgets($fp, 4096);
            $lineNum++;

            SRA_Util::printDebug("getContents - lineNum=$lineNum - Processing buffer: '$buffer'",
                            SRA_FILE_DEBUG, __FILE__, __LINE__);

            // Check for start
            if (!$start)
            {
                SRA_Util::printDebug("getContents - lineNum=$lineNum - Checking if buffer should be included in return value.",
                                SRA_FILE_DEBUG, __FILE__, __LINE__);

                // check if header has been skipped
                if (!$headerChecked && array_key_exists("header_lines", $options) && ($lineNum - 1) == $options["header_lines"])
                {
                    SRA_Util::printDebug("getContents - lineNum=$lineNum - Header has been checked.",
                                    SRA_FILE_DEBUG, __FILE__, __LINE__);
                    $lineNum=0;
                    $headerChecked=true;
                }

                // start_string overrides start_line_num
                if ($headerChecked && array_key_exists("start_string", $options))
                {
                    if (!$searchStartVals)
                    {
                        $search=str_replace("#EOL#", '', $options["start_string"]);
                        $search=str_replace("\\n", "\n", $search);
                        $searchStartVals=explode(':', $search);
                        for ($i=0; $i<count($searchStartVals); $i++)
                            $searchStartVals[$i]=explode("&", $searchStartVals[$i]);
                    }
                    SRA_Util::printDebug("getContents - lineNum=$lineNum - Checking for existence of start search strings.",
                                    SRA_FILE_DEBUG, __FILE__, __LINE__);
                    for ($i=0; $i<count($searchStartVals); $i++)
                    {
                        $start=true;
                        foreach ($searchStartVals[$i] as $search)
                        {
                            if (!strpos(' ' . $buffer, $search))
                            {
                                SRA_Util::printDebug("getContents - lineNum=$lineNum - Start search string $i '$search' NOT found in buffer. Proceeding to next
                                                 search start row...", SRA_FILE_DEBUG, __FILE__, __LINE__);
                                $start=false;
                                break;
                            }
                            else
                            {
                                SRA_Util::printDebug("getContents - lineNum=$lineNum - Start search string $i '$search' found in buffer.",
                                                SRA_FILE_DEBUG, __FILE__, __LINE__);
                            }
                        }
                        if ($start==true)
                            break;
                    }
                }
                // start line num has been reached
                else if ($headerChecked && array_key_exists("start_line_num", $options) && ($lineNum - 1) == $options["start_line_num"])
                {
                    SRA_Util::printDebug("getContents - lineNum=$lineNum - Start line num has been reached. Adding buffer.",
                                    SRA_FILE_DEBUG, __FILE__, __LINE__);
                    $start=true;
                }
                else if ($headerChecked && !array_key_exists("start_line_num", $options))
                {
                    SRA_Util::printDebug("getContents - lineNum=$lineNum - No start line num or start string specified. Adding buffer.",
                                    SRA_FILE_DEBUG, __FILE__, __LINE__);
                    $start=true;
                }
            }

            // Check for end
            // end_string overrides line_count
            if ($lineCount>0 &&$start && array_key_exists("end_string", $options))
            {
                SRA_Util::printDebug("getContents - lineNum=$lineNum - Checking if end of contents has been reached.",
                                SRA_FILE_DEBUG, __FILE__, __LINE__);
                if (!$searchStopVals)
                {
                    $stopSearch=str_replace("#EOL#", '', $options["end_string"]);
                    $stopSearch=str_replace("\\n", "\n", $stopSearch);
                    $searchStopVals=explode(':', $stopSearch);
                    for ($i=0; $i<count($searchStopVals); $i++)
                        $searchStopVals[$i]=explode("&", $searchStopVals[$i]);
                }

                for ($i=0; $i<count($searchStopVals); $i++)
                {
                    $stop=true;
                    foreach ($searchStopVals[$i] as $stopSearch)
                    {
                        if (!strpos(' ' . $buffer, $stopSearch) &&$stopSearch != "\n")
                        {
                            SRA_Util::printDebug("getContents - lineNum=$lineNum - Stop search string $i '$stopSearch' NOT found in buffer. Proceeding to next row...",
                                            SRA_FILE_DEBUG, __FILE__, __LINE__);
                            $stop=false;
                            break;
                        }
                        else
                        {
                            SRA_Util::printDebug("getContents - lineNum=$lineNum - Stop search string $i '$stopSearch' found in buffer.",
                                            SRA_FILE_DEBUG, __FILE__, __LINE__);;
                        }
                    }
                    if ($stop==true)
                        break;
                }

                if ($stop)
                {
                    if (array_key_exists("min_line_count", $options))
                    {
                        SRA_Util::printDebug("getContents - lineNum=$lineNum - Checking if min line count requirement has been fulfilled.",
                                        SRA_FILE_DEBUG, __FILE__, __LINE__);
                        if ($lineCount >= $options["min_line_count"])
                        {
                            fseek($fp, $prevPos);
                            break;
                        }
                        else
                        {
                            SRA_Util::printDebug("getContents - lineNum=$lineNum - Min line count " . $options["min_line_count"] . " has NOT been reached.",
                                            SRA_FILE_DEBUG, __FILE__, __LINE__);
                        }
                    }
                    else
                    {
                        fseek($fp, $prevPos);
                        break;
                    }
                }
            }
            // line count has been reached
            else if (array_key_exists("line_count", $options) &&$lineCount == $options["line_count"])
            {
                SRA_Util::printDebug("getContents - lineNum=$lineNum - line count " . $options["line_count"] . " has been reached.",
                                SRA_FILE_DEBUG, __FILE__, __LINE__);
                fseek($fp, $prevPos);
                break;
            }

            if ($start)
            {
                SRA_Util::printDebug("getContents - lineNum=$lineNum - Adding buffer to return string.",
                                SRA_FILE_DEBUG, __FILE__, __LINE__);
                $string .= $buffer;
                $lineCount++;
            }

        }
        if ($string=='')
            return FALSE;
        else
            return $string;
    }
    // }}}
	
    // {{{ appendToFile()
    /**
     * This method is used to append a data to a file. It is useful for such things as 
	 * writing to data files such as csv files. If the file does not exist it is created. 
	 * This is a static method of the SRA_File class. All data appened to the file will be
	 * followed by a newline.
     *
     * @param	fileName. String. The name of the file to write to. This file may use 
	 * 			dynamic Date/Time tags. It will be passed through the SRA_GregorianDate::parseString 
	 * 			method. If the file does not exist, it will be created.
     * @param	data. String. The data to append to the text. 
	 * @param	header. String. An optional header to add to the file if it is created 
	 * 			(does not already exist).
	 * @param	chownUser. String. A user to chown the file to if it is created.
     *
     * @return	String - false if not found
     * @author  Jason Read <jason@idir.org>
     */

    function appendToFile( $fileName, $data, $header=false, $chownUser=false )
    {
		$dateTime = new SRA_GregorianDate();
        $fileName = $dateTime->parseString($fileName);
		if (!file_exists($fileName))
		{
			if (!$fp = @fopen($fileName, "w"))
			{
				return SRA_Error::logError("SRA_File::appendToFile: Failed - Unable to create file '$fileName'", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
			}
			if ($header)
			{
				if (-1 === @fwrite($fp, $header . "\n"))
				{
					return SRA_Error::logError("SRA_File::appendToFile: Failed - Unable to write header to file.", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
				}
			}
			fclose($fp);
			if ($chownUser)
			{
				if (!chown($fileName, $chownUser))
				{
					return SRA_Error::logError("SRA_File::appendToFile: Failed - Unable to chown file '$fileName' to user '$chownUser'", 
											__FILE__, __LINE__, SRA_ERROR_PROBLEM);
				}
			}
		}
		if (!$fp = @fopen($fileName, "a"))
		{
			return SRA_Error::logError("SRA_File::appendToFile: Failed - Unable to open file '$fileName'", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
		}
		if (-1 === @fwrite($fp, $data . "\n"))
		{
			return SRA_Error::logError("SRA_File::appendToFile: Failed - Unable to write data to file.", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
		}
		fclose($fp);
    }
    // }}}

    // {{{ parseIni()
    /**
     * Load in the ini file specified in filename, and return
     * the settings in an associative array. By setting the
     * last $processSections parameter to true, you get a
     * multidimensional array, with the section names and
     * settings included. The default for processSections is
     * false.
     *
     * This functions should replace php native parseIni_file which has too
     * many shortcomings as of v 4.2.2.
     *
     * @param   fileName. String. ini file to parse.
     * @param   processSections. boolean. Optional parameter that specifies 
	 * whether or not to break ini sections out into seperate elements in 
	 * the returned array. The default value for this is false.
     *
     * @access	public static
     * @return	String[]: An associative array containing the data
     * @author	<info@megaman.nl>
     * @author	Sebastien Cevey <seb@cine7.net>
     * @author	Jason Read <jason@idir.org>
     */
    function parseIni($fileName, $processSections=false)
    {
        if (!is_readable($fileName))
        {
            // SRA_Error.
            $msg = "SRA_File::parseIni: Failed - Passed fileName isn't readable, $fileName.";
            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));
        }

        if (!is_bool($processSections))
        {
            // SRA_Error.
            $msg = "SRA_File::parseIni: Failed - Passed processSections isn't a boolean. It's a ". gettype($processSections);
            return(SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM));
        }

        $iniArray = array();
        $secName = '';

        $lines = file($fileName);

        foreach($lines as $line)
        {
            $line = trim($line);

            // Don't process blank lines or comments.
            if($line == '' || $line{0} == ";" || $line{0} == "#")
            {
                continue;
            }

            if($line{0} == "[" &&$line{strlen($line) - 1} == "]")
            {
                $secName = substr($line, 1, strlen($line) - 2);
            }
            else
            {
                $pos      = strpos($line, "=");
                $property = trim(substr($line, 0, $pos));
                $value    = trim(substr($line, $pos + 1));

                if($processSections)
                {
                    $iniArray[$secName][$property] = $value;
                }
                else
                {
                    $iniArray[$property] = $value;
                }
            }
        }

        return($iniArray);
    }
    // }}}
	
    // {{{ arrayToFile()
    /**
     * This method converts an array to a file. 
     *
	 * @param	$fileName. String. The name of the file to write to. Any existing data 
	 * 			in this file will be overwritten.
	 * @param	$arrayName. String. The name to specify for the array in the file. 
	 * @param	$array. String[]. The array to write to the file. 
	 * @param	$append. boolean. Whether or not the array should be appended to the 
	 * 			file if it already exists. The default value for this parameter is 
	 * 			false: existing data will be overwritten.
     * @access	private static
     * @return	boolean
     * @author	Jason Read <jason@idir.org>
     */
    function arrayToFile($fileName, $arrayName, &$array, $append = false)
    {
		// SRA_File is not writable
		if (!is_dir(dirname($fileName)) || !is_writable(dirname($fileName)))
		{
			return SRA_Error::logError("SRA_File::arrayToFile: Failed - Directory '" . dirname($fileName) . 
								   "' does not exist or is not writable", __FILE__, __LINE__, SRA_ERROR_PROBLEM);
		}
		// No array name provided
		if (!is_scalar($arrayName))
		{
			return SRA_Error::logError("SRA_File::arrayToFile: Failed - Array name not provided", 
								   __FILE__, __LINE__, SRA_ERROR_PROBLEM);
		}
		// No array given
		if (!is_array($array))
		{
			return SRA_Error::logError("SRA_File::arrayToFile: Failed - array parameter is not an array", 
								   __FILE__, __LINE__, SRA_ERROR_PROBLEM);
		}
		
		// Write array to file
		$buffer = SRA_Util::bufferArray($array, $arrayName);
		$buffer = "<?php\n" . $buffer . "\n?>\n";
		if ($append)
		{
			SRA_File::appendToFile($fileName, $buffer);
		}
		else
		{
			SRA_File::write($fileName, $buffer);
		}
	}
	// }}}
	
	// {{{ fileToArray
	/**
	 * Converts a file to an array. Similiar to the 'file' function but also 
	 * strips out ending newlines
	 * @param file : String - the path to the file to convert
   * @access  public
	 */
	function &fileToArray($file) {
		$data = array();
    if (file_exists($file) && is_readable($file)) {
      $lines = file($file);
      $keys = array_keys($lines);
      foreach ($keys as $key) {
        $data[] = str_replace("\n", '', $lines[$key]);
      }
    }
		return $data;
	}
	// }}}
	
    // {{{ serialize()
    /**
     * Serializes an object to a file. Returns an SRA_Error object if any occur.  
     *
	 * @param	object : String - The object to serialize. An SRA_Error will be 
	 * 			returned if this parameter is a scalar value.
	 * @param	file : String - The file to serialize the object to. If this 
	 * 			parameter is not specified, a random file will be created and 
	 * 			the name of that file returned.
     * @access	public static
     * @return	String
     * @author	Jason Read <jason@idir.org>
     */
    function serialize($object, $file = false)
    {
		// Validate parameters
		if (is_scalar($object))
		{
			$msg = "SRA_File::serialize: Failed - object parameter '$object' is scalar";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
		}
		if ($file && !file_exists($file))
		{
			$msg = "SRA_File::serialize: Failed - file parameter '$file' does not exist";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
		}
		
		// Get random file if necessary
		if (!$file)
		{
			if (SRA_Error::isError($conf =& SRA_Controller::getSysConf()))
			{
				$msg = "SRA_File::serialize: Failed - Could not obtain reference to sys conf";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
			}
			if (SRA_Error::isError($file = SRA_File::createRandomFile(SRA_Controller::getAppTmpDir())))
			{
				$msg = "SRA_File::serialize: Failed - Random file could not be created in directory: '" . SRA_Controller::getAppTmpDir() . "'";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_ASYNC_PROCESS_DEBUG);
			}
		}
		
		// Write serialized object to file
		$object = @serialize($object);
		if (FALSE === $object || SRA_Error::isError(SRA_File::write($file, $object)))
		{
			unlink($file);
			$msg = "SRA_File::serialize: Failed - Serialized object could not be serialized or written to file '$file'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_ASYNC_PROCESS_DEBUG);
		}
		
		return $file;
		
	}
	// }}}
	
    // {{{ unserialize()
    /**
     * Unserializes an object from a file. Returns an SRA_Error object if any occur.
     *
	 * @param	file : String - The name of the file to unserialize.
	 * @param	deleteFile : boolean - Whether or not the serialization file should
	 *			be deleted if the object is unserialized successfully.
     * @access	public static
     * @return	Object
     * @author	Jason Read <jason@idir.org>
     */
    function &unserialize($file, $deleteFile = false)
    {
		// Validate parameters
		if (!file_exists($file))
		{
			$msg = "SRA_File::unserialize: Failed - file parameter '$file' does not exist";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
		}
		
		// Unserialize object
		$object = @unserialize(SRA_File::toString($file));
		if (FALSE === $object)
		{
			$msg = "SRA_File::unserialize: Failed - object could not be unserialized from file '$file'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_CONTROLLER_DEBUG);
		}
		
		// Delete file 
		if ($deleteFile)
		{
			unlink($file);
		}
		
		return $object;
		
	}
	// }}}
	
    // {{{ propertiesArrayToFile()
    /**
     * This method performs the reverse of the propertiesFileToArray method. It writes an 
	 * associative properties file array to a properties file. 
     *
	 * @param	file : String -  The file to write to. Any existing data will be overwritten.
	 * @param	data : String[] - An associative array of properties to write.
     * @access	public static
     * @return	String[]
     * @author	Jason Read <jason@idir.org>
     */
    function &propertiesArrayToFile($file, &$data)
	{
		// Validate parameters
		if (!is_array($data))
		{
			$msg = "SRA_File::propertiesArrayToFile: Failed - data parameter '" .gettype($data) . "' is not an array";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
		}
		
		$buffer = '';
		foreach ($data as $key => $value)
		{
			$buffer .= $key . "=" . $value . "\n";
		}
		
		if (SRA_Error::isError(SRA_File::write($file, $buffer)))
		{
			$msg = "SRA_File::propertiesArrayToFile: Failed - Could not write data to file '$file'";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
		}
		
	}
	// }}}
	
  // {{{ propertiesFileToArray()
  /**
   * This method converts a properties file to an associative array. It also performs 
	 * caching of this data to temp files in the {SRA_DIR}/tmp/l10n directory. 
	 * The property or key may also include imbedded php code through use of the 
	 * php:: {code} ::php convention. The code itself should be a simple statement that 
	 * returns a value. This value will be inserted into the key or property in the 
	 * location in which they exist (multiple code segments may exist in various locations 
	 * of a key or property).
   *
	 * @param	file : String - The file to convert. if this value is a string (and 
   * not a file), it will be written to a temporary file, converted to a 
   * properties array, and returned
	 * @param	keyCase : int - The key case to use. By default, this method will maintain
	 * 			the case used in the properties file (0). Optionally, you may specify -1
	 * 			in this parameter for lowercase only keys, and 1 for uppercase only keys.
	 * @param	languageCode : String - The language code
   * @param boolean $cache whether or not to cache files
   * @access	public static
   * @return	String[]
   * @author	Jason Read <jason@idir.org>
   */
    function &propertiesFileToArray($file, $keyCase = 0, $languageCode = '', $cache=TRUE)
    {
      static $_sraFilePropertiesFileToArrayCache = array();
    
    $isRemote = strpos($file, '://') ? TRUE : FALSE;
    if ($cache &&$isRemote && isset($_sraFilePropertiesFileToArrayCache[$file])) { return $_sraFilePropertiesFileToArrayCache[$file]; }
    
    $tempFile = NULL;
    if (!$isRemote && is_string($file) && !file_exists($file)) {
      $tempFile = SRA_File::createRandomFile(FALSE, '', '', $file);
      $file = $tempFile;
    }
		
		// Validate parameters
		if (!$isRemote && !file_exists($file))
		{
			$msg = "SRA_File::propertiesFileToArray: Failed - file parameter '$file' does not exist";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
		}
		if ($keyCase != 0 &&$keyCase != -1 &&$keyCase != 1)
		{
			$msg = "SRA_File::propertiesFileToArray: Failed - keyCase parameter '$keyCase' is not valid - see api for valid values";
			return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
		}
		
		// Check for cached version
		$cacheFile = SRA_Controller::getAppTmpDir() . '/' . str_replace('/', '.', $file) . $languageCode;
		if ($cache && !$isRemote && !$tempFile && file_exists($cacheFile))
		{
			$msg = "SRA_File::propertiesFileToArray: Cache file found for properties file: '" . basename($file) . "'";
			SRA_Util::printDebug($msg, SRA_FILE_DEBUG, __FILE__, __LINE__);
			
			// Verify that cache file is up to date
			if (SRA_File::compareMTimes($cacheFile, $file) != -1)
			{
				include($cacheFile);
				if (!isset($properties) || !is_array($properties))
				{
					unlink($cacheFile);
					$msg = "SRA_File::propertiesFileToArray: Failed - Cache file '" . $cacheFile . 
						   "' does not contain valid data. Deleting, and continuing.";
					SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
				}
				else
				{
					$msg = "SRA_File::propertiesFileToArray: Accessed properties from cache file '$cacheFile'";
					SRA_Util::printDebug($msg, SRA_FILE_DEBUG, __FILE__, __LINE__);
				}
			}
			else
			{
				$msg = "SRA_File::propertiesFileToArray: Cache file is out of date and will be deleted for properties file: '" . basename($file) . "'";
				SRA_Util::printDebug($msg, SRA_FILE_DEBUG, __FILE__, __LINE__);
				unlink($cacheFile);
			}
		}
		
		// Cache not found... parse and process
		if (!isset($properties))
		{
			$properties = array();
			if (SRA_Error::isError($lines = file($file)))
			{
				$msg = "SRA_File::propertiesFileToArray: Failed - file parameter '$file' could not be opened";
				return SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
			}
			$start = false;
			$fromLanguage = false;
			$translationFailed = false;
			foreach ($lines as $line)
			{
				$line = trim($line);
				if (substr($line, 0, 1) != '#' && substr($line, 0, 1) != ';' && strstr($line, '='))
				{
					$line = str_replace("\n", '', $line);
					$pair = explode('=', $line);
					$pkey = trim($pair[0]);
					$pvalue = substr(strstr($line, '='), 1);
					
					// check for line breaks
          $pvalue = str_replace("\\\\n", "[#BREAK#]", $pvalue);
					$pvalue = str_replace("\\n", "\n", $pvalue);
          $pvalue = str_replace("[#BREAK#]", "\\n", $pvalue);
					
					if ($keyCase == 1)
					{
						$pkey = strtoupper($pkey);
					}
					else if ($keyCase == -1)
					{
						$pkey = strtolower($pkey);
					}
					
					// Check for embedded php code
					if (strstr($pkey, "php::"))
					{
						$pkey = preg_replace_callback("'php::(.*?)::php'si", "codeToString", $pkey);
					}
					if (strstr($pvalue, "php::"))
					{
						$pvalue = preg_replace_callback("'php::(.*?)::php'si", "codeToString", $pvalue);
					}
					
					$properties[$pkey] = $pvalue;
				}
			}
			
			// Cache data
      if ($cache && !$isRemote) {
        $msg = "SRA_File::propertiesFileToArray: " . count($properties) . " properties loaded for properties file: '" . basename($file) . "'";
        SRA_Util::printDebug($msg, SRA_FILE_DEBUG, __FILE__, __LINE__);
        if ($tempFile) {
          SRA_File::unlink($tempFile);
        }
        else {
          SRA_File::arrayToFile($cacheFile, "properties", $properties);
          SRA_File::chmod($cacheFile, 0666);
        }
      }
      else if ($cache) { 
        $_sraFilePropertiesFileToArrayCache[$file] = $properties; 
      }
		}
		
		return $properties;
	}
	// }}}
	
	
	// {{{ csvToArray
	/**
	 * Used to convert a CSV file into a two dimensional array
	 * {@see SRA_Util#arrayToCsv(String[][], boolean)}
	 * This method maintains a cache
	 * @param String file - the name of the csv file to convert
   * @param mixed indexCol - if the returned array should be indexed by one of 
   * the column values, this parameter should identify the 0-based index of that 
   * column. thus, if duplicate values exist in the database for that column, 
   * only a single instance of it will be returned (the last instance). this 
   * parameter may also be an array where the row indexes will be a concatendated 
   * value consisting of all of the values specified in that array where a 
   * numeric value will be pulled from the row and a string will be inserted into 
   * the key as-is. 
   * @param boolean cache whether or not to cache the results
   * @param char stringDelim - the string delimiter. strings may contain this 
   * delimiter value, but it should be preceded by \ or the same delimiter when 
   * this occurs
   * @access  private
	 * @return a two dimensional array representation of the csv file or FALSE
	 * if the there is a problem with the file
	 */
	function &csvToArray($file, $indexCol=NULL, $cache=TRUE, $stringDelim='"') {
		static $arrays = array();
		if (!$arrays[$file] && file_exists($file)) {
			// Check cache for array
			$cacheFile = SRA_FILE_CACHE_DIR . "/." . str_replace('/', ".", $file);
			if ($cache && file_exists($cacheFile)) {
				// Verify that cache file is up to date
				if (SRA_File::compareMTimes($cacheFile, $file) != -1) {
          $arr = unserialize(SRA_File::toString($cacheFile));
					if (!isset($arr) || !is_array($arr)) {
						unlink($cacheFile);
						$msg = "SRA_File::cvsToArray: Failed - Cache file '" . $cacheFile . 
								 "' does not contain valid data. Deleting, and continuing: ";
						SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
					}
					else {
						$msg = "SRA_File::cvsToArray: Accessing array from cache ($cacheFile) for file: " . $file;
						SRA_Util::printDebug($msg, SRA_FILE_DEBUG, __FILE__, __LINE__);
						$arrays[$file] =&$arr;
					}
				}
				else {
					unlink($cacheFile);
					$msg = "SRA_File::cvsToArray: Cache file '" . $cacheFile . "' has expired. Deleting and continuing.";
					SRA_Util::printDebug($msg, SRA_FILE_DEBUG, __FILE__, __LINE__);
				}
			}
			// array needs to be created
			if (!$arrays[$file]) {
				$arrays[$file] = array();
        $fp = fopen($file, 'r');
        // 0 start column/line
        // 1 start column
        // 2 parsing non-string
        // 3 parsing string
        // 4 parsing string in break
        $state = 0;
        $buff = '';
        $line = 0;
        $row = array();
        $previousColCount = NULL;
        while(!feof($fp)) {
          $char = fgetc($fp);
          if ($state < 3 && ($char == ',' || $char == "\n" || feof($fp))) {
            if ($char != ',' &&$char != "\n") { $buff .= $char; }
            $row[] = $buff;
            $state = feof($fp) || $char == "\n" ? 0 : 1;
            if ($state == 0 && count($row) > 1) {
              if (is_array($indexCol)) {
                $key = '';
                foreach($indexCol as $idx) {
                  $key .= is_numeric($idx) ? $row[$idx] : $idx;
                }
                $arrays[$file][$key] = $row;
              }
              else {
                $indexCol !== NULL ? $arrays[$file][$row[$indexCol]] = $row : $arrays[$file][] = $row;
              }
              $line++;
              $previousColCount = count($row);
              $row = array();
            }
            $buff = '';
            continue;
          }
          else if ($char == $stringDelim &&$state <= 1) {
            $state = 3;
            continue;
          }
          else if ($char == $stringDelim &&$state == 3) {
            $state = fgetc($fp) == $stringDelim ? 4 : 2;
            fseek($fp, -1, SEEK_CUR);
            continue;
          }
          else if ($char == '\\' &&$state == 3) {
            $state = 4;
          }
          else if ($state == 4) {
            $state = 3;
          }
          else if ($state <= 1) {
            $state = 2;
          }
          $buff .= $char;
          // echo "Char: $char State: $state Buff: $buff Line: $line Col: " . count($row) . " EOF: " . feof($fp) . "\n";
        }
        fclose($fp);
        if ($cache) {
          $fp = fopen($cacheFile, 'w');
          fwrite($fp, serialize($arrays[$file]));
          fclose($fp);
        }
			}
		}
		return $arrays[$file];
	}
	// }}}
	
	// {{{ findFirstMatchingPath()
	/**
	 * returns the first matching path using the following search algorithm
	 * 	1) $baseDir1/$prefix[/$dir]/$postfix[/$file] (if both $prefix and $postfix specified)
	 * 	2) $baseDir1/$prefix[/$dir][/$file] (if $prefix specified)
	 * 	3) $baseDir1[/$dir]/$postfix[/$file] (if $postfix specified)
	 * 	4) $baseDir1[/$dir][/$file] (if exists)
	 *	... $baseDirN ...
	 *	4N + 1) FALSE
	 *
	 * @param array or scalar $baseDirs - the base directories to check
	 * @param	string $dir - the directory name
	 * @param string $file - an optional file to look for in the directory
	 * @param string $prefix - a prefix to check in the app and SRA_DIR
	 * @param string $postfix - a postfix to check in the app and SRA_DIR
	 * @access	public static
	 * @return	String or FALSE
	 */
	function findFirstMatchingPath($baseDirs, $dir = FALSE, $file=FALSE, $prefix = '', $postfix = '') {
		if (substr($file, 0, 1) == '/' && file_exists($file)) {
			return $file;
		}
		if (strstr($file, '/')) {
			$pieces = explode('/', $file);
			for($i=0; $i<count($pieces) - 1; $i++) {
				$dir .= '/' . $pieces[$i];
			}
			$file = $pieces[$i];
		}
		if ($baseDirs && !is_array($baseDirs)) {
			$baseDirs = array($baseDirs);
		}
		if (is_array($baseDirs) && ($dir || $file)) {
			foreach ($baseDirs as $baseDir) {
				$paths = array();
				$sep = '';
				if ($dir) {
					$sep = '/' . $dir;
				}
				if ($prefix &&$postfix) {
					$paths[] = $baseDir . '/' . $prefix . $sep . '/' . $postfix;
				}
				if ($prefix) {
					$paths[] = $baseDir . '/' . $prefix . $sep;
				}
				if ($postfix) {
					$paths[] = $baseDir . $sep . '/' . $postfix;
				}
				if ($dir) {
					$paths[] = $baseDir . '/' . $dir;
				}
        if ($paths) {
          foreach ($paths as $path) {
            if ($file) {
              $path .= '/' . $file;
            }
            $path = str_replace('//', '/', $path);
            $path = str_replace('//', '/', $path);
            // echo "CHECK $path<br >\n";
            if (($file && is_file($path)) || (!$file && is_dir($path))) {
              return $path;
            }
          }
        }
        else if ($file && !$dir && !$prefix && !$postfix && is_file($baseDir . '/' . $file)) {
          return $baseDir . '/' . $file;
        }
			}
		}
		return FALSE;
	}
	// }}}
  
	// {{{ mergedFilesCached
	/**
	 * returns TRUE if an invocation to SRA_File::renderMergedFiles will result 
   * in cached output
	 * @param	mixed $files either an array of file names or a space separated list 
   * of file names. if any file in this value is not valid, an error will be 
   * logged, but the rendering will continue
   * @param int $obfuscate if the $files are javascript or css source code 
   * files, whether or not those files should be obfuscated. obfuscation, 
   * removes all unnecessary comments and whitespace from the source files in 
   * order to reduce the file size. obfuscation depends on ext/phpJSO.php. use 
   * 1 for javascript obfuscation and 2 for css obfuscation
	 * @access	public static
	 * @return	boolean
	 */
	function mergedFilesCached($files, $obfuscate=FALSE, $removeWhiteSpace) {
    if (!is_array($files)) { $files = explode(' ', $files); }
    // create cache file name and determine latest modification time
    $mergeFiles = array();
    $cacheFileName = '.rmf_';
    foreach($files as $file) {
      if (file_exists($file)) {
        $cacheFileName .= str_replace(strstr(basename($file), '.'), '', basename($file));
        $maxModTime = !$maxModTime || filemtime($file) > $maxModTime ? filemtime($file) : $maxModTime;
        $mergeFiles[] = $file;
      }
      else {
        $msg = "SRA_File::renderMergedFiles: File '$file' is not valid";
        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
      }
    }
    while(strlen($cacheFileName) > 50) {
      for($i=5; $i<strlen($cacheFileName); $i+=3) {
        $cacheFileName = substr($cacheFileName, 0, $i-1) . '*' . substr($cacheFileName, $i);
      }
      $cacheFileName = str_replace('*', '', $cacheFileName);
    }
    $cacheFileName = SRA_Controller::getAppTmpDir() . '/' . $cacheFileName . $obfuscate . $removeWhiteSpace;
    
    return count($mergeFiles) && file_exists($cacheFileName) && filemtime($cacheFileName) >= $maxModTime;
	}
	// }}}
  
	// {{{ renderMergedFiles
	/**
	 * this method merges multiple static or dynamic files and outputs their 
   * contents. to increase performance, the $files are cached into a single 
   * temporary file first and the contents of that file are output. the 
   * temporary file is re-written automatically whenever any of the $files are 
   * subsequently modified
	 * @param	mixed $files either an array of file names or a space separated list 
   * of file names. if any file in this value is not valid, an error will be 
   * logged, but the rendering will continue
   * @param int $obfuscate if the $files are javascript or css source code 
   * files, whether or not those files should be obfuscated. obfuscation, 
   * removes all unnecessary comments and whitespace from the source files in 
   * order to reduce the file size. obfuscation depends on ext/phpJSO.php. use 
   * 1 for javascript obfuscation and 2 for css obfuscation
   * @param boolean $removeWhiteSpace whether or not to remove whitespace from 
   * the merged files
   * @param boolean $processPhp whether or not to process php files (files that 
   * end with the SRA_SYS_PHP_EXTENSION extension)
   * @param string $search an optional value to replace with $replace. this can 
   * contain multiple values each separated by a pipe (|)
   * @param string $replace the value to replace $search with. this can contain 
   * multiple values each separated by a pipe (|)
	 * @access	public static
	 * @return	void
	 */
	function renderMergedFiles($files, $obfuscate=FALSE, $removeWhiteSpace=FALSE, $processPhp=TRUE, $search=NULL, $replace=NULL) {
    if (!is_array($files)) { $files = explode(' ', $files); }
    // create cache file name and determine latest modification time
    $mergeFiles = array();
    $cacheFileName = '.rmf_';
    foreach($files as $file) {
      if (file_exists($file)) {
        $cacheFileName .= str_replace(strstr(basename($file), '.'), '', basename($file));
        $maxModTime = !$maxModTime || filemtime($file) > $maxModTime ? filemtime($file) : $maxModTime;
        $mergeFiles[] = $file;
      }
      else {
        $msg = "SRA_File::renderMergedFiles: File '$file' is not valid";
        SRA_Error::logError($msg, __FILE__, __LINE__, SRA_ERROR_PROBLEM, SRA_FILE_DEBUG);
      }
    }
    while(strlen($cacheFileName) > 50) {
      for($i=5; $i<strlen($cacheFileName); $i+=3) {
        $cacheFileName = substr($cacheFileName, 0, $i-1) . '*' . substr($cacheFileName, $i);
      }
      $cacheFileName = str_replace('*', '', $cacheFileName);
    }
    $cacheFileName = SRA_Controller::getAppTmpDir() . '/' . $cacheFileName . $obfuscate . $removeWhiteSpace;
    
    // merge files if necessary
    if ((count($mergeFiles) && !file_exists($cacheFileName)) || filemtime($cacheFileName) < $maxModTime) {
      if ($obfuscate) { require_once('ext/phpJSO.php'); }
      $contents = '';
      foreach($mergeFiles as $file) {
        if ($processPhp && SRA_Util::endsWith($file, SRA_SYS_PHP_EXTENSION)) {
          ob_start();
          include($file);
          $str = ob_get_contents();
          ob_end_clean();
        }
        else {
          $str = & SRA_File::toString($file);
        }
        if ($obfuscate == 1) {
          $str = phpJSO_compress($str);
        }
        else if ($obfuscate == 2) {
          phpJSO_strip_strings_and_comments($str, $str_array, substr(md5(time()), 10, 2));
          $str = str_replace("\t", '', str_replace("\n", '', $str));
          phpJSO_restore_strings($str, $str_array);
          $str = str_replace("\n", '', $str);
        }
        $contents .= $str;
      }
      if ($search) {
        $replace = explode('|', $replace);
        foreach(explode('|', $search) as $key => $search) {
          $contents = str_replace($search, isset($replace[$key]) ? $replace[$key] : $replace[0], $contents);
        }
      }
      SRA_File::write($cacheFileName, $contents);
    }
    if (file_exists($cacheFileName)) {
      echo SRA_File::toString($cacheFileName);
    }
	}
	// }}}
	
	// {{{ getRelativePath()
	/**
	 * returns the full path for a app relative directory based on $dir 
	 * using the following search order: 
	 * 	1) app directory/$prefix[/$dir]/$postfix[/$file] (if both $prefix and $postfix specified)
	 * 	2) app directory/$prefix[/$dir][/$file] (if $prefix specified)
	 * 	3) app directory[/$dir]/$postfix[/$file] (if $postfix specified)
	 * 	4) app directory[/$dir][/$file] (if exists)
	 * 	5) SRA_File::getSysRelativePath($dir, $file, $prefix, $postfix) (otherwise)
	 *
	 * @param	string $dir - the base directory name (optional)
	 * @param string $file - an optional file to look for in the directory. either 
	 * $dir or $file should be specified or the app directory will always be 
	 * returned
	 * @param string $prefix - a prefix to check in the app and SRA_DIR
	 * @param string $postfix - a postfix to check in the app and SRA_DIR
	 * @access	public static
	 * @return	String or FALSE
	 */
	function getRelativePath($dir = FALSE, $file=FALSE, $prefix = '', $postfix = '') {
		if (SRA_Controller::isAppInitialized() && 
				($path = SRA_File::findFirstMatchingPath(SRA_Controller::getAppDir(), $dir, $file, $prefix, $postfix))) {
			return $path;
		}
    else if (file_exists($dir)) {
      return $dir;
    }
    else {
      return SRA_File::getSysRelativePath($dir, $file, $prefix, $postfix);
    }
	}
	// }}}
	
	// {{{ getSysRelativePath()
	/**
	 * returns the full path for the system relative directory based on $dir 
	 * using the following search order: 
	 * 	1) SRA_DIR/$prefix/$dir/$postfix[/$file] (if both $prefix and $postfix specified)
	 * 	2) SRA_DIR/$prefix/$dir[/$file] (if $prefix specified)
	 * 	3) SRA_DIR/$dir/$postfix[/$file] (if $postfix specified)
	 * 	4) SRA_DIR/$dir[/$file] (if exists)
	 * 	5) [$dir][/$file] (fixed path - if exists)
	 *	6) FALSE (otherwise)
	 *
	 * @param	string $dir - the directory name (optional)
	 * @param string $file - an optional file to look for in the directory. either 
	 * $dir or $file should be specified or the SRA_DIR will always be returned
	 * @param string $prefix - a prefix to check in the SRA_DIR
	 * @param string $postfix - a postfix to check in the SRA_DIR
	 * @access	public static
	 * @return	String or FALSE
	 */
	function getSysRelativePath($dir = FALSE, $file=FALSE, $prefix = '', $postfix = '') {
		return SRA_File::findFirstMatchingPath(array(SRA_DIR, '/'), $dir, $file, $prefix, $postfix);
	}
	// }}}
  
  // {{{ getFileSize
  /**
   * returns the size of $file in bytes
   * @param	string $file the file to return the size of
   * @param boolean $useLs whether or not to use the ls command instead of the 
   * built in filesize php function
   * @return int
   */
  function getFileSize($file, $useLs=FALSE) {
    $size = NULL;
    if ($useLs || (file_exists($file) && !($size = filesize($file)) && $size !== 0)) {
      if ($pieces = explode(' ', shell_exec(SRA_File::findInPath('ls') . ' -l ' . $file))) {
        $size = $pieces[4]*1;
      }
    }
    return $size;
  }
  // }}}
  
  // {{{ getFileSizeString
  
  function getFileSizeString($file, $useLs = FALSE, $decimals = 2)
  {
  	$size = $bytes = SRA_File::getFileSize($file, $useLs);
  	
  	$labels = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
  	for ($i = 0; $size >= 1024 && $i < count($labels); $i++)
  	{
  		$size = $size / 1024;
  	}
  	
  	return number_format($size, $decimals) . ' ' . $labels[$i];
  }
  // }}}
  
  // {{{ getUid
  /**
   * returns the uid of the owner of $file. this function works even if stat 
   * does not
   * @param	string $file the file to return the owner of
   * @return int
   */
  function getUid($file) {
    if (file_exists($file) && !($owner = fileowner($file)) && $owner !== 0) {
      if ($pieces = explode(' ', shell_exec(SRA_File::findInPath('ls') . ' -ln ' . $file))) {
        $owner = $pieces[2]*1;
      }
    }
    return $owner;
  }
  // }}}
  
  // {{{ getOwner
  /**
   * returns the name of the user that owns $file. this function works even if stat 
   * does not
   * @param	string $file the file to return the owner of
   * @return int
   */
  function getOwner($file) {
    if (($owner = SRA_File::getUid($file)) || $owner === 0) {
      $tmp = posix_getpwuid($owner);
      $owner = $tmp['name'];
    }
    return $owner;
  }
  // }}}
  
  // {{{ getGid
  /**
   * returns the gid of the group owner of $file. this function works even if stat 
   * does not
   * @param	string $file the file to return the owner of
   * @return int
   */
  function getGid($file) {
    if (file_exists($file) && !($owner = filegroup($file)) && $owner !== 0) {
      if ($pieces = explode(' ', shell_exec(SRA_File::findInPath('ls') . ' -ln ' . $file))) {
        $owner = $pieces[3]*1;
      }
    }
    return $owner;
  }
  // }}}
  
  // {{{ getGroupOwner
  /**
   * returns the name of the group that owns $file. this function works even if stat 
   * does not
   * @param	string $file the file to return the owner of
   * @return int
   */
  function getGroupOwner($file) {
    if (($owner = SRA_File::getGid($file)) || $owner === 0) {
      $tmp = posix_getgrgid($owner);
      $owner = $tmp['name'];
    }
    return $owner;
  }
  // }}}
  
  // {{{ getPermissions
  /**
   * returns the octal permissions of $file (i.e. 775 or 664). this function 
   * works even if stat does not
   * @param	string $file the file to return the permissions for
   * @param boolean $octal if TRUE, the return value will be an octal. otherwise 
   * it will be an int
   * @return mixed
   */
  function getPermissions($file, $octal=FALSE) {
    if (file_exists($file) && !($perms = fileperms($file))) {
      if ($pieces = explode(' ', shell_exec(SRA_File::findInPath('ls') . ' -l ' . $file))) {
        $operms = substr($pieces[0], 1, 3);
        $gperms = substr($pieces[0], 4, 3);
        $wperms = substr($pieces[0], 7, 3);
        $perms = '';
        for($i=1; $i<strlen($pieces[0]); $i++) {
          $perm = 0;
          if (substr($pieces[0], $i++, 1) != '-') { $perm += 4; }
          if (substr($pieces[0], $i++, 1) != '-') { $perm += 2; }
          if (substr($pieces[0], $i, 1) != '-') { $perm += 1; }
          $perms .= $perm;
        }
      }
    }
    else {
      $perms = sprintf('%o', $perms);
    }
    if ($perms) { return substr($perms, -3); }
  }
  // }}}
  
  // {{{ base64Decode
  /**
   * used the linux base64 command (if present) to decode $encoded. this 
   * function was created to avoid some unknown problems with the php 
   * base64_decode function
   * @param	string $encoded the base64 encoded string to convert
   * @param string $output an optional file path that the encoded contents 
   * should be written to. if specified, the return value will be NULL
   * @return mixed
   */
  function &base64Decode(&$encoded, $output=NULL) {
    //createRandomFile($dir=NULL, $pre='', $post='', $base='', $delete=FALSE, $mkdir=FALSE)
    if ($base64cmd = SRA_File::findInPath('base64')) {
      $tmp1 = is_file($encoded) ? $encoded : $tmp1 = SRA_File::createRandomFile();
      if (!is_file($encoded)) SRA_File::write($tmp1, $encoded);
      passthru($base64cmd . ' -d -i ' . $tmp1 . ' > ' . ($output ? $output : ($tmp2 = SRA_File::createRandomFile())));
      if (!is_file($encoded)) unlink($tmp1);
      
      if (!$output) {
        $decoded =& SRA_File::toString($tmp2);
        SRA_File::unlink($tmp2);
        return $decoded;
      }
      else {
        return NULL;
      }
    }
    else {
      $decoded = base64_decode(is_file($encoded) ? SRA_File::toString($encoded) : $encoded);
      if ($output) {
        SRA_File::write($output, $decoded);
      }
      else {
        return $decoded;
      }
    }
  }
  // }}}
	
}

// }}}

?>