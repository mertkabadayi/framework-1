<?php

namespace Pagekit\Database\ORM;

use Pagekit\Database\Connection;

trait ModelTrait
{
    /**
     * @var EntityManager
     */
    protected static $manager;

    /**
     * @var Connection
     */
    protected static $connection;

    /**
     * Gets the related Manager object.
     *
     * @return EntityManager
     */
    public function getManager()
    {
        return self::$manager;
    }

    /**
     * @param EntityManager $manager
     */
    public static function setManager($manager)
    {
        self::$manager = $manager;
    }

    /**
     * @return Connection
     */
    public static function getConnection()
    {
        return self::$connection;
    }

    /**
     * @param Connection $connection
     */
    public static function setConnection($connection)
    {
        self::$connection = $connection;
    }

    /**
     * Gets the related Metadata object with mapping information of the class.
     *
     * @return Metadata
     */
    public static function getMetadata()
    {
        static $metadata;

        if (!$metadata) {
            $metadata = self::$manager->getMetadata(get_called_class());
        }

        return $metadata;
    }

    /**
     * Create a new QueryBuilder instance.
     *
     * @return QueryBuilder
     */
    public static function query()
    {
        return new QueryBuilder(self::$manager, self::getMetadata());
    }

    /**
     * Create a new QueryBuilder instance and set the WHERE condition.
     *
     * @param  mixed $condition
     * @param  array $params
     * @return QueryBuilder
     */
    public static function where($condition, array $params = [])
    {
        return self::query()->where($condition, $params);
    }

    /**
     * Retrieve an entity by its identifier.
     *
     * @param  mixed $id
     * @return mixed
     * @throws \Exception
     */
    public static function find($id)
    {
        if ($entity = self::$manager->getById($id, get_called_class())) {
            return $entity;
        }

        return self::where([self::getMetadata()->getIdentifier() => $id])->first();
    }

    /**
     * Retrieve all entities.
     *
     * @return mixed
     */
    public static function findAll()
    {
        return self::query()->get();
    }

    /**
     * Saves the entity.
     *
     * @param array $data
     */
    public function save(array $data = [])
    {
        self::$manager->save($this, $data);
    }

    /**
     * Deletes the entity.
     */
    public function delete()
    {
        self::$manager->delete($this);
    }
}
