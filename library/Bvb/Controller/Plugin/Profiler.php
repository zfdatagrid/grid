<?php



/**
 * Mascker
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License 2.0
 * It is  available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/gpl-2.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1  mascker 
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */


class Bvb_Controller_Plugin_Profiler extends Zend_Controller_Plugin_Abstract
{


    /**
     * [PT] Fazer o log da queries para a base de dados usando o db->profiler
     * [EN] Save the queries log to the databse using db->profiler
     *
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {

        
        //[PT] Buscar as configurações do site
        //[EN] Get config options
        $config = Zend_Registry::get ( 'config' );
        $db = Zend_Registry::get ( 'db' );
        
        //[PT] Realizar o profiler apenas se a opção profiler estiver activa
        //[EN] Only use the progiler if set to true
        if ($config->db->config->profiler)
        {
            
            //[PT] Instanciar a adaptador de base de dados
            //[EN] Get DB Adaptor
            $profiler = $db->getProfiler ();
            $totalTime = $profiler->getTotalElapsedSecs ();
            $queryCount = $profiler->getTotalNumQueries ();
            $longestTime = 0;
            $longestQuery = null;
            
            $data_hora = new Zend_Date ( );
            
            //[PT] Vamos percorrer os arrays todos para saber qual foi a que demoraou mais tempo, média e por segundo
            //[EN] Read the array to get the longest query time, the average e number of queries per second
            if (is_array ( $profiler->getQueryProfiles () ))
            {
                foreach ( $profiler->getQueryProfiles () as $query )
                {
                    if ($query->getElapsedSecs () > $longestTime)
                    {
                        $longestTime = $query->getElapsedSecs ();
                    }
                    
                    $params = $this->_request->getParams ();
                    $per_second = ($queryCount / $totalTime);
                    $average = ($totalTime / $queryCount);
                }
            }
            
            //[PT] Vamos guardar todas as queries na base de dados
            //[EN] Save all queries to the database
            if (is_array ( $profiler->getQueryProfiles () ))
            {
                foreach ( $profiler->getQueryProfiles () as $query )
                {
                    $params = $this->_request->getParams ();
                    $per_second = ($queryCount / $totalTime);
                    $average = ($totalTime / $queryCount);
                    
                    $data = array ('queries_number' => $queryCount, 'time' => $query->getElapsedSecs (), 'average' => $average, 'per_second' => $per_second, 'longest' => $longestTime, 'query' => $db->quote ( str_replace ( "\n", "", $query->getQuery () ) ), 'controller' => $params ['controller'], 'action' => $params ['action'], 'params' => serialize ( $this->_request->getParams () ), 'data' => $data_hora );
                    
                    $db->insert ( 'db_profiler', $data );
                }
            
            }
        }
    
    }


}