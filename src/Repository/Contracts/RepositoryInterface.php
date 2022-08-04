<?php

/**
 * Created by PhpStorm.
 * User: zero
 * Date: 2020/2/7
 * Time: 13:42
 */

namespace Refineon\Basic\Repository\Contracts;

interface RepositoryInterface
{
    public function list();

    public function show($id);

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'));

    public function paginate($perPage = 10, $columns = array('*'));

    public function create(array $attributes);

    public function update(array $attributes, $id);

    public function delete($id);

    public function find($id, $columns = array('*'));

    public function findBy($field, $value, $columns = array('*'));

}