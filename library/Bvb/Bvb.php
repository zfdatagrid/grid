<?php


/**
 * Mascker
 *
 * LICENSE
 *
 * This source file is subject to the Attribution-No Derivative Works license
 * It is  available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nd/3.0/us/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com.com)
 * @license    http://creativecommons.org/licenses/by-nd/3.0/us/    Attribution-No Derivative Works license
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */


final class Bvb 
{
  
    static public  $cache;
    
    function grid()
    {
        $front = Zend_Controller_Front::getInstance();
        $export = $front->getRequest()->getParam('export');

        $db = Zend_Registry::get('db');

        switch ($export) {
            case 'excel':
                $grid = new Bvb_Grid_Deploy_Excel($db,'Grid Example','media/files');
                #$grid->cache = array('use'=>0,'instance'=>Bvb::get('cache'),'tag'=>'grid');
                break;
            case 'word':
                $grid = new Bvb_Grid_Deploy_Word($db,'Grid Example','media/files');
                #$grid->cache = array('use'=>0,'instance'=>Bvb::get('cache'),'tag'=>'grid');
                break;
            case 'pdf':
                $grid =  new Bvb_Grid_Deploy_Pdf($db,'Grid Example','media/files');
                #$grid->cache = array('use'=>0,'instance'=>Bvb::get('cache'),'tag'=>'grid');
                break;
            case 'print':
                $grid =  new Bvb_Grid_Deploy_Print($db,'Grid Example');
                #$grid->cache = array('use'=>0,'instance'=>Bvb::get('cache'),'tag'=>'grid');
                break;
            default:
                $grid = new Bvb_Grid_Deploy_Table($db);
                $grid->imagesUrl  = Bvb::getBaseUrl() . '/public/images/';
                #$grid->cache = array('use'=>0,'instance'=>Bvb::get('cache'),'tag'=>'grid');

                break;
        }

        return $grid;
    }



    function getBaseUrl()
    {
        return Zend_Controller_Front::getInstance()->getBaseUrl();

    }

    static function reg ($name, $value)
    {

        Zend_Registry::set($name, $value);
        return true;
    }

    /**
     * Simplifica√ß√£o do get do Registry
     *
     * @param unknown_type $string
     * @return unknown
     */
    static function get ($string)
    {
        return Zend_Registry::get($string);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $loc
     */
    public function construir_lingua ($loc)
    {
        $db = Zend_Registry::get('db');

        $t = $db->fetchAll("SELECT original, pt_pt, en_us FROM nr_languages");

        foreach ($t as $ling) {
            $l['pt_pt'][$ling->original] = $ling->pt_pt;
            $l['en_us'][$ling->original] = $ling->en_us;
        }

        $translate = new Zend_Translate('array', $l['pt_pt'], 'pt_PT');
        $translate->addTranslation($l['en_us'],'en_US');
        $translate->setLocale($loc);

        Zend_Registry::set('Zend_Translate', $translate);

        return true;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $object
     * @return unknown
     */
    static function object2array ($object)
    {

        $return = NULL;

        if (is_array($object)) {
            foreach ($object as $key => $value)
            $return[$key] = self::object2array($value);
        } else {
            $var = get_object_vars($object);

            if ($var) {
                foreach ($var as $key => $value)
                $return[$key] = self::object2array($value);
            } else {
                return strval($object);
            }
        }

        return $return;

    }



}
