<?php
/*******************************************************************************
 * Copyright (c) 2007-2009 Intalio, Inc.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Antoine Toulme, Intalio Inc. 217488: Remove Phoenix as a requirement for Babel server
*******************************************************************************/

    /**
     * Sanitize incoming value to prevent SQL injections
     * @param string value to sanitize
     * @param dbh database resource to use
     * @return string santized string
     */
    function sqlSanitize($_value, $_dbh = null) {
        if(get_magic_quotes_gpc()) {
            $_value = stripslashes($_value);
        }
        $_value = mysql_real_escape_string($_value, $_dbh);
        return $_value;
    }
    
    function returnQuotedString($_String) {
        # Accept: String - String to be quoted
        # return: string - Quoted String
        
        // replace " with '
        $_String = str_replace('"', "'", $_String);
    
        return "\"" . $_String . "\"";
    }

    function getCURDATE() {
        return date("Y-m-d");
    }
    
    /** @author droy
     * @since version - Oct 19, 2006
     * @param String _param_name name of the HTTP GET/POST parameter
     * @param String _method GET or POST, or the empty string for POST,GET order 
     * @return String HTTP GET/POST parameter value, or the empty string
     *  
     * Fetch the HTTP parameter
     * 
     */
    function getHTTPParameter($_param_name, $_method="") {
        $rValue = "";
        $_method = strtoupper($_method);

        # Always fetch the GET VALUE, override with POST unless a GET was specifically requested
        if(isset($_GET[$_param_name])) {
            $rValue = $_GET[$_param_name];
        }
        if(isset($_POST[$_param_name]) && $_method != "GET") {  
            $rValue = $_POST[$_param_name];
        }
        return $rValue;
    }
    
    function addAndIfNotNull($_String) {
        # Accept: String - String to be AND'ed
        # return: string - AND'ed String
        
        if($_String != "") {
            $_String = $_String . " AND ";
        }
        
        return $_String;
    }
?>