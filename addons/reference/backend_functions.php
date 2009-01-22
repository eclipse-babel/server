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

// Use a class to define the hooks to avoid bugs with already defined functions.
class Reference {
    /*
     * Authenticate a user.
     * Returns the User object if the user is found, or false
     */
    function authenticate($email, $password) {
        return false;
    }
}

function __register($addon) {
    $addon->register('user_authentication', array('Reference', 'authenticate'));
}



?>
