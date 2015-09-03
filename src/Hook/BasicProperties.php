<?php
namespace CDS\Hook;
class BasicProperties {
	private $data;

	public function __construct($data) {
		$this->data = $data;
	}

	public function content($value, $element, $rule) {
		if (in_array('before', $rule->getPseudoMatcher()->getPseudo())) $element->firstChild->nodeValue = implode('', $value) . $element->firstChild->nodeValue;
		else if (in_array('after', $rule->getPseudoMatcher()->getPseudo())) $element->firstChild->nodeValue .= implode('', $value);
		else $element->firstChild->nodeValue = implode('', $value);	
	}

	public function repeat($value, $element, $rule) {
		foreach ($value as $iteration) {
			$clone = $element->cloneNode(true);
			$this->data->bind($clone, $iteration);
			$element->parentNode->insertBefore($clone, $element);

			//Re-run the hook on the new element, but use the iterated data
			$newRules = $rule->getRules();

			//Don't run repeat on the clones element or it will loop forever
			unset($newRules['repeat']);

			$hook = new Rule($newRules, $rule->getPseudoMatcher(), $this->data);
			foreach ($rule->getProperties() as $name => $value) $hook->registerProperty($name, $value);
			$hook->run($clone);
		}

		//Remove the original element so only the ones that have been looped over will show
		$element->parentNode->removeChild($element);

		return false;
	}

	public function display($value, $element, $rule) {
		if (strtolower($value[0]) === 'none') $element->parentNode->removeChild($element);
	}

}