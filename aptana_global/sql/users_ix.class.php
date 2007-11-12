<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation

*******************************************************************************/
// ------...------...------...------...------...------...------...------...------...------...------

class users_ix extends cXSQL  {
  public $_id                  = '';
  public $_username            = '';
  public $_first_name          = '';
  public $_last_name           = '';
  public $_email               = '';
  public $_primary_language_id = '0';
  public $_hours_per_week      = '0';
  public $_password_salt       = '';
  public $_password_hash       = '';
  public $_updated_on          = '';
  public $_updated_at          = '';
  public $_created_on          = '';
  public $_created_at          = '';
}

// ------...------...------...------...------...------...------...------...------...------...------
?>