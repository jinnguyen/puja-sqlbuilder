# sqlbuilder
SqlBuilder allows users to build quickly and easily complex.

Install:
- With composer:
<pre>composer require jinnguyen/puja-sqlbuilder</pre>
Usage:
<pre>
    require_once 'path/to/vendor/autoload.php';
    use Puja\SqlBuilder\Builder;
    $builder = new Builder('php_');
</pre>


<strong>Examples</strong><br />
<strong>SELECT</strong>
<pre>
$select = $builder->select()
    ->from(array('c' => 'content'), array('id' => 'content_id'))
    ->from(array('ln' => 'content_ln'), array('title' => 'name', 'iso2_code'))
    ->joinLeft('category', 'category.category_id=content.category_id', array('name', 'category_id'))
    ->order('content.content_id') ->order('category.category_id', Builder::ORDER_DESC) ->limit(10)
    ->having('content.content_id=%d', 10) ->groupBy('content.content_id')
    ->where('c.content_id=%d AND ln.name LIKE "%%%s%%"', 4, 'search term')
    ->orWhere('category.name IS EMPTY AND category.category_id >= %d', 5);

echo 'Query:' . $select->getQuery();
echo 'Count:' . $select->getCount();</pre>

Result:
<pre>
Query:SELECT c.content_id AS id,ln.name AS title,ln.iso2_code,category.name,category.category_id FROM content AS c,content_ln AS ln LEFT JOIN category AS category ON category.category_id=content.category_id WHERE (c.content_id=4 AND ln.name LIKE "%search term%") OR (category.name IS EMPTY AND category.category_id >= 5) GROUP BY content.content_id HAVING (content.content_id=10) ORDER BY content.content_id ,category.category_id DESC LIMIT 0,10
Count:SELECT COUNT(*) AS total FROM content AS c,content_ln AS ln LEFT JOIN category AS category ON category.category_id=content.category_id WHERE (c.content_id=4 AND ln.name LIKE "%search term%") OR (category.name IS EMPTY AND category.category_id >= 5) GROUP BY content.content_id HAVING (content.content_id=10)
</pre>

<strong>INSERT</strong>
<pre>
$select = $builder->reset()->insert('content', array('name' => 'Jin', 'addtime__exact' => 'NOW()'));
echo $select->getQuery();
</pre>

Result:
<pre>
INSERT INTO content(`name`,`addtime`) VALUES ("Jin", NOW())
</pre>

<strong>UPDATE</strong>
<pre>
$select = $builder->reset()->update('content', array('name' => 'Jin', 'addtime__exact' => 'NOW()'))
    ->where('content_id=%d', 5);
echo $select->getQuery();</pre>

Result:
<pre>UPDATE content SET `name`="Jin",`addtime`=NOW() WHERE (content_id=5)</pre>

<strong>REPLACE</strong>
<pre>
$select = $builder->reset()->replace('content', array('name' => 'Jin', 'addtime__exact' => 'NOW()'))
    ->where('content_id=%d', 5);
echo $select->getQuery();</pre>

Result:
<pre>REPLACE INTO content(`name`,`addtime`) VALUES ("Jin", NOW())</pre>

<strong>DELETE</strong>
<pre>
$select = $builder->reset()->delete('content')->where('content_id=%d', 5);
echo $select->getQuery();</pre>

Result:
<pre>DELETE FROM content WHERE (content_id=5)</pre>
