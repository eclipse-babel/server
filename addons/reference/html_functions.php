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
    /**
     * Returns the root path to the images.
     * May be a distant server or a local folder.
     */
    function getImageRoot() {
        return "http://dev.eclipse.org";
    }
    
    function validateMapFileUrl($url) {
        return true;
    }

}

function __register($addon) {
    $addon->register('image_root', 'getImageRoot');
    $addon->register('validate_map_file_url', 'validateMapFileUrl');
}

?>
