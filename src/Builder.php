<?php
namespace Puja\SqlBuilder;
class Builder
{
	const JOIN_LEFT = 'LEFT JOIN';
	const JOIN_RIGHT = 'RIGHT JOIN';
	const JOIN_INNER = 'INNER JOIN';
    const OPERATION_AND = 'AND';
    const OPERATION_OR = 'OR';
    const QUERYTYPE_SELECT = 'SELECT';
    const QUERYTYPE_UPDATE = 'UPDATE';
    const QUERYTYPE_DELETE = 'DELETE';
    const QUERYTYPE_INSERT = 'INSERT';
    const QUERYTYPE_INSERT_IGNORE = 'INSERT IGNORE';
    const QUERYTYPE_REPLACE = 'REPLACE';
    const QUERYTYPE_TRUNCATE = 'TRUNCATE TABLE';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    protected $limit;
    protected $offset;
    protected $joins;
    protected $fields;
    protected $fromTable;
    protected $queryType;
    protected $tablePrefix;
    protected $wheres;
    protected $orderBy;
    protected $groupBy;
    protected $having;

    public function __construct($tblPrefix = '')
    {
        $this->tablePrefix = $tblPrefix;
        $this->reset();
    }
    /**
     * Reset query before run new select, update, insert,...
     * @return \Puja\SqlBuilder\Builder
     */
    public function reset()
    {
        $this->queryType = null;
        $this->offset = 0;
        $this->limit = null;
        $this->joins = array();
        $this->fields = array();
        $this->fromTable = null;
        $this->wheres = array();
        $this->orderBy = null;
        $this->groupBy = array();
        $this->having = array();
        return $this;
    }

    /**
     * Set from table and select fields
     * @param mixed $table
     * @param array $selectFields
     * @return \Puja\SqlBuilder\Builder
     */
    public function from($table, $selectFields = array('*'))
    {
    	list($tableName, $tableAlias) = $this->tableProcess($table);
        $this->fromTable[$tableAlias] = $tableName;
        if (empty($selectFields)) {
            return $this;
        }
        if ($this->queryType == self::QUERYTYPE_SELECT) {
            $this->fields[$tableAlias] = $this->fieldProcess($tableAlias, $selectFields);
        } else {
            $this->fields[$tableAlias] = $selectFields;
        }
        return $this;
    }

    /**
     * Set limit and offset
     * @param int $offset
     * @param int $limit
     * @throws Exception
     * @return \Puja\SqlBuilder\Builder
     */
    public function limit($offset = null, $limit = null)
    {
    	$args = func_get_args();
    	if (count($args) === 0) {
    		throw new Exception('At least 1 argument');
    	}
    	if (count($args) == 1) {
    		$this->limit =  $args[0];
    	} else {
    		list($this->offset, $this->limit) = $args;
    	}
        return $this;
    }
	
    /**
     * Set select stament
     * @param mixed $table
     * @param array $selectFields
     * @return \Puja\SqlBuilder\Builder
     */
    
    public function select($table = null, $selectFields = array('*'))
    {
    	$this->queryTypeProcess(self::QUERYTYPE_SELECT, $table, $selectFields);
        return $this;
    }
    
    /**
     * Set update stament
     * @param string $table
     * @param array $updateFields
     * @return \Puja\SqlBuilder\Builder
     */
    public function update($table, array $updateFields)
    {
        $this->queryTypeProcess(self::QUERYTYPE_UPDATE, $table, $updateFields);
        return $this;
    }
	
    /**
     * Set insert stament
     * @param string $table
     * @param array $updateFields
     * @return \Puja\SqlBuilder\Builder
     */
    public function insert($table, array $updateFields)
    {
        $this->queryTypeProcess(self::QUERYTYPE_INSERT, $table, $updateFields);
        //$this->fields = $updateFields;
        return $this;
    }

    /**
     * Set insert ignore stament
     * @param string $table
     * @param array $updateFields
     * @return \Puja\SqlBuilder\Builder
     */
    public function insertIgnore($table, array $updateFields)
    {
        $this->queryTypeProcess(self::QUERYTYPE_INSERT_IGNORE, $table, $updateFields);
        //$this->fields = $updateFields;
        return $this;
    }

    /**
     * Set replace stament
     * @param string $table
     * @param array $updateFields
     * @return \Puja\SqlBuilder\Builder
     */
    public function replace($table, array $updateFields)
    {
        $this->queryTypeProcess(self::QUERYTYPE_REPLACE, $table, $updateFields);
        //$this->fields = $updateFields;
        return $this;
    }

    /**
     * @param $table
     * @return \Puja\SqlBuilder\Builder
     */
    public function truncate($table)
    {
        $this->queryTypeProcess(self::QUERYTYPE_TRUNCATE, $table);
        return $this;
    }

    /**
     * Set delete stament
     * @param string $table
     * @return \Puja\SqlBuilder\Builder
     */
    public function delete($table)
    {
        $this->queryTypeProcess(self::QUERYTYPE_DELETE, $table);
        return $this;
    }

    public function joinLeft($table, $joinCond, array $selectFields = array('*'))
    {
        $this->joinProcess(self::JOIN_LEFT, $table, $joinCond, $selectFields);
        return $this;
    }

    public function joinRight($table, $joinCond, array $selectFields = array('*'))
    {
        $this->joinProcess(self::JOIN_RIGHT, $table, $joinCond, $selectFields);
        return $this;
    }

    public function joinInner($table, $joinCond, array $selectFields = array('*'))
    {
    	$this->joinProcess(self::JOIN_INNER, $table, $joinCond, $selectFields);
        return $this;
    }
	/**
	 * Set where AND
	 * @param string $cond
	 * @return \Puja\SqlBuilder\Builder
	 */
    public function where($cond)
    {
        $this->wheres[] = $this->whereProcess(self::OPERATION_AND, func_get_args());
        return $this;
    }
    
    /**
     * Set where OR
     * @param string $cond
     * @return \Puja\SqlBuilder\Builder
     */
    public function orWhere($cond)
    {
        $this->wheres[] = $this->whereProcess(self::OPERATION_OR, func_get_args());
        return $this;
    }

    /**
     * @param $field
     * @return \Puja\SqlBuilder\Builder
     */
    public function groupBy($field)
    {
        $this->groupBy[] = $field;
        return $this;
    }

    public function having($cond)
    {
        $this->having[] = $this->whereProcess(self::OPERATION_AND, func_get_args());
        return $this;
    }
    
    public function order($order, $direction = null)
    {
        if ($order) {
            $this->orderBy[] = $this->orderProcess($order, $direction);
        }
    	return $this;
    }

    public function getCount($removeCurrentLimit = true, $aliasCountField = 'total')
    {
        if ($this->queryType != self::QUERYTYPE_SELECT) {
            throw new Exception('\Puja\SqlBuilder\Builder::getCount is only used for SELECT statment');
        }

        return $this->buildQuerySelect(false, !$removeCurrentLimit, 'COUNT(*) AS ' . $aliasCountField);
    }

    public function getQuery()
    {
        switch ($this->queryType) {
            case self::QUERYTYPE_SELECT:
                $sql = $this->buildQuerySelect();
                break;
            case self::QUERYTYPE_UPDATE:
                $sql = $this->buildQueryUpdate();
                break;
            case self::QUERYTYPE_DELETE:
                $sql = $this->buildQueryDelete();
                break;
            case self::QUERYTYPE_INSERT:
            case self::QUERYTYPE_INSERT_IGNORE:
            case self::QUERYTYPE_REPLACE:
                $sql = $this->buildQueryInsert();
                break;
            case self::QUERYTYPE_TRUNCATE:
                $sql = $this->buildQueryTruncate();
                break;
            default:
                throw new Exception('Dont support query type: ' . $this->queryType);
                break;
        }


        //reset
        return $sql;
    }

    protected function buildQuerySelect($addOrderBy = true, $addLimit = true, $forceSelectField = null)
    {
        $sql = $this->queryType;
        if ($forceSelectField) {
            $sql .= ' ' . $forceSelectField;
        } else {
            $sql .= $this->buildPartFields();
        }
        $sql .= ' FROM ' . implode(',', $this->fromTable);
        $sql .= $this->buildPartJoin();
        $sql .= $this->buildPartWhere();
        $sql .= $this->buildPartGroupBy();
        $sql .= $this->buildPartHaving();

        if ($addOrderBy) {
            $sql .= $this->buildPartOrderBy();
        }

        if ($addLimit) {
            $sql .= $this->buildPartLimit();
        }

        return $sql;
    }

    protected function buildQueryUpdate()
    {
        $sql = $this->queryType;
        $sql .= $this->buildPartFrom(false, ' ');
        $sql .= $this->buildPartJoin();
        $sql .= ' SET ' . $this->fieldUpdateProcess();
        $sql .= $this->buildPartWhere();
        $sql .= $this->buildPartLimit();

        return $sql;
    }

    protected function buildQueryDelete()
    {

        $sql = $this->queryType;
        $sql .= $this->buildPartFrom(false);
        $sql .= $this->buildPartWhere();
        $sql .= $this->buildPartLimit();

        return $sql;
    }

    protected function buildQueryTruncate()
    {

        $sql = $this->queryType;
        $sql .= $this->buildPartFrom(false, ' ');

        return $sql;
    }

    protected function buildQueryInsert()
    {
        $insert = $this->fieldInsertProcess($this->fields);
        $sql = $this->queryType . ' INTO ' . implode(',', $this->fromTable) . '(' . implode(',', array_keys($insert)) . ')';
        $sql .= ' VALUES (' . implode(', ', $insert) . ')';

        return $sql;
    }

    protected function buildPartOrderBy()
    {
        $orderBy = null;
        if (!empty($this->orderBy)) {
            $orderBy = ' ORDER BY ' . implode(',', $this->orderBy);
        }

        return $orderBy;
    }

    protected function buildPartFrom($combineFromTables = true, $fromPrefix = ' FROM ')
    {
        if (empty($this->fromTable)) {
            throw new Exception('Must select from at least 1 table');
        }

        if ($combineFromTables) {
            return $fromPrefix . ' (' . implode(',', $this->fromTable) . ')';
        }

        return $fromPrefix . current($this->fromTable);
    }

    protected function buildPartFields()
    {
        if (empty($this->fields)) {
            throw new Exception('Must select at least 1 field in query');
        }

        return ' ' . implode(',', $this->fields);
    }

    protected function buildPartJoin()
    {
        $join = null;
        if (!empty($this->joins)) {
            $join = ' ' . implode(' ', $this->joins);
        }
        return $join;
    }

    protected function buildPartWhere()
    {
        $where = null;
        if (!empty($this->wheres)) {
            $where = ' WHERE ' . implode(' ', $this->wheres);
        }
        return $where;
    }

    protected function buildPartLimit()
    {
        $limit = null;
        if ($this->limit !== null) {
            $limit = ' LIMIT ' . $this->offset . ',' . $this->limit;
        }
        return $limit;
    }

    protected function buildPartGroupBy()
    {
        $groupBy = null;
        if (!empty($this->groupBy)) {
            $groupBy = ' GROUP BY ' . implode( ' ', $this->groupBy);
        }
        return $groupBy;
    }

    protected function buildPartHaving()
    {
    	
        $having = null;
        if (!empty($this->having)) {
        	if (empty($this->groupBy)) {
        		throw new Exception('HAVING is only used in case the query have GROUP BY');
        	}
            $having = ' HAVING ' . implode( ' ', $this->having);
        }

        return $having;
    }

    protected function joinProcess($joinType, $table, $joinCond, $selectFields)
    {
    	list($tableName, $tableAlias) = $this->tableProcess($table);
    	$this->joins[] = $joinType . ' ' . $tableName . ' ON ' . $joinCond;
        if (!empty($selectFields) && $this->queryType == self::QUERYTYPE_SELECT) {
            $this->fields[] = $this->fieldProcess($tableAlias, $selectFields);
        }
    }

    protected function tableProcess($table)
    {
    	if (is_array($table)) {
    		reset($table);
    		$tableName = $this->tablePrefix . current($table) . ' AS ' . key($table);
    		$tableAlias = key($table);
    	} else {
            if ($this->queryType == self::QUERYTYPE_SELECT) {
                $tableName = $this->tablePrefix . $table . ' AS ' . $table;
    		    $tableAlias = $table;
            } else {
                $tableName = $this->tablePrefix . $table;
                $tableAlias = null;
            }

    	}
    	
    	return array($tableName, $tableAlias);
    }

    protected function fieldProcess($table, $fields)
    {
        $return = array();

        foreach ($fields as $alias => $field) {
            if (is_numeric($alias)) {
                $return[] = $table . '.' . $field;
            } else {
            	if (substr($alias, -7) == '__exact') {
            		$return[] =  $field . ' AS ' . substr($alias, 0, -7);
            	} else {
            		$return[] = $table . '.' . $field . ' AS ' . $alias;
            	}

            }
        }
    	return implode(',', $return);
    }

    protected function buildFieldExtra($field, $value)
    {
        $result = array();
        list($key, $extra) = explode('__', $field . '__');
        switch ($extra) {
            case 'exact':
                $result = array("`{$key}`", $value, '=');
                break;
            case 'increase':
                $result = array("`{$key}`", "`{$key}`" . '+' . $value, '=');
                break;
            case 'int':
                $result = array("`{$key}`", (int) $value, '=');
                break;
            case 'float':
                $result = array("`{$key}`", (float) $value, '=');
                break;
            case 'bool':
                $result = array("`{$key}`", (bool) $value, '=');
                break;
            case 'null':
                $result = array("`{$key}`", 'NULL', '=');
                break;
            case 'gt':
                $result = array("`{$key}`", $value, '>');
                break;
            case 'gte':
                $result = array("`{$key}`", $value, '>=');
                break;
            case 'lt':
                $result = array("`{$key}`", $value, '<');
                break;
            case 'lte':
                $result = array("`{$key}`", $value, '<=');
                break;
            case 'diff':
                $result = array("`{$key}`", $value, '!=');
                break;
            default:
                $result = array("`{$key}`", '"' . addslashes($value) . '"', '=');
                break;

        }
        return $result;
    }

    protected function fieldUpdateProcess()
    {
        $return = array();
        $table = key($this->fields);

        foreach ($this->fields[$table] as $field => $value) {
            list($key, $val, $operator) = $this->buildFieldExtra($field, $value);
            if ($table) {
                $return[] = $table . '.' . $key . $operator . $val;
            } else {
                $return[] =  $key . '=' . $val;
            }
        }

        return implode(',', $return);
    }

    protected function fieldInsertProcess($fields)
    {
        $return = array();
        $table = key($this->fields);

        foreach ($this->fields[$table] as $field => $value) {
            list($key, $val) = $this->buildFieldExtra($field, $value);
            $return[$key] = $val;
        }

        return $return;
    }

    protected function queryTypeProcess($type, $table = null, $selectFields = null)
    {
        if ($this->queryType) {
            throw new Exception('Must reset before run new query');
        }

        if ($table) {
            $this->from($table, $selectFields);
        }

        $this->queryType = $type;
    }

    protected function whereProcess($operation, $args)
    {
        $cond = $args[0];
        if (is_string($cond)) {
            if (count($args) > 1) {
                $cond = vsprintf($cond, array_slice($args, 1));
            }
        } else {
            // array criteria
            foreach ($cond as $key => $value) {
                list($field, $fieldValue, $op) = $this->buildFieldExtra($key, $value);
                $cond[$key] = $field . $op . $fieldValue;
            }
            $cond = implode( ' AND ', $cond);
        }


        return (empty($this->wheres) ? null : $operation . ' ') . '(' . $cond . ')';
    }

    protected function orderProcess($order, $direction)
    {
        return $order . ' ' . $direction;
    }

}
