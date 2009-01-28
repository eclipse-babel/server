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

require (dirname(__FILE__) . "/../backend_functions.php");

// Use a class to define the hooks to avoid bugs with already defined functions.
class BabelEclipseOrg_backend_staging {
    /*
     * Authenticate a user.
     * Adds data to the user object passed in argument if authenticated.
     */
    function authenticate($User, $email, $password) {
        global $dbh;
                
        $email      = sqlSanitize($email, $dbh);
        $password   = sqlSanitize($password, $dbh);
    
        // since MySQL ENCRYPT is not supported on windows we have to move encryption
        // from the database layer out to the application layer
        //  https://bugs.eclipse.org/bugs/show_bug.cgi?id=242011
                 
        $hash_query = "SELECT users.password_hash FROM users WHERE email = '$email'";
        $hash_result = mysql_query($hash_query, $dbh);
             
        if ($hash_result && mysql_num_rows($hash_result) > 0) {
            $hash_row = mysql_fetch_assoc($hash_result);
            $hash = $hash_row['password_hash'];
                    
            $sql = "SELECT *
                        FROM users 
                        WHERE email = '$email' 
                            AND password_hash = '" . crypt($password, $hash) . "'";
                            
            $result = mysql_query($sql, $dbh);
            if($result && mysql_num_rows($result) > 0) {
                $rValue = true;
                $myrow = mysql_fetch_assoc($result);
                        
                $User->userid               = $myrow['userid'];
                $User->username             = $myrow['username'];
                $User->first_name           = $myrow['first_name'];
                $User->last_name            = $myrow['last_name'];
                $User->email                = $myrow['email'];
                $User->primary_language_id  = $myrow['primary_language_id'];
                $User->is_committer         = $myrow['is_committer'];
                $User->hours_per_week       = $myrow['hours_per_week'];
                $User->updated_on           = $myrow['updated_on'];
                $User->updated_at           = $myrow['updated_at'];
                $User->created_on           = $myrow['created_on'];
                $User->created_at           = $myrow['created_at'];
    
            } else {
                // password failed
                $GLOBALS['g_ERRSTRS'][1] = mysql_error();
            }
        } else {
            // username failed
            $GLOBALS['g_ERRSTRS'][1] = mysql_error();
        }
    }
}

function __register_backend_staging($addon) {
    __register_backend($addon);
}

global $register_function_backend;
$register_function_backend = '__register_backend_staging';

?>
