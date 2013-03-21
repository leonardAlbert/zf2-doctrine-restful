<?php
/**
 * API Restiful tutorial with Zend Framework 2 and Doctrine
 *
 * This entity is a simple example how to use Doctrine with ZF2
 * in a API Restful.
 *
 * @author Thiago Pelizoni <thiago.pelizoni@gmail.com>
 */
namespace StockRest\Entity;

use Doctrine\ORM\Mapping as ORM;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

use StockRest\Entity\EntityAbstract;

/**
 * Product
 *
 * @ORM\Entity
 * @ORM\Table(name="products")
 * @property int $id
 * @property string $name
 * @property string $description
 */
class Product extends EntityAbstract implements InputFilterAwareInterface 
{
    /**
     * @var Zend\InputFilter\InputFilter
     */
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;    

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used!");
    }

    public function getInputFilter()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'       => 'id',
                'required'   => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'description',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'max'      => 1000,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'       => 'created_at',
                'required'   => true,
            )));

            $this->inputFilter = $inputFilter;        
        }

        return $this->inputFilter;
    }
    
    /**
     * Non PHP-Doc
     *
     * @see EntityAbstract::exchangeArray()
     */
    public function exchangeArray($data)
    {
        $data['created_at'] = isset($data['created_at']) ?
            new \DateTime($data['created_at']) : new \DateTime();

        return parent::exchangeArray($data);
    }
    
    /**
     * Return this object as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        
        unset($data['inputFilter']);
        
        return $data;
    } 
}
