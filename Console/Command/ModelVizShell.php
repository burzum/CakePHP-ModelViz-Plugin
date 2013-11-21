<?php
/**
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Florian Krämer
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class ModelVizShell extends AppShell {

/**
 * Uses
 *
 * @var array
 */
	public $uses = array(
		'ModelViz.ModelViz'
	);

/**
 * Main
 *
 * @return void
 */
	public function main() {
		$result = $this->ModelViz->getModelData();
		$result = $this->ModelViz->buildGraphvizModelData($result);
		$this->out($result);
	}

}