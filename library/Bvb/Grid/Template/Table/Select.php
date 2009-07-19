<?php

/**
 * Mascker
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
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.4  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */



class Bvb_Grid_Template_Table_Select extends Bvb_Grid_Template_Table_Table
{

    public  $ic;

    public $insideLoop;

    public $go = 0;

    function globalStart ()
    {
        return "<input type=\"text\" id=\"inputResult\" style=\"display:none;\">
        <table id=\"newGrid\" width=\"100%\" name=\"listagem\" class=\"borders\" align=\"center\" cellspacing=\"0\" celpadding=\"0\">";
    }

    function loopStart ($values)
    {

        if($this->hasExtraRow==1 && $this->go==0 && $this->hasFilters==1 )
        {
            $this->go = 1;
            $this->ic++;
        }elseif($this->hasExtraRow==1 && $this->go==0 && $this->hasFilters==0 )
        {
            $this->go = 1;
            $this->ic--;
        }elseif($this->hasExtraRow==0 && $this->go==0 && $this->hasFilters==0)
        {
            $this->go = 1;
            $this->ic = $this->ic - 1;
        }

        $this->i++;
        $this->ic++;
        $this->ia++;
        $this->insideLoop = 1;

        return "<tr onclick=\"setpointer(this, ".($this->ic )." , '#90aaEB'); checkCheckbox($this->ia)\" >";
    }


    function loopLoop ($values)
    {

        if($this->insideLoop==1)
        {
            $input = "<input type=\"radio\" id=\"$this->ia\" value=\"{{value}}\"  name=\"grid\">";
            $input2 = "style=\"width:30px;\" ";
            $this->insideLoop = 2;
        }else{
            $input = "{{value}}";
            $input2 = '';
        }

        $class =  $this->i % 2 ? "alt" : "";

        return "<td class=\"$class {{class}}\" $input2 > $input &nbsp;</td>";
    }




}

