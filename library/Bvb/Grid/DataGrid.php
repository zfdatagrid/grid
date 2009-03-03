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
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */


class Bvb_Grid_DataGrid
{

    private $_searchPerformedInArray = false;

    protected $_adapter = 'db';

    public $arrayData = array ();

    public $escapeOutput = true;

    private $_fieldsOrder;

    /**
     * [PT] Utilizado para quando temos uma source externa (Zend_Db_*)
     * e por isso mesmo não podemos alterar nenhuns valores
     */
    private $noMoreDataActions = 0;

    
    private $sourceIsExternal = 0;

    
    public $libraryDir = 'library';

    
    public static $_cache;

    /**
     * [Onde temos as classes]
     *
     * @var array
     */
    protected $template = array ();

    /**
     * [O tipo de templates que pode ser utilizado]
     *
     * @var unknown_type
     */
    protected $_templates;

    /**
     * [PT] A lista de dir e prifx a ser utilizadores para formatar campos
     *
     * @var unknown_type
     */
    protected $_formatter;

    
    protected $pagination = 15;

    
    /**
     * Os tipo de de exportação que podemos utilizar
     *
     * @var unknown_type
     */
    public $export = array ('pdf', 'word', 'wordx', 'excel', 'print', 'xml', 'csv', 'ods', 'odt' );

    /**
     * [PT] Toda a informação que não está ligada com a base de dados
     * [EN] All info that is not directly related to the database
     */
    public $info = array ();

    /**
     * [PT] Vamos guardar as tabelas a que já foram aplicadas a query describe table
     * [EN] Save the result of the describeTables
     */
    protected $_describeTables = array ();

    /**
     * [PT] Armazenar as primrys keys para não termos que fazer o loop sempre 
     * [PT] Que precisarmos
     * [EN] Registry for PK
     */
    protected $_getPrimaryKey = array ();

    /**
     * [PT] A parte where da query // 
     * [EN] Where part from query
     */
    protected $_queryWhere = '';

    /**
     * [PT] O adpater da BD
     * [EN] DB Adapter
     *
     * @var object
     */
    protected $_db;

    /**
     * [PT] A url base para constriuir a url final
     * [EN] Baseurl
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * [PT] O array com o resultado da consulta à base de dados
     * [EN] Array containing the query result from table(s)
     *
     * @var array
     */
    protected $_result;

    /**
     * [PT]Total de registos encontrados
     * [EN]Total records from db query
     *
     * @var int
     */
    protected $_totalRecords;

    /**
     * [PT] Array com os titulos a ser aplicados
     * [EN] Array containing filed titles
     *
     * @var array
     */
    protected $_titles;

    /**
     * [PT] Array contento os campos da(s) tablea(s)
     * [EN] Array containing table(s) fields
     *
     * @var array
     */
    protected $_fields = array ();

    /**
     * [PT] Where definido pelo utilizador a acrescentar ao criado pela Grid
     * [EN] Where initially defined by user
     *
     * @var string
     */
    protected $_where;

    /**
     * [PT] A lista de filtros
     *
     * @var unknown_type
     */
    public $filters;

    /**
     * [PT] Valores dos filtros que o utiliador inseriu
     * [EN] Filters values inserted by the user
     *
     * @var array
     */
    protected $_filtersValues;

    /**
     * [PT] Toda a informação relacionada com a base de dados
     * [EN] All information databse related
     *
     * @var array
     */
    public $data = array ();

    /**
     * [PT] Lista de filtros a ser aplicado
     * [EN] Filters List to be applied
     *
     * @var array
     */
    public $params = array ();

    /**
     * [PT] Url parametros
     * [EN] URL params
     *
     * @var string
     */
    public $ctrlParams;

    /**
     * [PT] Campos extra
     * [EN] Extra fields array
     *
     * @var array
     */
    public $extra_fields;

    /**
     * [PT]A lista final de campos. Pode parecer estranho, mas é assim
     *
     * @var unknown_type
     */
    protected $_finalFields;

    /**
     * [PT] O colspan a aplicar na tabela
     *
     * @var unknown_type
     */
    public $_colspan;

    /**
     * [PT] O númeor de campos ocultos que existem
     * [PT] Para depois podermos calcular o colspan
     *
     * @var int
     */
    public $totalHiddenFields;

    /**
     * [PT] COnfirmar que foi tudo analisado antes de processar a query
     *
     * @var unknown_type
     */
    private $consolidated = 0;

    /**
     * [PT] Se vamos utilizar o cache ou não
     * [PT] De futuro quando utilizar-mos o cache ele é limpo automaticamente nas funções CRUD
     *
     * @var bool
     */
    public $cache = false;

    /**
     * [PT] A lista de dirs onde procurar por "valideiros" e filtros
     *
     * @var array
     */
    protected $_elements = array ();

    /**
     * [PT] TIpos dpermitidos na aplicação aos elementos do formulários
     *
     * @var array
     */
    private $_elementsAllowed = array ('filter', 'validator' );

    /**
     * [PT] O campo para depos podermos ordenar caso exista a separação horizontal
     *
     * @var string
     */
    private $fieldHorizontalRow;

    /**
     * [PT] A instancia do template
     * [EN] The template instance
     *
     * @var unknown_type
     */
    protected $temp;

    /**
     * [PT] AS classes dos diferentes tipos de templates que já foram instanciados
     *
     * @var unknown_type
     */
    public $activeTemplates = array ();

    
    /**
     * [PT] Se a query tem group by
     */
    private $hasGroup = 0;

    private $_resultRaw;


    /**
     * [PT] A função __construct recebe o adapter para se liga à base de dados
     * [PT] É também tratada toda a informação relacionada com a url e os params
     * 
     * [EN] The __construct function receives the db adapter. All information related to the
     * [EN] URL is also processed here
     *
     * @param array $data
     */
    function __construct($db)
    {

        //[PT] Iniciar o adapter da base de dados
        $this->_db = $db;
        $this->_db->setFetchMode ( Zend_Db::FETCH_OBJ );
        

        $this->ctrlParams = Zend_Controller_Front::getInstance ()->getRequest ()->getParams ();
        $this->_baseUrl = Zend_Controller_Front::getInstance ()->getBaseUrl ();
        
        /**
         * [PT] OS plugins loaders
         * 
         */
        $this->_formatter = new Zend_Loader_PluginLoader ( );
        $this->_elements ['filter'] = new Zend_Loader_PluginLoader ( );
        $this->_elements ['validator'] = new Zend_Loader_PluginLoader ( );
        

        if (is_array ( $this->export ))
        {
            foreach ( $this->export as $temp )
            {
                $this->_templates = new Zend_Loader_PluginLoader ( array (), $temp );
            }
        }
        

        //[EN] Add Zend_Validate and Zend_Filter to the form element
        //[PT] Vamos adicionar os elementos da Zend
        $this->addElementDir ( 'Zend/Filter', 'Zend_Filter', 'filter' );
        $this->addElementDir ( 'Zend/Validate', 'Zend_Validate', 'validator' );
        

        //[EN] Add the frormatter fir for fields content
        $this->addFormatterDir ( 'Bvb/Grid/Formatter', 'Bvb_Grid_Formatter' );
        

        //[EN] Add the templates dir's
        $this->addTemplateDir ( 'Bvb/Grid/Template/Table', 'Bvb_Grid_Template_Table', 'table' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Pdf', 'Bvb_Grid_Template_Pdf', 'pdf' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Print', 'Bvb_Grid_Template_Print', 'print' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Word', 'Bvb_Grid_Template_Word', 'word' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Wordx', 'Bvb_Grid_Template_Wordx', 'wordx' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Csv', 'Bvb_Grid_Template_Csv', 'csv' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Xml', 'Bvb_Grid_Template_Xml', 'xml' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Ods', 'Bvb_Grid_Template_Ods', 'ods' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Odt', 'Bvb_Grid_Template_Odt', 'odt' );
    
    }


    function setAdapter($adapter)
    {

        $this->_adapter = strtolower ( $adapter ) != 'db' ? 'array' : 'db';
        
        return $this;
    }


    function setDataFromCsv($file, $field = null, $separator = ',')
    {

        $this->_adapter = 'array';
        

        if ($this->cache ['use'] == 1)
        {
            $cache = $this->cache ['instance'];
            
            if (! $final = $cache->load ( md5 ( 'array' . $file ) ))
            {
                

                $row = 0;
                $handle = fopen ( $file, "r" );
                while ( ($data = fgetcsv ( $handle, 1000, $separator )) !== FALSE )
                {
                    $num = count ( $data );
                    
                    if (null != $field)
                    {
                        for($c = 0; $c < $num; $c ++)
                        {
                            $final [$row] [$field [$c]] = $data [$c];
                        }
                    } else
                    {
                        if ($row == 0)
                        {
                            for($c = 0; $c < $num; $c ++)
                            {
                                $field [] = $data [$c];
                            }
                        
                        } else
                        {
                            for($c = 0; $c < $num; $c ++)
                            {
                                $final [$row - 1] [$field [$c]] = $data [$c];
                            }
                        }
                    }
                    
                    $row ++;
                }
                
                fclose ( $handle );
                
                $cache->save ( $final, md5 ( 'array' . $file ), array ($this->cache ['tag'] ) );
                $cache->save ( $field, md5 ( 'field' . $file ), array ($this->cache ['tag'] ) );
            
            } else
            {
                $final = $cache->load ( md5 ( 'array' . $file ) );
                $field = $cache->load ( md5 ( 'field' . $file ) );
            }
        
        } else
        {
            
            $row = 0;
            $handle = fopen ( $file, "r" );
            while ( ($data = fgetcsv ( $handle, 1000, $separator )) !== FALSE )
            {
                $num = count ( $data );
                

                if (null != $field)
                {
                    
                    for($c = 0; $c < $num; $c ++)
                    {
                        $final [$row] [$field [$c]] = $data [$c];
                    }
                

                } else
                {
                    if ($row == 0)
                    {
                        for($c = 0; $c < $num; $c ++)
                        {
                            $field [] = $data [$c];
                        }
                    
                    } else
                    {
                        for($c = 0; $c < $num; $c ++)
                        {
                            $final [$row - 1] [$field [$c]] = $data [$c];
                        }
                    }
                }
                
                $row ++;
            }
            
            fclose ( $handle );
        }
        

        $this->addArrayColumns ( $field );
        $this->addArrayData ( $final );
        
        return $this;
    
    }


    function setDataFromXml($url, $loop = null, $columns = null)
    {

        $this->_adapter = 'array';
        

        if ($this->cache ['use'] == 1)
        {
            $cache = $this->cache ['instance'];
            
            if (! $xml = $cache->load ( md5 ( $url ) ))
            {
                $xml = simplexml_load_file ( $url );
                $xml = $this->object2array ( $xml );
                $cache->save ( $xml, md5 ( $url ), array ($this->cache ['tag'] ) );
            } else
            {
                $xml = $cache->load ( md5 ( $url ) );
            }
        } else
        {
            $xml = simplexml_load_file ( $url );
            $xml = $this->object2array ( $xml );
        }
        

        $cols = explode ( ',', $loop );
        if (is_array ( $cols ))
        {
            foreach ( $cols as $value )
            {
                $xml = $xml [$value];
            }
        }
        

        if (is_array ( $columns ))
        {
            foreach ( $columns as $value )
            {
                $columns = $columns [$value];
            }
        } else
        {
            $columns = array_keys ( $xml [0] );
        }
        
        $this->addArrayColumns ( $columns );
        $this->addArrayData ( $xml );
        
        return $this;
    
    }


    function setDataFromJson($array, $file = false, $loop = null, $columns = null)
    {

        $this->_adapter = 'array';
        
        if (true === $file)
        {
            
            if ($this->cache ['use'] == 1)
            {
                $cache = $this->cache ['instance'];
                
                if (! $result = $cache->load ( md5 ( $array ) ))
                {
                    $result = file_get_contents ( $array );
                    
                    $cache->save ( $result, md5 ( $array ), array ($this->cache ['tag'] ) );
                } else
                {
                    $result = $cache->load ( md5 ( $array ) );
                }
            } else
            {
                $result = file_get_contents ( $array );
            }
        

        } else
        {
            $result = $array;
        }
        
        $xml = Zend_Json::decode ( $result );
        
        $cols = explode ( ',', $loop );
        if (is_array ( $cols ))
        {
            foreach ( $cols as $value )
            {
                $xml = $xml [$value];
            }
        }
        
        if (is_array ( $columns ))
        {
            foreach ( $columns as $value )
            {
                $columns = $columns [$value];
            }
        } else
        {
            $columns = array_keys ( $xml [0] );
        }
        

        $this->addArrayColumns ( $columns );
        $this->addArrayData ( $xml );
        
        return $this;
    
    }


    function setDataFromArray($array)
    {

        $this->_adapter = 'array';
        
        $this->addArrayColumns ( array_keys ( $array [0] ) );
        $this->addArrayData ( $array );
        
        return $this;
    
    }


    /**
     * [PT] O tradutor
     * [EN] The translator
     *
     * @param string $message
     * @return string
     */
    function __($message)
    {

        if (Zend_Registry::isRegistered ( 'Zend_Translate' ))
        {
            $message = Zend_Registry::get ( 'Zend_Translate' )->translate ( $message );
        }
        return $message;
    }


    /**
     * [PT] não faz mais nada senão passar o valor para __set
     * [PT] só que assim podemos utilizar o para∏¿≈}{⁄{}{⁄ (não me lembro do nome)
     * [PT] $grid->from('barcelos')
     *             ->noFilters(1)->
     *             ->noOrder(1);
     *
     * 
     * [EN] Use the overload function so we can return an object to  make possibler
     * [EN] the use of 
     * $grid->from('barcelos')
     *             ->noFilters(1)->
     *             ->noOrder(1);
     * @param string $name
     * @param string $value
     * @return unknown
     */
    function __call($name, $value)
    {

        if (substr ( strtolower ( $name ), 0, 3 ) == 'set')
        {
            $name = substr ( $name, 3 );
        }
        $this->__set ( $name, $value [0] );
        return $this;
    }


    /**
     * [Para podemros utiliza]
     *
     * @param string $var
     * @param string $value
     */
    function __set($var, $value)
    {

        //[PT] A variavel data contém os cmapos que são parte integrante da query e que 
        //[PT] isso mesmo tem que ser colocado num array distinto
        //[EN] The data variavel contains options related to the query,
        //[EN] because of thatm they need to go to a separate Array
        $data = array ('from', 'order', 'where', 'primaryKey', 'table', 'fields', 'hide' );
        if (in_array ( $var, $data ))
        {
            
            if ($var == 'from' && ! strpos ( " ", trim ( $value ) ))
            {
                $this->data ['from'] = trim ( $value );
                $this->data ['table'] = trim ( $value );
                $this->_adapter = 'db';
            
            } else
            {
                $this->data [$var] = $value;
            }
        } else
        {
            $this->info [$var] = $value;
        }
    }


    /**
     * [PT]
     * Define which allowed types of exportation 
     *
     * @param array $var
     * @return $this
     */
    function export(array $var)
    {

        $this->export = $var;
        return $this;
    }


    /**
     * [PT] Buscar a copmposição da tabela
     * [PT] Depois disso vamos meter a tabela num array. se precisar-mos mais tarde,
     * [PT] Ela já está lá
     * 
     * [EN] Get table description and then save it to a array.
     *
     * @param array|string $table
     * @return array
     */
    function getDescribeTable($table)
    {

        if (! @is_array ( $this->_describeTables [$table] ))
        {
            
            if ($this->cache ['use'] == 1)
            {
                
                $cache = $this->cache ['instance'];
                

                if (! $describe = $cache->load ( md5 ( 'describe' . $table ) ))
                {
                    $describe = $this->_db->describeTable ( $table );
                    $cache->save ( $describe, md5 ( 'describe' . $table ), array ($this->cache ['tag'] ) );
                
                } else
                {
                    $describe = $cache->load ( md5 ( 'describe' . $table ) );
                }
            

            } else
            {
                $describe = $this->_db->describeTable ( $table );
            }
            

            $this->_describeTables [$table] = $describe;
        }
        

        return $this->_describeTables [$table];
    }


    /**
     * [PT]
     * Adicionar um campo à lista ja existente.
     * Temos que verificar se não existe. 
     * 
     * Temos também o caso em que o campo pode estar marcado como 
     * linha horizontal, por isso mesmo temos que o definir na variavel $this->info
     * 
     * [EN]
     * Add a new coolumn to the list, if not there yet
     * 
     * We must also check if one of the parameters is an horizontal row.
     * It it is, we mus define it on the $this->info array
     *
     * @param string $field
     * @param array $options
     * @return unknown
     */
    function addColumn($field, $options = array())
    {

        if (@is_array ( $this->data ['fields'] ))
        {
            
            if (! array_key_exists ( $field, $this->data ['fields'] ))
            {
                $this->data ['fields'] [$field] = $options;
                if (isset ( $options ['hRow'] ))
                {
                    if ($options ['hRow'] == 1)
                    {
                        $this->fieldHorizontalRow = $field;
                        $this->info ['hRow'] = array ('field' => $field, 'title' => $options ['title'] );
                    }
                }
                
                if (isset ( $options ['groupBy'] ))
                {
                    $this->info ['groupby'] = $field;
                }
            
            } else
            {
                
                $this->data ['fields'] [$field] = array_merge_recursive ( $this->data ['fields'] [$field], $options );
            
            }
        

        } else
        {
            $this->data ['fields'] [$field] = $options;
        }
        

        return $this;
    }


    /**
     * [PT] 
     * Adicionar mais uma opção para encontro de formatadores de cnteúdo
     * desde data, numeros, imagens, etc, etc
     *
     * [EN]
     * Add a new dir to look for when formating a field
     * 
     * @param string $dir
     * @param string $prefix
     * @return $this
     */
    function addFormatterDir($dir, $prefix)
    {

        $this->_formatter->addPrefixPath ( trim ( $prefix, "_" ), trim ( $dir, "/" ) . '/' );
        return $this;
    }


    /**
     * [PT]
     * Adicionar um direcório de pesquisa de filtros ou 'validadores'
     * para quando utilizarmos o formulário
     * 
     * 
     * [EN]
     * Add new elements form dir.
     * TRhey can be filters os validators
     *
     * @param string $dir
     * @param string $prefix
     * @param string $type
     * @return $this
     */
    function addElementDir($dir, $prefix, $type = 'filter')
    {

        if (! in_array ( strtolower ( $type ), $this->_elementsAllowed ))
        {
            throw new Exception ( 'Type not recognized' );
        }
        
        $this->_elements [$type]->addPrefixPath ( trim ( $prefix, "_" ), trim ( $dir, "/" ) . '/' );
        
        return $this;
    }


    /**
     * [PT] 
     * Formatar o valor de cada campo que vem da base de dados
     * 
     * [EN]
     * Format a field 
     *
     * @param unknown_type $value
     * @param unknown_type $formatter
     * @param unknown_type $options
     * @return unknown
     */
    function applyFormat($value, $formatter)
    {

        
        //[PT] Para simplificar as coisas vemos se é um array.
        //[PT] Pode não ser necessários argumentos adicionais
        if (is_array ( $formatter ))
        {
            $result = $formatter [0];
            $options = $formatter [1];
        } else
        {
            $result = $formatter;
            $options = null;
        }
        
        $class = $this->_formatter->load ( $result );
        
        $t = new $class ( $options );
        $return = $t->format ( $value );
        

        return $return;
    }


    /**
     * [PT] Todos os dados relacionados com a conneção á base de dados
     * [PT] Os filtros, campos extra, etc, etc
     * 
     * [EN] All information related with database.
     *[EN] Filters, extra fields, etc, etc
     * @param string $data
     * 
     * */
    function setData($data)
    {

        $this->data = $data ['data'];
        $this->info = $data;
        if (! is_array ( $this->data ['table'] ))
        {
            $this->data ['table'] = $this->data ['from'];
        }
    }


    /**
     * Criar a fgrid através de uma ficheiro XML
     * 
     * 
     * TESTE
     * TEST
     * TESTE
     * TEST
     * TESTE
     * TEST
     * TESTE
     * TEST
     *
     */
    function setGridFromXml($file)
    {

        $file = rtrim ( $file, ".xml" ) . ".xml";
        $xml = $this->object2array ( simplexml_load_file ( $file ) );
        

        if (strlen ( $xml ['data'] ['where'] ) > 0)
        {
            $final = $xml ['data'] ['where'];
            $final1 = preg_match_all ( "/{eval}(.*?){\/eval}/", $final, $t );
            $t2 = $t;
            $i = 0;
            foreach ( $t2 [1] as $value )
            {
                $h = eval ( "return " . $value . ";" );
                $final = str_replace ( $t [0] [$i], $h, $final );
                $i ++;
            }
            $xml ['data'] ['where'] = $final;
        }
        
        foreach ( $xml ['data'] ['fields'] as $key => $final )
        {
            if (is_array ( $final ['@attributes'] ))
            {
                unset ( $xml ['data'] ['fields'] [$key] );
                $xml ['data'] ['fields'] [$key . " AS " . $final ['@attributes'] ['as']] = $final;
            }
        }
        

        self::setData ( $xml );
        $this->info = $xml ['info'];
    
    }


    /**
     * [PT]  Os campos permitidos para uma tabela
     * [EN] The allowed fields from a table
     *
     * @param string $mode
     * @param string $table
     * @return string
     */
    function getFields($mode = 'edit', $table)
    {

        $get = $this->info [$mode] ['fields'];
        if (! is_array ( $get ))
        {
            $get = $this->getTableFields ( $table );
        }
        return $get;
    }


    /**
     * [PT] Os campos de uma tabela
     * [EN] Get table fields
     *
     * @param string $table
     * @return string
     */
    function getTableFields($table)
    {

        $table = $this->getDescribeTable ( $table );
        foreach ( array_keys ( $table ) as $key )
        {
            $val [$key] = $key;
        }
        return $val;
    }


    /**
     * Definir a paginação
     *
     */
    function setPagination($number = 15)
    {

        $this->pagination = ( int ) $number;
        return $this;
    }


    /**
     * [PT] Calcular o colspan para a paginação e topo
     * [EN] Calculate colspan for pagination and top
     *
     * @return int
     */
    function colspan()
    {

        $totalFields = count ( $this->_fields );
        $a = 0;
        $i = 0;
        foreach ( $this->data ['fields'] as $value )
        {
            if (isset ( $value ['hide'] ))
            {
                if ($value ['hide'] == 1)
                {
                    $i ++;
                }
            }
            if (isset ( $value ['hRow'] ))
            {
                if ($value ['hRow'] == 1)
                {
                    $totalFields --;
                }
            }
        }
        $totalFields = $totalFields - $i;
        if (@$this->info ['delete'] ['allow'] == 1)
        {
            $a ++;
        }
        if (@$this->info ['edit'] ['allow'] == 1)
        {
            $a ++;
        }
        $totalFields = $totalFields + $a;
        $colspan = $totalFields + count ( $this->extra_fields );
        
        if (@is_object ( $this->temp [$this->output] ))
        {
            $this->temp [$this->output]->colSpan = $colspan;
        }
        return $colspan;
        #return count ( $this->_fields ) - $this->totalHiddenFields + count($this->extra_fields);
    }


    /**
     * [PT] Aplicar o quoteidentifier aos campos da base de dados
     * [EN] Apply quoteidentifier to the table fields
     *
     * @return string
     */
    function buildSelectFields($values)
    {

        if ($this->sourceIsExternal == 1)
        {
            return implode ( ', ', $values );
        }
        

        foreach ( $values as $value )
        {
            
            /*
            if (isset ( $this->data ['fields'] [$value] ['sqlexp'] ))
            {
                $sqlExp = $this->data ['fields'] [$value] ['sqlexp'];
                
                if (stripos ( $value, ' AS ' ))
                {
                    $asFinal = substr ( $value, stripos ( $value, ' as' ) + 4 );
                    $asValue = substr ( $value, 0, stripos ( $value, ' as' ) );
                } else
                {
                    $asFinal = substr ( $value, stripos ( $value, ' as' ) + 5 );
                    $asValue = $value;
                }
                
               
                if (strpos ( $value, "." ))
                {
                    $ini = substr ( $value, 0, (strpos ( $value, "." )) );
                    $fields [] = $sqlExp . '(' . $this->_db->quoteIdentifier ( $asValue ) . ') AS ' . $asFinal;
                } else
                {
                    $fields [] = $sqlExp . '(' . $this->_db->quoteIdentifier ( $asValue ) . ') AS ' . $this->_db->quoteIdentifier ( $asValue );
                }
                
                
            } else
            
            */
            if (isset ( $this->data ['fields'] [$value] ['sqlexp'] ))
            {
                $sqlExp = trim ( $this->data ['fields'] [$value] ['sqlexp'] );
                
                if (stripos ( $sqlExp, " AS " ))
                {
                    $fields [] = $sqlExp;
                } else
                {
                    $fields [] = $sqlExp . ' AS ' . str_replace ( '.', '', $value );
                }
            

            } else
            {
                
                if (stripos ( $value, ' AS ' ))
                {
                    $asFinal = substr ( $value, stripos ( $value, ' as' ) + 4 );
                    $asValue = substr ( $value, 0, stripos ( $value, ' as' ) );
                    
                    $fields [] = $this->_db->quoteIdentifier ( $asValue ) . ' AS ' . $asFinal;
                
                } elseif (strpos ( $value, "." ))
                {
                    $ini = substr ( $value, 0, (strpos ( $value, "." )) );
                    $fields [] = $this->_db->quoteIdentifier ( $ini ) . substr ( $value, strpos ( $value, "." ) );
                } else
                {
                    $fields [] = $this->_db->quoteIdentifier ( $value );
                }
            }
        }
        

        return implode ( ', ', $fields );
    }


    /**
     * [PT] O tipo de procura que vai ser utilizado nos filtros
     * [PT] Por defeito é LIKE, mas não funciona em numeros onde se quero 12 também vai
     * [PT] considerar o 1123, 1232, etc, 
     *
     * @param unknown_type $filtro
     * @param unknown_type $key
     * @return unknown
     */
    function buildSearchType($filtro, $key)
    {

        $fieldsSemAsFinal = $this->removeAsFromFields ();
        if (@$fieldsSemAsFinal [$key] ['searchType'] != "")
        {
            $op = @$fieldsSemAsFinal [$key] ['searchType'];
        }
        $op = @strtolower ( $op );
        switch ($op)
        {
            case 'equal' :
            case '=' :
                $return = " = {$this->_db->quote ($filtro)}  ";
                break;
            case 'rlike' :
                $return = " LIKE {$this->_db->quote ( $filtro . "%" )} ";
                break;
            case 'llike' :
                $return = " LIKE {$this->_db->quote ( "%" . $filtro )} ";
                break;
            case '>=' :
                $return = " >= {$this->_db->quote ($filtro )} ";
                break;
            case '>' :
                $return = " > {$this->_db->quote ($filtro )}  ";
                break;
            case '<>' :
            case '!=' :
                $return = " <> {$this->_db->quote ($filtro )}  ";
                break;
            case '<=' :
                $return = " <= {$this->_db->quote ($filtro )}  ";
                break;
            case '<' :
                $return = " < {$this->_db->quote ($filtro )}  ";
                break;
            default :
                $return = " LIKE  {$this->_db->quote ( "%" . $filtro . "%" )} ";
                break;
        }
        return $return;
    }


    /**
     * [PT] Construir o WHERE da query
     * [EN] Build the query WHERE
     *
     * @return string
     */
    function buildQueryWhere()
    {

        if (strlen ( $this->_queryWhere ) > 1)
        {
            return $this->_queryWhere;
        }
        if (strlen ( trim ( $this->_where ) ) > 1)
        {
            $query_where = " WHERE " . $this->_where . "  ";
            $tem_where_1 = true;
        } else
        {
            $query_where = '';
            $tem_where_1 = false;
        }
        $query_final = '';
        $new_where = '';
        $tem_where = false;
        //Vamos criar a aray para sabermos o valor dos filtro
        $valor_filters = array ();
        $filters = @urldecode ( $this->ctrlParams ['filters'] );
        $filters = str_replace ( "filter_", "", $filters );
        $filters = Zend_Json::decode ( $filters );
        $fieldsSemAsFinal = $this->removeAsFromFields ();
        

        if (is_array ( $filters ))
        {
            foreach ( $filters as $key => $filtro )
            {
                $key = str_replace ( "bvbdot", ".", $key );
                if (strlen ( $filtro ) == 0 || ! in_array ( $key, $this->map_array ( $this->_fields, 'replace_AS' ) ))
                {
                    unset ( $filters [$key] );
                } else
                {
                    $oldKey = $key;
                    if (@$fieldsSemAsFinal [$key] ['searchField'] != "")
                    {
                        $key = $this->replaceAsString ( $fieldsSemAsFinal [$key] ['searchField'] );
                    }
                    $new_where .= " AND $key " . $this->buildSearchType ( $filtro, $oldKey ) . "  ";
                    $tem_where = true;
                    $valor_filters [$key] = $filtro;
                }
            }
        }
        
        $this->_filtersValues = $valor_filters;
        if ($tem_where)
        {
            $query_final = "  " . $query_where;
            if ($tem_where && $tem_where_1)
            {
                $query_final = $query_final . " AND ";
            }
            $query_final .= "(" . substr ( $new_where, 4 ) . ")";
            if (! $tem_where_1)
            {
                $query_final = " WHERE " . $query_final;
            }
        } else
        {
            $query_final = $query_where;
        }
        $this->_queryWhere = $query_final;
        return $this->_queryWhere;
    }


    /**
     * [PT] compor a query. Apenas o order e o limit
     * [EN] Build query. only LIMIT and ORDER
     *
     * @return string
     */
    function buildQuery()
    {

        @$inicio = ( int ) $this->ctrlParams ['start'];
        $order = @$this->ctrlParams ['order'];
        $order1 = explode ( "_", $order );
        $orderf = strtoupper ( end ( $order1 ) );
        

        if ($orderf != 'DESC' and $orderf != 'ASC')
        {
            
            $orderf = 'ASC';
            $order_field = $order;
            $query_order = " ORDER BY " . $this->_db->quoteIdentifier ( $order_field ) . " $orderf ";
        
        } else
        {
            array_pop ( $order1 );
            $order_field = implode ( "_", $order1 );
            $query_order = " ORDER BY " . $this->_db->quoteIdentifier ( $order_field ) . " $orderf ";
        }
        

        $this->order [$order_field] = $orderf == 'ASC' ? 'DESC' : 'ASC';
        

        if (! in_array ( $order_field, $this->map_array ( $this->_fieldsOrder, 'replace_AS' ) ))
        {
            unset ( $query_order );
            $query_order = '';
            if (@strlen ( $this->data ['order'] ) > 0)
            {
                $query_order = " ORDER BY  " . $this->data ['order'];
            }
        }
        

        if (strlen ( $this->fieldHorizontalRow ) > 0)
        {
            $split = $this->_db->quoteIdentifier ( $this->fieldHorizontalRow );
            if (strlen ( $query_order ) > 4)
            {
                $query_order .= ' ,' . $split . ' ASC ';
            }
        }
        
        $groupBy = '';
        if (isset ( $this->info ['groupby'] ))
        {
            $groupBy = " GROUP BY  " . $this->_db->quoteIdentifier ( $this->info ['groupby'] );
        }
        

        $having = '';
        
        if (isset ( $this->info ['having'] ))
        {
            if (is_array ( $this->info ['having'] ))
            {
                $having = " HAVING  " . $this->_db->quoteIdentifier ( $this->info ['having'] ['field'] ) . "  " . $this->info ['having'] ['operand'] . " " . $this->_db->quote ( $this->info ['having'] ['value'] );
            }
        }
        
        if (@strlen ( $this->info ['limit'] ) > 0 || @is_array ( $this->info ['limit'] ))
        {
            if (is_array ( $this->info ['limit'] ))
            {
                $limit = " LIMIT " . $this->info ['limit'] [0] . ',' . $this->info ['limit'] [1];
            } else
            {
                $limit = " LIMIT " . $this->info ['limit'];
            }
        } elseif ($this->pagination > 0)
        {
            $limit = " LIMIT " . $inicio . " , $this->pagination";
        } else
        {
            $limit = '';
        }
        

        $final = "$groupBy $having $query_order  $limit ";
        

        return $final;
    }


    /**
     * [PT] A URL actual excepto o parametros definido em situation
     * [EN] Returns the url, without the param(s) specified 
     *
     * @param array|string $situation
     * @return string
     */
    function getUrl($situation = '')
    {

        $url = '';
        $params = $this->ctrlParams;
        if (is_array ( $situation ))
        {
            foreach ( $situation as $value )
            {
                unset ( $params [$value] );
            }
        } else
        {
            unset ( $params [$situation] );
        }
        

        if (count ( $this->params ) > 0)
        {
            //User as defined its own params (probably using routes)
            $myParams = array ('comm', 'order', 'filters', 'add', 'edit' );
            $newParams = $this->params;
            foreach ( $myParams as $value )
            {
                if (strlen ( $params [$value] ) > 0)
                {
                    $newParams [$value] = $params [$value];
                }
            }
            $params = $newParams;
        }
        

        $params_clean = $params;
        unset ( $params_clean ['controller'] );
        unset ( $params_clean ['module'] );
        unset ( $params_clean ['action'] );
        foreach ( $params_clean as $key => $param )
        {
            //[PT] Se estivermos a falar dos filtros, temos que fazer o urldecode por causa
            //[PT] dos caracteres especiais que tem a url ( JSON )
            //[EN] Apply the urldecode function to the filtros param, because its  JSON
            if ($key == 'filters')
            {
                $url .= "/" . trim ( $key ) . "/" . trim ( urlencode ( $param ) );
            } else
            {
                @$url .= "/" . trim ( $key ) . "/" . trim ( $param );
            }
        }
        
        if (strlen ( $params ['action'] ) > 0)
        {
            $action = "/" . $params ['action'];
        }
        

        //[PT] Não precisamos das keys de action e controller, por isso removemos
        //[EN] Remove the action e controller keys, they are not necessary (in fact they aren't part of url)
        if (array_key_exists ( 'ajax', $this->info ))
        {
            return $params ['module'] . "/" . $params ['controller'] . $action . $url . "/modo/ajax";
        } else
        {
            return $this->_baseUrl . "/" . $params ['module'] . "/" . $params ['controller'] . $action . $url;
        }
    }


    /**
     * [PT] Construir os filtros
     * [PT] E se necessário colocar lá os valores
     *
     * [EN] Build Filters. If defined put the values
     * [EN] Also check if the user wants to hide a field
     *  
     * 
     * @return string
     */
    function buildFilters()
    {

        $return = array ();
        if (@$this->info ['noFilters'])
        {
            return false;
        }
        

        $data = $this->map_array ( $this->_fields, 'replace_AS' );
        $tcampos = count ( $data );
        
        for($i = 0; $i < count ( $this->extra_fields ); $i ++)
        {
            if ($this->extra_fields [$i] ['position'] == 'left')
            {
                $return [] = array ('type' => 'extraField', 'class' => $this->template ['classes'] ['filter'], 'position' => 'left' );
            }
        }
        

        for($i = 0; $i < $tcampos; $i ++)
        {
            if (! isset ( $this->data ['fields'] [$this->_fields [$i]] ['hide'] ))
            {
                if (@array_key_exists ( $data [$i], $this->filters ))
                {
                    if (isset ( $this->filters [$data [$i]] ['decorator'] ) && is_array ( $this->filters [$data [$i]] ))
                    {
                        $return [] = array ('type' => 'field', 'value' => $this->filters [$data [$i]] ['decorator'], 'field' => $data [$i] );
                    } else
                    {
                        $return [] = array ('type' => 'field', 'class' => $this->template ['classes'] ['filter'], 'value' => self::formatField ( $data [$i], $data [$i] ), 'field' => $data [$i] );
                    }
                } else
                {
                    $return [] = array ('type' => 'field', 'class' => $this->template ['classes'] ['filter'], 'field' => $data [$i] );
                }
            }
        }
        

        for($i = 0; $i < count ( $this->extra_fields ); $i ++)
        {
            if ($this->extra_fields [$i] ['position'] == 'right')
            {
                $return [] = array ('type' => 'extraField', 'class' => $this->template ['classes'] ['filter'], 'position' => 'right' );
            }
        }
        

        return $return;
    }


    /**
     * [PT] Vamos consildar os campoos que estão no array pelo dos fields da tabela
     * 
     * [En] Consolidate the fields that are in the array with the one on the table
     *
     * @param array $fields
     * @param string $table
     * @return array
     */
    function consolidateFields($fields, $table)
    {
        $table = $this->_db->describeTable($table);
        
        
        foreach ( $table as $value )
        {
            if ($value['IDENTITY'] === false)
            {
                $table_fields [] = $value['COLUMN_NAME'];
            }
        }
        
        foreach ( $fields as $key => $value )
        {
            if (! in_array ( $value, $table_fields ))
            {
                unset ( $fields [$key] );
            }
        }
        //[PT] Vamos zerar a key
        foreach ( $fields as $value )
        {
            $fields_final [] = $value;
        }
        return $fields_final;
    }


    /**
     * [PT]Aplicar diversas funções a arrays
     *
     * @param unknown_type $campos
     * @param unknown_type $callback
     * @return unknown
     */
    function map_array($campos, $callback)
    {

        if (! is_array ( $campos ))
            return FALSE;
        

        $ncampos = array ();
        foreach ( $campos as $key => $value )
        {
            if (is_array ( $value ))
                return FALSE;
            

            if (strlen ( $value ) == 0)
            {
                
                $ncampos [] = stripos ( $key, ' AS ' ) ? substr ( $key, 0, stripos ( $key, ' AS ' ) ) : $key;
            } else
            {
                $ncampos [] = stripos ( $value, ' AS ' ) ? substr ( $value, 0, stripos ( $value, ' AS ' ) ) : $value;
                ;
            }
        
        }
        
        $campos = $ncampos;
        unset ( $ncampos );
        $ncampos = array ();
        switch ($callback)
        {
            case 'prepare_replace' :
                foreach ( $campos as $value )
                {
                    $ncampos [] = "{{" . $value . "}}";
                }
                break;
            case 'replace_AS' :
                $ncampos = $campos;
                break;
            case 'prepare_output' :
                foreach ( $campos as $value )
                {
                    $ncampos [] = htmlspecialchars ( $value );
                }
                break;
            default :
                break;
        }
        

        return $ncampos;
    }


    /**
     * [PT] Construir os títulos
     * [PT] Já com os links para ordenar
     * 
     * [EN] Build the titles with the order links (if wanted)
     *
     * @return string
     */
    function buildTitles()
    {

        $return = array ();
        $url = $this->getUrl ( array ('order', 'start', 'comm' ) );
        $tcampos = count ( $this->_fields );
        

        for($i = 0; $i < count ( $this->extra_fields ); $i ++)
        {
            if ($this->extra_fields [$i] ['position'] == 'left')
            {
                $return [$this->extra_fields [$i] ['name']] = array ('type' => 'extraField', 'value' => $this->extra_fields [$i] ['name'], 'position' => 'left' );
            }
        }
        
        $titles = $this->map_array ( $this->_fields, 'replace_AS' );
        
        $novaData = array ();
        
        if (is_array ( $this->data ['fields'] ))
        {
            foreach ( $this->data ['fields'] as $key => $value )
            {
                $nkey = stripos ( $key, ' AS ' ) ? substr ( $key, 0, stripos ( $key, ' AS ' ) ) : $key;
                $novaData [$nkey] = $value;
            }
        }
        
        $links = $this->_fields;
        

        for($i = 0; $i < $tcampos; $i ++)
        {
            if (isset ( $this->ctrlParams ['order'] ))
            {
                $this->order [reset ( explode ( '_', $this->ctrlParams ['order'] ) )] = strtoupper ( end ( explode ( '_', $this->ctrlParams ['order'] ) ) ) == 'ASC' ? 'DESC' : 'ASC';
            }
            $fieldsToOrder = $this->reset_keys ( $this->data ['fields'] );
            
            @$orderFinal = strlen ( $fieldsToOrder [$i] ['orderField'] ) > 0 ? $fieldsToOrder [$i] ['orderField'] : $titles [$i];
            
            $order = $orderFinal == @key ( $this->order ) ? $this->order [$orderFinal] : 'ASC';
            

            if (! isset ( $novaData [$titles [$i]] ['hide'] ))
            {
                
                if ($titles [$i] == @key ( $this->order ))
                {
                    if ($order == 'ASC')
                    {
                        $order_img = 'desc';
                    } else
                    {
                        $order_img = 'asc';
                    }
                    $img = $this->template ['images'] [$order_img];
                } else
                {
                    $img = "";
                }
                

                $noOrder = isset ( $this->info ['noOrder'] ) ? $this->info ['noOrder'] : '';
                

                if ($noOrder == 1)
                {
                    $return [$titles [$i]] = array ('type' => 'field', 'name' => $links [$i], 'field' => $links [$i], 'value' => $this->_titles [$links [$i]] );
                } else
                {
                    $return [$titles [$i]] = array ('type' => 'field', 'name' => $titles [$i], 'field' => $orderFinal, 'url' => "$url/order/{$orderFinal}_$order", 'img' => $img, 'value' => $this->_titles [$links [$i]] );
                }
            }
        }
        

        for($i = 0; $i < count ( $this->extra_fields ); $i ++)
        {
            if ($this->extra_fields [$i] ['position'] == 'right')
            {
                $return [$this->extra_fields [$i] ['name']] = array ('type' => 'extraField', 'value' => $this->extra_fields [$i] ['name'], 'position' => 'right' );
            }
        }
        
        $this->_finalFields = $return;
        

        return $return;
    }


    /**
     * Add the columns using an array
     */
    function addArrayColumns(array $columns)
    {

        $filter = array ();
        
        if ($this->_adapter != 'array')
            return false;
        
        foreach ( $columns as $value )
        {
            if (is_array ( $value ))
            {
                $this->addArrayColumns ( $value );
            } else
            {
                $this->addColumn ( $value );
                $filter [$value] = $value;
            }
        }
        
        $this->filters = $filter;
        
        return true;
    }


    /**
     * Add the records using an array
     */
    function addArrayData($data)
    {

        if ($this->_adapter != 'array')
            return false;
        

        $this->_result = $data;
        $this->_resultRaw = $data;
        return $this;
    }


    /**
     * Vamos remover os AS dos fields que estão a ser pesquisados para que possamos 
     * fazer a substituição no campo de procura
     *
     * @return unknown
     */
    function removeAsFromFields()
    {

        $fieldsSemAs = $this->data ['fields'];
        if (is_array ( $fieldsSemAs ))
        {
            foreach ( $fieldsSemAs as $key => $value )
            {
                if (strpos ( $key, ' ' ) === false)
                {
                    $fieldsSemAsFinal [$key] = $value;
                } else
                {
                    $fieldsSemAsFinal [substr ( $key, 0, strpos ( $key, ' ' ) )] = $value;
                }
            }
        }
        return $fieldsSemAsFinal;
    }


    /**
     * [PT]Vamos substituir os pontos dos campos 
     * [PT]para depois não dar erro no javascript
     * [PT]já que o ponto é OO
     *
     * @param unknown_type $string
     * @return unknown
     */
    function replaceDots($string)
    {

        return str_replace ( ".", "bvbdot", $string );
    }


    /**
     * Remover O As *.* das queries da bd
     *
     * @param unknown_type $string
     * @return unknown
     */
    function replaceAsString($string)
    {

        return stripos ( $string, ' AS ' ) ? substr ( $string, 0, stripos ( $string, ' AS ' ) ) : $string;
    }


    /**
     * Create the filters when using the array adapter
     */
    function builFilterFromArray($field)
    {

        $filter = array ();
        foreach ( $this->_resultRaw as $value )
        {
            $filter [$value [$field]] = $value [$field];
        }
        
        return array_unique ( $filter );
    }


    /**
     * [PT] Formatar o tipo de campo nos filtros
     * [PT] Ou do tipo select ou text
     * 
     * [EN] Field type on the filters area. If the field type is enum, build the options
     * [EN] Also, we first need to check if the user has defined values to presente.
     * [EN] If set, this values override the others
     *
     * @param string $campo
     * @param string $valor
     * @return string
     */
    function formatField($campo, $valor, $options = array())
    {

        if ($this->_adapter == 'db')
        {
            //[PT] Aqui vemos se no filtros nos pede os campos distinctos.
            if (@is_array ( $this->filters [$valor] ['distinct'] ))
            {
                $this->filters [$valor] ['distinct'] ['field'] = @$this->replaceAsString ( $this->filters [$valor] ['distinct'] ['field'] );
                $this->filters [$valor] ['distinct'] ['name'] = @$this->replaceAsString ( $this->filters [$valor] ['distinct'] ['name'] );
                $this->filters [$valor] ['values'] = $this->_db->fetchPairs ( "SELECT DISTINCT({$this->filters[$valor]['distinct']['field']}) , " . $this->filters [$valor] ['distinct'] ['name'] . "  FROM " . $this->data ['from'] . " " . $this->buildQueryWhere () . " ORDER BY {$this->filters[$valor]['distinct']['name']} ASC" );
            }
        
        }
        
        if ($this->_adapter == 'array' && @in_array ( 'distinct', $this->filters [$valor] ))
        {
            
            $this->filters [$valor] ['values'] = $this->builFilterFromArray ( $campo );
        }
        
        //[PT] Remover os paramteros que não queremos na url
        $url = urlencode ( $this->getUrl ( array ('filters', 'start', 'comm' ) ) );
        

        //Vamos remover os AS dos indices da data global por cauda de substituirmos o campo de procura
        $fieldsSemAsFinal = $this->removeAsFromFields ();
        if (isset ( $fieldsSemAsFinal [$campo] ['searchField'] ))
        {
            $nkey = $this->replaceAsString ( $fieldsSemAsFinal [$campo] ['searchField'] );
            @$this->_filtersValues [$campo] = $this->_filtersValues [$nkey];
        }
        
        if ($this->_adapter == 'db')
        {
            if (! is_array ( $this->data ['table'] ))
            {
                $table = $this->getDescribeTable ( $this->data ['table'] );
            } else
            {
                $ini = substr ( $campo, 0, (strpos ( $campo, "." )) );
                $table = $this->getDescribeTable ( $this->data ['table'] [$ini] );
            }
        }
        

        $campo_simples = substr ( $campo, strpos ( $campo, "." ) + 1 );
        @$tipo = $table [$campo_simples];
        $tipo = $tipo ['DATA_TYPE'];
        $help_javascript = '';
        

        if (substr ( $tipo, 0, 4 ) == 'enum')
        {
            $enum = str_replace ( array ('(', ')' ), array ('', '' ), $tipo );
            $tipo = 'enum';
        }
        

        foreach ( array_keys ( $this->filters ) as $value )
        {
            //[PT] Temos que ver se o campo não está oculto
            //[PT] Temos que ver se o campo não é a linha horizontal
            //[PT] Temos que saber se tem um content próprio
            $hRow = isset ( $this->data ['fields'] [$value] ['hRow'] ) ? $this->data ['fields'] [$value] ['hRow'] : '';
            if (! isset ( $this->data ['fields'] [$value] ['hide'] ) && $hRow != 1)
            {
                $help_javascript .= "filter_" . $value . ",";
            }
        }
        

        if (@$options ['noFilters'] != 1)
        {
            $help_javascript = str_replace ( ".", "bvbdot", $help_javascript );
            $onchange = "onchange=\"changeFilters('$help_javascript','$url');\"";
        }
        $opcoes = $this->filters [$campo];
        
        if (strlen ( @$opcoes ['style'] ) > 1)
        {
            $opt = " style=\"{$opcoes['style']}\"  ";
        } else
        {
            $opt = " style=\"width:95%\"  ";
        }
        

        if (@is_array ( $opcoes ['values'] ))
        {
            
            $tipo = 'invalid';
            $avalor = $opcoes ['values'];
            $valor = "<select name=\"$campo\" $opt $onchange id=\"filter_" . $this->replaceDots ( $campo ) . "\"  >";
            $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
            foreach ( $avalor as $key => $value )
            {
                $selected = $this->_filtersValues [$campo] == $key ? "selected" : "";
                $valor .= "<option value=\"" . stripslashes ( $key ) . "\" $selected >" . stripslashes ( $value ) . "</option>";
            }
            $valor .= "</select>";
        }
        

        switch ($tipo)
        {
            case 'invalid' :
                break;
            case 'enum' :
                $avalor = explode ( ",", substr ( $enum, 4 ) );
                $valor = "<select  id=\"filter_" . str_replace ( ".", "bvbdot", $campo ) . "\" $opt $onchange name=\"\">";
                $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
                foreach ( $avalor as $value )
                {
                    $value = substr ( $value, 1 );
                    $value = substr ( $value, 0, - 1 );
                    $selected = @$this->_filtersValues [$campo] == $value ? "selected" : "";
                    $valor .= "<option value=\"$value\" $selected >" . ucfirst ( $value ) . "</option>";
                }
                $valor .= "</select>";
                break;
            default :
                $valor = "<input type=\"text\" $onchange id=\"filter_" . @str_replace ( ".", "bvbdot", $campo ) . "\"   class=\"input_p\" value=\"" . @$this->_filtersValues [$campo] . "\" $opt>";
                break;
        }
        
        return $valor;
    }


    /**
     * Para subsituir no caso de termos JOINS
     *
     * @param unknown_type $campos
     * @return unknown
     */
    function replace_AS($campos)
    {

        return trim ( stripos ( $campos, ' AS ' ) ? substr ( $campos, 0, stripos ( $campos, ' AS ' ) ) : $campos );
    }


    /**
     * Escape the output
     */
    function escapeOutput($escape)
    {

        $this->escapeOutput = ( bool ) $escape;
        
        return $this;
    }


    /**
     * [PT] Contruir o loop para o centro da tabela
     * 
     * [EN] The loop for the results.
     * [EN] Check the extra-fields,
     *
     * @return string
     */
    function buildGrid()
    {

        $return = array ();
        /**
         * [PT] Para criamos as variaveis a substituir
         */
        $extra_fields = $this->extra_fields;
        

        $search = $this->map_array ( $this->_fields, 'prepare_replace' );
        

        foreach ( $this->_fields as $field )
        {
            $fields_duble [] = $field;
            if (strpos ( $field, "." ))
            {
                $fields [] = substr ( $field, strpos ( $field, "." ) + 1 );
            } else
            {
                $fields [] = $field;
            }
        }
        

        $i = 0;
        foreach ( $this->_result as $dados )
        {
            /**
             *Deal with extrafield from the left
             */
            if (is_array ( $extra_fields ))
            {
                foreach ( $extra_fields as $value )
                {
                    if ($value ['position'] == 'left')
                    {
                        $fi = get_object_vars ( $dados );
                        $new_value = str_replace ( $search, $fi, $value ['decorator'] );
                        if (isset ( $value ['eval'] ))
                        {
                            $evalf = str_replace ( $search, $fi, $value ['eval'] );
                            $new_value = eval ( 'return ' . $evalf );
                        }
                        $return [$i] [] = @array ('class' => $class . ' ' . $value ['class'], 'value' => $new_value );
                    }
                }
            }
            /**
             * Deal with the grid itself
             */
            $is = 0;
            $integralFields = array_keys ( $this->removeAsFromFields () );
            

            foreach ( $fields as $campos )
            {
                
                $campos = stripos ( $campos, ' AS ' ) ? substr ( $campos, stripos ( $campos, ' AS ' ) + 3 ) : $campos;
                $campos = trim ( $campos );
                
                if (isset ( $this->data ['fields'] [$fields_duble [$is]] ['eval'] ))
                {
                    $finalDados = is_object ( $dados ) ? get_object_vars ( $dados ) : $dados;
                    

                    $evalf = str_replace ( $search, $this->reset_keys ( $this->map_array ( $finalDados, 'prepare_output' ) ), $this->data ['fields'] [$fields_duble [$is]] ['eval'] );
                    $new_value = eval ( 'return ' . $evalf . ';' );
                
                } else
                {
                    if ($this->_adapter == 'db')
                    {
                        $final = $this->object2array ( $dados );
                    } else
                    {
                        $final = $dados;
                    }
                    
                    if (! is_array ( $final [$campos] ))
                    {
                        $new_value = $final [$campos];
                    }
                }
                

                if ($this->escapeOutput === true)
                {
                    $new_value = htmlspecialchars ( $new_value );
                }
                
                //[PT]Aplicar o formato da célula
                if (isset ( $this->data ['fields'] [$fields_duble [$is]] ['format'] ))
                {
                    
                    $new_value = $this->applyFormat ( $new_value, $this->data ['fields'] [$fields_duble [$is]] ['format'], $this->data ['fields'] [$fields_duble [$is]] ['format'] [1] );
                }
                

                if (isset ( $this->data ['fields'] [$fields_duble [$is]] ['decorator'] ))
                {
                    $finalDados = is_object ( $dados ) ? get_object_vars ( $dados ) : $dados;
                    $new_value = str_replace ( $search, $this->reset_keys ( $this->map_array ( $finalDados, 'prepare_output' ) ), $this->data ['fields'] [$fields_duble [$is]] ['decorator'] );
                }
                

                if (! isset ( $this->data ['fields'] [$fields_duble [$is]] ['hide'] ))
                {
                    $fieldClass = isset ( $this->data ['fields'] [$fields_duble [$is]] ['class'] ) ? $this->data ['fields'] [$fields_duble [$is]] ['class'] : '';
                    $class = isset ( $class ) ? $class : '';
                    $return [$i] [] = @array ('class' => $class . " " . $fieldClass, 'value' => stripslashes ( $new_value ), 'field' => $integralFields [$is] );
                }
                

                $is ++;
            

            }
            /**
             * Deal with extra fields from the right
             */
            if (is_array ( $extra_fields ))
            {
                foreach ( $extra_fields as $value )
                {
                    if ($value ['position'] == 'right')
                    {
                        $fi = get_object_vars ( $dados );
                        $new_value = str_replace ( $search, $fi, $value ['decorator'] );
                        if (isset ( $value ['eval'] ))
                        {
                            $evalf = str_replace ( $search, $fi, $value ['eval'] );
                            $new_value = eval ( 'return ' . trim ( $evalf, ';' ) );
                        }
                        $finalClass = isset ( $value ['class'] ) ? $value ['class'] : '';
                        $class = isset ( $class ) ? $class : '';
                        $return [$i] [] = array ('class' => $class . ' ' . $finalClass, 'value' => $new_value );
                    }
                }
            }
            $i ++;
        }
        return $return;
    }


    /**
     * Para voltar a colocar a numeração em 0
     * e, todas as keys de array
     *
     * @param unknown_type $array
     * @return unknown
     */
    function reset_keys($array)
    {

        
        if (! is_array ( $array ))
            return FALSE;
        

        $novo_array = array ();
        $i = 0;
        foreach ( $array as $value )
        {
            $novo_array [$i] = $value;
            $i ++;
        }
        return $novo_array;
    }


    /**
     * Apply SQL Functions
     *
     */
    function buildSqlExp()
    {

        if ($this->_adapter == 'array')
        {
            return FALSE;
        }
        

        $exp = isset ( $this->info ['sqlexp'] ) ? $this->info ['sqlexp'] : '';
        

        if (! is_array ( $exp ))
        {
            return false;
        }
        $final = $exp;
        foreach ( $final as $key => $value )
        {
            $result [$key] = $this->_db->fetchOne ( "SELECT $value($key) AS TOTAL FROM " . $this->data ['from'] . "  " . $this->buildQueryWhere () );
        }
        if (is_array ( $result ))
        {
            $return = array ();
            foreach ( $this->_finalFields as $key => $value )
            {
                if (array_key_exists ( $key, $result ))
                {
                    $class = isset ( $this->template ['classes'] ['sqlexp'] ) ? $this->template ['classes'] ['sqlexp'] : '';
                    $return [] = array ('class' => $class, 'value' => round ( $result [$key], 1 ), 'field' => $key );
                } else
                {
                    $class = isset ( $this->template ['classes'] ['sqlexp'] ) ? $this->template ['classes'] ['sqlexp'] : '';
                    $return [] = array ('class' => $class, 'value' => '', 'field' => $key );
                }
            }
        }
        return $return;
    }


    /**
     * [PT]COnfirmar que os campos existem mesmo na tabela, se não existir removemos
     * [EN] Make sure the fields exists on the database, if not remove them from the array
     *
     * @param array $fields
     */
    function validateFields($fields)
    {

        if (is_array ( $fields ))
        {
            $hide = 0;
            $fields_final = array ();
            $i = 0;
            

            foreach ( $fields as $key => $value )
            {
                

                //A parte da order
                if (isset ( $value ['orderField'] ))
                {
                    $orderFields [$key] = $value ['orderField'];
                } else
                {
                    $orderFields [$key] = $key;
                }
                
                if (isset ( $value ['title'] ))
                {
                    $titulos [$key] = $value ['title'];
                } else
                {
                    $titulos [$key] = ucfirst ( $key );
                }
                

                if (isset ( $value ['order'] ))
                {
                    if (@$value ['order'] > - 1)
                    {
                        $fields_final [( int ) $value ['order']] = $key;
                    }
                } else
                {
                    $fields_final [$i] = $key;
                }
                

                if (isset ( $value ['hide'] ))
                {
                    if ($value ['hide'] == 1)
                    {
                        $hide ++;
                    }
                }
                $i ++;
            }
            

            ksort ( $fields_final );
            $fields_final = $this->reset_keys ( $fields_final );
        

        } else
        {
            //Não forneceu dados, temos que ir buscá-los todos às tabelas
            if (is_array ( $this->data ['table'] ))
            {
                foreach ( $this->data ['table'] as $key => $value )
                {
                    $tab = $this->getDescribeTable ( $value );
                    foreach ( $tab as $list )
                    {
                        $fl [] = $key . "." . $list ['COLUMN_NAME'];
                        $titulos [$key . "." . $list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                    }
                }
            } else
            {
                $tab = $this->getDescribeTable ( $this->data ['table'] );
                foreach ( $tab as $list )
                {
                    $fl [] = $list ['COLUMN_NAME'];
                    $titulos [$list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                }
            }
            

            $fields_final = $fl;
            $orderFields = $fl;
        }
        

        //Vamos remover os campos que não quermeos mostrar
        $naoMostrar = array_flip ( $fields_final );
        

        foreach ( $naoMostrar as $key => $field )
        {
            if (@in_array ( $key, $this->data ['hide'] ))
            {
                unset ( $naoMostrar [$key] );
                unset ( $orderFields [$key] );
                unset ( $titulos [$key] );
                unset ( $this->data ['fields'] [$key] );
            
            }
        }
        
        $fields_final = array_values ( array_flip ( $naoMostrar ) );
        

        $this->totalHiddenFields = $hide;
        $this->_fields = $fields_final;
        $this->_titles = $titulos;
        $this->_fieldsOrder = $orderFields;
    }


    /**
     * [PT] Verificar que os campos especificados no array existem mesmo
     * [PT] Se não existirem removemos
     * [PT] Se no final tivermos uma array vazia, criamos uma nova com todos os campos
     * [PT] criados com o $this->_fields
     *
     * [En] Make sure the filters exists, they are the name from the table field.
     * [EN] If not, remove them from the array
     * [EN] If we get an empty array, we then creat a new one with all the fields specifieds
     * [EN] in $this->_fields method
     *
     * @param string $filters
     */
    function validateFilters($filters)
    {

        if (@$this->info ['noFilters'])
        {
            return false;
        }
        
        $titulos = null;
        
        if (is_array ( $filters ))
        {
            return $filters;
        } elseif ($this->_adapter == 'db')
        {
            //Não forneceu dados, temos que ir buscá-los todos às tabelas
            if (is_array ( $this->data ['table'] ))
            {
                foreach ( $this->data ['table'] as $key => $value )
                {
                    $tab = $this->getDescribeTable ( $value );
                    foreach ( $tab as $list )
                    {
                        $titulos [$key . "." . $list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                    }
                }
            } else
            {
                $tab = $this->getDescribeTable ( $this->data ['table'] );
                foreach ( $tab as $list )
                {
                    $titulos [$list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                }
            }
        }
        

        if (@is_array ( $this->data ['hide'] ))
        {
            
            foreach ( $this->data ['hide'] as $value )
            {
                if (array_key_exists ( $value, $titulos ))
                {
                    unset ( $titulos [$value] );
                }
            }
        } else
        {
            if (@is_array ( $titulos ))
            {
                foreach ( $titulos as $key => $value )
                {
                    if (! in_array ( $key, $this->map_array ( $this->_fields, 'replace_AS' ) ))
                    {
                        unset ( $titulos [$key] );
                    }
                }
            }
        }
        

        return $titulos;
    }


    /**
     * [PT] Ir buscar a chave primaria de uma tabela
     * [PT] Isto é importante porque apenas deixamos editar e adicionar ou remover
     * [PT] dados de tabelas que contenham chaves primarias
     * 
     * [EN] Get the primary table key
     * [EN] This is important because we only allow edit, add or remove records
     * [EN] From tables that have on primary key
     *
     * @return string
     */
    function getPrimaryKey()
    {

        if (is_array ( $this->data ['table'] ))
        {
            return false;
        }
        if (isset ( $this->_getPrimaryKey [$this->data ['table']] ))
        {
            return $this->_getPrimaryKey [$this->data ['table']];
        }
        if (isset ( $this->data ['primaryKey'] ))
        {
            $this->_getPrimaryKey [$this->data ['table']] = $this->data ['primaryKey'];
            return $this->_getPrimaryKey [$this->data ['table']];
        }
        $param = $this->getDescribeTable ( $this->data ['table'] );
        foreach ( $param as $value )
        {
            if ($value ['PRIMARY'] == 1)
            {
                $primary_key [] = $value ['PRIMARY'];
                $field = $value ['COLUMN_NAME'];
            }
        }
        
        if (@count ( $primary_key ) != 1)
        {
            return false;
            #throw new Exception('Incaple to get the table primary key. The system can only perform adicional actions on tables with ONE primary key');
        }
        
        $this->_getPrimaryKey [$this->data ['table']] = $field;
        
        return $this->_getPrimaryKey [$this->data ['table']];
    }


    /**
     * [PT]Servepara confirmar que todos os campos foram adicionados
     * [PT] porque existem certaz situações em que o sistem os aidicona automaticamente
     * [PT] como é no caso dos filtros e do searchField nas opções ao adicionar um campo
     *
     * @return true
     */
    function consolidateQuery()
    {

        $this->consolidated = 1;
        $cFields = @$this->data ['fields'];
        
        if (! is_array ( $cFields ))
        {
            
            if (is_array ( $this->data ['table'] ))
            {
                
                foreach ( $this->data ['table'] as $key => $table )
                {
                    
                    $tableFinal = array_keys ( $this->getDescribeTable ( $table ) );
                    
                    foreach ( $tableFinal as $field )
                    {
                        $this->addColumn ( $key . '.' . $field );
                    }
                }
            

            } else
            {
                
                $table = array_keys ( $this->getDescribeTable ( $this->data ['table'] ) );
            }
            
            foreach ( $table as $field )
            {
                $this->addColumn ( $field );
            }
        }
        

        if (! @is_array ( $this->filters ) && @is_array ( $this->data ['filters'] ))
        {
            $this->filters = $this->data ['filters'];
        }
        //[PT]Aqui vamos veriifcar se o campo tem a opção de pesquisa num campo da base de dados
        //[PT]diferente do que é mostrado. Se assim for adicionamos nos o campo
        //[PT]de forma automática e, naturalmente, ocultá-mo-lo
        if (is_array ( $cFields ))
        {
            foreach ( $cFields as $value )
            {
                if (@$value ['searchField'] != "")
                {
                    if (! in_array ( $value ['searchField'], $this->data ['fields'] ))
                    {
                        $this->addColumn ( $value ['searchField'], array ('title' => 'Barcelos', 'hide' => 1 ) );
                    }
                }
            }
        }
        //[PT]Esta parte de cerificação de campos é dos filtros. Se for distinct e os campos
        //[PT]Definidos ainda não estiverem lá, adiciona-mos nós e de forma oculta
        if (is_array ( $this->filters ))
        {
            foreach ( $this->filters as $value )
            {
                
                if (is_array ( $value ) && isset ( $value ['distinct'] ['field'] ) && isset ( $value ['distinct'] ['name'] ))
                {
                    
                    if (! array_key_exists ( $value ['distinct'] ['field'], $this->data ['fields'] ))
                    {
                        $this->addColumn ( $value ['distinct'] ['field'] . ' AS f' . md5 ( $value ['distinct'] ['name'] ), array ('title' => 'Barcelos', 'hide' => 1 ) );
                    }
                    
                    if (! array_key_exists ( $value ['distinct'] ['name'], $this->data ['fields'] ))
                    {
                        $this->addColumn ( $value ['distinct'] ['name'] . ' AS f' . md5 ( $value ['distinct'] ['name'] ), array ('title' => 'Barcelos', 'hide' => 1 ) );
                    }
                    $this->data ['fields'] [$value ['distinct'] ['name']] ['searchField'] = $value ['distinct'] ['field'];
                }
            
            }
        }
        //[PT] Os campos extra, que não estão na base de dados. São sobretudo uteis para criar links
        //[EN] The extra fields, they are not part of database table.
        //[EN] Usefull for adding links (a least for me :D )
        if (@is_array ( $this->info ['extra_fields'] ))
        {
            if (! is_array ( $this->extra_fields ))
            {
                $this->extra_fields = $this->info ['extra_fields'];
            } else
            {
                $this->extra_fields = array_merge ( $this->extra_fields, $this->info ['extra_fields'] );
            }
        }
        //[PT] Temos que validar os campos da tabela.
        //[EN] Validate table fields, make sure they exist...
        $this->validateFields ( $this->data ['fields'] );
        

        //[PT] Os filtros, não é obrigatório filtrar todos os resultados
        //[PT] Temos também que os comparar com os campos da base de dados
        //[EN] Filters. Not required that every field as filter.
        //[EN] Make sute they exists on the table
        $this->filters = self::validateFilters ( $this->filters );
        

        //[PT]O colspan a ser aplicado em tabelas
        $this->_colspan = $this->colspan ();
        return true;
    }


    /**
     * [PT]Obter a query que vai ser executada.
     * [PT]pode dar jeito em certas situações.
     *
     * @return unknown
     */
    function getQuery()
    {

        if ($this->consolidated == 0)
        {
            $this->consolidateQuery ();
        }
        //[PT] O where que é sempre aplicado
        //[EN] Get the WHERE condition and apply from now on...
        $this->_where = @$this->data ['where'];
        $select_fields = $this->buildSelectFields ( $this->_fields );
        $query_where = $this->buildQueryWhere ();
        

        if (! is_array ( $this->data ['table'] ))
        {
            $from = $this->_db->quoteIdentifier ( $this->data ['from'] );
        } else
        {
            $from = $this->data ['from'];
        }
        
        $query = "SELECT $select_fields FROM " . $from . " $query_where " . $this->buildQuery ();
        return $query;
    }


    /**
     * [PT] Criar a query para contar o total de registos que serão devolvidos sem aplicar os limites
     *
     * @return unknown
     */
    function getQueryCount()
    {

        if ($this->consolidated == 0)
        {
            $this->consolidateQuery ();
        }
        //[PT] O where que é sempre aplicado
        //[EN] Get the WHERE condition and apply from now on...
        $this->_where = @$this->data ['where'];
        $query_where = $this->buildQueryWhere ();
        
        if (! is_array ( $this->data ['table'] ))
        {
            $from = $this->_db->quoteIdentifier ( $this->data ['from'] );
        } else
        {
            $from = $this->data ['from'];
        }
        

        foreach ( $this->data ['fields'] as $total )
        {
            
            if (isset ( $total ['sqlexp'] ))
            {
                $this->hasGroup = 1;
            }
        
        }
        
        $queryGroup = '';
        
        if (@strlen ( $this->info ['groupby'] ) > 0)
        {
            $queryGroup = " GROUP BY " . $this->info ['groupby'];
        
        }
        
        $query_count = "SELECT COUNT(*) AS TOTAL FROM " . $from . " $query_where " . $queryGroup;
        

        return $query_count;
    }


    /**
     * [PT]Executar a query
     *
     * @return unknown
     */
    function getQueryResult()
    {

        $this->_result = $this->_db->fetchAll ( $this->getQuery () );
        return $this->_result;
    }


    /**
     * [PT] Obter o total de resultados 
     *
     * @return results number
     */
    function getQueryCountResult()
    {

        $this->_totalRecords = $this->_db->fetchOne ( $this->getQueryCount () );
        return $this->_totalRecords;
    }


    /**
     * [PT] Fazer o return da grid. Está um bocado sem sentido, mas é o que se arranja por enquanto
     * 
     * [EN] Done. Send the gri to the user
     *
     * @return string
     */
    function deploy()
    {

        
        if ($this->consolidated == 0)
        {
            $this->consolidateQuery ();
        }
        
        if ($this->_adapter == 'db')
        {
            $query = $this->getQuery ();
            $query_count = $this->getQueryCount ();
            #$result = $this->_db->fetchAll ( $query ); 
            


            if ($this->cache ['use'] == 1)
            {
                
                $cache = $this->cache ['instance'];
                

                if (! $result = $cache->load ( md5 ( $query ) ))
                {
                    $result = $this->_db->fetchAll ( $query );
                    $resultCount = $this->_db->fetchOne ( $query_count );
                    $cache->save ( $result, md5 ( $query ), array ($this->cache ['tag'] ) );
                    $cache->save ( $resultCount, md5 ( $query_count ), array ($this->cache ['tag'] ) );
                
                } else
                {
                    $result = $cache->load ( md5 ( $query ) );
                    $resultCount = $cache->load ( md5 ( $query_count ) );
                }
            

            } else
            {
                
                $result = $this->_db->fetchAll ( $query );
                
                if ($this->sourceIsExternal != 1)
                {
                    $resultCount = $this->_db->fetchOne ( $query_count );
                } else
                {
                    $resultCount = count ( $this->_db->fetchAll ( $query ) );
                }
            }
            

            //[PT] O total de registos encontrados na query sem aplicar os limites
            $this->_totalRecords = $resultCount;
            

            //[PT]Os registos dentro dos limites
            $this->_result = $result;
        

        } else
        {
            
            $filters = Zend_Json::decode ( @$this->ctrlParams ['filters'] );
            if (is_array ( $filters ))
            {
                
                foreach ( $filters as $key => $filter )
                {
                    $key = end ( explode ( '_', $key ) );
                    $filterValue [$key] = $filter;
                }
                
                $filters = $filterValue;
                

                $find = $this->findInArray ( $filters );
                
                $this->_filtersValues = $filterValue;
                
                if (count ( $find ) > 0)
                {
                    $this->_result = $find;
                
                } elseif ($this->_searchPerformedInArray === true)
                {
                    $this->_result = array ();
                }
            

            }
            

            if (isset ( $this->ctrlParams ['order'] ) || strlen ( @$this->data ['order'] ) > 3)
            {
                
                if (strlen ( $this->data ['order'] ) > 3 && ! isset ( $this->ctrlParams ['order'] ))
                {
                    
                    $order = reset ( explode ( ' ', $this->data ['order'] ) );
                    $orderType = end ( explode ( ' ', $this->data ['order'] ) );
                    if (strtoupper ( $orderType ) != 'ASC' && strtoupper ( $orderType ) != 'DESC')
                    {
                        $orderType = 'ASC';
                    }
                    

                    $orderType = strtoupper ( $orderType ) == 'ASC' ? SORT_ASC : SORT_DESC;
                
                } else
                {
                    $order = reset ( explode ( '_', $this->ctrlParams ['order'] ) );
                    $orderType = end ( explode ( '_', $this->ctrlParams ['order'] ) );
                    
                    $orderType = strtoupper ( $orderType ) == 'ASC' ? SORT_ASC : SORT_DESC;
                }
                

                // Obtain a list of columns
                foreach ( $this->_result as $key => $row )
                {
                    $result [$key] = $row [$order];
                }
                

                @array_multisort ( $result, $orderType, $this->_result );
            
            }
            

            if (@strlen ( $this->info ['limit'] ) > 0 || @is_array ( $this->info ['limit'] ))
            {
                if (is_array ( $this->info ['limit'] ))
                {
                    $this->_totalRecords = $this->info ['limit'] [1];
                    $result = array_slice ( $this->_result, $this->info ['limit'] [0], $this->info ['limit'] [1] );
                } else
                {
                    $this->_totalRecords = $this->info ['limit'];
                    $result = array_slice ( $this->_result, 0, $this->info ['limit'] );
                }
            
            } elseif($this->pagination==0)
            {
                $this->_totalRecords = count ( $this->_result );
                $result = $this->_result;
            
            }else
            {
                $this->_totalRecords = count ( $this->_result );
                $result = array_slice ( $this->_result, ( int ) @$this->ctrlParams ['start'] < $this->_totalRecords ? ( int ) @$this->ctrlParams ['start'] : 0, 15 );
            
            }
            

            $this->_result = $result;
        }
        

        //[PT]Alguma coisa correu mal. Não adicionaram opção
        if (! is_array ( $this->data ))
        {
            throw new Exception ( 'Database options not found. ' );
        }
    }


    /**
     * Search function for array adapters
     */
    function findInArray($filters)
    {

        $filtersNumber = 0;
        foreach ( $filters as $value )
        {
            if (strlen ( $value ) > 0)
            {
                $filtersNumber ++;
            }
        }
        
        $this->_searchPerformedInArray = true;
        
        $find = array ();
        
        foreach ( $this->_result as $result )
        {
            
            $i = 0;
            
            foreach ( $filters as $filterKey => $filterValue )
            {
                foreach ( $result as $fieldKey => $fieldValue )
                {
                    if (strlen ( $filterValue ) > 0 && $fieldKey == $filterKey)
                    {
                        
                        if ($this->applySearchTypeToArray ( $fieldValue, $filterValue, $filterKey ))
                        {
                            $i ++;
                        }
                    }
                }
            }
            

            if ($i == $filtersNumber)
            {
                $find [] = $result;
            }
        
        }
        

        return $find;
    
    }


    /**
     * Apply the search to a give field when the adaptar is an array
     */
    function applySearchTypeToArray($final, $search, $key)
    {

        $enc = stripos ( $final, $search );
        
        if (@$this->data ['fields'] [$key] ['searchType'] != "")
        {
            $filtro = $this->data ['fields'] [$key] ['searchType'];
        }
        $filtro = @strtolower ( $filtro );
        

        switch ($filtro)
        {
            case 'equal' :
            case '=' :
                if ($search == $final)
                    return true;
                break;
            case 'rlike' :
                if (substr ( $final, 0, strlen ( $search ) ) == $search)
                    return true;
                break;
            case 'llike' :
                if (substr ( $final, - strlen ( $search ) ) == $search)
                    return true;
                break;
            case '>=' :
                if ($final >= $search)
                    return true;
                break;
            case '>' :
                if ($final > $search)
                    return true;
                break;
            case '<>' :
            case '!=' :
                if ($final != $search)
                    return true;
                break;
            case '<=' :
                if ($final <= $search)
                    return true;
                break;
            case '<' :
                if ($final < $search)
                    return true;
                break;
            default :
                $enc = stripos ( $final, $search );
                if ($enc !== false)
                {
                    return true;
                }
                break;
        }
        
        return false;
    
    }


    /**
     * Remove a column
     */
    function removeColumn($column)
    {

        unset ( $this->data ['fields'] [$column] );
        return $this;
    }


    /**
     * Remove an array of columns
     */
    function removeColumns(array $columns)
    {

        foreach ( $columns as $column )
        {
            $this->removeColumn ( $column );
        }
        
        return $this;
    }


    /**
     * Reset a column to clear all customizations 
     */
    function resetColumn($column)
    {

        $this->removeColumn ( $column );
        $this->addColumn ( $column );
    }


    /**
     * [PT]Converter um object para um array.
     * [PT] è necessário quando quisermos faszer o load 
     * [PT]através de um ficheiro XML
     *
     * @param object $object
     * @return array
     */
    function object2array($object)
    {

        $return = NULL;
        if (is_array ( $object ))
        {
            foreach ( $object as $key => $value )
                $return [$key] = self::object2array ( $value );
        } else
        {
            $var = get_object_vars ( $object );
            if ($var)
            {
                foreach ( $var as $key => $value )
                    $return [$key] = self::object2array ( $value );
            } else
            {
                return strval ( $object );
            }
        }
        return $return;
    }


    /**
     * Definir a localização de templates. É muito provável e aconselhável que 
     * armazene os templates personalizados fora desta pasta
     *
     * @param string $path
     * @param string $prefix
     * @return unknown
     */
    function addTemplateDir($dir, $prefix, $type)
    {

        $this->_templates->addPrefixPath ( trim ( $prefix, "_" ), trim ( $dir, "/" ) . '/', $type );
        return $this;
    }


    /**
     * [PT]Definir o template para a grid
     * [PT] por defeito ele tenta bvb/grid/template/table/table
     *
     * @param string $template
     * @return unknown
     */
    function setTemplate($template, $output = 'table', $options = array())
    {

        $class = $this->_templates->load ( $template, $output );
        
        $this->temp [$output] = new $class ( $options );
        $this->activeTemplates [] = $output;
        
        $this->temp [$output]->templateInfo = array ('name' => $template, 'dir' => $this->_templates->getClassPath ( $template, $output ), 'class' => $this->_templates->getClassName ( $template, $output ), 'options' => $options );
        
        return $this->temp [$output];
    
    }


    /**
     * [PT]Método alternativo para adicionar campos
     *
     */
    function addColumns()
    {

        $fields = func_get_args ();
        foreach ( $fields as $value )
        {
            if ($value instanceof Bvb_Grid_Column)
            {
                $value = $this->object2array ( $value );
                foreach ( $value as $field )
                {
                    $finalField = $field ['field'];
                    unset ( $field ['field'] );
                    $this->addColumn ( $finalField, $field );
                }
            }
        }
    }


    /**
     * [PT]Método alternativo para adicionar filtros
     *
     */
    function addFilters($filters)
    {

        $filters = $this->object2array ( $filters );
        $this->filters = $filters ['_filters'];
    }


    /**
     * [PT]Método alternativo para adicionar novas colunas
     *
     * @return unknown
     */
    function addExtraColumns()
    {

        $extra_fields = func_get_args ();
        $final = array ();
        foreach ( $extra_fields as $value )
        {
            if ($value instanceof Bvb_Grid_ExtraColumns)
            {
                $value = $this->object2array ( $value );
                array_push ( $final, $value ['_field'] );
            }
        }
        $this->extra_fields = $final;
        return $this;
    }


    /**
     * Create the grid using a Zend_Db_Select Object
     */
    function queryFromZendDbSelect(Zend_Db_Select $select, $db)
    {

        $this->sourceIsExternal = 1;
        
        unset ( $this->data );
        
        $final = new Bvb_Grid_Source_Db_Select ( $select, $db );
        

        $final = $this->object2array ( $final );
        
        $column = $final ['data'] ['columns'];
        $orderField = $final ['data'] ['orderField'];
        

        for($i = 0; $i < count ( $column ); $i ++)
        
        {
            $title = ucwords ( str_replace ( "_", ' ', end ( explode ( '.', $column [$i] ) ) ) );
            
            if (stripos ( $title, 'as' ))
            {
                $title = end ( explode ( ' ', $title ) );
            }
            
            #$column = reset ( explode ( ' ', $column ) );
            


            $this->addColumn ( $column [$i], array ('title' => $title, 'orderField' => $orderField [$i] ) );
        }
        

        if (strlen ( $final ['data'] ['from'] ) > 0)
        {
            $this->from = $final ['data'] ['from'];
        }
        

        $table = array ();
        $prefix = array ();
        
        foreach ( $final ['data'] ['table'] as $value )
        {
            array_push ( $table, $value ['table'] );
            array_push ( $prefix, $value ['prefix'] );
        }
        
        $this->table = array_combine ( $prefix, $table );
        

        if (strlen ( $final ['data'] ['groupBy'] ) > 0)
        {
            $this->groupby = $final ['data'] ['groupBy'];
        }
        
        if (strlen ( $final ['data'] ['having'] ) > 0)
        {
            $this->having = $final ['data'] ['having'];
        }
        
        if (strlen ( $final ['data'] ['where'] ) > 0)
        {
            $this->where = $final ['data'] ['where'];
        }
        
        if (strlen ( $final ['data'] ['order'] ) > 0)
        {
            $this->order = $final ['data'] ['order'];
        }
        
        $this->noMoreDataActions = 1;
        
        return true;
    }


}

