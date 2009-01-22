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

define('BABEL_BASE_DIR', "../../");

require("../spec_helper.php");
require(BABEL_BASE_DIR . "classes/system/addons_management.php");



class DescribeAddonsBackendFunctionsLoading extends PHPSpec_Context {

    private $addon;
    
    public function before() {
        $this->addon = new AddonsManagement('reference');
        $this->addon->load_backend_functions();
    }
    
    public function itShouldHaveAddedAHookForUserAuthentication() {
        $this->spec($this->addon->hook("user_authentication"))->shouldNot->beNull();
        call_user_func($this->addon->hook("user_authentication"), "babel@babel.eclipse.org", "somepassword");
    }
    
}

?>
