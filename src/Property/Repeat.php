<?php
/* @description     Transformation Style Sheets - Revolutionising PHP templating    *
 * @author          Tom Butler tom@r.je                                             *
 * @copyright       2015 Tom Butler <tom@r.je> | https://r.je/                      *
 * @license         http://www.opensource.org/licenses/bsd-license.php  BSD License *
 * @version         1.0                                                             */
namespace Transphporm\Property;
class Repeat implements \Transphporm\Property {
	private $functionSet;
	private $elementData;
	private $line;

	public function __construct(\Transphporm\FunctionSet $functionSet, \Transphporm\Hook\ElementData $elementData, &$line) {
		$this->functionSet = $functionSet;
		$this->elementData = $elementData;
		$this->line = &$line;
	}

	public function run(array $values, \DomElement $element, array $rules, \Transphporm\Hook\PseudoMatcher $pseudoMatcher, array $properties = []) {
		$values = $this->fixEmpty($values);
		if ($element->getAttribute('transphporm') === 'added') return $element->parentNode->removeChild($element);
		$max = $this->getMax($values);
		$count = 0;

		foreach ($values[0] as $key => $iteration) {
			if ($count+1 > $max) break;
			$clone = $this->cloneElement($element, $iteration, $key, $count++);
			//Re-run the hook on the new element, but use the iterated data
			//Don't run repeat on the clones element or it will loop forever
			unset($rules['repeat']);
			$this->createHook($rules, $pseudoMatcher, $properties)->run($clone);
		}
		//Remove the original element
		$element->parentNode->removeChild($element);
		return false;
	}

	private function fixEmpty($value) {
		if (empty($value[0])) $value[0] = [];
		return $value;
	}

	private function cloneElement($element, $iteration, $key, $count) {
		$clone = $element->cloneNode(true);
		$this->tagElement($clone, $count);

		$this->elementData->bind($clone, $iteration, 'iteration');
		$this->elementData->bind($clone, $key, 'key');
		$element->parentNode->insertBefore($clone, $element);
		return $clone;
	}

	private function tagElement($element, $count) {
		//Mark all but one of the nodes as having been added by transphporm, when the hook is run again, these are removed
		if ($count > 0) $element->setAttribute('transphporm', 'added');
	}

	private function getMax($values) {
		return isset($values[1]) ? $values[1] : PHP_INT_MAX;
	}

	private function createHook($newRules, $pseudoMatcher, $properties) {
		$var = ""; // PropertyHook requires that baseDir be passed by refrence
		// and there is no reason to pass it so create $var to avoid errors
		$hook = new \Transphporm\Hook\PropertyHook($newRules, $var, $this->line, "", $this->line, $pseudoMatcher, new \Transphporm\Parser\Value($this->functionSet), $this->functionSet);
		foreach ($properties as $name => $property) $hook->registerProperty($name, $property);
		return $hook;
	}
}
