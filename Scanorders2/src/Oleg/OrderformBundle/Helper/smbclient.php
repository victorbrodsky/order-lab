<?php

namespace Oleg\OrderformBundle\Helper;

/**
 * Class for interacting with an SMB server using the system command "smbclient".
 * Of course this assumes that you have the smbclient executable installed and
 * in your path.
 * 
 * It is not the most efficient way of interacting with an SMB server -- for instance,
 * putting multiple files involves running the executable multiple times and
 * establishing a connection for each file.  However, if performance is not an
 * issue, this is a quick-and-dirty way to move files to and from the SMB
 * server from PHP.
 *
 */
class smbclient
{
    private $_service;
    private $_username;
    private $_password;
    
    private $_cmd;
    
    private $_last_cmd_stdout;
    /**
     * Gets stndard output from the last run command; can be useful in
     * case the command reports an error; smbclient writes a lot of
     * diagnostics to stdout.
     *
     * @return array each line of stdout is one string in the array
     */
    public function get_last_cmd_stdout () { return $this->_last_cmd_stdout; }
    
    private $_last_cmd_stderr;
    /**
     * Gets stndard error from the last run command
     *
     * @return array each line of stderr is one string in the array
     */
    public function get_last_cmd_stderr () { return $this->_last_cmd_stderr; }

    private $_last_cmd_exit_code;
    /**
     * Gets the exit code of the last command run
     *
     * @return int
     */    
    public function get_last_cmd_exit_code () { return $this->_last_cmd_exit_code; }
    
    /**
     * Creates an smbclient object
     *
     * @param string $service the UNC service name
     * @param string $username the username to use when connecting
     * @param string $password the password to use when connecting
     */
    public function __construct ($service, $username, $password)
    {
        $this->_service = $service;
        $this->_username = $username;
        $this->_password = $password;
    }
    

    /**
     * Gets a remote file
     *
     * @param string $remote_filename remote filename (use the local system's directory separators)
     * @param string $local_filename the full path to the local filename
     * @return bool true if successful, false otherwise
     */
    public function get ($remote_filename, $local_filename)
    {
        // convert to windows-style backslashes
        $remote_filename = str_replace (DIRECTORY_SEPARATOR, '\\', $remote_filename);
        
        $cmd = "get \"$remote_filename\" \"$local_filename\"";
        
        $retval = $this->execute ($cmd);
        return $retval;
    }

    /**
     * Puts multiple local files on the server
     *
     * @param array $local_files array of local filename paths
     * @param string $remote_path path to remote directory (use the local system's directory separators)
     * @return bool true if successful, false otherwise
     */
    public function mput ($local_files, $remote_path)
    {
        foreach ($local_files as $local_file)
        {
            $pi = pathinfo ($local_file);
            
            $remote_file = $remote_path . '/' . $pi['basename'];
            
            if (!$this->put ($local_file, $remote_file))
            {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Puts a local file
     *
     * @param string $local_filename the full path to local filename
     * @param string $remote_filename (use the local system's directory separators)
     * @return bool true if successful, false otherwise
     */
    public function put ($local_filename, $remote_filename)
    {
        // convert to windows-style backslashes
        $remote_filename = str_replace (DIRECTORY_SEPARATOR, '\\', $remote_filename);
        
        $cmd = "put \"$local_filename\" \"$remote_filename\"";
        
        $retval = $this->execute ($cmd);
        return $retval;
    }
    
    /**
     * Deletes a remote file
     *
     * @param string $remote_filename (use the local system's directory separators)
     * @return bool true if successful, false otherwise
     */
    public function del ($remote_filename)
    {
        // can't do this in one command -- need to break it into a cd and then
        // a del
        
        $pi = pathinfo ($remote_filename);
        $remote_path = $pi['dirname'];        
        $basename = $pi['basename'];
        
        // convert to windows-style backslashes
        if ($remote_path)
        {
            $remote_path = str_replace (DIRECTORY_SEPARATOR, '\\', $remote_path);
            $cmd = "cd \"$remote_path\"; del \"$basename\"";
        }
        else 
        {
            $cmd = "del \"$basename\"";
        }        
        
        $retval = $this->execute ($cmd);
        return $retval;
    }
    
    
    private function execute ($cmd)
    {
        $this->build_full_cmd($cmd);
        
        $outfile = tempnam(".", "cmd");
        $errfile = tempnam(".", "cmd");
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("file", $outfile, "w"),
            2 => array("file", $errfile, "w")
        );
        $proc = proc_open($this->_cmd, $descriptorspec, $pipes);
       
        if (!is_resource($proc)) return 255;
    
        fclose($pipes[0]);    //Don't really want to give any input
    
        $exit = proc_close($proc);
        $this->_last_cmd_stdout = file($outfile);
        $this->_last_cmd_stderr = file($errfile);
        $this->_last_cmd_exit_code = $exit;
    
        unlink($outfile);
        unlink($errfile);
        
        if ($exit)
        {
            return false;
        }
        return true;
    }    
    
    private function build_full_cmd ($cmd = '')
    {
        $this->_cmd = "smbclient '" . $this->_service . "'";
        
        if ($this->_username)
        {
            $this->_cmd .= " -U '" . $this->_username . "'";
        }
        
        if ($cmd)
        {
            $this->_cmd .= " -c '$cmd'";
        }
        
        if ($this->_password)
        {
            $this->_cmd .= " '" . $this->_password . "'";
        }

        echo "fullcmd=".$this->_cmd."<br>";
    }
}


?>