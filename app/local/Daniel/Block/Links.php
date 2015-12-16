<?php
class Daniel_PrevNext_Block_Links extends Mage_Core_Block_Template
{
	/**
	 * The product being visited
	 * @var Mage_Catalog_Model_Product
	 */
	protected $_currentProduct;
	/**
	 * The product selected category, or the category visited before
	 * the product.
	 * @var Mage_Catalog_Model_Category
	 */
	protected $_category;
	/**
	 * [$_collectionArray description]
	 * @var array
	 */
	protected $_collectionArray;
	/**
	 * Block constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_init();
		$this->setTemplate('daniel/prevnext/links.phtml');
	}
	/**
	 * Private function to initialize the block properties
	 */
	private function _init()
	{
		if (Mage::registry('current_category'))
		{
			$this->_category = Mage::registry('current_category');	
		}
		if (Mage::registry('current_product'))
		{
			$this->_currentProduct = Mage::registry('current_product');	
		}

		if (!$this->_category)
		{
			$this->_doNothing = true;
			return;
		}

		$this->_initProductCollectionArray();
	}
	/**
	 * Get the product collection of the selected category as an array.
	 * @return array
	 */
	protected function getProductCollectionArray ()
	{
		if (!$this->_collectionArray)
		{
			$this->_initProductCollectionArray();
		}
		return $this->_collectionArray;
	}
	/**
	 * Save the collection array as object property
	 * Added name and url_key as part of the array.
	 */
	private function _initProductCollectionArray ()
	{
		if (!$this->_collectionArray && !$this->_doNothing)
		{
			$productCollection = $this->_category->getProductCollection();
			$productCollection->addAttributeToSelect(array('entity_id', 'name', 'url_key'))
				->addAttributeToFilter('status', 1)
				->addAttributeToFilter('visibility', 4)
				->addCategoryFilter($this->_category)
				->setOrder('entity_id', 'ASC');
			$this->_collectionArray = $productCollection->exportToArray();
		}
	}
	/**
	 * Get current product Object
	 * @return Mage_Catalog_Model_Product
	 */
	public function getCurrentProduct ()
	{
		return $this->_currentProduct;
	}
	/**
	 * Get previous product array
	 * @return array 	Product relevant info: url, id, name
	 */
	public function getPreviousProductArray ()
	{
		if ($this->_doNothing)
		{
			return false;
		}
		$result = array();
		$productsArray = $this->getProductCollectionArray();
		$currentId = $this->getCurrentProduct()->getId();
		$productIds = array_keys($productsArray);
		$currentPosition = array_search($currentId, $productIds);
		if ($currentPosition - 1 < 0)
		{
			$prevId = array_pop($productIds);
		}
		else
		{
			$prevId = $productIds[$currentPosition - 1];
		}
		$prevProduct = $productsArray[$prevId];
		$prevProductUrl = Mage::getUrl(
			$prevProduct['url_key'], array(
				'_nosid' => true,
				'_store' => 'default',
				'_type' => 'direct_link'
		));
		$result['url'] = $prevProductUrl;
		$result['id'] = $prevProduct['entity_id'];
		$result['name'] = $prevProduct['name'];

		return $result;
	}
	/**
	 * Get next product array
	 * @return array 	Product relevant info: url, id, name
	 */
	public function getNextProductArray ()
	{
		if ($this->_doNothing)
		{
			return false;
		}		
		$result = array();
		$productsArray = $this->getProductCollectionArray();
		$currentId = $this->getCurrentProduct()->getId();
		$productIds = array_keys($productsArray);
		$currentPosition = array_search($currentId, $productIds);
		if (($currentPosition + 1) > (count($productsArray) - 1))
		{
			$nextId = array_shift($productIds);
		}
		else
		{
			$nextId = $productIds[$currentPosition + 1];
		}
		$nextProduct = $productsArray[$nextId];
		$nextProductUrl = Mage::getUrl($nextProduct['url_key'], array(
			'_nosid' => true,
			'_store' => 'default',
			'_type' => 'direct_link'
		));
		$result['url'] = $nextProductUrl;
		$result['name'] = $nextProduct['name'];
		$result['id'] = $nextId;

		return $result;
	}
	/**
	 * Get the product image URL, by product ID.
	 * @param  int $productId 	 the product ID
	 * @param  string $imageType type of image we want to get ('small_image', 'thumbnail' ...)
	 * @return string            image url
	 */
	public function getImageByProductId ($productId, $imageType = 'small_image')
	{
		$model = Mage::getModel('catalog/product');
		$product = $model->load($productId);
		if (!$product)
		{
			return false;
		}
		$imageUrl = Mage::helper('catalog/image')->init($product, $imageType);
		return $imageUrl;
	}
	/**
	 * Function for debugging purposes.
	 * Will be deleted at the end of the development phase
	 */
	public function test ($data)
	{
		Zend_Debug::dump($data, null, true);
	}

}