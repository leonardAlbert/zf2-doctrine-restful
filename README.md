API RESTful com Zend Framework 2 e Doctrine ORM
============

## Descrição

Este projeto consiste em um exemplo simples e prático de como criar um a [API RESTful](http://en.wikipedia.org/wiki/Representational_state_transfer) com [Zend Framework 2](http://framework.zend.com/manual/2.0/en/index.html) com o ORM [Doctrine](http://www.doctrine-project.org/).

A configuração da máquina utilizada para realização deste tutorial foi:

* Ubuntu 13.04
* Apache 2.2.22
* MySQL 5.5.29
* PHP 5.4.6
* Git 1.7.10.4

## Preparação do ambiente

#### Obtendo o Zend Framework 2

Este tutorial assume que o local deste projeto será no diretório **/var/www**.

```
cd /var/www
git clone git@github.com:zendframework/ZendSkeletonApplication.git zf2-doctrine-restful
```

## Instalando dependências

##### composer.json

Acrescentar as depencencias referentes ao Doctrine no arquivo:

```
"doctrine/doctrine-orm-module": "dev-master",
"doctrine/migrations": "dev-master"
```

Desta forma, o arquivo, ficará da seguinte maneira:

```
{
    "name": "zendframework/skeleton-application",
    "description": "Skeleton Application for ZF2",
    "license": "BSD-3-Clause",
    "keywords": [
        "framework",
        "zf2"
    ],
    "homepage": "http://framework.zend.com/",
    "require": {
        "php": ">=5.3.3",
        "zendframework/zendframework": "2.*",
        "doctrine/doctrine-orm-module": dev-master",
        "doctrine/migrations": "dev-master"
    }
}
```

Após efetuar as alterações no arquivo composer.json, basta executar o comando:

```
php composer.phar self-update && php composer.phar install
```

## VirtualHost

```
<VirtualHost *:80>
    ServerName zf2-doctrine-restful.local
    DocumentRoot /var/www/zf2-doctrine-restful/public

    SetEnv APPLICATION_ENV "development"
    SetEnv PROJECT_ROOT "/var/www/zf2-doctrine-restful"

    <Directory "/var/www/zf2-doctrine-restful/public">
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>

</VirtualHost>
```

## Hosts

```
echo "127.0.0.1 zf2-doctrine-restful.local" >> /etc/hosts
```

## Database (script para geração da base de dados)

```
DROP DATABASE IF EXISTS zf2;
CREATE DATABASE zf2;
USE zf2;

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;


INSERT INTO `products` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Achocolatado Nescau 2.0', 'NESCAU 2.0 é uma evolução do Nescau que todo mundo adora. Ele ganhou ainda mais vitaminas e um novo blend de Cacau surpreendente.', NULL),
(2, 'Chocolate CHARGE', 'Açúcar, xarope de glicose, amendoim, gordura vegetal, leite condensado, cacau, lactose, leite em pó integral, cacau em pó, sal, açúcar invertido, proteí­na láctea, umectantes sorbitol e glicerol, emulsificantes monoestearato de glicerila, aromatizantes, acidulante ácido cí­trico e regulador de acidez tricarbonato de sódio. Contém Glúten. Contém traços de castanha de caju e avelã.', '2013-03-21 14:02:08'),
(3, 'Chocolate Crunch', 'Açúcar, cacau, flocos de arroz, leite em pó integral, soro de leite em pó, gordura vegetal, gordura anidra de leite, emulsificantes lecitina de soja e ricinoleato de glicerila e aromatizante. Contém Glúten. Contém traços de castanha de caju e amendoim.', '2013-03-21 13:21:38'),
(8, 'Chocolate CHOKITO', 'Água, açúcar, cacau, gordura vegetal, leite em pó desnatado, crocante de caramelo, leite em pó integral, flocos de arroz, xarope de glicose, soro de leite, gordura anidra de leite, corantes caramelo e naturais urucum e carmim cochonila, emulsificantes mono e diglicerídios de ácidos graxos e ricinoleato de glicerila, espessantes goma jataí e carboximetilcelulose sódica, estabilizantes lecitina de soja e alga euchema processada e aromatizantes. CONTÉM GLÚTEN. Contém traços de castanha de caju, amendoim, avelã e amêndoa.', '2013-03-21 13:23:52');
```

## Configurando o projeto

#### config/autoload/local.php

```php
<?php
return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'root',
                    'dbname'   => 'zf2',
                    'charset'  => 'UTF-8',
                ),
            ),
        ),
    ),
);
```

#### Adicionando o módulo nas configurações da aplicação

```php
<?php
// config/application.config.php
return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
        'Application',
        'DoctrineModule',       // Adicionar
        'DoctrineORMModule',    // Adicionar
        'StockRest',            // Adicionar (módulo que iremos criar)
    ),
    .
    .
    .
```

## Criação do Módulo

Iremos criar um módulo do Zend Framework 2 para que possamos utilizar o Doctrine, portanto, dentro do diretório *zf2-doctrine/module* do projeto, devemos criar a seguinte estrutura de diretório:

```
StockRest
  config
  src
    StockRest
      Controller
      Entity
```

Criando a estrutura de diretórios.

```
mkdir StockRest
mkdir -p StockRest/config
mkdir -p StockRest/src/StockRest/Controller
mkdir -p StockRest/src/StockRest/Entity
```

## StockRest/Module.php
```php
<?php
namespace StockRest;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
}
```

## StockRest/config/module.config.php

```php
<?php
namespace StockRest;

return array(

    // Controllers in this module
    'controllers' => array(
        'invokables' => array(
            'Product' => 'StockRest\Controller\ProductController',
        ),
    ),

    // Routes for this module
    'router' => array(
        'routes' => array(
            // Products
            'product-rest' => array(
                'type'    => 'segment',
                'options' => array(
                    'route' => '/product-rest[/:params]',
                    'constraints' => array(
                        'params' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Product',
                    ),
                ),
            ),
        ),
    ),
    
    // View Strategy
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

    // Doctrine configuration
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ),
            ),
        ),
    ),

);
```

## StockRest/autoload_classmap.php
```php
<?php
return array();
```

## StockRest/src/StockRest/Entity/Product.php
```php
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
```

## StockRest/src/StockRest/Entity/EntityAbstract.php
```php
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
```

## StockRest/src/StockRest/Controller/ProductController.php
```php
<?php
namespace StockRest\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Doctrine\ORM\EntityManager;

use StockRest\Entity\Product;

class ProductController extends AbstractRestfulController
{
    
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
 
    /**
     * Return a EntityManager
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if ($this->em === null) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        
        return $this->em;
    } 
    
    /**
     * Return list of resources
     *
     * @return array
     */
    public function getList()
    {
        $params = $this->params()->fromQuery();

        $entityManager = $this->getEntityManager()
            ->getRepository('StockRest\Entity\Product');
    
        $product = new Product();
        $paginator = $product->find($entityManager, $params); 

        $data = array();

        foreach ($paginator as $product) {
            $data[] = $product->toArray();
        }

        return new JsonModel(array(
            'data' => $data,
            'success' => true,
        )); 
    }
    
    /**
     * Find a simple runner by ID.
     *
     * @return StockRest\Entity\Product
     */
    public function getProduct($id)
    {
        $product = $this->getEntityManager()
            ->getRepository('StockRest\Entity\Product')
            ->find($id);

        return $product;            
    }

    /**
     * Return single resource
     *
     * @param mixed $id
     * @return mixed
     */
    public function get($id) 
    {
        $id = (int) $id;

        $product = $this->getProduct($id);
        
        if ($product == null) {
            return new JsonModel(array(
                'data' => '',
                'success' => false,
                'message' => 'Product not found!',
            )); 
        }     
                        
        return new JsonModel(array(
            'data' => $product->toArray(),
            'success' => true,
        ));  
    }

    /**
     * Create a new resource
     *
     * @param mixed $data
     * @return json
     */
    public function create($data) 
    {
        $data = $this->params()->fromQuery();
  
        $product = new Product($data);
        $product->getInputFilter()->setData($product->toArray());

        if (! $product->getInputFilter()->isValid()) {
            $messages = $product->getInputFilter()->getMessages();
            
            return new JsonModel(array(
                'data' => '',
                'success' => false,
                'message' => $messages,
            )); 
        }
        
        try {
            $this->getEntityManager()->persist($product);
            $this->getEntityManager()->flush();
        } catch (Exception $e) {
            return new JsonModel(array(
                'data' => '',
                'success' => false,
                'message' => $e->getMessage(),
            )); 
        }               
        
        return new JsonModel(array(
            'data' => $product->toArray(),
            'success' => true,
        )); 

        
    }

    /**
     * Update an existing resource
     *
     * @param int $id
     * @param array $data
     * @return json
     */
    public function update($id, $data) 
    {
        $id = (int) $id;
    
        $data = $this->params()->fromQuery();
        
        $product = $this->getProduct($id);
                    
        if ($product == null) {
            return new JsonModel(array(
                'data' => '',
                'success' => false,
                'message' => 'Product not found!',
            )); 
        }
        
        $product->exchangeArray($data);

        try {
            $this->getEntityManager()->persist($product);
            $this->getEntityManager()->flush();
        } catch (Exception $e) {
            return new JsonModel(array(
                'data' => '',
                'success' => false,
                'message' => $e->getMessage(),
            )); 
        }               
        
        return new JsonModel(array(
            'data' =>  $product->toArray(),
            'success' => true,
        )); 
    }

    /**
     * Delete an existing resource
     *
     * @param  int $id
     * @return json
     */
    public function delete($id) 
    {
        $id = (int) $id;
    
        $product = $this->getProduct($id);
        
         if ($product == null) {
            return new JsonModel(array(
                'data' => '',
                'success' => false,
                'message' => 'Product not found!',
            )); 
        }
        
        try {
            $this->getEntityManager()->remove($product);
            $this->getEntityManager()->flush();
        } catch (Exception $e) {
            return new JsonModel(array(
                'data' => '',
                'success' => false,
                'message' => $e->getMessage(),
            )); 
        }               
        
        return new JsonModel(array(
            'data' =>  '',
            'success' => true,
            'message' => 'Product deleted successful!'
        )); 
    }
}
```

## Product

* **id:** Código do produto.
* **name:** Nome do produto, tendo por limite 100 caracteres.
* **description:** Descrição do produto, campo do tipo texto, pode ser escrito à vontade.
* **created_at:** Data de criação deste registro.

## API options
* **firstResult:** Informa qual é o número do primeiro resultado a ser trazido, caso não informado é assumido por padrão o número 1.
* **maxResults:**  Informa o número de resultados desta consulta, caso não informado é assumido por padrão o número 10 não podendo exceder à 500 registros.
* **orderBy:** Informa o nome da coluna referente a busca do recurso ao qual terá a ordenação.
* **typeOfOrder:** Informa o tipo de ordenação *(ASC|DESC)*, caso não informado é assumido por padrão o tipo de ordenação ascendente (asc).
* **queryType:** Informa o tipo de query que será feita *(AND|OR)* de acordo com os dados informados na url, caso não informado é assumido por padrão o tipo de query é *AND*.

## Exemplo de utilização

#### GET http://zf2-doctrine-restful.local/product-rest
Obtém os primeiros 10 registros deste recurso. Por padrão **firstResult** = 1 e **maxResults=10** já que nada foi informado.

#### GET http://zf2-doctrine-restful.local/product-rest?firstResult=10&maxResults=100
Obtém os 100 registros a partir do 10º registro deste recurso.

#### GET http://zf2-doctrine-restful.local/product-rest?id=1
Obtém o registro cujo seu código é igual a 1.

#### GET http://zf2-doctrine-restful.local/product-rest?name=charge
Obtém os primeiros 10 registros deste recurso cujo nome possui a palavra "charge". Por padrão **firstResult** = 1 e **maxResults=10** já que nada foi informado. Vale salientar que a busca não é case sentitive, ou seja, se estivesse escrito "CHARGE" ou "charge" o resultado seria o mesmo.

#### GET http://zf2-doctrine-restful.local/product-rest?name=charge&Glutem&queryType=or&orderBy=id&typeOfOrder=desc
Obtém os primeiros 10 registros deste recurso cujo nome possui a palavra "charge" ou que na descrição contenha a palavra "Glútem" ordenando o resultado pelo código de forma descendente.

#### POST http://zf2-doctrine-restful.local/product-rest?name=product&description=description
Efetua o cadastro de um novo produto.

#### PUT http://zf2-doctrine-restful.local/product-rest?id=50&name=Product
Altera o nome do produto cujo código é 50.

#### DELETE http://zf2-doctrine-restful.local/product-rest?id=50
Exclui o registro cujo código é 50.

