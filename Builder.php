<?php

interface SQLQueryBuilder
{
    public function select(string $table, array $fields): SQLQueryBuilder;
    public function where(string $field, string $value, string $operator = '='): SQLQueryBuilder;
    public function getSQL(): string;
}


class MysqlQueryBuilder implements SQLQueryBuilder
{
    protected $query;

    protected function reset(): void
    {
        $this->query = new \stdClass;
    }

    public function select(string $table, array $fields): SQLQueryBuilder
    {

        $this->reset();
        $this->query->base = "SELECT " . implode(", ", $fields) . " FROM " . $table;
        $this->query->type = 'select';

        return $this;
    }

    public function where(string $field, string $value, string $operator = '='): SQLQueryBuilder
    {

        if (!in_array($this->query->type, ['select', 'update', 'delete'])) {
            return false;
        }

        $this->query->where[] = "$field $operator '$value'";

        return $this;
    }


    public function getSQL(): string
    {

        $query = $this->query;
        $sql = $query->base;

        if (!empty($query->where)) {
            $sql .= " WHERE " . implode(' AND ', $query->where);
        }

        $sql .= ";";
        return $sql;
    }
}


function clientCode(SQLQueryBuilder $queryBuilder)
{

    // $query = $queryBuilder->select('users', ['email', 'username'])->getSQL();
    // $query = $queryBuilder->('users', ['email'])->where('id', 1, ">")->getSQL();

    // echo $query;

}


clientCode(new MysqlQueryBuilder);
