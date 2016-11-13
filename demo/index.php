<?php
include '../vendor/autoload.php';
use Puja\SqlBuilder\Builder;

$builder = new Builder();
$select = $builder->select()
    ->from(array('c' => 'content'), array('id' => 'content_id'))
    ->from(array('ln' => 'content_ln'), array('title' => 'name', 'iso2_code'))
    ->joinLeft('category', 'category.category_id=content.category_id', array('name', 'category_id'))
    ->order('content.content_id') ->order('category.category_id', Builder::ORDER_DESC) ->limit(10)
    ->having('content.content_id=%d', 10) ->groupBy('content.content_id')
    ->where('c.content_id=%d AND ln.name LIKE "%%%s%%"', 4, 'search term')
    ->orWhere('category.name IS EMPTY AND category.category_id >= %d', 5);
echo '<pre>Query:' . $select->getQuery() . '</pre>';
echo '<pre>Count:' . $select->getCount() . '</pre>';

$select = $builder->reset()->insert('content', array('name' => 'Jin', 'addtime__exact' => 'NOW()'));
echo '<pre>Insert:' . $select->getQuery() . '</pre>';

$select = $builder->reset()->update('content', array('name' => 'Jin', 'addtime__exact' => 'NOW()'))
    ->where('content_id=%d', 5);
echo '<pre>Query:' . $select->getQuery() . '</pre>';


$select = $builder->reset()->replace('content', array('name' => 'Jin', 'addtime__exact' => 'NOW()'))
    ->where('content_id=%d', 5);
echo '<pre>Replace:' . $select->getQuery() . '</pre>';

$select = $builder->reset()->delete('content')->where(array('id' => 'id-string'));
echo '<pre>Delete:' . $select->getQuery() . '</pre>';

$select = $builder->reset()->truncate('content');
echo '<pre>Truncate:' . $select->getQuery() . '</pre>';
