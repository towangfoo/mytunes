<?php
/**
 * Helper for the Mytunes admin interface
 *
 * TODO: License
 *
 * @category   Que
 * @package    Que_Mytunes
 * @author     Steffen Mücke <mail@quellkunst.de>
 */
class Que_Mytunes_Helper_Admin extends Mage_Core_Helper_Abstract
{

    /**
     * move a file from a source path to a destination path
     * Will only be done when >> $file['status'] == 'new'
     *
     * @param string  from
     * @param string  to
     * @param integer albumId
     * @param array   file
     * @param boolean disperse = false
     *
     * @return string filename
     *
     * @throws Mage_Core_Exception
     */
    public function moveFile($from, $to, $albumId = null, $file, $disperse = false) {
        if (isset($file['file'])) {
            if ($albumId != null) {
                $to = $this->_appendAlbumIdToPath($to, $albumId);
            }
            $fileName = $file['file'];
            if ($file['status'] == 'new') {
                try {
                    $fileName = $this->_moveFileFromTmp($from, $to, $file['file'], $disperse);
                } catch (Exception $e) {
                    Mage::throwException(Mage::helper('mytunes')->__('An error occurred while saving the file(s).'));
                }
            }
            return $fileName;
        }
        return '';
    }

    /**
     * Create an audio sample from the full file marked $tcStart and $tcEnd
     *
     * @param string  $fullPath
     * @param string  $samplePath
     * @param integer $albumId
     * @param string  $file
     * @param string  $tcStart
     * @param string  $tcEnd
     * @param integer $fade
     * @param boolean $autorename
     *
     * @return string | null   $sampleFileName
     *
     * @throws Mage_Core_Exception
     */
    public function createSample($fullPath, $samplePath, $albumId, $file,
        $tcStart = null, $tcEnd = null, $fade = null, $autorename = null)
    {
        if (!$this->_isSoXAvailable()) {
            throw new Mage_Core_Exception("Mytunes Exception: Sox command line tool is not available.");
        }

        if ($tcStart == null)
            $tcStart = Mage::getStoreConfig('mytunes_settings/sox/trim_start_default');
        if ($tcEnd == null)
            $tcEnd = Mage::getStoreConfig('mytunes_settings/sox/trim_end_default');
        if ($fade == null)
            $fade = Mage::getStoreConfig('mytunes_settings/sox/fade_duration_default');
        if ($autorename == null) {
            $autorename = (Mage::getStoreConfig('mytunes_settings/sox/autorename_samples') == 1)? true : false;
        }

        if ($albumId != null) {
            $fullPath = $this->_appendAlbumIdToPath($fullPath, $albumId);
            $samplePath = $this->_appendAlbumIdToPath($samplePath, $albumId);
        }

        // create output file and directory
        $destFile = basename($file);
        if ($autorename) {
            // TODO: implement autorenaming
        }
        $destDirectory = dirname($this->getFilePath($samplePath, $file));
        $ioObject = new Varien_Io_File();
        try {
            $ioObject->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destDirectory, 0777, true);
            $ioObject->open(array('path'=>$destDirectory));
        }

        // build sox command
        // @see http://sox.sourceforge.net/sox.html
        $command = Mage::getStoreConfig('mytunes_settings/sox/binary');
        	// override existing output file without asking
        	$command .= " --clobber";
        	// sox verbosity V1 > show errors | V0 > show nothing
            $command .= " -V1";
            // input mp3 file (full version)
            $command .= " " . $this->getFilePath($fullPath, $file);
            // output mp3 sample file
            $command .= " " . $this->getFilePath($destDirectory, $destFile);
            // apply trim effect with $tcStart to $tcEnd
            $command .= " trim " . $tcStart . " " . $tcEnd;
            // apply fade-in and fade-out
            $command .= " fade t " . $fade . " " . $this->getDuration($tcStart, $tcEnd) . " " . $fade;
            
        $output = array(); $returnStatus = -1;
        exec($command, $output, $returnStatus);

        if ($returnStatus == 0) {
            return $file;
        }
        else {
            if ($returnStatus == 1) {
                throw new Mage_Core_Exception("Mytunes Exception: Could not create sample file. Check Sox Parameters (Return Status 1).");
            }
            else if($returnStatus == 2) {
                throw new Mage_Core_Exception("Mytunes Exception: Could not create sample file. Check that SoX supports creating mp3 output files (Return Status 2).");
            }
            else {
            	throw new Mage_core_Exception("Mytunes Exception: Could not create sample file. Unknown return status.");
            }
        }
    }

    /**
     * Transcode an mp3 file to an ogg file.
     *
     * @param string  $path
     * @param integer $albumId
     * @param string  $mp3File
     *
     * @return boolean success
     * 
     * @throws Mage_Core_Exception
     */
    public function transcodeOgg($path, $albumId, $mp3File) {
        if (strrpos(strtolower($mp3File), '.mp3') == strlen($mp3File)-4) {
            if ($albumId != null) {
                $path = $this->_appendAlbumIdToPath($path, $albumId);
            }
            
	        if (!$this->_isSoXAvailable()) {
	            throw new Mage_Core_Exception("Mytunes Exception: Sox command line tool is not available.");
	        }

            $oggFile = substr($mp3File, 0, -4) . '.ogg';
            // build sox command
            // @see http://sox.sourceforge.net/sox.html
            $command = Mage::getStoreConfig('mytunes_settings/sox/binary');
                // override existing output file without asking
                $command .= " --clobber";
                // sox verbosity V1 > show errors | V0 > show nothing
                $command .= " -V0";
                // input mp3 file
                $command .= " " . $this->getFilePath($path, $mp3File);
                // output mp3 sample file
                $command .= " " . $this->getFilePath($path, $oggFile);
            
            $output = array(); $returnStatus = -1;
            exec($command, $output, $returnStatus);

            return $returnStatus == 0;
        }
        return false;
    }

    /**
     * Get the duration between the start and end timecodes of an audio
     *
     * @param string  $tcStart
     * @param string  $tcEnd
     *
     * @return string $duration
     * 
     * @throws Mage_Core_Exception
     */
    public function getDuration($tcStart, $tcEnd)
    {
    	if (strlen($tcStart) == 5) {
    		$tcStart = "00:" . $tcStart;
    	}
    	if (strlen($tcEnd) == 5) {
    		$tcEnd = "00:" . $tcEnd;
    	}
    	$start = explode(":", $tcStart);
        $end = explode(":", $tcEnd);
        $h = (int) $end[0] - (int) $start[0];
        $m = (int) $end[1] - (int) $start[1];
        $s = (int) $end[2] - (int) $start[2];

        if ($s < 0) {
            $s = $s + 60;
            $m = $m - 1;
        }
        if ($m < 0) {
            $m = $m + 60;
            $h = $h - 1;
        }
        
        if ($h < 0 || $m < 0 || $s <0) {
        	throw new Mage_Core_Exception("Mytunes Exception: Got negative duration. Time code markers are probably the wrong way round.");
        }

        return str_pad((string) $h, 2, "0", STR_PAD_LEFT).
           ":".str_pad((string) $m, 2, "0", STR_PAD_LEFT).
           ":".str_pad((string) $s, 2, "0", STR_PAD_LEFT);
    }

    /**
     * Return full path to file
     *
     * @param string  $path
     * @param string  $file
     * @param integer $albumId
     *
     * @return string
     */
    public function getFilePath($path, $file, $albumId = null)
    {
        $file = $this->_prepareFileForPath($file);

        if(substr($path, -1) == DS) {
            $path = substr($path, 0, -1);
        }

        if ($albumId != null) {
            $path = $this->_appendAlbumIdToPath($path, $albumId);
        }

        if(substr($file, 0, 1) == DS) {
            return $path . DS . substr($file, 1);
        }

        return $path . DS . $file;
    }


    /**
     * Return file name from file path.
     *
     * @param string $pathFile
     * @return string
     */
    public function getFileFromPathFile($pathFile)
    {
        return substr($pathFile, strrpos($this->_prepareFileForPath($pathFile), DS)+1);
    }

    /**
     * Get a symlink to an mp3 file, creating it if necessary.
     *
     * @param string $resource path
     */
    public function getAdminSymlink($resource, $sessionId)
    {
        $symlinkName = $sessionId . "/" . md5($resource) . ".mp3";
        $symlinkPath = Que_Mytunes_Model_Track::getAdminSymlinkPath();
        $symlink = $this->getFilePath($symlinkPath, $symlinkName);
        if (file_exists($symlink) && is_readable($symlink)) {
            return $symlinkName;
        }
        // create new symlink
        $io = new Varien_Io_File();
        $destDirectory = dirname($symlink);
        try {
            $io->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $io->mkdir($destDirectory, 0777, true);
            $io->open(array('path'=>$destDirectory));
        }

        if (symlink($resource, $symlink)) {
            return $symlinkName;
        }
        else {
            throw new Exception("Could not create symlink for resource " + $resource);
        }

    }

    /**
     * Get a the URL for an admin symlink.
     *
     * @param string $symlink
     */
    public function getAdminSymlinkUrl($symlink)
    {
        return Que_Mytunes_Model_Track::getAdminSymlinkUrl() . "/" . $symlink;
    }

    /**
     * Move file from tmp path to base path
     *
     * @param string $baseTmpPath
     * @param string $basePath
     * @param string $file
     * @param bool   disperse = false
     * @return string filename
     */
    protected function _moveFileFromTmp($fromPath, $toPath, $file, $disperse = false)
    {
        if (strrpos($file, '.tmp') == strlen($file)-4) {
            $file = substr($file, 0, strlen($file)-4);
        }
        $destFile = $file;
        if ($disperse) {
            $destFile = Varien_File_Uploader::getDispretionPath($file) . "/" . $file;
        }
        $ioObject = new Varien_Io_File();
        $destDirectory = dirname($this->getFilePath($toPath, $destFile));
        try {
            $ioObject->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destDirectory, 0777, true);
            $ioObject->open(array('path'=>$destDirectory));
        }

        $prefix = "";
        if (strlen(dirname($destFile))>1) {
            $prefix = dirname($destFile) . $ioObject->dirsep();
        }
        $destFile = $prefix . Varien_File_Uploader::getNewFileName($this->getFilePath($toPath, $destFile));
        $result = $ioObject->mv(
            $this->getFilePath($fromPath, $file),
            $this->getFilePath($toPath, $destFile)
        );
        return str_replace($ioObject->dirsep(), '/', $destFile);
    }

    /**
     * Replace slashes with directory separator
     *
     * @param string $file
     * @return string
     */
    protected function _prepareFileForPath($file)
    {
        return str_replace('/', DS, $file);
    }

    /**
     * Append the album Id to the end of a path.
     *
     * @param string  $path
     * @param integer $albumId
     *
     * @return string $path
     */
    protected function _appendAlbumIdToPath($path, $albumId) {
        if (substr($path, -1) == DS) {
            $path = substr($path, 0, -1);
        }
        $path .= DS . (string) $albumId;
        return $path;
    }

    /**
     * Check whether SoX is available on the system
     * @see http://sox.sourceforge.net/Main/HomePage
     *
     * @return boolean
     */
    protected function _isSoXAvailable() {
        $command = Mage::getStoreConfig('mytunes_settings/sox/binary');
            $command .= " --version";

        $output = array(); $returnStatus = -1;
        exec($command, $output, $returnStatus);

        if ($returnStatus == 0 && isset($output[0])) {
            $version = preg_replace("/[^0-9\.]/", "", $output[0]);
            return version_compare($version, Mage::getStoreConfig('mytunes_settings/sox/min_version'), '>=');
        }
        return false;
    }

}