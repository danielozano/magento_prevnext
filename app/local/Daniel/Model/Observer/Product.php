<?php
class Daniel_PrevNext_Model_Observer_Product
{
	/**
	 * NOTE: Añadida función para comprobar si el producto está en la categoría, ¿necesario?.
	 */
	/**
	 * The current category object
	 */
	protected $category;
	/**
	 * The current product object
	 */
	private $product;
	/**
	 * Set the current category depending on the actual product,
	 * and the way it's visited.
	 * @param Varien_Event_Observer $observer [description]
	 */
	public function setCategory(Varien_Event_Observer $observer)
	{
		$product = $observer->getEvent()->getData('product');
		$category = $product->getData('category');
		if (!$category)
		{
			$product = Mage::registry('current_product');
			$categoryCollection = $product->getCategoryCollection();
			$categoryCollection->addAttributeToSelect(array('entity_id', 'name'))
				->addFieldToFilter('level', array('eq' => 2))
				->addFieldToFilter('is_active', 1)
				->addAttributeToSort('position', 'asc');
			if (!$categoryCollection->count())
			{
				$categoryCollection = $product->getCategoryCollection();
				$categoryCollection->addAttributeToSelect(array('entity_id', 'name'))
					->addFieldToFilter('level', array('eq' => 1))
					->addFieldToFilter('is_active', 1)
					->addAttributeToSort('position', 'asc');
			}
			if ($categoryCollection->count() && !Mage::registry('current_category'))
			{
				Mage::unregister('current_category');
				$this->category = $categoryCollection->getFirstItem();
				Mage::register('current_category', $this->category);
			}
		}
	}
	/**
	 * Check if product is inside of the category
	 * 
	 * @param  Mage_Catalog_Model_Product   $product  
	 * @param  Mage_Catalog_Model_Category  $category 
	 * @return boolean
	 */
	private function _isProductInCategory ($product, $category)
	{
		$categoryIds = $product->getCategoryIds();
		$categoryId = $category->getId();
		if (in_array($categoryId, $categoryIds))
		{
			return true;
		}
		return false;
	}
}