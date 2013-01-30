<?php
namespace CGROSS\Drinkaccounting\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Drinkaccounting".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as FLOW3;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * Defautl Controller to provide features to all controllers like languageDetection
 *
 */
class DefaultController extends ActionController {

	/**
	 * @var \TYPO3\Flow\I18n\Locale
	 */
	protected $lang;

	public function initializeView(\TYPO3\Flow\Mvc\View\ViewInterface $view) {
		$detector   = new \TYPO3\Flow\I18n\Detector();
		$this->lang = $detector->detectLocaleFromHttpHeader($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		$view->assign('langDetector', $this->lang);
	}
}
?>