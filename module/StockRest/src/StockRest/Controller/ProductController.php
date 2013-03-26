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
