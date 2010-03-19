<?php

/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */


class OFC_Chart
{

    public function __construct ()
    {
        $this->elements = array();
    }

    public function set_title ($t)
    {
        $this->title = $t;
    }

    public function set_x_axis ($x)
    {
        $this->x_axis = $x;
    }

    public function set_y_axis ($y)
    {
        $this->y_axis = $y;
    }

    public function add_y_axis ($y)
    {
        $this->y_axis = $y;
    }

    public function set_y_axis_right ($y)
    {
        $this->y_axis_right = $y;
    }

    public function add_element ($e)
    {
        $this->elements[] = $e;
    }

    public function set_x_legend ($x)
    {
        $this->x_legend = $x;
    }

    public function set_y_legend ($y)
    {
        $this->y_legend = $y;
    }

    public function set_bg_colour ($colour)
    {
        $this->bg_colour = $colour;
    }

    public function toString ()
    {
        return json_encode($this);
    }

    public function toPrettyString ()
    {
        return $this->toString();
    }
}

