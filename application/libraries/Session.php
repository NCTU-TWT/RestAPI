<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package        CodeIgniter
 * @author        Dariusz Debowczyk
 * @copyright    Copyright (c) 2006, D.Debowczyk
 * @license        http://www.codeignitor.com/user_guide/license.html 
 * @link        http://www.codeigniter.com
 * @since        Version 1.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Session class using native PHP session features and hardened against session fixation.
 * 
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Sessions
 * @author        Dariusz Debowczyk
 * @link        http://www.codeigniter.com/user_guide/libraries/sessions.html
 */
class CI_Session {
	var $sess_encrypt_cookie		= FALSE;
	var $sess_use_database			= FALSE;
	var $sess_table_name			= '';
	var $sess_expiration			= 7200;
	var $sess_expire_on_close		= FALSE;
	var $sess_match_ip				= FALSE;
	var $sess_match_useragent		= TRUE;
	var $sess_cookie_name			= 'PHP_SESSID';
	var $sess_time_to_update		= 300;
	var $cookie_prefix				= '';
	var $cookie_path				= '';
	var $cookie_domain				= '';
	var $cookie_secure				= FALSE;
	var $encryption_key				= '';
	var $flashdata_key				= '__flash';
	var $time_reference				= 'time';
	var $gc_probability				= 5;
    
    function CI_Session () {
        log_message('debug', "Native_session Class Initialized");
		$this->CI =& get_instance();
        $this->_sess_run();
    }

    /**
    * Regenerates session id
    */
    function regenerate_id()
    {
        // copy old session data, including its id
        $old_session_id = session_id();
        $old_session_data = $_SESSION;

        // regenerate session id and store it
        session_regenerate_id();
        $new_session_id = session_id();
        
        // switch to the old session and destroy its storage
        session_id($old_session_id);
        session_destroy();
        
        // switch back to the new session id and send the cookie
        session_id($new_session_id);
        session_start();
        
        // restore the old session data into the new session
        $_SESSION = $old_session_data;
        
        // update the session creation time
        $_SESSION['regenerated'] = time();

        // session_write_close() patch based on this thread
        // http://www.codeigniter.com/forums/viewthread/1624/
        // there is a question mark ?? as to side affects

        // end the current session and store session data.
        session_write_close();
    }
    
	function sess_destroy()
	{
		$this->destroy();
	}
	
    /**
    * Destroys the session and erases session storage
    */
    function destroy()
    {
        unset($_SESSION);
        if ( isset( $_COOKIE[session_name()] ) )
        {
              setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy();
    }
    
    /**
    * Reads given session attribute value
    */    
    function userdata($item)
    {
        if($item == 'session_id'){ //added for backward-compatibility
            return session_id();
        }else{
            return ( ! isset($_SESSION[$item])) ? false : $_SESSION[$item];
        }
    }
    
    /**
    * Sets session attributes to the given values
    */
    function set_userdata($newdata = array(), $newval = '')
    {
        if (is_string($newdata))
        {
            $newdata = array($newdata => $newval);
        }
    
        if (count($newdata) > 0)
        {
            foreach ($newdata as $key => $val)
            {
                if(is_array($val)){
                     foreach($val as $key2 => $val2){
                         $_SESSION[$key][$key2] = $val2;
                     }
               }else{
                   $_SESSION[$key] = $val;
               }
            }
        }
    }
    
    /**
    * Erases given session attributes
    */
    function unset_userdata($newdata = array())
    {
        if (is_string($newdata))
        {
            $newdata = array($newdata => '');
        }
    
        if (count($newdata) > 0)
        {
            foreach ($newdata as $key => $val)
            {
                unset($_SESSION[$key]);
            }
        }        
    }
    
    /**
    * Starts up the session system for current request
    */
    function _sess_run()
    {
		foreach (array('sess_encrypt_cookie', 'sess_use_database', 'sess_table_name', 'sess_expiration', 'sess_expire_on_close', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'sess_time_to_update', 'cookie_path', 'cookie_domain', 'cookie_secure', 'time_reference', 'cookie_prefix', 'encryption_key') as $key)
		{
			$this->$key = $this->CI->config->item($key);
		}
		$rootDomain = '.ensky.tw';
		
		session_set_cookie_params(
			$this->sess_expiration,
			'/',
			$this->cookie_domain,
			False,
			True
		);
		ini_set("session.gc_maxlifetime", $this->sess_expiration);
		session_name($this->CI->config->item('sess_cookie_name'));
		session_start();
		
		// check if session id needs regeneration
		if ( $this->_session_id_expired() )
		{
			// regenerate session id (session data stays the
			// same, but old session storage is destroyed)
			$this->regenerate_id();
		}
		
		// delete old flashdata (from last request)
		$this->_flashdata_sweep();
		
		// mark all new flashdata as old (data will be deleted before next request)
		$this->_flashdata_mark();
    }
    
    /**
    * Checks if session has expired
    */
    function _session_id_expired()
    {
        if ( !isset( $_SESSION['regenerated'] ) )
        {
            $_SESSION['regenerated'] = time();
            return false;
        }
        
        $expiry_time = time() - $this->sess_time_to_update;
        
        if ( $_SESSION['regenerated'] <=  $expiry_time )
        {
            return true;
        }

        return false;
    }
    
    /**
    * Sets "flash" data which will be available only in next request (then it will
    * be deleted from session). You can use it to implement "Save succeeded" messages
    * after redirect.
    */
    function set_flashdata($newdata, $newval='')
    {
		if (is_string($newdata))
        {
            $newdata = array($newdata => $newval);
        }

        if (count($newdata) > 0)
        {
            foreach ($newdata as $key => $val)
            {
                $flash_key = $this->flashdata_key.':new:'.$key;
                $this->set_userdata($flash_key, $val);
            }
        }
    }
    
    /**
    * Keeps existing "flash" data available to next request.
    */
    function keep_flashdata($key)
    {
        $old_flashdata_key = $this->flashdata_key.':old:'.$key;
        $value = $this->userdata($old_flashdata_key);

        $new_flashdata_key = $this->flashdata_key.':new:'.$key;
        $this->set_userdata($new_flashdata_key, $value);
    }

    /**
    * Returns "flash" data for the given key.
    */
    function flashdata($key)
    {
        $flashdata_key = $this->flashdata_key.':old:'.$key;
        return $this->userdata($flashdata_key);
    }
    
    /**
    * PRIVATE: Internal method - marks "flash" session attributes as 'old'
    */
    function _flashdata_mark()
    {
        foreach ($_SESSION as $name => $value)
        {
            $parts = explode(':new:', $name);
            if (is_array($parts) && count($parts) == 2)
            {
                $new_name = $this->flashdata_key.':old:'.$parts[1];
                $this->set_userdata($new_name, $value);
                $this->unset_userdata($name);
            }
        }
    }

    /**
    * PRIVATE: Internal method - removes "flash" session marked as 'old'
    */
    function _flashdata_sweep()
    {
        foreach ($_SESSION as $name => $value)
        {
            $parts = explode(':old:', $name);
            if (is_array($parts) && count($parts) == 2 && $parts[0] == $this->flashdata_key)
            {
                $this->unset_userdata($name);
            }
        }
    }
}
