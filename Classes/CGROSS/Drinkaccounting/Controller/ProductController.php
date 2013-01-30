<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use \CGROSS\Drinkaccounting\Domain\Model\Product;

/**
 * Product controller for the Drinkaccounting package
 *
 * @FLOW3\Scope("singleton")
 */
class ProductController extends DefaultController {

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @FLOW3\Inject
	 */
	protected $systemLogger;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \TYPO3\Flow\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\Flow\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\ProductRepository
	 */
	protected $productRepository;

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\StatementRepository
	 */
	protected $statementRepository;

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\PurchaseRepository
	 */
	protected $purchaseRepository;

	/**
	 * Shows a list of products
	 *
	 * @return void
	 */
	public function indexAction() {
		$products = $this->productRepository->findAll();
		// Calculate average price
		for($p = 0; $p < count($products); $p++) {
			$products[$p]->averagePrice = $products[$p]->getPurchasePrice() / $products[$p]->getCrateSize();
		}
		$this->view->assign('products', $products);
	}

	/**
	 * Shows a list of inactive products
	 *
	 * @return void
	 */
	public function inactiveAction() {
		$products = $this->productRepository->findAllInactive();
		// Calculate average price
		for($p = 0; $p < count($products); $p++) {
			$products[$p]->averagePrice = $products[$p]->getPurchasePrice() / $products[$p]->getCrateSize();
		}
		$this->view->assign('products', $products);
	}

	/**
	 * Shows a single product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to show
	 * @return void
	 */
	public function showAction(Product $product) {
		$this->view->assign('product', $product);
	}

	/**
	 * Shows a form for creating a new product object
	 * Set default values
	 *
	 * @return void
	 */
	public function newAction() {
		$newProduct = new Product();
		$newProduct->setPurchasePrice(0);
		$newProduct->setDeposit(0);
		$newProduct->setColWidth(1);
		$this->view->assign('newProduct', $newProduct);
	}

	/**
	 * Adds the given new product object to the product repository
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $newProduct A new product to add
	 * @return void
	 */
	public function createAction(Product $newProduct) {
		$newProduct->setHidden(0);
		$newProduct->setActive(1);
		$newProduct->setPosition(100);
		$this->productRepository->add($newProduct);
		$this->addFlashMessage('Created a new product.');
		$this->redirect('index');
	}

	/**
	 * Shows a form for editing an existing product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to edit
	 * @return void
	 */
	public function editAction(Product $product) {
		// Only allow edit if product is not used in unbilled statements or unbilled purchases
		if($this->productUsedUnbilled($product)) {
			// Redirect to limited editing
			$this->redirect('used', 'Product', NULL, array('product' => $product));
		}

		// Create a clone which will become the new product
		// Also provide the original product to set the ancestor and hide it later
		$clone = $this->cloneProduct($product);
		$this->view->assign('product', $clone);
		$this->view->assign('original', $product);

		// Get subproducts if available
		$this->view->assign('subproducts', $this->productRepository->findAllSubproducts($product));
	}

	/**
	 * Shows a form for editing an existing product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to edit
	 * @return void
	 */
	public function usedAction(Product $product) {

		$usedUnbilledPurchases = new \Doctrine\Common\Collections\ArrayCollection();

		// Get subproducts if available and add info if used or not
		$subproducts = $this->productRepository->findAllSubproducts($product);
		if($subproducts) {
			for($p = 0; $p < count($subproducts); $p++) {
				$subproducts[$p]->used = ($this->productUsedUnbilled($subproducts[$p])) ? TRUE : FALSE;

				// If used get purchases it is used in
				$usedIn = $this->purchaseRepository->getUsedPurchases();
				foreach($usedIn as $purchase) {
					$usedUnbilledPurchases->add($purchase);
				}
			}
		}

		// Check if used in statements
		if($this->statementRepository->productUsedInUnbilledStatement($product)) {
			$usedUnbilledStatements = $this->statementRepository->getUsedStatements();
		}

		// Assign view-vars
		$this->view->assign('product', $product);
		$this->view->assign('subproducts', $subproducts);
		$this->view->assign('usedPurchases', $usedUnbilledPurchases);
		$this->view->assign('usedStatements', $usedUnbilledStatements);
	}

	/**
	 * Shows a form for editing an existing product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to edit
	 * @return void
	 */
	public function usedUpdateAction(Product $product) {

		$this->productRepository->update($product);

		$this->redirect('used', 'Product', NULL, array('product' => $product));
	}

	/**
	 * Updates the given product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to update
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $original The original product
	 * @return void
	 */
	public function updateAction(Product $product, Product $original) {
		// Only allow edit if product is not used in unbilled statements or unbilled purchases
		if($this->productUsedUnbilled($original)) {
			$this->addFlashMessage('You cannot edit a product that is used in an unbilled statement or purchase!');
			$this->redirect('index');
		}

		// Get old product and hide it
		$original->setActive(FALSE);
		$original->setHidden(TRUE);

		// Check if original was used in a statement, stock, consumption or purchase
		if(!$this->productUsed($original)) {
			$this->systemLogger->log("Original was NOT used");
			/* Original was not used:
				- Delete subproducts of original
				- Only clone used subproducts
				- Delete original
				- Persist
				- Add new product
				- Add cloned subproducts
			*/
			// Check if original had a ancestor and set ancestor of edited product
			if($original->getAncestor()) {
				$product->setAncestor($original->getAncestor());
			}
			// Don't hide new edited product and add it to the repo
			$product->setHidden(FALSE);
			$product->setActive(TRUE);
			$product->setPosition($original->getPosition());

			// Original or it's subproducts were not used and can be removed / cloned
			$subproducts = $this->productRepository->findAllSubproducts($original);
			$newSubproducts = new \Doctrine\Common\Collections\ArrayCollection();
			// Before we can delete the old product we need to remove the subproducts
			if(count($subproducts) > 0) {
				$this->systemLogger->log("Has subproducts - ".count($subproducts));
				foreach($subproducts as $subproduct) {
					// Clone subproducts to avoid persistence issues
					$newSubproduct = $this->cloneProduct($subproduct);

					// Set Position, Name if necessesary
					if($product->getPosition() != $newSubproduct->getPosition()) {
						$newSubproduct->setPosition($product->getPosition());
					}
					if($product->getName() != $original->getName()) {
						$newSubproduct->setName($product->getName().' ('.$newSubproduct->getCrateSize().')');
					}

					$newSubproducts->add($newSubproduct);
					$this->productRepository->remove($subproduct);
				}
			}
			$this->productRepository->remove($original);

			// Need to call persist all, because Product-ancestor is OneToOne and
			// the old ancestor needs to be removed first before we can call add($product)
			$this->persistenceManager->persistAll();


			$this->productRepository->add($product);

			// Now we can add the subproducts
			if($newSubproducts->count()) {
				foreach($newSubproducts as $newSubproduct) {
					$newSubproduct->setParent($product);
					$this->productRepository->add($newSubproduct);
				}
			}
		} else {
			$this->systemLogger->log("Original was used");
			/* Original was used:
				- Clone subproducts if used and assign new parent
				- Assign new parent if subproduct not used
			*/
			// Set ancestor of edited product and some values
			$product->setAncestor($original);
			$product->setHidden(FALSE);
			$product->setActive(TRUE);
			$product->setPosition($original->getPosition());

			// Clone subproducts
			// Original or it's subproducts were not used and can be removed / cloned
			$subproducts = $this->productRepository->findAllSubproducts($original);
			$newSubproducts = new \Doctrine\Common\Collections\ArrayCollection();
			// Before we can delete the old product we need to remove the subproducts
			if(count($subproducts) > 0) {
				$this->systemLogger->log("Has subproducts - ".count($subproducts));
				foreach($subproducts as $subproduct) {
					if($this->productUsed($subproduct)) {
						$newSubproduct = $this->cloneProduct($subproduct);

						// Set Position, Name if necessesary
						if($product->getPosition() != $newSubproduct->getPosition()) {
							$newSubproduct->setPosition($product->getPosition());
						}
						if($product->getName() != $original->getName()) {
							$newSubproduct->setName($product->getName().' ('.$newSubproduct->getCrateSize().')');
						}

						$newSubproduct->setParent($product);

						$this->productRepository->add($newSubproduct);
					} else {
						// Set Position, Name if necessesary
						if($product->getPosition() != $subproduct->getPosition()) {
							$subproduct->setPosition($product->getPosition());
						}
						if($product->getName() != $subproduct->getName()) {
							$subproduct->setName($product->getName().' ('.$subproduct->getCrateSize().')');
						}

						$subproduct->setParent($product);

						$this->productRepository->update($subproduct);
					}
				}
			}

			// Update original in repo
			$this->productRepository->update($original);
			$this->productRepository->add($product);
		}

		$this->addFlashMessage('Updated the product.');
		$this->redirect('index');
	}

	/**
	 * Add subproduct (To have individual crate sizes)
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $subproduct The subproduct
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $parent The parent of the subproduct
	 * @FLOW3\ValidationGroups({"addSubproductAction"})
	 */
	public function addSubproductAction(\CGROSS\Drinkaccounting\Domain\Model\Product $subproduct, \CGROSS\Drinkaccounting\Domain\Model\Product $parent) {
		// Make a clone of the parent
		$newSubproduct = $this->cloneProduct($parent);

		// Set name, cratesize and parent of the new subproduct
		$newSubproduct->setName($parent->getName().' ('.$subproduct->getCrateSize().')');
		$newSubproduct->setCrateSize($subproduct->getCrateSize());
		$newSubproduct->setParent($parent);

		// Add to repo
		$this->productRepository->add($newSubproduct);
		$this->redirect('edit', 'Product', NULL, array('product' => $parent));
	}

	/**
	 * Remove subproduct
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $subproduct The subproduct to remove
	 */
	public function deleteSubproductAction(\CGROSS\Drinkaccounting\Domain\Model\Product $subproduct) {
		// If it is used throw error message
		if($this->productUsed($subproduct)) {
			$this->addFlashMessage('Can not delete product. It is used somewhere.');
			$this->redirect('edit', 'Product', NULL, array('product' => $subproduct->getAncestor()));
		} // Else delete it
		else {
			$this->addFlashMessage('Subproduct deleted.');
			$this->productRepository->remove($subproduct);
		}
		$this->redirect('edit', 'Product', NULL, array('product' => $subproduct->getParent()));
	}

	/**
	 * Ajax edit the name of the subproduct
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $subproduct The subproduct to edit
	 * @return string
	 */
	public function ajaxUpdateAction(\CGROSS\Drinkaccounting\Domain\Model\Product $subproduct) {
		$this->productRepository->update($subproduct);
		return ($this->arguments['subproduct']->isValid()) ? "VALID" : "INVALID";
	}

	/**
 	 * Check if product was referenced in statement, consumption, stock or purchaseposition
	 * Also check if possible subproducts were used or product is a subproduct
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to check
	 * @return boolean
	 */
	public function productUsed(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		// Check if product is supproduct
		if($product->getParent()) {
			// Is subproduct => Only check purchaseRepo
			if($this->purchaseRepository->productUsed($product)) {
				$this->systemLogger->log("Subproduct and used in purchaseRepo");
				return true;
			}
		} else { // Else it must be a main product, check if product used first, if not also check subproducts
			if ($this->statementRepository->productUsed($product) || $this->purchaseRepository->productUsed($product)) {
				$this->systemLogger->log("Mainproduct and used in Statement and/or Purchase");
				return true;
			} else {
				// Check if possible subproducts are used
				if($this->productRepository->findAllSubproducts($product)->count() > 0) {
					foreach($this->productRepository->findAllSubproducts($product) as $subproduct) {
						// We only have to check the purchaseRepo (only mainproduct shows up in statements
						if($this->purchaseRepository->productUsed($subproduct)) {
							$this->systemLogger->log("Mainproduct unused, but subproduct used.");
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if product was referenced in an unbilled statement or purchaseposition
	 * Also check if possible subproducts were used or product is a subproduct
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to check
	 * @return boolean
	 */
	public function productUsedUnbilled(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		// Check if product is supproduct
		if($product->getParent()) {
			// Is subproduct => Only check purchaseRepo
			if($this->purchaseRepository->productUsedInUnbilledPurchase($product)) {
				$this->systemLogger->log("Subproduct and used in purchaseRepo");
				return true;
			}
		} else { // Else it must be a main product, check if product used first, if not also check subproducts
			if ($this->statementRepository->productUsedInUnbilledStatement($product) || $this->purchaseRepository->productUsedInUnbilledPurchase($product)) {
				$this->systemLogger->log("Mainproduct and used in Statement and/or Purchase");
				return true;
			} else {
				// Check if possible subproducts are used
				if($this->productRepository->findAllSubproducts($product)->count() > 0) {
					foreach($this->productRepository->findAllSubproducts($product) as $subproduct) {
						// We only have to check the purchaseRepo (only mainproduct shows up in statements
						if($this->purchaseRepository->productUsedInUnbilledPurchase($subproduct)) {
							$this->systemLogger->log("Mainproduct unused, but subproduct used.");
							return true;
						}
					}
				}
			}
		}

		return false;
	}


	/**
	 * Clone product
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to clone
	 * @return \CGROSS\Drinkaccounting\Domain\Model\Product The clone
	 */
	public function cloneProduct(\CGROSS\Drinkaccounting\Domain\Model\Product $product) {
		// Create new product
		$newProduct = new \CGROSS\Drinkaccounting\Domain\Model\Product();
		$newProduct->setName($product->getName());
		$newProduct->setPrice($product->getPrice());
		$newProduct->setCrateSize($product->getCrateSize());
		$newProduct->setPurchasePrice($product->getPurchasePrice());
		$newProduct->setDeposit($product->getDeposit());
		$newProduct->setActive($product->getActive());
		$newProduct->setPosition($product->getPosition());
		$newProduct->setColWidth($product->getColWidth());
		$newProduct->setHidden($product->getHidden());
		if($product->getParent()) {
			$newProduct->setParent($product->getParent());
		}

		return $newProduct;
	}

	/**
	 * Deactivates the given product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to deactivate
	 * @return void
	 */
	public function deactivateAction(Product $product) {
		$product->setActive(FALSE);
		$this->productRepository->update($product);
		$this->addFlashMessage('Deactivated product '.$product->getName().'.');
		$this->redirect('index');
	}

	/**
	 * Activates the given product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to activate
	 * @return void
	 */
	public function activateAction(Product $product) {
		$product->setActive(TRUE);
		$this->productRepository->update($product);
		$this->addFlashMessage('Activated product '.$product->getName().'.');
		$this->redirect('index');
	}

	/**
	 * Deletes the given product object
	 *
	 * @param \CGROSS\Drinkaccounting\Domain\Model\Product $product The product to delete
	 * @return void
	 */
	public function deleteAction(Product $product) {
		// If product used hide it, else remove it from repository
		if($this->productUsed($product)) {
			$product->setActive(FALSE);
			$product->setHidden(TRUE);
			$this->productRepository->update($product);
			// Delete unused subproducts
			if($this->productRepository->findAllSubproducts($product)->count() > 0) {
				foreach($this->productRepository->findAllSubproducts($product) as $subproduct) {
					if(!$this->productUsed($subproduct)) {
						$this->productRepository->remove($subproduct);
					}
				}
			}
			$this->addFlashMessage('Deleted product '.$product->getName().'*.');
		} else {
			// Check if it has subproducts and delete them too (it was checked if they were used before)
			if($this->productRepository->findAllSubproducts($product)->count() > 0) {
				foreach($this->productRepository->findAllSubproducts($product) as $subproduct) {
					$this->productRepository->remove($subproduct);
				}
			}
			$this->productRepository->remove($product);
			$this->addFlashMessage('Deleted product '.$product->getName().'.');
		}

		$this->redirect('inactive');
	}
}

?>