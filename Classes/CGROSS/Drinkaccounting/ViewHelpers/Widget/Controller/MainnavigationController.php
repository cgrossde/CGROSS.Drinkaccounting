<?php
namespace CGROSS\Drinkaccounting\ViewHelpers\Widget\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Conference".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as FLOW3;

/**
 */
class MainnavigationController extends \TYPO3\Fluid\Core\Widget\AbstractWidgetController {

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
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\UserRepository
	 */
	protected $userRepository;

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\Domain\Repository\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @return void
	 */
	public function indexAction() {
		$detector   = new \TYPO3\Flow\I18n\Detector();
		$lang = $detector->detectLocaleFromHttpHeader($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		$this->view->assign('langDetector', $lang);

		$this->view->assign('products', $this->productRepository->findAll());
		$this->view->assign('users', $this->userRepository->findAll());
		$this->view->assign('accounts', $this->accountRepository->findAll());
		$this->view->assign('statements', $this->statementRepository->findAllUnbilled());
	}

}
?>