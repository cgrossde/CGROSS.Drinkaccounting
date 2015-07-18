<?php
namespace CGROSS\Drinkaccounting\ViewHelpers;

use TYPO3\Flow\Annotations as Flow;

/**
 * AjaxViewHelper
 *
 * = Examples =
 * {namespace da=Accounting\ViewHelpers}
 * <da:ajax object="{transaction}" property="desc" arguments="{account: account}" action="accounting/transaction/edit" />
 */

 class AjaxViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 *
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

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
	 * To generate trustedProerties token
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService
	 */
	protected $mvcPropertyMappingConfigurationService;

	/**
	 * Inject the settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Allowed arguments
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
	}

	/**
	 * @var string
	 */
	 protected $tagName = 'div';

	 /**
	  * ViewHelper that generates an ajax div
	  *
	  * @param mixed $object Object of the property we want to modify
	  * @param string $property Property we want to modify
	  * @param string $action Path to controller ex.: package/controller/action
	  * @param array $arguments optional
	  * @param string $name optional
	  * @return string
	  */
	 public function render($object, $property, $action, $arguments = NULL, $name = NULL) {

		// Get objectidentifier
		$objectID = $this->persistenceManager->getIdentifierByObject($object);

		// Retrieve objectname from class or if set from $name
		if(is_null($name)) {
			$objectClassNameTemp = explode('\\', get_class($object));
			$objectClassName = lcfirst(end($objectClassNameTemp));
		} else {
			$objectClassName = $name;
		}

		// Generate allowed propertymapping
		$formFieldNames = array(
	 		$objectClassName."[__identity]",
	 		$objectClassName."[".$property."]");

		$trustedProperties = $this->mvcPropertyMappingConfigurationService->generateTrustedPropertiesToken($formFieldNames);
		$span = 'span3'; //Size of input

		// Value of property
		$ajaxValue = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($object, $property);
		// In case of dates
		if($ajaxValue instanceof \DateTime) {
			$ajaxValue = $ajaxValue->format($this->settings['system']['dateFormat']);
			$span = 'span2';
		}

	 	$jsCode="
	 	if($(this).attr('value') == 'Edit') {
					$(this).attr('class','icon-ok');
					$(this).attr('value','Update');
					text = $(this).parent().find('span.ajaxValue').text();
					// Create input field
					$(this).parent().find('span.ajaxValue').replaceWith($('<input>', {
						'class' : 'ajaxValue ".$span."',
						'type' : 'text',
						'value' : text
					}));
				} else if($(this).attr('value') == 'Update'){
					$(this).attr('value','InProgress');
					$(this).attr('class','icon-time');
					newValue = $(this).parent().find('input.ajaxValue').val();
					current = $(this);
					// Make Ajaxrequest
					$.ajax({
						type: 'POST',
						url: '" . $action . "',
						data: {";
						// Add identities of arguments
						if(!is_null($arguments)) {
							foreach ($arguments as $key => $value) {
								$jsCode .= "'".$key."[__identity]' : '".$this->persistenceManager->getIdentifierByObject($value)."',";
							}
						}
						// Object identity + value of object
						$jsCode .= "'" . $objectClassName."[__identity]' : '" . $objectID . "',";
						$jsCode .= "'" . $objectClassName."[".$property."]' : newValue  ,";
						// Trusted properties
						$jsCode .= "'__trustedProperties' : '" . htmlspecialchars($trustedProperties) . "'";
						$jsCode .= "
						}
					}).success(function(jqXHR,msg) {
						current.parent().find('input.ajaxValue').replaceWith($('<span/>', {
							'class' : 'ajaxValue',
							text : newValue
						}));
						current.attr('value','Edit');
						current.attr('class', 'icon-pencil');
					}).error(function(jqXHR,msg) {
						alert('ERROR: ' + msg);
					});
				} else {
					alert('Please wait...');
				}
	 	";

		// Add code and render
	 	$this->tag->setContent("<span class=\"ajaxValue\">" .$ajaxValue . "</span>
	 							<i value=\"Edit\" class=\"icon-pencil\" OnClick=\"". $jsCode ."\"></i>");

		return $this->tag->render();
	 }
 }
