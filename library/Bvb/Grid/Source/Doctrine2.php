<?php

use Doctrine\ORM\EntityRepository,
    Doctrine\ORM\QueryBuilder,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Query,
    Doctrine\ORM\Query\AST,
    Doctrine\DBAL\Types\Type,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\NonUniqueResultException;

/**
 * Provides you the ability to use Doctrine as a source
 * with the Grid.
 *
 * @package   Bvb_Grid
 * @author Martin Parsiegla <martin.parsiegla@speanet.info>
 */
class Bvb_Grid_Source_Doctrine2 extends Bvb_Grid_Source_Db_DbAbstract implements Bvb_Grid_Source_SourceInterface
{

    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * An array containing title, type and fieldname from the query fields.
     *
     * @var array
     */
    private $fields;

    /**
     * Array with all defined where conditions.
     *
     * @var array
     */
    private $whereConditions = array();

    /**
     * Ascii coded alias (97 = 'a').
     *
     * @var integer
     */
    private $alias = 97;

    /**
     * The parameter number to use.
     *
     * @var int
     */
    private $paramterNumber = 1;

    /**
     * Class construct.
     *
     * @param string|QueryBuilder $value
     * @param EntityManager $entityManager
     */
    function __construct($value, $entityManager = null)
    {
        $this->setEntityManager($entityManager);
        $this->setQueryBuilder($value);
    }

    /**
     * Retriev the entity manager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Sets the entity manager.
     *
     * If no entity manager is assigned, we try to get it from the registry.
     *
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager = null)
    {
        if (is_null($entityManager)) {
            if (Zend_Registry::isRegistered('doctrine')) {
                $entityManager = Zend_Registry::get('doctrine')->getEntityManager();
            } elseif (Zend_Registry::isRegistered('EntityManager')) {
                $entityManager = Zend_Registry::get('EntityManager');
            } else {
                throw new Bvb_Grid_Source_Doctrine2_Exception('No suitable EntityManager found in registry, please set a specific one.');
            }
        } elseif (!($entityManager instanceof EntityManager)) {
            throw new Bvb_Grid_Source_Doctrine2_Exception('Parameter must be an instance of \Doctrine\ORM\EntityManager');
        }

        $this->entityManager = $entityManager;
    }

    /**
     * Returns the query builder.
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Sets the query builder.
     *
     * @param string|EntityManager|EntityRepository $value
     * @return Bvb_Grid_Source_Doctrine2
     */
    public function setQueryBuilder($value)
    {
        //if the value is a string, check if the entity class
        //exists and create the query builder
        if (is_string($value)) {
            $em = $this->getEntityManager();
            //check if the class exists, surpress any warnings
            if (!@class_exists($value, true)) {
                throw new Bvb_Grid_Source_Doctrine2_Exception('Entity with name ' . $value . ' does not exist.');
            }

            $qb = $em->getRepository($value)->createQueryBuilder('d');
        } elseif ($value instanceof EntityRepository) {
            $qb = $value->createQueryBuilder('d');
        } elseif (!($value instanceof QueryBuilder)) {
            throw new Bvb_Grid_Source_Doctrine2_Exception('Parameter must be an instance of \Doctrine\ORM\EntityRepository or \Doctrine\ORM\QueryBuilder.');
        } else {
            $qb = $value;
        }

        $this->qb = $qb;

        return $this;
    }

    /**
     * Adds a new condition to the current query
     * $filter is the value to be filtered
     * $op is the opreand to be used: =,>=, like, llike,REGEX,
     * $completeField. use the index $completField['field'] to
     * specify the field, to avoid ambiguous
     *
     * @param $filter
     * @param $op
     * @param $completeField
     * @return Bvb_Grid_Source_Doctrine
     */
    public function addCondition($filter, $op, $completeField)
    {
        $field = $completeField['field'];

        $this->whereConditions[$field]['filter'] = $filter;
        $this->whereConditions[$field]['op'] = $op;

        $qb = $this->getQueryBuilder();

        $this->_addCondition($qb, $field, $op, $filter);

        return $this;
    }

    /**
     * Adds a new condition to the given QueryBuilder.
     *
     * @param QueryBuilder $qb
     * @param string $field The field to be used for the condition
     * @param string $op The operator to be used for the condition.
     * @param string $filter
     */
    private function _addCondition(QueryBuilder $qb, $field, $op, $filter)
    {
        $pn = $this->_getNewParameterNumber();
        $pnUse = '?' . $pn;

        $func = 'where';
        if (strpos($field, '(') !== false) {
            $func = 'having';
        }

        $havingPart = $qb->getDQLPart('having');
        $wherePart = $qb->getDQLPart('where');
        if ($func == 'having' && !empty($havingPart)) {
            $func = 'andHaving';
        } elseif ($func == 'where' && !empty($wherePart)) {
            $func = 'andWhere';
        }

        switch (strtolower($op)) {
            case 'sqlexp':
                $qb->$func($filter);
                break;
            case 'isnull':
                $qb->$func($field . ' IS NULL ');
                break;
            case 'isnotnull':
                $qb->$func($field . ' IS NOT NULL ');
                break;
            case 'empty':
                $expr = $qb->expr()->eq($field, '');
                $qb->$func($expr);
                break;
            case 'equal':
            case '=':
                $expr = $qb->expr()->eq($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, $filter);
                break;
            case 'regex':
                $qb->$func($field . " REGEXP " . $pnUse);
                $qb->setParameter($pn, $filter);
                break;
            case 'rlike':
                $expr = $qb->expr()->like($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, $filter . "%");
                break;
            case 'llike':
                $expr = $qb->expr()->like($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, "%" . $filter);
                break;
            case '>=':
                $expr = $qb->expr()->gte($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, $filter);
                break;
            case '>':
                $expr = $qb->expr()->gt($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, $filter);
                break;
            case '<>':
            case '!=':
                $expr = $qb->expr()->neq($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, $filter);
                break;
            case '<=':
                $expr = $qb->expr()->lte($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, $filter);
                break;
            case '<':
                $expr = $qb->expr()->lt($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, $filter);
                break;
            case 'in':
                $filter = explode(',', $filter);
                $qb->$func($qb->expr()->in($field, $filter));
                break;
            case '&':
            case 'and':
            case 'AND':
            case 'flag':
            case 'FLAG':
                $qb->$func($field . " & " . $pnUse . " <> 0");
                $qb->setParameter($pn, $filter);
                break;
            case 'range':
                $pn2 = $this->_getNewParameterNumber();
                $pn2Use = '?' . $pn2;
                $expr = $qb->expr()->between($field, $pnUse, $pn2Use);
                $start = substr($filter, 0, strpos($filter, '<>'));
                $end = substr($filter, strpos($filter, '<>') + 2);
                $qb->$func($expr);
                $qb->setParameter($pn, $start);
                $qb->setParameter($pn2, $end);
                break;
            case '||':
                $expr = $qb->expr()->like($field, $pnUse);
                $qb->orWhere($expr);
                $qb->setParameter($pn, "%" . $filter . "%");
                break;
            case 'like':
            default:
                $expr = $qb->expr()->like($field, $pnUse);
                $qb->$func($expr);
                $qb->setParameter($pn, "%" . $filter . "%");
                break;
        }

        return $this;
    }

    public function addFullTextSearch($filter, $field)
    {
        throw new Bvb_Grid_Source_Doctrine2_Exception("Fulltext searching is currently not supported by the Doctrine2 source.");
    }

    /**
     * builds a key=>value array
     *
     * they must have two options
     * title and field
     * field is used to perform queries.
     * Must have table name or table alias as a prefix
     * ex: user.id | country.population
     *
     * The key for this array is the output field
     * If raw sql is somehting like
     *
     * select name as alias, country from users
     *
     * the return array must be like this:
     *
     * array('alias'=>array('title'=>'alias','field'=>'users.name'));
     *
     * its not bad idea to apply this to fields titles
     * $title = ucwords(str_replace('_',' ',$title));
     *
     * @return array
     */
    public function buildFields()
    {
        if (empty($fields)) {
            $ast = $this->getQueryBuilder()->getQuery()->getAST();

            //used for expressions without an identification variable
            $fieldNumber = 1;
            $returnFields = array();
            foreach ($ast->selectClause->selectExpressions as $selectExpression) {
                $expression = $selectExpression->expression;

                //if the expression is a string, there is an alias used to get all fields
                //to get all fields from the alias, we fetch the entity class and retrieve
                //the metadata from it, so we can set the fields correctly
                if (is_string($expression)) {
                    //the expression itself is a pathexpression, where we can directly
                    //fetch the title and field

                    $alias = $expression;
                    $tableName = $this->_getModelFromAlias($alias);
                    $metadata = $this->getEntityManager()->getClassMetadata($tableName);

                    foreach ($metadata->fieldMappings as $key => $details) {
                        $returnFields[$key]['title'] = ucwords(str_replace('_', ' ', $key));
                        $returnFields[$key]['field'] = $alias . '.' . $key;
                        $returnFields[$key]['type'] = $details['type'];
                    }
                } elseif ($expression instanceof AST\PathExpression) {
                    $field = ($selectExpression->fieldIdentificationVariable != null) ? $selectExpression->fieldIdentificationVariable : $expression->field;
                    $returnFields[$field]['title'] = ucwords(str_replace('_', ' ', $field));
                    $returnFields[$field]['field'] = $expression->identificationVariable . '.' . $expression->field;
                } elseif ($expression instanceof AST\Subselect) {
                    //handle subselects. we only need the identification variable for the field
                    $field = $selectExpression->fieldIdentificationVariable;

                    $title = ucwords(str_replace('_', ' ', $field));

                    $returnFields[$field]['title'] = $title;
                    $returnFields[$field]['field'] = $field;
                } else {
                    $field = $selectExpression->fieldIdentificationVariable;

                    //doctrine uses numeric keys for expressions which got no
                    //identification variable, so the key will be set to the
                    //current counter $i
                    if ($field === null) {
                        $field = $this->_getNameForExpression($expression);
                        $key = $fieldNumber;
                        $fieldNumber++;
                    } else {
                        $key = $field;
                    }

                    $title = ucwords(str_replace('_', ' ', $field));

                    $returnFields[$key]['title'] = $title;
                    $returnFields[$key]['field'] = $field;
                }
            }

            $this->fields = $returnFields;
        }

        return $this->fields;
    }

    /**
     * Generates the expression used in the select expression
     *
     * @param FunctionNode $expression
     * @return string
     */
    private function _getNameForExpression($expression)
    {
        $str = '';

        foreach ($expression as $key => $sub) {
            if ($sub instanceof AST\PathExpression) {
                $str .= $sub->identificationVariable . '.' . $sub->field;
                if ($expression instanceof AST\Functions\FunctionNode) {
                    $str = $expression->name . '(' . $str . ')';
                } elseif ($expression instanceof AST\AggregateExpression) {
                    $str = $expression->functionName . '(' . $str . ')';
                }
                //when we got another array, we will call the method recursive and add
                //brackets for readability.
            } elseif (is_array($sub)) {
                $str .= '(' . $this->_getNameForExpression($sub) . ')';
                //call the method recursive to get all names.
            } elseif (is_object($sub)) {
                $str .= $this->_getNameForExpression($sub);
                //key is numeric and value is a string, we probably got an
                //arithmetic identifier (like "-" or "/")
            } elseif (is_numeric($key) && is_string($sub)) {
                $str .= ' ' . $sub . ' ';
                //we got a string value for example in an arithmetic expression
                //(a.value - 1) the "1" here is the value we append to the string here
            } elseif ($key == 'value') {
                $str .= $sub;
            }
        }

        return $str;
    }

    /**
     * Build the form based on a Model or query.
     *
     * @return array
     */
    public function buildForm($inputsType = array())
    {
        $qb = $this->getQueryBuilder();

        //create the form based on the main table
        $mainTable = $this->getMainTable();

        $em = $this->getEntityManager();
        $metadata = $em->getClassMetadata($mainTable['table']);

        return $this->buildFormElements($metadata->fieldMappings, $metadata, $inputsType);
    }

    /**
     * Will build out an array of form elements,
     * based on the column type and return the array
     * to be used when loading the Bvb_Grid_Form
     *
     * @param array $cols
     * @param array $info
     * @return array
     */
    public function buildFormElements(array $cols, $info = array(), $inputsType = array())
    {
        $form = array();
        $em = $this->getEntityManager();

        foreach ($cols as $column => $detail) {

            if (isset($detail['id']) && $detail['id']) {
                continue;
            }

            $label = ucwords(str_replace('_', ' ', $column));
            switch ($detail['type']) {
                case Type::STRING:
                    $length = (is_null($detail['length'])) ? 255 : $detail['length'];
                    $return[$column] = array('type' => 'smallText',
                        'length' => $length,
                        'label' => $label,
                        'required' => !$detail['nullable'],
                        'default' => "");
                    break;
                case Type::DATE:
                    $return[$column] = array('type' => 'date',
                        'label' => $label,
                        'required' => !$detail['nullable'],
                        'default' => "");
                    break;
                case Type::TIME:
                    $return[$column] = array('type' => 'time',
                        'label' => $label,
                        'required' => !$detail['nullable'],
                        'default' => "");
                    break;
                case Type::DATETIME:
                case Type::DATETIMETZ:
                    $return[$column] = array('type' => 'datetime',
                        'label' => $label,
                        'required' => !$detail['nullable'],
                        'default' => "");
                    break;

                case Type::TEXT:
                    $return[$column] = array('type' => 'longtext',
                        'label' => $label,
                        'required' => !$detail['nullable'],
                        'default' => "");
                    break;

                case Type::INTEGER:
                case Type::BIGINT:
                case Type::SMALLINT:
                    $return[$column] = array('type' => 'number',
                        'label' => $label,
                        'required' => !$detail['nullable'],
                        'default' => "");
                    break;

                case Type::FLOAT:
                case Type::DECIMAL:
                    $return[$column] = array('type' => 'decimal',
                        'label' => $label,
                        'required' => !$detail['nullable'],
                        'default' => "");
                    break;

                case Type::BOOLEAN:
                    $return[$column] = array('type' => 'select',
                        'label' => $label,
                        'required' => $detail['nullable'],
                        'default' => array(true => 'Yes', false => 'No'));

                default:
                    break;
            }
        }

        if (count($info->associationMappings > 0)) {
            foreach ($info->associationMappings as $column => $detail) {
                //many to many relations are not supported
                if ($detail['type'] == ClassMetadata::MANY_TO_MANY || $detail['isOwningSide'] == false) {
                    continue;
                }

                $label = ucwords(str_replace('_', ' ', $column));

                $targetEntity = $detail['targetEntity'];
                $dummy = new $targetEntity();

                //check if the entity class got a __toString method
                //if the class got one, we use this for the value in the select field
                //otherwise use fetch an array and search for a display column.
                if (method_exists($dummy, '__toString')) {
                    $hydrate = Query::HYDRATE_OBJECT;
                } else {
                    $hydrate = Query::HYDRATE_ARRAY;
                }

                $metadata = $em->getClassMetadata($targetEntity);
                $assoc = $em->getRepository($targetEntity)
                        ->createQueryBuilder('c')
                        ->getQuery()
                        ->getResult($hydrate);

                $primaryColumn = $metadata->identifier[0];

                $displayField = null;
                //seach for a field with the type string and use this value of the field for the select
                foreach ($metadata->fieldMappings as $fieldMapping) {
                    if ($fieldMapping['type'] == Type::STRING) {
                        $displayField = $fieldMapping['fieldName'];
                    }
                }

                $final['values'][$column] = array();

                //if no display field was found, use the primary column
                $displayField = (is_null($displayField)) ? $primaryColumn : $displayField;

                $isNullable = $detail['joinColumns'][0]['nullable'];
                if ($isNullable) {
                    $final['values'][$column][""] = "-- Empty --";
                }


                foreach ($assoc as $field) {
                    if (is_object($field)) {
                        $method = 'get' . ucfirst($primaryColumn);
                        if (!method_exists($field, $method)) {
                            throw new Bvb_Grid_Source_Doctrine2_Exception('No getter method for the primary field found (used name: ' . $method . ').');
                        }
                        $final['values'][$column][$field->$method()] = $field->__toString();
                    } else {
                        $final['values'][$column][$field[$primaryColumn]] = $field[$displayField];
                    }
                }

                $return[$column] = array('type' => 'select',
                    'label' => $label,
                    'default' => $final['values'][$column]);
            }
        }

        $form = $this->buildFormElementsFromArray($return);

        foreach ($inputsType as $field => $type) {
            $form['elements'][$field][0] = strtolower($type);
        }

        return $form;
    }

    /**
     * Build the query limit clause
     *
     * @param $start
     * @param $offset
     * @return Mp_Grid_Source_Doctrine2
     */
    public function buildQueryLimit($start, $offset)
    {
        if ($start == 0 && $offset == 0) {
            $this->resetLimit();
        } else {
            $this->getQueryBuilder()->setMaxResults($start)->setFirstResult($offset);
        }

        return $this;
    }

    /**
     * Build the order part from the query.
     *
     * The first arg is the field to be ordered and the $order
     * arg is the correspondent order (ASC|DESC)
     *
     * If the $reset is set to true, all previous order will be removed.
     *
     * @param string $field
     * @param string $order
     * @param bool $reset
     * @return Mp_Grid_Source_Doctrine2
     */
    public function buildQueryOrder($field, $order, $reset = false)
    {
        //fetch the fieldname from the created fields array
        $fieldName = $this->_getFieldName($field);

        $qb = $this->getQueryBuilder();
        if ($reset) {
            $qb->resetDQLPart('orderBy');
        }

        $qb->addOrderBy($fieldName, $order);

        return $this;
    }

    /**
     * Delete a record from a table
     *
     *
     * Ex: array('user_id'=>'1','id_site'=>'12');
     * Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $condition
     * @return integer Of Affected rows
     */
    public function delete($table, array $condition)
    {
        $qbDelete = new QueryBuilder($this->getEntityManager());

        $alias = $this->_getNewAlias();
        //create a delete query for the given entity
        $qbDelete->delete($table, $alias);

        $first = true;
        foreach ($condition as $column => $value) {
            //remove alias and prepend own one
            $column = $this->_removeAlias($column);
            $column = $alias . '.' . $column;

            $this->_addCondition($qbDelete, $column, '=', $value);
        }

        $return = $qbDelete->getQuery()->execute();

        return $return;
    }

    /**
     * Runs the query and returns the result as a associative array
     *
     * @return array
     */
    public function execute()
    {
        $qb = $this->getQueryBuilder();
        $qb = clone $qb;

        try {
            $result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
        } catch (Query\QueryException $e) {
            Zend_Debug::dump($e);
        }

        $result = $this->_cleanQueryResults($result);

        return $result;
    }

    /**
     * Get a record detail based the current query
     *
     * <code>
     * $where = array(
     *     array('columnName' => 'searchValue')
     * )
     * </code>
     *
     * @param array $where
     * @return array
     */
    public function fetchDetail(array $where)
    {
        $qb = $this->getQueryBuilder();

        /**
         * Remove these since we are trying to retrieve
         * a specific row
         */
        $qb->setFirstResult(null)->setMaxResults(null);

        $this->_createWhereConditions($qb, $where);

        $results = $this->execute();
        if(is_array($results)){
            return $results[0];
        }else{
            return false;
        }
    }

    /**
     * This method is not implemented in the interface (yet), nevertheless it
     * has to be implemented so crud-operations can be used.
     *
     * The only thing that is done here, is the creation of a new field 'NULLABLE',
     * which defines wether or not the field is nullable.
     *
     * @param array|string $table Table Name
     * @return array
     */
    public function getDescribeTable($class)
    {
        $metadata = $this->getEntityManager()->getClassMetadata($class);

        $fieldMappings = $metadata->fieldMappings;

        $return = array();
        foreach ($fieldMappings as $key => $fields) {
            $return[$key] = $fields;
            $return[$key]['NULLABLE'] = ($fields['nullable']) ? 1 : 0;
        }

        $associationMappings = $metadata->associationMappings;
        foreach ($associationMappings as $key => $detail) {
            if ($detail['type'] == ClassMetadata::MANY_TO_MANY || $detail['isOwningSide'] == false) {
                continue;
            }

            $return[$key]['fieldName'] = $key;
            $return[$key]['NULLABLE'] = ($detail['joinColumns'][0]['nullable']) ? 1 : 0;
        }

        return $return;
    }

    /**
     * Should preform a query based on the provided by the user
     * select the two fields and return an array $field=>$value
     * as result
     *
     * ex: SELECT $field, $value FROM *
     * array('1'=>'Something','2'=>'Number','3'=>'history')....;
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public function getDistinctValuesForFilters($field, $fieldValue, $order = 'name ASC')
    {
        $return = array();

        $newQuery = clone $this->getQueryBuilder();

        $newQuery->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->setMaxResults(null)
                ->setFirstResult(null);

        $newQuery->select("DISTINCT(" . $field . ") AS field, " . $fieldValue . " AS value")
                ->orderBy($fieldValue, "ASC");


        $result = $newQuery->getQuery()->getResult(Query::HYDRATE_ARRAY);

        foreach ($result as $value) {
            $return[$value['field']] = $value['value'];
        }

        return $return;
    }

    /**
     * Return te field type
     * char, varchar, int
     *
     * Note: If the field is enum or set,
     * the value returned must be set or enum,
     * and not the full definition
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldType($field)
    {
        //check if the type was already fetched
        if (!isset($this->fields[$field]['type'])) {
            //get the field with the alias
            $fieldName = $this->_getFieldName($field);
            //remove an existing alias
            $field = $this->_removeAlias($field);

            $tableModel = $this->_getModelFromColumn($fieldName);

            //fetch data type from metadata
            try {
                if ($tableModel !== null) {
                    $metadata = $this->getEntityManager()->getClassMetadata($tableModel);
                }
            } catch (Exception $e) {
                $this->fields[$field]['type'] = Type::STRING;
            }

            if (isset($metadata->fieldMappings[$field]['type'])) {
                $this->fields[$field]['type'] = $metadata->fieldMappings[$field]['type'];
            } else {
                $this->fields[$field]['type'] = Type::STRING;
            }
        }

        return $this->fields[$field]['type'];
    }

    /**
     * Return possible filters values based on field definition
     * This is mostly used for enum fields where the possibile
     * values are extracted
     *
     * Ex: enum('Yes','No','Empty');
     *
     * should return
     *
     * array('Yes'=>'Yes','No'=>'No','Empty'=>'Empty');
     *
     * @param $field
     * @return string
     */
    public function getFilterValuesBasedOnFieldDefinition($field)
    {
        //since there is no enum support in doctrine 2, we only return 'text'
        return 'text';
    }

    /**
     * Find identifier columns.
     *
     * @return array Primary Keys for specified table
     */
    public function getIdentifierColumns($table = null)
    {
        $return = array();
        if (is_null($table)) {
            $mainTable = $this->getMainTable();
            $table = $mainTable['table'];
        }

        $metadata = $this->getEntityManager()->getClassMetadata($table);
        $identifier = $metadata->identifier;

        $fromPart = $this->getQueryBuilder()->getDQLPart('from');
        $alias = $fromPart[0]->getAlias();

        foreach ($identifier as $id) {
            $return[] = $alias . '.' . $id;
        }

        return $return;
    }

    /**
     * Returns the "main" table
     * the one after SELECT * FROM {MAIN_TABLE}
     *
     * @return array
     */
    public function getMainTable()
    {
        $fromPart = $this->getQueryBuilder()->getDQLPart('from');

        return array('table' => $fromPart[0]->getFrom());
    }

    /**
     * Returns tables primary keys separeted by commas ","
     * This is necessary for mass actions
     * @param $table
     */
    public function getMassActionsIds($table, $fields, $separator = '-')
    {
        $qb = new QueryBuilder($this->getEntityManager());

        if (count($fields) == 0) {
            $metadata = $this->getEntityManager()->getClassMetadata($table);
            $pks = $metadata->identifier;
        } else {
            $pks = $fields;
        }

        //the alias used in this query
        $alias = 'm';
        $qb->from($table, 'm');

        foreach ($pks as $key => $pk) {
            //remove any existing alias and add the one used in this query
            $pk = $this->_removeAlias($pk);
            $pks[$key] = $alias . '.' . $pk;
        }

        $qb->select(implode(',', $pks));

        try {
            $result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
        } catch (Query\QueryException $e) {
            return array();
        }

        $return = array();
        foreach ($result as $value) {
            $return[] = implode($separator, $value);
        }

        return implode(',', $return);
    }

    /**
     * Returns a specified record
     *
     * @param string $table     Table Name
     * @param array  $condition Conditions to build query
     *
     * @return false|array
     */
    public function getRecord($table, array $condition)
    {
        $em = $this->getEntityManager();
        $qb = new QueryBuilder($em);

        $alias = $this->_getNewAlias();
        $newCondition = array();
        //remove alias and set the one used for this query
        foreach ($condition as $fieldName => $value) {
            $field = $this->_removeAlias($fieldName);
            $field = $alias . '.' . $field;
            $newCondition[$field] = $value;
        }

        $metadata = $em->getClassMetadata($table);
        $select = $alias;

        $qb->from($table, $alias);

        //create a query where all fields are contained, even the associations
        foreach ($metadata->associationMappings as $column => $detail) {
            //skip relations where the type is many to many or this side is not the
            //owning side
            if ($detail['type'] == ClassMetadata::MANY_TO_MANY || $detail['isOwningSide'] == false) {
                continue;
            }

            $joinAlias = $this->_getNewAlias();
            $refColumn = $detail['joinColumns'][0]['referencedColumnName'];

            //join the table
            $qb->leftJoin($alias . '.' . $detail['fieldName'], $joinAlias);


            //append primary key from the joined table to the select
            //and use the defined column "AS" the name
            $select .= ', ' . $joinAlias . '.' . $refColumn . ' AS ' . $column;
        }

        $qb->select($select);



        $this->_createWhereConditions($qb, $newCondition);

        try {
            $result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
            $result = $this->_cleanQueryResults($result);
        } catch (Query\QueryException $e) {
            $result[0] = array();
            Zend_Debug::dump($e);
        }

        return $result[0];
    }

    /**
     * Returns the select object
     *
     * @return QueryBuilder
     */
    public function getSelectObject()
    {
        return $this->getQueryBuilder();
    }

    /**
     * Returns the selected order
     * that was defined by the user in the query entered
     * and not the one generated by the system
     *
     * If empty an empty array must be returned.
     *
     * Else the array must be like this:
     *
     * <code>
     * $return = array(
     *     0 => field
     *     1 => (ASC|DESC)
     * );
     * </code>
     *
     * @return array
     */
    public function getSelectOrder()
    {
        $qb = $this->getQueryBuilder();
        $orderBy = $qb->getDqlPart('orderBy');

        if (empty($orderBy)) {
            return array();
        }

        $AST = $qb->getQuery()->getAST();
        $orderByClause = $AST->orderByClause;

        $orderByItem = $orderByClause->orderByItems[0];

        if (is_string($orderByItem->expression)) {
            //the expressin is a string, use the value directly
            $return[0] = $orderByItem->expression;
        } else {
            //use alias and field name
            $return[0] = $orderByItem->expression->identificationVariable . '.' . $orderByItem->expression->field;
        }

        $return[1] = $orderByItem->type;

        return $return;
    }

    /**
     * Return the database driver name.
     *
     * Ex: mysql, pgsql, array, xml
     *
     * @return string
     */
    public function getSourceName()
    {
        $driver = $this->getEntityManager()->getConnection()->getDriver();
        $adapter = str_ireplace('pdo_', '', $driver->getName());

        return strtolower($adapter);
    }

    /**
     * Perform a sqlexp
     *
     * $value =  array ('functions' => array ('AVG'), 'value' => 'Population' );
     *
     * Should be converted to
     * SELECT AVG(Population) FROM *
     *
     * $value =  array ('functions' => array ('SUM','AVG'), 'value' => 'Population' );
     *
     * Should be converted to
     * SELECT SUM(AVG(Population)) FROM *
     *
     * @param array $value
     */
    public function getSqlExp(array $value, $where = array())
    {
        $return = array();

        $qb = $this->getQueryBuilder();
        $qb = clone $qb;

        foreach (array_reverse($value['functions']) as $key => $func) {
            if ($key == 0) {
                $exp = $func . '(' . $value['value'] . ')';
            } else {
                $exp = $func . '(' . $exp . ')';
            }
        }

        $qb->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->setMaxResults(null)
                ->setFirstResult(null)
                ->select($exp . ' AS TOTAL');

        return $qb->getQuery()->getScalarResult();
    }

    public function getTableList()
    {
        throw new Bvb_Grid_Source_Doctrine2_Exception('Not yet implemented.');
    }

    /**
     * Return the total number of records.
     *
     * @return integer
     */
    public function getTotalRecords()
    {
        $qb = $this->getQueryBuilder();
        $qb = clone $qb;

        $qb->setFirstResult(null)
                ->setMaxResults(null)
                ->resetDQLPart('orderBy');

        $AST = $qb->getQuery()->getAST();

        $hasExpr = false;
        foreach ($AST->selectClause->selectExpressions as $selectExpressions) {
            if ($selectExpressions->expression instanceof Query\AST\AggregateExpression) {
                $hasExpr = true;
            }
        }

        if ($hasExpr) {
            $result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
            return count($result);
        }

        $qb->resetDQLPart('select');
        $fromPart = $qb->getDQLPart('from');
        $qb->select('COUNT(DISTINCT ' . $fromPart[0]->getAlias() . ')');

        try {
            $count = $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            //when the result is non unique its most likely that a group by was used
            //if so, we just get the complete result and count the number of results
            //fixes issue #745
            $result = $qb->getQuery()->getResult();
            $count = count($result);
        }
        
        return $count;
    }

    /**
     * @todo Implement
     * @see library/Bvb/Grid/Source/Bvb_Grid_Source_SourceInterface::getValuesForFiltersFromTable()
     */
    public function getValuesForFiltersFromTable($table, $field, $fieldValue, $order = 'name ASC')
    {
        throw new Bvb_Grid_Source_Doctrine2_Exception('Not yet Implemented.');
    }

    /**
     * Simple method for the Grid to determine if this
     * Source can handle CRUD
     *
     * @return boolean
     */
    public function hasCrud()
    {
        return true;
    }

    /**
     * Insert an array of key=>values in the specified table
     *
     * @param string $table
     * @param array $post
     * @return boolean
     */
    public function insert($table, array $post)
    {
        $entity = new $table();

        $post = $this->_setReferences($table, $post);

        $this->_setEntityValues($entity, $post);

        $em = $this->getEntityManager();
        $em->persist($entity);

        $em->flush();
    }

    /**
     *
     * Quotes a string
     *
     * @param string $value Field Value
     */
    public function quoteValue($value)
    {
        return $this->getEntityManager()->getConnection()->quote($value);
    }

    /**
     * Removes any limit in query
     *
     * @return Bvb_Grid_Source_Doctrine
     */
    public function resetLimit()
    {
        $qb = $this->getQueryBuilder();
        $qb->setMaxResults(null)
                ->setFirstResult(null);

        return $this;
    }

    /**
     * Removes any order in query
     *
     * @return Bvb_Grid_Source_Doctrine
     */
    public function resetOrder()
    {
        $this->getQueryBuilder()->resetDQLPart('orderBy');

        return $this;
    }

    /**
     * Cache handler.
     *
     * @param Zend_Cache
     */
    public function setCache($cache)
    {
        
    }

    /**
     * Update values in a table using the $condition clause
     *
     * The condition clause is a $field=>$value array
     *
     * Ex: array('user_id'=>'1','id_site'=>'12');
     *
     * Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $post
     * @param array $condition
     */
    public function update($table, array $post, array $condition)
    {
        $em = $this->getEntityManager();
        $newCondition = array();
        foreach ($condition as $fieldName => $value) {
            $field = $this->_removeAlias($fieldName);
            $newCondition[$field] = $value;
        }

        $post = $this->_setReferences($table, $post);

        $entity = $em->getRepository($table)->findOneBy($newCondition);

        $this->_setEntityValues($entity, $post);

        $em->persist($entity);
        $em->flush();

        return $this;
    }

    /**
     * Creates for association fields a reference instead of the id itself.
     *
     * @param string $table
     * @param array $post
     */
    private function _setReferences($table, array $post)
    {
        $em = $this->getEntityManager();
        $metadata = $em->getClassMetadata($table);

        foreach ($post as $fieldName => $value) {
            if (isset($metadata->associationMappings[$fieldName])) {
                if (empty($value)) {
                    $post[$fieldName] = NULL;
                } else {
                    $targetEntity = $metadata->associationMappings[$fieldName]['targetEntity'];
                    $post[$fieldName] = $em->getRepository($targetEntity)->find($value);
                }
            }
        }

        return $post;
    }

    public function getAutoCompleteForFilter($term, $field, $specialKey = '', $output = 'json')
    {
        $qb = $this->getQueryBuilder();
        $qb = clone $qb;

        $fieldName = $this->_getFieldName($field);
        //clear where part and bound parameters
        $qb->resetDQLPart('where');
        $qb->setParameters(array());
        foreach ($this->whereConditions as $key => $detail) {
            if ($key != $fieldName) {
                $this->_addCondition($qb, $key, $detail['op'], $detail['filter']);
            }
        }

        //add condition for the given field
        $op = ($specialKey != '') ? $specialKey : 'like';
        $this->_addCondition($qb, $fieldName, $op, $term);

        $qb->select('DISTINCT ' . $fieldName);

        $result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);

        $result = $this->_cleanQueryResults($result);

        $json = array();
        foreach ($result as $row) {
            $json[] = $specialKey . $row[$field];
        }

        echo Zend_Json::encode($json);
        die();
    }

    /**
     * Defines total records
     *
     * @param int $total Total records found
     */
    public function setTotalRecords($total)
    {
        $this->_totalRecords = (int) $total;
    }

    /**
     * Clean a query result.
     *
     * Doctrine2 uses a DateTime object for date/datetime types.
     * Hence the grid can not use them correctly, this types will be convertet to a string.
     *
     * @param array $result
     * @return array
     */
    private function _cleanQueryResults(array $result)
    {
        $newResult = array();
        foreach ($result as $key => $values) {
            foreach ($values as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $newResult[$key][$k] = $this->_convertResult($k, $v);
                    }
                } else {
                    $newResult[$key][$name] = $this->_convertResult($name, $value);
                }
            }
        }

        return $newResult;
    }

    private function _getNewAlias()
    {
        $alias = chr($this->alias);
        $this->alias++;

        return $alias;
    }

    private function _getNewParameterNumber()
    {
        return $this->paramterNumber++;
    }

    /**
     * Convert datetime object to readable strings and booleans to 0/1.
     *
     * @param string $field The fieldname.
     * @param mixed $value The value to be converted.
     * @return mixed The converted value.
     */
    private function _convertResult($field, $value)
    {
        if ($value instanceof DateTime) {
            $type = $this->getFieldType($field);

            //format the field depending on the field type
            if ($type == Type::DATE) {
                return $value->format('Y-m-d');
            } elseif ($type == Type::DATETIME) {
                return $value->format('Y-m-d H:i');
            }
        } elseif (is_bool($value)) {
            //convert boolean types to integer, so the default value is correctly set
            //in the form element
            return ($value) ? 1 : 0;
        }

        return $value;
    }

    /**
     * Sets all values in an entity.
     *
     * @param object $entity
     * @param array $values
     */
    private function _setEntityValues($entity, array $values)
    {
        foreach ($values as $key => $value) {
            $method = 'set' . ucfirst($key);
            $type = $this->getFieldType($key);

            //if the column is from type date or datetime, create a DateTime object
            if ($type == Type::DATE || $type == Type::DATETIME) {
                $value = new DateTime($value);
            }


            if (method_exists($entity, $method)) {
                $entity->$method($value);
            } else {
                $method = preg_replace_callback('/(_\w{1})/i', create_function(
                                '$matches', 'return strtoupper($matches[0]);'
                        ), $method);
                $method = str_replace("_", "", $method);
                if (method_exists($entity, $method)) {
                    $entity->$method($value);
                }
            }
        }
    }

    /**
     * Find the table for which a column belongs.
     *
     * @param string $column
     * @return string Name of the table used
     */
    private function _getModelFromColumn($column)
    {
        if (!is_string($column)) {
            $type = gettype($column);
            require_once 'Bvb/Grid/Source/Doctrine/Exception.php';
            throw new Bvb_Grid_Source_Doctrine2_Exception('The $column param needs to be a string, ' . $type . ' provided');
        }

        if (strpos($column, '.') === false) {
            return null;
        }

        list($alias, $field) = explode('.', $column);

        return $this->_getModelFromAlias($alias);
    }

    /**
     * Finds a model for which an alias belongs.
     *
     * @param string $alias
     * @return string The name of the entity.
     */
    private function _getModelFromAlias($alias)
    {
        $qb = $this->getQueryBuilder();
        $fromParts = $qb->getDQLPart('from');

        //first try to get the model from the from part
        foreach ($fromParts as $fromPart) {
            if ($fromPart->getAlias() == $alias) {
                return $fromPart->getFrom();
            }
        }

        //when the from part doesnt have it, we first find the join field defined
        //by the alias
        $AST = $qb->getQuery()->getAST();

        $field = null;
        foreach ($AST->fromClause->identificationVariableDeclarations[0]->joinVariableDeclarations as $joinVariable) {
            if ($alias == $joinVariable->join->aliasIdentificationVariable) {
                $field = $joinVariable->join->joinAssociationPathExpression->associationField;
                break;
            }
        }
        if (is_null($field)) {
            throw new Bvb_Grid_Source_Doctrine2_Exception("No field found.");
        }

        //iterate over the fromparts, get the metadata from it and
        //iterate then over the association mappings to find the specific
        //model for the alias
        foreach ($fromParts as $fromPart) {
            $metadata = $this->getEntityManager()->getClassMetadata($fromPart->getFrom());
            foreach ($metadata->associationMappings as $mapping) {
                if ($mapping['fieldName'] == $field) {
                    return $mapping['targetEntity'];
                }
            }
        }

        throw new Bvb_Grid_Source_Doctrine2_Exception("No model found.");
    }

    /**
     * Creates the where conditions for a query builder.
     *
     * @param QueryBuilder $qb
     * @param array $where
     * @return QueryBuilder
     */
    private function _createWhereConditions(QueryBuilder $qb, array $where)
    {
        foreach ($where as $column => $value) {
            $this->_addCondition($qb, $column, '=', $value);
        }

        return $qb;
    }

    /**
     * Removes the alias from the given field.
     *
     * @param string $field
     * @return string field withou alias
     */
    private function _removeAlias($field)
    {
        if (strpos($field, '.') !== false) {
            list($alias, $column) = explode('.', $field);
        } else {
            $column = $field;
        }

        return $column;
    }

    /**
     * Retrieves the complete fieldname of a column (with the alias).
     *
     * @param string $field
     * @return string Complete fieldname
     */
    private function _getFieldName($field)
    {
        if (strpos($field, '.') === false) {
            if (!isset($this->fields[$field]['field'])) {
                $ids = $this->getIdentifierColumns();
                list($alias, $field) = explode('.', $ids[0]);

                //no matching field was found in the fields array
                //so we append the default alias to the field
                return $alias . '.' . $field;
            }
            return $this->fields[$field]['field'];
        }

        return $field;
    }

    public function beginTransaction()
    {
        return false;
    }

    public function commit()
    {
        return false;
    }

    public function rollBack()
    {
        return false;
    }

    public function getConnectionId()
    {
        return 0;
    }

}