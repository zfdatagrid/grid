<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Csv.php 492 2010-01-26 17:08:02Z pao.fresco $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

interface Bvb_Grid_Deploy_DeployInterface
{

    public function __construct (array $options = array());

    public function deploy();
}
