<?php
require 'src/Autoload.php';
require 'BaseTest.php';

use Puja\SqlBuilder\Builder;

class BuilderTest extends BaseTest
{
	
	/**
	 * @dataProvider testConstructData
	 */
	public function testConstruct($tblPrefix)
	{
		$builder = new Builder($tblPrefix);
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'limit'));
		$this->assertEquals(0, $this->getPrivateProperty($builder, 'offset'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'joins'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'fields'));
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'fromTable'));
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'queryType'));
		$this->assertEquals($tblPrefix, $this->getPrivateProperty($builder, 'tablePrefix'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'wheres'));
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'orderBy'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'groupBy'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'having'));
		
	}
	
	public function testConstructData()
	{
		$data = array();
		$data['with_tbl_prefix'] = ['tbl_prefix'];
		$data['without_tbl_prefix'] = [null];
		return $data;
	
	}
	
	public function testReset()
	{
		$builder = new Builder();
		$builder->select('testtbl', ['name', 'intro'])
			->limit(1,10)
			->joinLeft('testtbl2', 'cond')
			->where('where cond')
			->order('name asc')
			->groupBy('content_id')
			->having('having cond');
		$builder->reset();
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'limit'));
		$this->assertEquals(0, $this->getPrivateProperty($builder, 'offset'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'joins'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'fields'));
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'fromTable'));
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'queryType'));
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'tablePrefix'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'wheres'));
		$this->assertEquals(null, $this->getPrivateProperty($builder, 'orderBy'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'groupBy'));
		$this->assertEquals(array(), $this->getPrivateProperty($builder, 'having'));
	}
	
	/**
	 * @dataProvider testFromData
	 */
	public function testFrom($fromTbl, $fromExpected, $selectFields, $fieldsExpected)
	{
		$builder = new Builder();
		$builder->reset()->from($fromTbl, $selectFields);
		
		$fromTblActual = $this->getPrivateProperty($builder, 'fromTable');
		$this->assertEquals($fromExpected, $fromTblActual);
		
		$selectFieldsActual = $this->getPrivateProperty($builder, 'fields');
		$this->assertEquals($fieldsExpected, $selectFields);
		
	}
	
	public function testFromData()
	{
		$data = array();
		// [ <table>, <from result>, <field>, <field expected>]
		$data['Set table without alias 1'] = array(
				'content', array('' => 'content'),
				array('*'), array('*')
		);
		
		$data['Set table with alias 1'] = array(
				array('cc' => 'content'), array('cc' => 'content AS cc'),
				array('cc' => 'content_id'), array('cc' => 'content_id')
		);
		
		$data['Set table with alias 2'] = array(
				array('cc' => 'content'), array('cc' => 'content AS cc'),
				array('content_id'), array('content_id')
		);
		return $data;
	}
	
	public function testGetCount()
	{
		$builder = new Builder();
		$builder->reset()->select('tbname')->limit(0, 10);
		$countQuery = $builder->getCount();
		$this->assertEquals('SELECT COUNT(*) AS total FROM tbname', $countQuery);
		
		$countQuery = $builder->getCount(true, 'number');
		$this->assertEquals('SELECT COUNT(*) AS number FROM tbname', $countQuery);
		
		$countQuery = $builder->getCount(false);
		$this->assertEquals('SELECT COUNT(*) AS total FROM tbname LIMIT 0,10', $countQuery);
		
		$countQuery = $builder->getCount(false, 'number');
		$this->assertEquals('SELECT COUNT(*) AS number FROM tbname LIMIT 0,10', $countQuery);
	}
	
	public function testGetQuery()
	{
		$builder = new Builder();
		$builder->reset()->select()->from('tbname');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname', $query);
		
		$builder->reset()->select()->from('tbname')->joinInner('tbljoin', 'tbljoin_id=tbname_id');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.*,tbljoin.* FROM tbname AS tbname INNER JOIN tbljoin AS tbljoin ON tbljoin_id=tbname_id', $query);
		
		$builder->reset()->select()->from('tbname')->joinLeft('tbljoin', 'tbljoin_id=tbname_id');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.*,tbljoin.* FROM tbname AS tbname LEFT JOIN tbljoin AS tbljoin ON tbljoin_id=tbname_id', $query);
		
		$builder->reset()->select()->from('tbname')->joinRight('tbljoin', 'tbljoin_id=tbname_id');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.*,tbljoin.* FROM tbname AS tbname RIGHT JOIN tbljoin AS tbljoin ON tbljoin_id=tbname_id', $query);
		
		$builder->reset()->select()->from('tbname')->groupBy('fieldA');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname GROUP BY fieldA', $query);
		
		$builder->reset()->select()->from('tbname')->groupBy('fieldA')->having('fieldA > 0');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname GROUP BY fieldA HAVING (fieldA > 0)', $query);
		
		// check with exception
		//$builder->reset()->select()->from('tbname')->having('fieldA > 0');
		//$query = $builder->getQuery();
		
		$builder->reset()->select()->from('tbname')->limit(0, 10);
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname LIMIT 0,10', $query);
		
		$builder->reset()->select()->from('tbname')->order('fieldA', 'DESC');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname ORDER BY fieldA DESC', $query);
		
		$builder->reset()->select()->from('tbname')->where('fieldA > 0')->where('fieldB > 0');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname WHERE (fieldA > 0) AND (fieldB > 0)', $query);
		
		$builder->reset()->select()->from('tbname')->where('fieldA > 0')->orWhere('fieldB > 0');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname WHERE (fieldA > 0) OR (fieldB > 0)', $query);
		
		$builder->reset()->delete('tbname')->where('fieldA > 0')->orWhere('fieldB > 0');
		$query = $builder->getQuery();
		$this->assertEquals('DELETE FROM tbname WHERE (fieldA > 0) OR (fieldB > 0)', $query);
		
		$builder->reset()->insert('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()'));
		$query = $builder->getQuery();
		$this->assertEquals('INSERT INTO tbname(`fieldA`,`fieldB`) VALUES ("1", NOW())', $query);
		
		$builder->reset()->insertIgnore('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()'));
		$query = $builder->getQuery();
		$this->assertEquals('INSERT IGNORE INTO tbname(`fieldA`,`fieldB`) VALUES ("1", NOW())', $query);
		
		$builder->reset()->replace('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()'));
		$query = $builder->getQuery();
		$this->assertEquals('REPLACE INTO tbname(`fieldA`,`fieldB`) VALUES ("1", NOW())', $query);
		
		$builder->reset()->update('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()'))->where('fieldC = 0');
		$query = $builder->getQuery();
		$this->assertEquals('UPDATE tbname SET `fieldA`="1",`fieldB`=NOW() WHERE (fieldC = 0)', $query);
	}
	
	public function testDelete()
	{
		$builder = new Builder();
	
		$builder->reset()->delete('tbname');
		$actualQuery = $builder->getQuery();
		$this->assertEquals('DELETE FROM tbname', $actualQuery);
	
		$builder->reset()->delete('tbname')->where('fieldA = 0');
		$actualQuery = $builder->getQuery();
		$this->assertEquals('DELETE FROM tbname WHERE (fieldA = 0)', $actualQuery);
	}
	
	public function testInsert()
	{
		$builder = new Builder();
		$builder->reset()->insert('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()'));
		$query = $builder->getQuery();
		$this->assertEquals('INSERT INTO tbname(`fieldA`,`fieldB`) VALUES ("1", NOW())', $query);
	}
	
	public function testInsertIgnore()
	{
		$builder = new Builder();
		$builder->reset()->insertIgnore('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()'));
		$query = $builder->getQuery();
		$this->assertEquals('INSERT IGNORE INTO tbname(`fieldA`,`fieldB`) VALUES ("1", NOW())', $query);
	}
	
	public function testReplace()
	{
		$builder = new Builder();
		$builder->reset()->replace('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()'));
		$query = $builder->getQuery();
		$this->assertEquals('REPLACE INTO tbname(`fieldA`,`fieldB`) VALUES ("1", NOW())', $query);
	}
	
	public function testUpdate()
	{
		$builder = new Builder();
		$builder->reset()->update('tbname', array('fieldA' => 1, 'fieldB__exact' => 'NOW()', 'fieldD__increase' => 1))->where('fieldC = 0');
		$query = $builder->getQuery();
		$this->assertEquals('UPDATE tbname SET `fieldA`="1",`fieldB`=NOW(),`fieldD`=`fieldD`+1 WHERE (fieldC = 0)', $query);
	}
	
	public function testGroupBy()
	{
		$builder = new Builder();
		$builder->reset()->select()->from('tbname')->groupBy('fieldA');
		$query = $builder->getQuery();
		$this->assertEquals('SELECT tbname.* FROM tbname AS tbname GROUP BY fieldA', $query);
	}
	
	public function testSelect()
	{
		
	}
}