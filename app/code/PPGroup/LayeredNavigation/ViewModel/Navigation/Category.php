<?php

namespace PPGroup\LayeredNavigation\ViewModel\Navigation;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Category implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getBrandRootCategory(\Magento\Catalog\Model\Category $category): ?CategoryInterface
    {
        if ($category->getDisplayMode() != \Magento\Catalog\Model\Category::DM_PRODUCT) {
            return null;
        }

        $catPath = explode('/', $category->getPath());
        if (count($catPath) < 4) {
            return null;
        }

        try {
            $brandRootCatId = $catPath[3];
            $brandRootCat = $this->categoryRepository->get($brandRootCatId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $brandRootCat;
    }

    public function getCategoryById($id): ?CategoryInterface
    {
        try {
            $brandRootCat = $this->categoryRepository->get($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $brandRootCat;
    }
}
