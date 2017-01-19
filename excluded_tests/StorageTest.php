<?php

namespace Test;

use BlueDot\Common\ArgumentBag;
use BlueDot\Common\StorageInterface;
use BlueDot\Exception\BlueDotRuntimeException;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    public function testStorage()
    {
        $storage = new ArgumentBag();

        $storage
            ->add('root', 'string value')
            ->add('array', array())
            ->add('number', 2);

        $this->assertTrue($storage->has('root'), StorageInterface::class.' should contain a \'root\' entry');
        $this->assertInternalType('string', $storage->get('root'), StorageInterface::class.' should contain a string');
        $this->assertEquals('string value', $storage->get('root'), StorageInterface::class.' should contain a string \'string value\' for entry \'root\'');

        $this->assertTrue($storage->has('array'), StorageInterface::class.' should contain a \'array\' entry');
        $this->assertInternalType('array', $storage->get('array'), StorageInterface::class.' should contain an array');
        $this->assertEquals(array(), $storage->get('array'), StorageInterface::class.' should contain an empty string for entry \'array\'');


        $this->assertTrue($storage->has('number'), StorageInterface::class.' should contain a \'number\' entry');
        $this->assertInternalType('int', $storage->get('number'), StorageInterface::class.' should contain an integer');
        $this->assertEquals(2, $storage->get('number'), StorageInterface::class.' should contain a number \'2\' for entry \'number\'');

        $mergingStorage = new ArgumentBag();

        $mergingStorage->add('merge_entry', 'merge_entry');

        $storage->mergeStorage($mergingStorage);

        $this->assertTrue($storage->has('merge_entry'), StorageInterface::class.' should contain a \'merge_entry\' entry');
        $this->assertInternalType('string', $storage->get('merge_entry'), StorageInterface::class.' should contain a string');
        $this->assertEquals('merge_entry', $storage->get('merge_entry'), StorageInterface::class.' should contain a string \'merge_entry\' for entry \'merge_entry \'');

        $storage->rename('merge_entry', 'merge');

        $this->assertTrue($storage->has('merge'), StorageInterface::class.' should contain a \'merge\' entry');
        $this->assertInternalType('string', $storage->get('merge'), StorageInterface::class.' should contain a string');
        $this->assertEquals('merge_entry', $storage->get('merge'), StorageInterface::class.' should contain a string \'merge_entry\' for entry \'merge\'');


        $storage->remove('merge_entry');

        $this->assertFalse($storage->has('merge_entry'), StorageInterface::class.' should not contain a \'merge_entry\' entry after removal');

        $appendingStorage = new ArgumentBag();
        $appendingStorage->add('sublevel_root', 'some value');

        try {
            $storage->append('root', $appendingStorage);
            $this->fail(BlueDotRuntimeException::class.' should have been thrown for StorageInterface::append() method');
        } catch (BlueDotRuntimeException $e) {

        }

        $storage->append('new_internal_storage', $appendingStorage);

        $this->assertInternalType('array', $storage->get('new_internal_storage'), 'Entry \'new_internal_storage\' should be an array');
        $this->assertInstanceOf(StorageInterface::class, $storage->get('new_internal_storage')[0], 'Zero index entry of \'new_internal_storage\' should contain an instance of '.StorageInterface::class);

        try {
            $storage->addTo('root', array('name', 'lastname', 'gender'));
            $this->fail(BlueDotRuntimeException::class.' should have been thrown for StorageInterface::addTo() method');
        } catch (BlueDotRuntimeException $e) {

        }
    }
}