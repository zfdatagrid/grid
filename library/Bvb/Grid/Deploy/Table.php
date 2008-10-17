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
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */

class Bvb_Grid_Deploy_Table extends Bvb_Grid_DataGrid
{

    protected $messageOk;

    /**
     * [PT] Se o formulário foi submetido com sucesso
     *
     * @var bool
     */
    protected $formSuccess = 0;

    /**
     * [PT] Se o formulário foi submetido 
     *
     * @var bool
     */
    protected $formPost = 0;

    /**
     * [PT] Guarda os valores dos forms se der erro
     */
    protected $_formValues = array ();

    /**
     * [PT] AS mensagens de erro no form
     *
     * @var unknown_type
     */
    protected $_formMessages = array ();

    protected $output = 'table';

    /**
     * [PT] Permissão para adicionar
     * [EN] Permission to add records
     *
     * @var array
     */
    private $allowAdd = null;

    /**
     * [PT] Permissão para editar registos
     * [EN] Permission to edit records
     *
     * @var array
     */
    private $allowEdit = null;

    /**
     * [PT] Permissão para remove registos
     * [EN] Permission to delete records
     *
     * @var array
     */
    private $allowDelete = null;

    /**
     * [PT] String com a mensagem depois de submetido o formulario
     * [EN] Message after form submission
     *
     * @var string
     */
    public $message;

    /**
     * [PT] Dados do template
     * [EN] Template data
     *
     * @var array
     */
    public $template;

    protected $_editNoForm;

    /**
     * [PT] A url das imagens para exportação
     *
     * @var string
     */
    public $imagesUrl;

    /**
     * [PT] Se quando é permitido adicionar registo, mostramos as duas tabelas
     * [PT] uma com os registos e a outra com os formulários
     *
     * @var bool
     */
    protected $double_tables = 0;

    /**
     * [PT] Set se falhar a validação do formulario
     * [EN] Set if form vaidation failed
     *
     * @var bool
     */
    protected $_failedValidation;

    /**
     * [PT] Utilizado para receber os dados de remoção de registos
     * [EN] Url param with the information about removing records
     *
     * @var string
     */
    protected $_comm;

    /**
     * [PT] o template
     *
     * @var object
     */
    public $temp;


    /**
     * [PT] A função __construct recebe o adapter para se liga à base de dados
     * [PT] É também tratada toda a informação relacionada com a url e os params
     * [PT] É também instaciada o Auth do Zend_Auth. A autenticação deve ter sido
     * [PT] Efectuada utilizando o método store, para agora poder validar o user
     * 
     * [EN] The __construct function receives the db adapter. All information related to the
     * [EN] URL is also processed here
     * [EN] To edit, add, or delete records, a user must be authenticated, so we instanciate 
     * [EN] it here. Remember to use the method write when autenticating a user, so we can know 
     * [EN] if its logged or not
     *
     * @param array $data
     */
    function __construct($db)
    {

        parent::__construct ( $db );
        
        $this->setTemplate ( 'table', 'table' );
        #$this->temp['table'] = new Bvb_Grid_Template_Table_Table();
    


    }


    /**
     * [Para podemros utiliza]
     *
     * @param string $var
     * @param string $value
     */
    
    function __set($var, $value)
    {

        parent::__set ( $var, $value );
    }


    /**
     * 
     * [PT] Aqui é processada toda a informação relacionada com os formulários
     * [PT] Verificamos se o utilizador pode ou não adicionar dados e
     * [PT] Depois verificamos o o request->method para saber se é post. Se for processamos
     * 
     * [EN] Process all infmation forms related
     * [EN] First we check for pemrissions to add, edit, delete
     * [EN] And then the request->isPost. If true we process the data
     *
     */
    
    protected function processForm()
    {

        $pk = parent::getPrimaryKey ();
        
        if (@$this->info ['add'] ['allow'] == 1 && ! is_array ( $this->data ['table'] ) && $pk)
        {
            $this->allowAdd = 1;
        }
        
        if (@$this->info ['delete'] ['allow'] == 1 && ! is_array ( $this->data ['table'] ) && $pk)
        {
            $this->allowDelete = 1;
        }
        
        if (@$this->info ['edit'] ['allow'] == 1 && ! is_array ( $this->data ['table'] ) && $pk)
        {
            $this->allowEdit = 1;
        }
        
        //[PT] Se podermos remover ou editar, temos que inicializar a classe de encriptação
        //[PT] Para remover enviamos os parametros pela URL encriptados. Para editar enviamos uma flag
        //[EN] IF a user can edit or delete data we must instanciate the crypt classe.
        //[EN] This is an extra-security step.
        if ($this->allowEdit == 1 || $this->allowDelete)
        {
            $dec = isset ( $this->ctrlParams ['comm'] ) ? $this->ctrlParams ['comm'] : '';
            $this->_comm = $dec;
        }
        
        /**
         * [PT] Remover se houver alguma coisa a remover
         * [EN] emove if there is something to remove
         */
        if ($this->allowDelete)
        {
            self::deleteRecord ( $dec );
        
        }
        
        if (Zend_Controller_Front::getInstance ()->getRequest ()->isPost ())
        {
            
            $param = Zend_Controller_Front::getInstance ()->getRequest ();
            
            $opComm = isset ( $this->ctrlParams ['comm'] ) ? $this->ctrlParams ['comm'] : '';
            $op_query = self::convertComm ( $opComm );
            
            $get_mode = isset ( $op_query ['mode'] ) ? $op_query ['mode'] : '';
            $mode = $get_mode == 'edit' ? 'edit' : 'add';
            
            //[PT] Temos que saber quais os campos que vamos buscar ao Post. Só vamos buscar os nomes
            //[PT] Dos campos da tabela da base de dados.
            //[PT] Temos por isso que verificar se o utilizador definiu campos a serem processados
            //[PT] Ou se são para processar todos
            


            //[EN] We mst know what fields to get with getPost(). We only gonna get the fieds
            //[EN] That belong to the database table. We must ensure we process the right data.
            //[EN] So we also must verify if have been defined the fields to process
            if (is_array ( $this->info [$mode] ['fields'] ))
            {
                $fields = array ();
                
                foreach ( $this->info [$mode] ['fields'] as $key => $value )
                {
                    $fields [$key] = $key;
                }
            
            } else
            {
                $fields = parent::getFields ( $mode, $this->data ['table'] );
            }
            
            //[PT] APlicar os filtros e a validalção. Primeiro são aplicados os filtros
            //[EN] Apply filter and validators. Firtst we apply the filters
            foreach ( $fields as $value )
            {
                
                $this->_formValues [$value] = $param->getPost ( $value );
                
                $result = self::applyFilters ( $param->getPost ( $value ), $value, $mode );
                
                $result = self::Validate ( $result, $value, $mode );
                
                $final [$value] = $result;
            
            }
            
            //[PT] Se passou na validação
            //[EN] If pass validation
            if ($this->_failedValidation !== true)
            {
                
                //[PT] vamos saber se existem campos com entrada forçada.
                //[EN] Check ig the user has defined "force" fields. If so we need to merge them
                //[EN] With the ones we get from the form process
                $force = $this->info [$mode] ['force'];
                if (is_array ( $force ))
                {
                    $final_values = array_merge ( $final, $force );
                
                } else
                {
                    $final_values = $final;
                }
                
                unset ( $final_values [parent::getPrimaryKey ()] );
                
                //[PT] Processar dados
                //[EN] Process data
                if ($mode == 'add' && is_array ( $final_values ))
                {
                    $this->_db->insert ( $this->data ['table'], $final_values );
                    $this->message = $this->__ ( 'Record saved' );
                    $this->messageOk = true;
                }
                
                //[PT] Processar dados
                //[EN] Process data
                if ($mode == 'edit' && is_array ( $final_values ))
                {
                    if (strlen ( $this->info ['edit'] ['where'] ) > 1)
                    {
                        $where = " AND " . $this->info ['edit'] ['where'];
                    }
                    
                    $this->_db->update ( $this->data ['table'], $final_values, " $pk=" . $this->_db->quote ( $op_query ['id'] ) . " $where " );
                    
                    $this->message = $this->__ ( 'Record saved' );
                    $this->messageOk = true;
                    
                    //[PT] Notificar que ja nao é preciso mostrar o form
                    $this->_editNoForm = 1;
                    
                    unset ( $this->ctrlParams ['comm'] );
                    unset ( $this->ctrlParams ['edit'] );
                
                }
                
                if ($this->cache ['use'] == 1)
                {
                    $this->cache ['instance']->clean ( Zend_Cache::CLEANING_MODE_MATCHING_TAG, array ($this->cache ['tag'] ) );
                }
                $this->formSuccess = 1;
            
            } else
            {
                
                $this->message = $this->__ ( 'Validation failed' );
                $this->messageOk = false;
                $this->formSuccess = 0;
                $this->formPost = 1;
                
                $final_values = null;
            
            }
            
            //[PT] Agone unset todos os campso, para aurl não ficar comprida. $this->getUrl
            //[EN] Unset all params so we can have a more ckean URl when calling $this->getUrl
            if (is_array ( $final_values ))
            {
                foreach ( $final_values as $key => $value )
                {
                    unset ( $this->ctrlParams [$key] );
                }
            }
        }
    }


    /**
     * [PT] Aplicar os filtros ao formulário. Os filtros são aplicados usando
     * [PT] os que vem por defeito na ZF
     * 
     * [EN] Apply filter susing the Zend Framework set.
     *
     * @param string $value
     * @param string $field
     * @param string $mode
     * @return string
     */
    function applyFilters($value, $field, $mode, $options = array())
    {

        $filters = isset ( $this->info [$mode] ['fields'] [$field] ['filters'] ) ? $this->info [$mode] ['fields'] [$field] ['filters'] : '';
        
        if (is_array ( $filters ))
        {
            
            //[PT] OK. Tem filtros para aplicar. Vamos buscar os dirs
            foreach ( $filters as $func )
            {
                
                //[Vamos meter isto por ordem reversa para os ultimos serem os primeiros
                //Faz sentido e acho que é uq eo que é mais utilizado
                $format = array_reverse ( $this->_elements ['filter'] );
                
                foreach ( $format as $find )
                {
                    $file = $find ['dir'] . ucfirst ( $func ) . '.php';
                    $class = $find ['prefix'] . '_' . ucfirst ( $func );
                    
                    if (file_exists ( $file ))
                    {
                        require_once ($file);
                    }
                    
                    if (@class_exists ( $class ))
                    {
                        $t = new $class ( $options );
                        return $t->filter ( $value );
                    
                    }
                }
            
            }
        } else
        {
            return $value;
        }
    
    }


    /**
     * [PT] Validar o formulário usando as funções por defeito da ZF
     * 
     * [EN] Validate fields using the set on he Zend Framework
     *
     * @param string $value
     * @param string $field
     * @param string $mode
     * @return string
     */
    function Validate($value, $field, $mode = 'edit')
    {

        //[Vamos meter isto por ordem reversa para os ultimos serem os primeiros
        //Faz sentido e acho que é uq eo que é mais utilizado
        $format = array_reverse ( $this->_elements ['validator'] );
        
        //[PT] Array de valores possiveis
        $values = isset ( $this->info [$mode] ['fields'] [$field] ['values'] ) ? $this->info [$mode] ['fields'] [$field] ['values'] : '';
        
        //[PT]A Lista de possiveis "validadores"
        $validators = isset ( $this->info [$mode] ['fields'] [$field] ['validators'] ) ? $this->info [$mode] ['fields'] [$field] ['validators'] : '';
        
        //[PT] Podemos validar logo se o valor estiver na array de valores permitidos
        if (is_array ( $values ) && $mode == 'edit')
        {
            
            if (! in_array ( $value, $values ))
            {
                $this->_failedValidation = true;
                return false;
            }
        
        } elseif (is_array ( $validators ))
        {
            
            foreach ( $validators as $key => $func )
            {
                
                if (is_array ( $validators [$key] ))
                {
                    $func = $key;
                }
                
                foreach ( $format as $find )
                {
                    
                    $file = $find ['dir'] . ucfirst ( $func ) . '.php';
                    $class = $find ['prefix'] . '_' . ucfirst ( $func );
                    
                    if (file_exists ( $file ))
                    {
                        
                        @require_once ($file);
                    }
                    
                    if (@class_exists ( $class ))
                    {
                        
                        //[PT] Se for array, significa que o Zend_Validate recebe argumentos
                        //[EN] If an array, means the Validator receives arguments
                        if (is_array ( $validators [$key] ))
                        {
                            
                            $t = new $class ( implode ( ',', $validators [$key] ) );
                            $return = $t->isValid ( $value );
                            
                            if ($return === false)
                            {
                                $this->_failedValidation = true;
                                foreach ( $t->getMessages () as $messageId => $message )
                                {
                                    $this->_formMessages [$field] [] = array ($messageId => $message );
                                }
                                return false;
                            }
                        
                        } else
                        {
                            
                            $t = new $class ( );
                            $return = $t->isValid ( $value );
                            
                            if ($return === false)
                            {
                                $this->_failedValidation = true;
                                
                                foreach ( $t->getMessages () as $messageId => $message )
                                {
                                    $this->_formMessages [$field] [] = array ($messageId => $message );
                                }
                                
                                return false;
                            }
                        }
                    
                    }
                }
            
            }
        
        }
        
        return $value;
    
    }


    /*


	
	/**
	 * [PT] Remover da base de dados
	 * [PT] Não esquecer de verificar se o utilizador anexou um WHERE
	 * [PT] O que significa que temos de o junter a query
	 * [EN] Remove the record from the table
	 * [EN] Don't forget to see if the user as set an "extra" WHERE.
	 * 
	 * @param string $sql
	 * @param string $user
	 * @return string
	 */
    function deleteRecord($sql)
    {

        $param = explode ( ";", $sql );
        
        foreach ( $param as $value )
        {
            $dec = explode ( ":", $value );
            @$final [$dec [0]] = $dec [1];
        }
        
        if (@$final ['mode'] != 'delete')
        {
            return 0;
        }
        

        $id = $this->_db->quoteIdentifier ( parent::getPrimaryKey () );
        
        if (isset ( $this->info ['delete'] ['where'] ))
        {
            
            $where = " AND " . $this->info ['delete'] ['where'];
        } else
        {
            $where = '';
        }
        

        try
        {
            $this->_db->delete ( $this->data ['table'], "  $id =" . $this->_db->quote ( $final ['id'] ) . " $where " );
        } catch ( Zend_Exception $e )
        {
            $this->messageOk = FALSE;
            $this->message = $this->__ ( 'Error deleting record' ) . $e->getMessage ();
        }
        if ($this->cache ['use'] == 1)
        {
            $this->cache ['instance']->clean ( Zend_Cache::CLEANING_MODE_MATCHING_TAG, array ($this->cache ['tag'] ) );
        }
        
        return true;
    
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

        $url = parent::getUrl ( array ('filters', 'start', 'comm' ) );
        
        if (! is_array ( $this->data ['table'] ))
        {
            $table = parent::getDiscribeTable ( $this->data ['table'] );
        } else
        {
            
            $ini = substr ( $campo, 0, (strpos ( $campo, "." )) );
            $table = parent::getDiscribeTable ( $this->data ['table'] [$ini] );
        }
        
        $tipo = $table [$campo];
        
        $tipo = $tipo ['DATA_TYPE'];
        
        if (substr ( $tipo, 0, 4 ) == 'enum')
        {
            $enum = str_replace ( array ('(', ')' ), array ('', '' ), $tipo );
            $tipo = 'enum';
        }
        
        foreach ( array_keys ( $this->filters ) as $value )
        {
            
            if (! $this->data ['fields'] [$value] ['hide'])
            {
                $help_javascript .= "filter_" . $value . ",";
            }
        }
        
        if ($options ['noFilters'] != 1)
        {
            $onchange = "onchange=\"changeFilters('$help_javascript','$url');\" id=\"filter_{$campo}\"";
        }
        
        $opcoes = $this->filters [$campo];
        
        if ($opcoes ['style'])
        {
            $opt = " style=\"{$opcoes['style']}\"  ";
        } else
        {
            $opt = " style=\"width:95%\"  ";
        }
        
        if (is_array ( $opcoes ['valores'] ))
        {
            $tipo = 'invalid';
            $avalor = $opcoes ['valores'];
            
            $valor = "<select name=\"$campo\" $opt $onchange  >";
            $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
            
            foreach ( $avalor as $value )
            {
                
                $selected = $this->_filtersValues [$campo] == $value ['value'] ? "selected" : "";
                $valor .= "<option value=\"{$value['value']}\" $selected >{$value['name']}</option>";
            }
            
            $valor .= "</select>";
        }
        
        switch ($tipo)
        {
            
            case 'invalid' :
                break;
            case 'enum' :
                
                $avalor = explode ( ",", substr ( $enum, 4 ) );
                $valor = "<select  id=\"filter_{$campo}\" $opt $onchange name=\"\">";
                $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
                
                foreach ( $avalor as $value )
                {
                    
                    $value = substr ( $value, 1 );
                    $value = substr ( $value, 0, - 1 );
                    $selected = $this->_filtersValues [$campo] == $value ? "selected" : "";
                    $valor .= "<option value=\"$value\" $selected >" . ucfirst ( $value ) . "</option>";
                
                }
                
                $valor .= "</select>";
                
                break;
            
            default :
                
                $valor = "<input type=\"text\" $onchange id=\"filter_{$campo}\"   class=\"input_p\" value=\"" . $this->_filtersValues [$campo] . "\" $opt>";
                
                break;
        }
        
        return $valor;
    }


    /**
     * [PT] Construir o header da tabela (a primeira linha e nao os titulos)
     * 
     * [EN] Build the first line of the table (Not the TH )
     *
     * @return string
     */
    function buildHeader()
    {

        $url = parent::getUrl ( array ('comm', 'edit', 'filters', 'order' ) );
        
        $final = '';
        
        if ((@$this->info ['double_tables'] == 0 && @$this->ctrlParams ['add'] != 1 && @$this->ctrlParams ['edit'] != 1) && $this->getPrimaryKey () && @$this->info ['add'] ['allow'] == 1 && @$this->info ['add'] ['button'] == 1 && @$this->info ['add'] ['no_button'] != 1)
        {
            
            $final = "<div class=\"addRecord\" ><a href=\"$url/add/1\">" . $this->__ ( 'Add Record' ) . "</a></div>";
        }
        
        //[PT] O início do template
        $final .= $this->temp ['table']->globalStart ();
        
        /**
         * [PT] TEmos que saber se exite uma ordem ou um filtro para podermos apresentar
         * a td para dar a opção de limpar tudo de uma única vez
         */
        if (isset ( $this->ctrlParams ['filters'] ) || isset ( $this->ctrlParams ['order'] ))
        {
            
            $url = $this->getUrl ( 'filters' );
            $url2 = $this->getUrl ( 'order' );
            $url3 = $this->getUrl ( array ('filters', 'order' ) );
            
            $this->temp ['table']->hasExtraRow = 1;
            
            //[PT] Ordem e filtros
            if (isset ( $this->ctrlParams ['filters'] ) and isset ( $this->ctrlParams ['order'] ))
            {
                $final1 = "<a href=\"$url\">" . $this->__ ( 'Remove Filters' ) . "</a> | <a href=\"$url2\">" . $this->__ ( 'Remove Order' ) . "</a> | <a href=\"$url3\">" . $this->__ ( 'Remove Filters &amp; Order' ) . "</a>";
                //[PT] FIltros apenas
            } elseif (isset ( $this->ctrlParams ['filters'] ) && ! isset ( $this->ctrlParams ['order'] ))
            {
                $final1 = "<a href=\"$url\">" . $this->__ ( 'Remove Filters' ) . "</a>";
                
            //[PT] Ordem apenas
            } elseif (! isset ( $this->ctrlParams ['filters'] ) && isset ( $this->ctrlParams ['order'] ))
            {
                $final1 = "<a href=\"$url2\">" . $this->__ ( 'Remove Order' ) . "</a>";
            }
            
            //[PT]Substituir os valores no loop (na realidade é apenas uma ocorrencia)
            //[PT]Não sendo por isso loop :D
            $final .= str_replace ( "{{value}}", $final1, $this->temp ['table']->extra () );
            
        //[PT] Fechar o ciclo
        


        }
        
        return $final;
    }


    /**
     * [PT]Construir os filtros. A informação que recebos de forma abstracta vem como array.
     * [PT]Aqui só temos que a meter no sítio certo 
     *
     * @param unknown_type $filters
     * @return unknown
     */
    function buildFiltersTable($filters)
    {

        //[PT]Não existem filtros, vamos embora
        if (! is_array ( $filters ))
        {
            $this->temp ['table']->hasFilters = 0;
            return '';
        }
        
        //[PT]Iniciar o template
        $grid = $this->temp ['table']->filtersStart ();
        
        //[PT]Vamos percorrer os filtros todos a colocá-los no campo respectivo
        foreach ( $filters as $filter )
        {
            
            //[PT]Temos que percorrer todos os extra_fields e saber se pertencem a esquerda
            //[PT]para poderem ser adicionados à lista de campos
            if ($filter ['type'] == 'extraField' && $filter ['position'] == 'left')
            {
                
                //[PT]Substituir o valor no template
                $filterValue = isset ( $filter ['value'] ) ? $filter ['value'] : '';
                
                $grid .= str_replace ( '{{value}}', $filterValue . '&nbsp;', $this->temp ['table']->filtersLoop () );
            }
            

            $hRowField = isset ( $this->info ['hRow'] ['field'] ) ? $this->info ['hRow'] ['field'] : '';
            
            //[PT]Aqui estamos a verificar se não temos a situação da linha horizontal hRow
            if ((@$filter ['field'] != $hRowField && isset ( $this->info ['hRow'] ['title'] )) || ! isset ( $this->info ['hRow'] ['title'] ))
            {
                
                if ($filter ['type'] == 'field')
                {
                    
                    $filterValue = isset ( $filter ['value'] ) ? $filter ['value'] : '';
                    
                    //[PT]Para podemrmos trabalhar nas urls se não depois no JS dá erro
                    //[PT]Se o valor for nulo, mete-mos um espaço para  a tabela não ficar com falha
                    $newValue = strlen ( urldecode ( $filterValue ) ) > 0 ? urldecode ( $filter ['value'] ) : "&nbsp;";
                    
                    //[PT]Substituir o valor no template
                    $grid .= str_replace ( '{{value}}', $newValue, $this->temp ['table']->filtersLoop () );
                
                }
            }
            

            //[PT]Temos que percorrer todos os exra_fields para saber-mos se pertencem à direita
            //[PT]e adicioná.los à lista de campos
            if ($filter ['type'] == 'extraField' && $filter ['position'] == 'right')
            {
                $grid .= str_replace ( '{{value}}', $filter ['value'], $this->temp ['table']->filtersLoop () );
            }
        
        }
        
        //[PT]Fechar o template dos Filtros
        $grid .= $this->temp ['table']->filtersEnd ();
        
        return $grid;
    }


    /**
     * [PT]Construir os titulos da tabela.
     * [PT]São recebidos como array. O "mesmo" procedimento que com os filtros
     *
     * @param array $titles
     * @return string
     */
    function buildTitltesTable($titles)
    {

        //[PT]Temos que saber qual é o campo que está ordenado para depois poder-mos meter a imagem
        //[PT]Caso esteja definda no template
        $order = array_keys ( $this->order );
        $order2 = array_keys ( array_flip ( $this->order ) );
        
        //[PT]O campo que esta a ser ordenado
        $orderField = $order [0];
        
        //[PT]A ordem contrária a que está a ser ordenada.
        $order = strtolower ( $order2 [0] );
        
        //[PT]Vamos buscar as "imagens" da ordenação.
        //[PT]quem diz imagens, diz outra coisa qualquer já que vamos buscar texto
        $images = $this->temp ['table']->images ( $this->imagesUrl );
        
        //[PT]O inicio do template para os titulos
        $grid = $this->temp ['table']->titlesStart ();
        
        //[PT]O ciclo por entre os títulos
        foreach ( $titles as $title )
        {
            
            //[PT]Veiricamos aqui se o campo que esta a ser ordenado é o mesmo que estamos a
            //[PT]passar no ciclo. Se for definimos a imagem.
            


            if (isset ( $title ['field'] ))
            {
                if ($title ['field'] == $orderField)
                {
                    $imgFinal = $images [$order];
                } else
                {
                    $imgFinal = '';
                }
            } else
            {
                $imgFinal = '';
            }
            
            //[PT]Tratar dos campos extra (extra_fields) e aplicar o template
            if ($title ['type'] == 'extraField' && $title ['position'] == 'left')
            {
                $grid .= str_replace ( '{{value}}', $title ['value'], $this->temp ['table']->titlesLoop () );
            }
            
            $hRowTitle = isset ( $this->info ['hRow'] ['field'] ) ? $this->info ['hRow'] ['field'] : '';
            
            if ((@$title ['field'] != $hRowTitle && isset ( $this->info ['hRow'] ['title'] )) || ! isset ( $this->info ['hRow'] ['title'] ))
            {
                
                if ($title ['type'] == 'field')
                {
                    
                    $noOrder = isset ( $this->info ['noOrder'] ) ? $this->info ['noOrder'] : '';
                    
                    if ($noOrder == 1)
                    {
                        
                        //[PT]Defieniu expressamente que não quer ordem, por isso mesmo
                        //[PT]nãohá ordem para ninguém
                        //[PT]Além disso fazemos o replace no template
                        $grid .= str_replace ( '{{value}}', $this->__ ( $title ['value'] ), $this->temp ['table']->titlesLoop () );
                    
                    } else
                    {
                        
                        //[PT]Versão em estado incial de ajax. Não e´para levar a sério por enquanto
                        if (array_key_exists ( 'ajax', $this->info ))
                        {
                            
                            $grid .= str_replace ( '{{value}}', "<a href=\"javascript:openAjax('grid','" . $title ['url'] . "') \">" . $title ['value'] . $imgFinal . "</a>", $this->temp ['table']->titlesLoop () );
                        
                        } else
                        {
                            
                            //[PT]Substituir os valores no template
                            $grid .= str_replace ( '{{value}}', "<a href='" . $title ['url'] . "'>" . $title ['value'] . $imgFinal . "</a>", $this->temp ['table']->titlesLoop () );
                        
                        }
                    
                    }
                }
            
            }
            
            //[PT]Tratar dos campos extra (extra_fields) e aplicar o template
            if ($title ['type'] == 'extraField' && $title ['position'] == 'right')
            {
                $grid .= str_replace ( '{{value}}', $title ['value'], $this->temp ['table']->titlesLoop () );
            }
        
        }
        
        //[PT]Finalizaro template nos títulos
        $grid .= $this->temp ['table']->titlesEnd ();
        
        return $grid;
    
    }


    /**
     * [PT] Converter os daods da url
     * 
     * [EN] Convert url  params
     *
     * @return array
     */
    function convertComm()
    {

        $t = explode ( ";", $this->_comm );
        
        foreach ( $t as $value )
        {
            $value = explode ( ":", $value );
            @$final [$value [0]] = $value [1];
        }
        
        return $final;
    }


    /**
     * [PT] Construir o form que é para ediar ou remover
     * [PT] é diferento do dos filtros
     * 
     * [EN] Build the form elements for the edit or add action
     * [EN] This is different from the filters
     *
     * @param string $field
     * @param string $inicial_value
     * @param srint edit|add
     * @return string
     */
    function buildFormElement($field, $inicial_value = '', $mod = 'edit', $fieldValue = '')
    {

        //[PT]Se não for edição remover o valor incicial (ele assume o nome dos campos)
        if ($mod != 'edit')
        {
            $field = $inicial_value;
            
            if ($this->formSuccess == 0)
            {
                $inicial_value = $fieldValue;
            } else
            {
                $inicial_value = '';
            }
        }
        
        //[PT]Vamos buscar a descrição da atbela para sabermos com que tipo de campo
        //[PT]estamos a lidar
        $table = parent::getDiscribeTable ( $this->data ['table'] );
        
        $tipo = $table [$field];
        
        $tipo = $tipo ['DATA_TYPE'];
        
        if (substr ( $tipo, 0, 4 ) == 'enum')
        {
            $enum = str_replace ( array ('(', ')' ), array ('', '' ), $tipo );
            $tipo = 'enum';
        }
        
        $opcoes = $this->info [$mod] ['fields'] [$field];
        
        //[PT]Se nas ipções do campo tiveram sido definidos styles, apli´ca-los
        //[PT]Caso contrário fazer um wdth de 95% para ficar mais vistoso
        if (isset ( $opcoes ['style'] ))
        {
            $opt = " style=\"{$opcoes['style']}\"  ";
        } else
        {
            $opt = " style=\"width:95%\"  ";
        }
        
        //[PT]Significa que alguém que especificar os valoes que pode ser mostrados através
        //[PT] de um menu select (dropdown?)
        if (isset ( $opcoes ['values'] ))
        {
            if (is_array ( $opcoes ['values'] ))
            {
                //[PT]Apesar de não ser invalido, declaramos assim para depois podermos
                //[PT]passar despercebidos no switch que vem aí
                $tipo = 'invalid';
                $avalor = $opcoes ['values'];
                
                $valor = "<select name=\"$field\" $opt  >";
                
                foreach ( $avalor as $value )
                {
                    
                    //[PT]Se o modo for de edição vefiicar se não é o valor que vem da base de dados
                    if ($mod == 'edit')
                    {
                        $selected = $inicial_value == $value ? "selected" : "";
                    } else
                    {
                        $selected = null;
                    }
                    $valor .= "<option value=\"{$value}\" $selected >" . ucfirst ( $value ) . "</option>";
                }
                
                $valor .= "</select>";
            }
        }
        
        switch ($tipo)
        {
            
            case 'invalid' :
                break;
            case 'enum' :
                
                //[PT]Vamos construir as opções consoante os valores definidos no enum na base de dados
                //[PT]De notar que isto só é utilizado se o utilizar não definir opções manualmente
                $avalor = explode ( ",", substr ( $enum, 4 ) );
                
                $valor = "<select  name=\"$field\" $opt  >";
                foreach ( $avalor as $value )
                {
                    $selected = $value == "'" . $inicial_value . "'" ? "selected" : "";
                    $value = substr ( $value, 1 );
                    $value = substr ( $value, 0, - 1 );
                    $valor .= "<option value=\"$value\" $selected >" . ucfirst ( $value ) . "</option>";
                
                }
                
                $valor .= "</select>";
                
                break;
            case 'text' :
                
                //[PT]Como é text vamos mostrar uma textarea
                $height = strlen ( $inicial_value / 60 ) < 30 ? 30 : strlen ( $inicial_value / 60 );
                
                $valor = "<textarea  name=\"{$field}\"   class=\"input_p\"  style=\"height:{$height}px;\">" . stripslashes ( $inicial_value ) . "</textarea>";
                break;
            
            default :
                
                //[PT]Seja qual for o outro valor mostradors um campo de texto
                //[PT]Ainda é preciso saber como tratar dos campo blob,
                //[PT]por enquanto não existe nada para eles. nem esta nos meus planos
                $valor = "<input type=\"text\"  name=\"{$field}\"   class=\"input_p\" value=\"" . stripslashes ( $inicial_value ) . "\" $opt>";
                
                break;
        }
        
        return $valor;
    
    }


    /**
     * [PT] A tabela a meostrar quando queremos adicionar ou editar registos
     * 
     * [EN] The table to show when editing or adding records
     *
     * @return string
     */
    function gridForm()
    {

        //[PT]Vamos remover os argumentos da url que não queremos
        $url = parent::getUrl ( array ('comm', 'edit', 'add' ) );
        
        $button_name = $this->__ ( 'Add' );
        
        $final = self::convertComm ();
        
        $pk = $this->_db->quoteIdentifier ( parent::getPrimaryKey () );
        
        if (is_array ( $this->info ['edit'] ['fields'] ))
        {
            foreach ( $this->info ['edit'] ['fields'] as $value )
            {
                $fields_to [] = $value ['field'];
            }
            
            $select_fields = parent::buildSelectFields ( $fields_to );
        
        } else
        {
            $select_fields = " * ";
        }
        
        $fields = $this->_fields;
        
        if (is_array ( $this->info ['add'] ['fields'] ))
        {
            unset ( $fields_to );
            
            foreach ( $this->info ['add'] ['fields'] as $value )
            {
                
                $fields_to [$value ['field']] = $value ['field'];
            
            }
            
            $fields = $fields_to;
            $mod = 'add';
        
        }
        
        $form_hidden = " <input type=\"button\"    onclick=\"window.location='$url'\" value=\"" . $this->__ ( 'Cancel' ) . "\"><input type=\"hidden\" name=\"_form_edit\" value=\"1\">";
        
        $fields = parent::consolidateFields ( $fields, $this->data ['table'] );
        
        if (count ( $fields ) == 0)
        {
            throw new Exception ( 'Upsss. It seams there was an error while intersecting your fields with the table fields. Please make sure you allow the fields you are defining...' );
        }
        
        $grid = $this->temp ['table']->formStart ();
        
        if (isset ( $final ['mode'] ))
        {
            if ($final ['mode'] == 'edit' && ! $this->_editNoForm)
            {
                $fields = $this->_db->fetchRow ( " SELECT $select_fields FROM " . $this->_db->quoteIdentifier ( $this->data ['table'] ) . " WHERE $pk = " . $this->_db->quote ( $final ['id'] ) . " " );
                
                $button_name = $this->__ ( 'Edit' );
                
                $mod = 'edit';
                
                $form_hidden = " <input type=\"button\"  onclick=\"window.location='$url'\" value=\"" . $this->__ ( 'Cancel' ) . "\"><input type=\"hidden\" name=\"_form_edit\" value=\"1\">";
                
                $fields = self::removeAutoIncrement ( $fields, $this->data ['table'] );
            
            }
        }
        
        $titles = $this->_fields;
        

        if (is_array ( $this->info [$mod] ['fields'] ))
        {
            unset ( $titles );
            foreach ( $this->info [$mod] ['fields'] as $key => $value )
            {
                $titles [] = $key;
            }
        }
        
        $titles = parent::consolidateFields ( $titles, $this->data ['table'] );
        
        $grid .= $this->temp ['table']->formHeader ();
        
        $i = 0;
        
        foreach ( $fields as $key => $value )
        {
            
            $grid .= $this->temp ['table']->formStart ();
            
            $finalV = '';
            if (isset ( $this->_formMessages [$titles [$i]] ))
            {
                
                if (is_array ( $this->_formMessages [$titles [$i]] ))
                {
                    
                    foreach ( $this->_formMessages [$titles [$i]] as $key => $formS )
                    {
                        $finalV .= '<br>' . implode ( '<br>', $formS );
                    }
                }
            
            } else
            {
                $finalV = '';
            }
            

            $fieldValue = isset ( $this->_formValues [$value] ) ? $this->_formValues [$value] : '';
            $fieldDescription = isset ( $this->info ['add'] ['fields'] [$titles [$i]] ['description'] ) ? $this->info ['add'] ['fields'] [$titles [$i]] ['description'] : '';
            
            $fieldTitle = isset ( $this->info ['add'] ['fields'] [$titles [$i]] ['title'] ) ? $this->info ['add'] ['fields'] [$titles [$i]] ['title'] : '';
            
            $grid .= str_replace ( "{{value}}", $this->__ ( $fieldTitle ) . '<br><em>' . $this->__ ( $fieldDescription ) . '</em>', $this->temp ['table']->formLeft () );
            $grid .= str_replace ( "{{value}}", self::buildFormElement ( $key, $value, $mod, $fieldValue ) . $finalV, $this->temp ['table']->formRight () );
            
            $grid .= $this->temp ['table']->formEnd ();
            
            $i ++;
        }
        
        $grid .= $this->temp ['table']->formStart ();
        $grid .= str_replace ( "{{value}}", "<input type=\"submit\"  value=\"" . $button_name . "\"> " . $form_hidden . "", $this->temp ['table']->formButtons () );
        $grid .= $this->temp ['table']->formEnd ();
        
        return $grid;
    
    }


    function buildGridTable($grids)
    {

        $i = 0;
        $grid = '';
        
        //Temos uma td a mais com a opção para remover-mos a ordem e filtros
        if (isset ( $this->ctrlParams ['filters'] ) || isset ( $this->ctrlParams ['order'] ))
        {
            $i ++;
        }
        
        if (isset ( $this->info ['hRow'] ['title'] ))
        {
            
            $bar = $grids;
            
            $hbar = trim ( $this->info ['hRow'] ['field'] );
            
            $p = 0;
            
            foreach ( $grids [0] as $value )
            {
                if ($value ['field'] == $hbar)
                {
                    $hRowIndex = $p;
                }
                
                $p ++;
            }
            $aa = 0;
        }
        
        foreach ( $grids as $value )
        {
            //Os decorators
            $search = $this->_fields;
            unset ( $fi );
            foreach ( $value as $tia )
            {
                
                if (isset ( $tia ['field'] ))
                {
                    $fi [] = $tia ['value'];
                }
            }
            
            if (count ( $fi ) != count ( $search ))
            {
                $diff = count ( $fi ) > count ( $search ) ? count ( $fi ) - count ( $search ) : count ( $search ) - count ( $fi );
                
                if (count ( $search ) > count ( $fi ) && $diff == 1)
                {
                    //[PT] Tiramos os primeiro elementos por causa de ser um id_
                    array_shift ( $search );
                }
            }
            
            $finalFields = @array_combine ( $search, $fi );
            
            //A linha horizontal
            if (isset ( $this->info ['hRow'] ['title'] ))
            {
                
                if ($bar [$aa] [$hRowIndex] ['value'] != @$bar [$aa - 1] [$hRowIndex] ['value'])
                {
                    $i ++;
                    
                    $grid .= str_replace ( array ("{{value}}", "{{class}}" ), array ($bar [$aa] [$hRowIndex] ['value'], @$value ['class'] ), $this->temp ['table']->hRow ( $finalFields ) );
                }
            }
            
            $i ++;
            
            //A TR do ciclo
            $grid .= $this->temp ['table']->loopStart ( $finalFields );
            
            $set = 0;
            $aa = 0;
            foreach ( $value as $final )
            {
                
                $finalField = isset ( $final ['field'] ) ? $final ['field'] : '';
                $finalHrow = isset ( $this->info ['hRow'] ['field'] ) ? $this->info ['hRow'] ['field'] : '';
                
                if (($finalField != $finalHrow && isset ( $this->info ['hRow'] ['title'] )) || ! isset ( $this->info ['hRow'] ['title'] ))
                {
                    
                    $set ++;
                    
                    $grid .= str_replace ( array ("{{value}}", "{{class}}" ), array ($final ['value'], $final ['class'] ), $this->temp ['table']->loopLoop ( $finalFields ) );
                
                }
            }
            
            $set = null;
            $grid .= $this->temp ['table']->loopEnd ( $finalFields );
            
            $aa ++;
        }
        
        if ($this->_totalRecords == 0)
        {
            $grid = str_replace ( "{{value}}", $this->__ ( 'No records found' ), $this->temp ['table']->noResults () );
        }
        
        return $grid;
    
    }


    function buildSqlexpTable($sql)
    {

        $grid = '';
        if (is_array ( $sql ))
        {
            $grid .= $this->temp ['table']->sqlExpStart ();
            
            foreach ( $sql as $exp )
            {
                if ($exp ['field'] != @$this->info ['hRow'] ['field'])
                {
                    $grid .= str_replace ( "{{value}}", $exp ['value'], $this->temp ['table']->sqlExpLoop () );
                }
            }
            $grid .= $this->temp ['table']->sqlExpEnd ();
        
        } else
        {
            return false;
        }
        
        return $grid;
    
    }


    /**
     * [PT] Contruir a paginação
     * [EN] Build pagination
     *
     * @return string
     */
    function pagination()
    {

        $url = parent::getUrl ( array ('start' ) );
        
        $actual = ( int ) isset ( $this->ctrlParams ['start'] ) ? $this->ctrlParams ['start'] : 0;
        
        $ppagina = $this->data ['pagination'] ['per_page'];
        $result2 = '';
        
        $pa = $actual == 0 ? 1 : ceil ( $actual / $ppagina ) + 1;
        
        //[PT] Calcular o número total de páginas
        //[EN] Calculate the number of pages
        $npaginas = ceil ( $this->_totalRecords / $ppagina );
        
        $actual = floor ( $actual / $ppagina ) + 1;
        
        if (array_key_exists ( 'ajax', $this->info ))
        {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"javascript:openAjax('grid','$url/start/0')\">1</a>";
        } else
        {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"$url/start/0\">1</a>";
        
        }
        if ($npaginas > 5)
        {
            $in = min ( max ( 1, $actual - 4 ), $npaginas - 5 );
            $fin = max ( min ( $npaginas, $actual + 4 ), 6 );
            
            for($i = $in + 1; $i < $fin; $i ++)
            {
                if (array_key_exists ( 'ajax', $this->info ))
                {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=javascript:openAjax('grid','$url/start/" . (($i - 1) * $ppagina) . "')> $i </a>";
                } else
                {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href='$url/start/" . (($i - 1) * $ppagina) . "'> $i </a>";
                }
            
            }
            
            $pag .= ($fin < $npaginas) ? " ... " : "  ";
        } else
        {
            
            for($i = 2; $i < $npaginas; $i ++)
            {
                if (array_key_exists ( 'ajax', $this->info ))
                {
                    
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"javascript:openAjax('grid','" . $url . "/start/" . (($i - 1) * $ppagina) . "')\">$i</a> ";
                
                } else
                {
                    
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"" . $url . "/start/" . (($i - 1) * $ppagina) . "\">$i</a> ";
                
                }
            
            }
        }
        
        if (array_key_exists ( 'ajax', $this->info ))
        {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"javascript:openAjax('grid','$url/start/" . (($npaginas - 1) * $ppagina) . "')\">$npaginas</a> ";
        
        } else
        {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"$url/start/" . (($npaginas - 1) * $ppagina) . "\">$npaginas</a> ";
        
        }
        
        if ($actual != 1)
        {
            
            if (array_key_exists ( 'ajax', $this->info ))
            {
                $pag = " <a href=\"javascript:openAjax('grid','$url/start/0')\">" . $this->__ ( 'First' ) . "</a>&nbsp;&nbsp;<a href=\"javascript:aopenAjax'grid','$url/start/" . (($actual - 2) * $ppagina) . "')\">" . $this->__ ( 'Previous' ) . "</a>&nbsp;&nbsp;" . $pag;
            
            } else
            {
                
                $pag = " <a href=\"$url/start/0\">" . $this->__ ( 'First' ) . "</a>&nbsp;&nbsp;<a href=\"$url/start/" . (($actual - 2) * $ppagina) . "\">" . $this->__ ( 'Previous' ) . "</a>&nbsp;&nbsp;" . $pag;
            }
        
        }
        
        if ($actual != $npaginas)
        {
            if (array_key_exists ( 'ajax', $this->info ))
            {
                
                $pag .= "&nbsp;&nbsp;<a href=\"javascript:openAjax('grid','$url/start/" . ($actual * $ppagina) . "')\">" . $this->__ ( 'Next' ) . "</a> <a href=\"javascript:openAjax('grid','$url/start/" . (($npaginas - 1) * $ppagina) . "')\">" . $this->__ ( 'Last' ) . "&nbsp;&nbsp;</a>";
            } else
            {
                
                $pag .= "&nbsp;&nbsp;<a href=\"$url/start/" . ($actual * $ppagina) . "\">" . $this->__ ( 'Next' ) . "</a>&nbsp;&nbsp;<a href=\"$url/start/" . (($npaginas - 1) * $ppagina) . "\">" . $this->__ ( 'Last' ) . "</a>";
            }
        
        }
        
        if ($npaginas > 1)
        {
            
            //[PT] Construir o select
            //[EN] Buil the select form element
            if (array_key_exists ( 'ajax', $this->info ))
            {
                $f = "<select id=\"idf\" onchange=\"javascript:openAjax('grid','{$url}/start/'+this.value)\">";
            } else
            {
                $f = "<select id=\"idf\" onchange=\"window.location='{$url}/start/'+this.value\">";
            }
            
            for($i = 1; $i <= $npaginas; $i ++)
            {
                $f .= "<option ";
                if ($pa == $i)
                {
                    $f .= " selected ";
                }
                $f .= " value=\"" . (($i - 1) * $ppagina) . "\">$i</option>\n";
            }
            $f .= "</select>";
        
        }
        
        if ($npaginas > 1 || count ( $this->export ) > 0)
        {
            
            //Vamos calcular o registo actual
            if ($actual <= 1)
            {
                $registoActual = 1;
                $registoFinal = $this->_totalRecords > $ppagina ? $ppagina : $this->_totalRecords;
            } else
            {
                $registoActual = $actual * $ppagina - $ppagina;
                
                if ($actual * $ppagina > $this->_totalRecords)
                {
                    $registoFinal = $this->_totalRecords;
                } else
                {
                    $registoFinal = $actual * $ppagina;
                }
            
            }
            
            $images = $this->temp ['table']->images ( $this->imagesUrl );
            
            $exp = '';
            
            foreach ( $this->export as $export )
            {
                $exp .= "<a target='_blank' href='$url/export/{$export}'>" . $images [$export] . "</a>";
            }
            
            if ($npaginas > 1 && count ( $this->export ) > 0)
            {
                $result2 = str_replace ( array ('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}' ), array ($exp, $pag, $f, $registoActual . ' to ' . $registoFinal . ' of ' . $this->_totalRecords ), $this->temp ['table']->pagination () );
            
            } elseif ($npaginas < 2 && count ( $this->export ) > 0)
            {
                
                $result2 .= str_replace ( array ('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}' ), array ($exp, '', '', $this->_totalRecords ), $this->temp ['table']->pagination () );
            
            }
        
        } else
        {
            return '';
        }
        
        return $result2;
    }


    /**
     * [PT] Remover o field the auto-incrment do array. Se Ã© aut-increment não vamos deixar
     * [PT] O utilizador inserir dados nesse campo
     * 
     * [EN] Remeve the auto-increment field from the array. If a field is auto-increment,
     * [EN] we won't let the user insert data on the field
     *
     * @param array $fields
     * @param string $table
     * @return array
     */
    function removeAutoIncrement($fields, $table)
    {

        $table = $this->_db->fetchAll ( "SHOW COLUMNS FROM $table" );
        
        foreach ( $table as $value )
        {
            
            if ($value->Extra == 'auto_increment')
            {
                $table_fields = $value->Field;
            }
        }
        
        if (array_key_exists ( $table_fields, $fields ))
        {
            unset ( $fields->$table_fields );
        }
        
        return $fields;
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

        if ($this->info ['noFilters'])
        {
            return false;
        }
        
        if (is_array ( $filters ))
        {
            
            return $filters;
        
        } else
        {
            
            //Não forneceu dados, temos que ir buscá-los todos às tabelas
            if (is_array ( $this->data ['table'] ))
            {
                
                foreach ( $this->data ['table'] as $key => $value )
                {
                    
                    $tab = parent::getDiscribeTable ( $value );
                    
                    foreach ( $tab as $list )
                    {
                        $titulos [$key . "." . $list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                    }
                }
            
            } else
            {
                
                $tab = parent::getDiscribeTable ( $this->data ['table'] );
                
                foreach ( $tab as $list )
                {
                    $titulos [$list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                }
            }
        
        }
        
        if (is_array ( $this->data ['hide'] ))
        {
            foreach ( $this->data ['hide'] as $value )
            {
                if (! in_array ( $value, $titulos ))
                {
                    unset ( $titulos [$value] );
                }
            }
        } else
        {
            
            foreach ( $titulos as $key => $value )
            {
                
                if (! in_array ( $key, $this->_fields ))
                {
                    unset ( $titulos [$key] );
                }
            
            }
        
        }
        
        return $titulos;
    
    }


    function deploy()
    {

        $url = parent::getUrl ( 'comm' );
        

        //[PT] Precisamos de processar os forimulário, se necessário,
        //[PT] Antes de fazer-mos a query
        self::processForm ();
        

        parent::deploy ();
        
        if (! $this->temp ['table'] instanceof Bvb_Grid_Template_Table_Table)
        {
            $this->setTemplate ( 'table', 'table' );
        }
        
        //[PT] As classes em CSS para aplicar as diferentes zonas
        //[EN] The CSS classes to apply
        


        //[PT] Os campos extra, que não estão na base de dados. São sobretudo uteis para criar links
        //[EN] The extra fields, they are not part of database table.
        //[EN] Usefull for adding links (a least for me :D )
        


        #$this->extra_fields =  $this->info[ 'extra_fields' ];
        


        $grid = '';
        
        $images = $this->temp ['table']->images ( $this->imagesUrl );
        
        if ($this->allowEdit == 1)
        {
            if (! is_array ( $this->extra_fields ))
            {
                $this->extra_fields = array ();
            }
            
            array_unshift ( $this->extra_fields, array ('position' => 'left', 'name' => 'E', 'decorator' => "<a href=\"$url/edit/1/comm/" . "mode:edit;id:{{id}}\" > " . $images ['edit'] . "</a>", 'edit' => true ) );
        
        }
        
        if ($this->allowDelete)
        {
            if (! is_array ( $this->extra_fields ))
            {
                $this->extra_fields = array ();
            }
            
            array_unshift ( $this->extra_fields, array ('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"#\" onclick=\"confirmDel('" . $this->__ ( 'Are you sure?' ) . "','$url/comm/" . "mode:delete;id:{{id}}');\" > " . $images ['delete'] . "</a>", 'delete' => true ) );
        }
        
        if (strlen ( $this->message ) > 0)
        {
            $grid = str_replace ( "{{value}}", $this->message, $this->temp ['table']->formMessage ( $this->messageOk ) );
        }
        
        if ((@$this->ctrlParams ['edit'] == 1 || @$this->ctrlParams ['add'] == 1 || @$this->info ['double_tables'] == 1) || ($this->formPost == 1 && $this->formSuccess == 0))
        {
            
            if (($this->allowAdd == 1 && $this->_editNoForm != 1) || ($this->allowEdit == 1 && strlen ( $this->_comm ) > 1))
            {
                
                $url = parent::getUrl ( array ('filters', 'add' ) );
                
                $grid .= "<form method=\"post\" action=\"$url\">" . $this->temp ['table']->formGlobal () . self::gridForm () . "</table></form><br><br>";
            
            }
        }
        
        $grid .= "<input type=\"hidden\" name=\"inputId\" id=\"inputId\">";
        
        if (@$this->info ['double_tables'] == 1 || (@$this->ctrlParams ['edit'] != 1 && @$this->ctrlParams ['add'] != 1))
        {
            
            if (($this->formPost == 1 && $this->formSuccess == 1) || $this->formPost == 0)
            {
                /*

                        $totalRegistos = parent::buildGrid ();
                        if( count($totalRegistos)==0 )
                        {
                        Bvb_View_Message::addInfo('Não existem registos a mostrar');
                        return false;
                        }
                        */
                
                $grid .= self::buildHeader ();
                $grid .= self::buildTitltesTable ( parent::buildTitles () );
                $grid .= self::buildFiltersTable ( parent::buildFilters () );
                $grid .= self::buildGridTable ( parent::buildGrid () );
                $grid .= self::buildSqlexpTable ( parent::buildSqlExp () );
                $grid .= self::pagination ();
            
            }
        }
        $grid .= $this->temp ['table']->globalEnd ();
        
        return $grid;
    
    }


    /**
     * [PT]Método alternativo para adicionar novas colunas
     *
     * @return unknown
     */
    function addForm($form)
    {

        //Vamos primeiros construir os campos
        


        $form = $this->object2array ( $form );
        
        $fieldsGet = $form ['fields'];
        $fields = array ();
        
        if (is_array ( $fieldsGet ))
        {
            foreach ( $fieldsGet as $value )
            {
                $fields [$value ['options'] ['field']] = $value ['options'];
            }
        }
        
        $options = $form ['options'];
        

        $this->info ['double_tables'] = isset ( $options ['double_tables'] ) ? $options ['double_tables'] : '';
        
        if (isset ( $options ['delete'] ))
        {
            if ($options ['delete'] == 1)
            {
                $this->delete = array ('allow' => 1 );
                
                if (isset ( $options ['onDeleteAddWhere'] ))
                {
                    
                    $this->info ['delete'] ['where'] = $options ['onDeleteAddWhere'];
                }
            }
        }
        

        if ($options ['add'] == 1)
        {
            $this->add = array ('allow' => 1, 'button' => $options ['button'], 'fields' => $fields, 'force' => $options ['onAddForce'] );
        }
        
        if (isset ( $options ['edit'] ))
        {
            if ($options ['edit'] == 1)
            {
                $this->edit = array ('allow' => 1, 'button' => $options ['button'], 'fields' => $fields, 'force' => $options ['onEditForce'] );
            }
        }
        if (isset ( $options ['onUpdateAddWhere'] ))
        {
            $this->info ['edit'] ['where'] = $options ['onUpdateAddWhere'];
        }
        return $this;
    }

}

