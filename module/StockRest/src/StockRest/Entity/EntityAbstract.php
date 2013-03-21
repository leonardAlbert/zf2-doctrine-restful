<?php
namespace StockRest\Entity;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Entity abstract with some methods that other classes can use it.
 *
 * @author Thiago Pelizoni <thiago.pelizoni@gmail.com>
 */
abstract class EntityAbstract
{
    /**
     * Default class constructor. This model can be filled automatically
     * with the form data. 
     *
     * @param   array   $data
     * @return  StockRest\Entity\EntityAbstract
     */
    public function __construct($data = null)
    {
        $this->exchangeArray($data);
        
        return $this;
    }
    
    /**
     * Fill this object from an array
     *
     * @return StockRest\Entity\EntityAbstract
     */
    public function exchangeArray($data)
    {
        if ($data != null) {
            foreach ($data as $attribute => $value) {
                if (! property_exists($this, $attribute)) {
                    continue;
                }
                $this->$attribute = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * Magic method used to set a value in a attribute.
     *
     * @param   string $attribute
     * @param   mixed  $value 
     * @return  StockRest\Entity\EntityAbstract;
     */
    public function __set($attribute, $value)
    {
        $this->$attribute = $value;
        
        return $this;
    }
    
    /**
     * Magic method used to return a value of this class
     *
     * @param   string $attribute
     * @return  StockRest\Entity\EntityAbstract;
     */
    public function __get($attribute)
    {
        return $this->$attribute;
    }
    
    /**
     * Return this object in array form. 
     *
     * @return array
     */
    public function toArray()
    {
        $data = get_object_vars($this); 
        
        foreach ($data as $attribute => $value) {
            if (is_object($value)) {
                $data[$attribute] = get_object_vars($value); 
            }
        }

        return $data;
    }
    
    /**
     * Method to find one or more records where will be returning a Doctrine 
     * Paginator object.
     *
     * @return Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function find($entityManager, $params)
    {
        $firstResult = (int) isset($params['firstResult']) ? 
            $params['firstResult'] : 1;
            
        $maxResults  = (int) isset($params['maxResults']) ? 
            $params['maxResults'] : 10;

        if ($firstResult < 1) {
            $firstResult = 1;
        }

        if ($maxResults < 1 || $maxResults > 500) {
            $maxResults = 10;
        }
        
        $alias = 'entity';

        $query = $entityManager->createQueryBuilder($alias)
           ->setFirstResult($firstResult)
           ->setMaxResults($maxResults);
           
        $query = $this->getCriteria($query, $params, $alias);
        
        $orderBy  = isset($params['orderBy']) ? 
            strtolower($params['orderBy']) : null;           

        if ($orderBy != null) {
            $typeOfOrder = isset($params['typeOfOrder']) ? 
                strtolower($params['typeOfOrder']) : 'asc';
            
            if ($typeOfOrder != 'asc' && $typeOfOrder != 'desc') {
                $typeOfOrder = 'asc';
            }
            $query->orderBy("{$alias}.{$orderBy}", $typeOfOrder);
        }             

        $paginator = new Paginator($query);
        
        return $paginator; 
    }
    
    /**
     * Processes the parameters to perform a search with the specific type 
     * according to the parameters passed.
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    private function getCriteria($query, $params, $alias)
    {
        $queryType = isset($params['queryType']) 
            ? strtolower($params['queryType']) : 'and';
            
        if ($queryType != 'and' && $queryType != 'or') {
            $queryType = 'and';
        }
    
        foreach ($params as $attribute => $value) {
            if (! property_exists($this, $attribute)) {
                continue;
            }
            
            if ($value == null) {
                continue;
            }
            
            $value = strtoupper($value);
            $value = preg_replace('/[^[:ascii:]]/', '%', $value);
            $value = preg_replace('/[%]{1,}/', '%', $value);

            $criteria = "UPPER({$alias}.{$attribute}) LIKE '%{$value}%'";

            if ($queryType == 'and') {
                $query->andWhere($criteria);
            } else {
                $query->orWhere($criteria);
            }
        }
        
        return $query;
    }
}
