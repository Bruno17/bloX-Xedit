<?php
/**
 * bloX
 *
 * Copyright 2009 by Bruno Perner <b.perner@gmx.de>
 *
 * This file is part of bloX.
 *
 * bloX is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * bloX is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * bloX; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package bloX
 */
/**
 * bloX
 *
 * a component for listing all kind of resources and preparing them for Xedit. 
 *
 * @name bloX
 * @author Bruno Perner <b.perner@gmx.de>
 * @package bloX
 */

// Path where bloX is installed
$bloxpath='assets/components/blox/';
$GLOBALS['blox_path']=$bloxpath;
//include snippet file
$output = "";
include($modx->getOption('base_path').$bloxpath.'blox.php');

return $output;
?>