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


require("../spec_helper.php");
require(dirname(__FILE__) . "/../../classes/system/addons_management.php");



class DescribeAddonsHtmlFunctionsLoading extends PHPSpec_Context {

    private $addon;
    
    public function before() {
        $this->addon = new AddonsManagement('reference');
        $this->addon->load_html_functions();
    }
    
    public function itShouldHaveAddedAHookForImageRoot() {
        $this->spec($this->addon->hook("image_root"))->shouldNot->beNull();
        $this->spec(call_user_func($this->addon->hook("image_root")))->should->equal("http://dev.eclipse.org");
    }
    
    public function itShouldProvideAWayToValidateTheUrlOfAMapFile() {
        $this->spec($this->addon->hook("validate_map_file_url"))->shouldNot->beNull();
        $this->spec(call_user_func($this->addon->hook("validate_map_file_url")))->should->beTrue();
    }
    
    
}

?>
