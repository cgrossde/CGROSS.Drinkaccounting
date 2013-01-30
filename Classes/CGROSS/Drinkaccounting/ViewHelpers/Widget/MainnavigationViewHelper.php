<?php
namespace CGROSS\Drinkaccounting\ViewHelpers\Widget;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as FLOW3;

class MainnavigationViewHelper extends \TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @FLOW3\Inject
	 * @var \CGROSS\Drinkaccounting\ViewHelpers\Widget\Controller\MainnavigationController
	 */
	protected $controller;

	/**
	 * Render main navigation
	 *
	 * @return string
	 */
	public function render() {
		return $this->initiateSubRequest();
	}
}
?>