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
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Filters_Render_Table_Text extends Bvb_Grid_Filters_Render_RenderAbstract
{
    /**
     * @see library/Bvb/Grid/Filters/Render/Bvb_Grid_Filters_Render_RenderInterface::render()
     */
    public function render()
    {
        return $this->getView()->formText($this->getFieldName(), 
                                          $this->getDefaultValue(), 
                                          $this->getAttributes());
    }
}