<?php
/*******************************************************************************
 * Copyright (c) 2007-2009 Intalio, Inc.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Antoine Toulme, Intalio Inc.
*******************************************************************************/

class AddonsManagement {

    private $addon;
    private $hooks = array();
  
    /**
     * Constructor. Registers the addon name to use.
     * The addon name should be the name of the folder in use.
     * You can directly pass the addon name, or have it be consumed
     * from a properties file as a separate location, under the key "addon".
     */
    function AddonsManagement($addon = null, $ini_file_path = null) {
        if (!isSet($addon)) {
            if (!isSet($ini_file_path)) {
                $ini_file_path = BABEL_BASE_DIR . 'classes/base.conf';
            }
            if (!($ini = @parse_ini_file($ini_file_path)) || !isSet($ini['addons'])) {
                error_log("Failed to find/read conf file - aborting.");
                exitTo("error.php?errNo=101300","error: 101300 - conf can not be found");
            }
            $addon = $ini['addons'];
        }
        $this->addon = $addon;
        debug_backtrace();
    }
    
    /**
     * Loads the addon, register the hooks for html functions.
     */
    public function load_html_functions() {
        require_once(BABEL_BASE_DIR . "addons/" . $this->addon . "/html_functions.php");
        __register($this);
    }
    
    /**
     * Loads the addon, register the hooks for backend functions.
     */
    public function load_backend_functions() {
        require_once(BABEL_BASE_DIR . "addons/" . $this->addon . "/backend_functions.php");
        __register($this);
    }
    
    /**
     * Registers a function for a specific key.
     * The function will be called later on.
     */
    public function register($hook_key, $function_name) {
        $this->hooks[$hook_key] = $function_name;
    }
    
    /**
     * Returns the name of the function to be used in the hook.
     */ 
    public function hook($hook_key) {
        return $this->hooks[$hook_key];
    }
}

/*
 * The default addon instance, to use in the product.
 */
$addon = new AddonsManagement();

?>
