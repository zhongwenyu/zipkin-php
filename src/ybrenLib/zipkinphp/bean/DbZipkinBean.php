<?php
namespace ybrenLib\zipkinphp\bean;

class DbZipkinBean{

    private $sql;

    private $database;

    private $username;

    private $type;

    private $exception;

    public function __construct($data = []){
        isset($data['sql']) && $this->sql = $data['sql'];
        isset($data['database']) && $this->database = $data['database'];
        isset($data['username']) && $this->username = $data['username'];
        isset($data['type']) && $this->type = $data['type'];
        isset($data['exception']) && $this->exception = $data['exception'];
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param mixed $sql
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param mixed $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param mixed $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }


}