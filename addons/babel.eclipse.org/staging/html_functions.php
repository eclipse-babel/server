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

require (dirname(__FILE__) . "/../html_functions.php");

// Use a class to define the hooks to avoid bugs with already defined functions.
class BabelEclipseOrgStaging extends BabelEclipseOrg {

}

function __register_html_staging($addon) {
    $addon->register('image_root', array('BabelEclipseOrgStaging', '_imageRoot'));
    $addon->register('validate_map_file_url', array('BabelEclipseOrgStaging', 'validateMapFileUrl'));
}

global $register_function_html;
$register_function_html = '__register_html_staging';
?>
