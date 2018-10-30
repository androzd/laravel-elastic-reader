<?php


namespace Merkeleon\ElasticReader\Elastic;


class QueryBuilder
{
    protected $query = [];

    public function from(int $from)
    {
        $this->query['from'] = $from;

        return $this;
    }

    public function size(int $size)
    {
        $this->query['size'] = $size;

        return $this;
    }

    public function sort($sort)
    {
        $this->query['sort'] = $sort;

        return $this;
    }

    public function build()
    {
        $build = [
            'from' => (int)array_get($this->query, 'from', 0),
            'size' => (int)array_get($this->query, 'size', 50)
        ];

        if ($body = array_get($this->query, 'body'))
        {
            $build['body'] = $body;
        }

        if ($sort = array_get($this->query, 'sort'))
        {
            $build['sort'] = $sort;
        }
        return $build;
    }

    protected function merge(array $query, $mode = 'filter')
    {
        $this->query['body']['query']['bool'][$mode][] = $query;

        return $this;
    }

    public function where($field, $value)
    {
        $query = ['term' => [$field => $value]];

        $this->merge($query);

        return $this;
    }

    public function range($field, $start = null, $end = null)
    {
        $query = [];

        if ($start)
        {
            $query['range'][$field]['gte'] = $start;
        }

        if ($end)
        {
            $query['range'][$field]['lte'] = $end;
        }

        if ($query)
        {
            $this->merge($query);
        }

        return $this;
    }

    public function matchSubString($value, $field = null)
    {
        $words = explode(' ', $value);

        foreach ($words as $word)
        {
            $query = ['query_string' => ["query" => addslashes($word) ]];

            if ($field)
            {
                $query['query_string']['default_field'] = $field;
            }

            $this->merge($query, 'must');
        }

        return $this;
    }

    public function whereOr($params)
    {
        $query = [];
        foreach ($params as $field => $value)
        {
            $query[] = ['term' => [$field => addslashes($value)]];
        }

        $this->merge($query, 'should');
    }
}