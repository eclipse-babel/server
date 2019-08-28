<?php
/*******************************************************************************
 * Copyright (c) 2007-2018 Intalio, Inc. and other
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Antoine Toulme, Intalio Inc.
 *    Denis Roy, Eclipse Foundation Inc.
*******************************************************************************/


// Use a class to define the hooks to avoid bugs with already defined functions.
class BabelEclipseOrg_backend {

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
        $hash_result = mysqli_query($dbh, $hash_query);

        if ($hash_result && mysqli_num_rows($hash_result) > 0) {
            $hash_row = mysqli_fetch_assoc($hash_result);
            $hash = $hash_row['password_hash'];

            # Handle crypt and sha-256 passwords
            # Bug 287844
            if(preg_match("/{([^}]+)}$/", $hash, $matches)) {
              $hash_method = $matches[0];
              $salt = substr($hash,0,8);
              # 2018-02-27 - Comma separator between salt and hash
              $pw = $salt . "," . str_replace("=", "", base64_encode(mhash(MHASH_SHA256, $password . $salt))) . $hash_method;				
            }
            else {
              $pw = crypt($password, $hash);
            }

            $sql = "SELECT *
                        FROM users 
                        WHERE email = '$email' 
                            AND password_hash = '" . $pw . "'";

            $result = mysqli_query($dbh, $sql);
            if($result && mysqli_num_rows($result) > 0) {
                $rValue = true;
                $myrow = mysqli_fetch_assoc($result);

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
                $GLOBALS['g_ERRSTRS'][1] = mysqli_error($dbh);
            }
        } else {
            // username failed
            $GLOBALS['g_ERRSTRS'][1] = mysqli_error($dbh);
        }
    }

    /**
     * Returns the genie user that represents the headless admin for most operations,
     * like importing a zip of translations.
     */
    function genieUser() {
        $User = new User();
        $User->loadFromID(2);
        return $User;
    }

    /**
     * Returns a user that is specialized in running the syncup script.
     */
    function syncupUser() {
        $User = new User();
        $User->loadFromID(3);
        return $User;
    }

	/**
	 * Returns the name of the current context, one of live, staging or dev.
	 */
    function context() {
        $ini = @parse_ini_file(dirname(__FILE__) . '/base.conf');
		if (!$ini) {
			die("Could not read the configuration file " . dirname(__FILE__) . "/base.conf");
		}
		return $ini["context"];
	}

	/**
	 * Returns a hash of the parameters.
	 */
	function db_params() {
		$ini = @parse_ini_file(dirname(__FILE__) . '/base.conf');
		if (!$ini) {
			die("Could not read the configuration file " . dirname(__FILE__) . "/base.conf");
		}
		return array('db_read_host' => $ini['db_read_host'],
					 'db_read_user' => $ini['db_read_user'],
					 'db_read_pass' => $ini['db_read_pass'], 
					 'db_read_name' => $ini['db_read_name']);
	}

	/**
	 * Returns a hash of the oauth parameters.
	 */
	function oauth_params() {
		$ini = @parse_ini_file(dirname(__FILE__) . '/base.conf');
		if (! $ini) {
			die("Could not read the configuration file " . dirname(__FILE__) . "/base.conf");
		}
		return array(
			'client_id' => $ini['oauth_client_id'],
			'client_secret' => $ini['oauth_client_secret'],
			'client_callback' => $ini['oauth_client_callback']
		);
	}

	/**
	 * Deals with error messages.
	 */
	function error_log() {
		$args = func_get_args();
		error_log($args);
	}
	
	/**
	 * Returns the name of the directory Babel should use to work in.
	 */
	function babel_working() {
		return "/home/babel-working/";
	}
}

function __register_backend($addon) {
    $addon->register('user_authentication', array('BabelEclipseOrg_backend', 'authenticate'));
    $addon->register('syncup_user', array('BabelEclipseOrg_backend', 'syncupUser'));
    $addon->register('genie_user', array('BabelEclipseOrg_backend', 'genieUser'));
    $addon->register('context', array('BabelEclipseOrg_backend', 'context'));
    $addon->register('db_params', array('BabelEclipseOrg_backend', 'db_params'));
    $addon->register('oauth_params', array('BabelEclipseOrg_backend', 'oauth_params'));
	$addon->register('error_log', array('BabelEclipseOrg_backend', 'error_log'));
	$addon->register('babel_working', array('BabelEclipseOrg_backend', 'babel_working'));
}

global $register_function_backend;
$register_function_backend = '__register_backend';

date_default_timezone_set('America/Montreal');

?>