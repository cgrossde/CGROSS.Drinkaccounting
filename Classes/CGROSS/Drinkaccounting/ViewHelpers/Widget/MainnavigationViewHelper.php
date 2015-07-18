<?php
namespace CGROSS\Drinkaccounting\ViewHelpers\Widget;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

class MainnavigationViewHelper extends \TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @Flow\Inject
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