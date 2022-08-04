<?php

/**
 * Created by PhpStorm.
 * User: zero
 * Date: 2020/2/7
 * Time: 13:47
 */

namespace Meibuyu\Micro\Repository\Eloquent;

use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model;
use Hyperf\HttpServer\Contract\RequestInterface;
use Refineon\Basic\Exceptions\HttpResponseException;
use Refineon\Basic\Exceptions\RepositoryException;
use Refineon\Basic\Exceptions\ValidatorException;
use Refineon\Basic\Repository\Contracts\RepositoryInterface;
use Refineon\Basic\Validator\Contracts\ValidatorInterface;
use Psr\Container\ContainerInterface;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Model|Builder
     */
    protected $model;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * BaseRepository constructor.
     * @param ContainerInterface $container
     * @param RequestInterface $request
     * @throws RepositoryException
     */
    public function __construct(ContainerInterface $container, RequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
        $this->makeModel();
        $this->makeValidator();
    }

    /**
     * Specify Model class name
     * @return mixed
     */
    abstract public function model();

    /**
     * Specify Validator class name
     * @return null|mixed
     */
    public function validator()
    {
        return null;
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->container->make($this->model());
        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Hyperf\\DbConnection\\Model\\Model");
        }
        return $this->model = $model;
    }

    /**
     * @param null $validator
     *
     * @return null|ValidatorInterface
     * @throws RepositoryException
     */
    public function makeValidator($validator = null)
    {
        $validator = !is_null($validator) ? $validator : $this->validator();

        if (!is_null($validator)) {
            $this->validator = $this->container->make($validator);

            if (!$this->validator instanceof ValidatorInterface) {
                throw new RepositoryException("Class {$validator} must be an instance of Meibuyu\\Micro\\Validator\\Contracts\\ValidatorInterface");
            }

            return $this->validator;
        }

        return null;
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed|Model
     * @throws HttpResponseException
     */
    public function find($id, $columns = ['*'])
    {
        $model = $this->model->find($id, $columns);
        if (!$model) {
            throw new HttpResponseException('数据不存在');
        }
        return $model;
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->model->get($columns);
    }

    public function list()
    {
        return $this->all();
    }

    /**
     * @param $id
     * @return Model|mixed
     * @throws HttpResponseException
     */
    public function show($id)
    {
        return $this->find($id);
    }

    public function paginate($perPage = 10, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $attributes
     * @return Model
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        if (!is_null($this->validator)) {
            $this->validator->with($attributes)->passesOrFail(ValidatorInterface::RULE_CREATE);
        }

        $model = $this->model->newInstance($attributes);
        $model->save();
        return $model;
    }

    /**
     * @param array $attributes
     * @param $id
     * @return Model | mixed
     * @throws HttpResponseException
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        if (!is_null($this->validator)) {
            $this->validator->with($attributes)->setId($id)->passesOrFail(ValidatorInterface::RULE_UPDATE);
        }

        $model = $this->find($id);
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws HttpResponseException
     */
    public function delete($id)
    {
        $model = $this->find($id);
        try {
            $delete = $model->delete();
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            if ($e->getCode() == 23000 && strpos($msg, 'foreign key') !== false) {
                throw new HttpResponseException('此数据下有关联的数据,不可进行操作');
            } else {
                throw new HttpResponseException($msg);
            }
        } catch (\Exception $e) {
            throw new HttpResponseException($e->getMessage());
        }
        if ($delete !== false) {
            return $delete;
        } else {
            throw new HttpResponseException('删除失败,请刷新重试');
        }
    }

    public function findBy($field, $value, $columns = ['*'])
    {
        return $this->model->where($field, '=', $value)->first($columns);
    }

}