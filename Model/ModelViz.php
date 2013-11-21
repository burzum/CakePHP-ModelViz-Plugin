<?php
/**
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Florian Krämer
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class ModelViz extends AppModel {

/**
 *
 */
	public function buildGraphvizModelData($modelRelationArray) {
		$code = 'digraph prof {' . '
ratio = fill;' . '
node [style=filled];' . PHP_EOL . '';

		foreach ($modelRelationArray as $modelClassName => $modelRelationTypes) {
			$rColor = mt_rand(100, 900) / 1000;
			$gColor = mt_rand(100, 900) / 1000;
			$bColor = mt_rand(100, 900) / 1000;
			$code .= "\t" . sprintf('"%s" [color="%s %s %s"]' . PHP_EOL, $modelClassName, $rColor, $gColor, $bColor);
			foreach ($modelRelationTypes as $modelRelationTypeName => $modelRelations):
				foreach ($modelRelations as $relationAlias => $modelRelation):
					$code .= "\t" . sprintf('"%s" -> "%s" [color="%s %s %s", label = "%s as %s"]' . PHP_EOL,
						$modelClassName,
						$modelRelation['className'],
						$rColor,
						$gColor,
						$bColor,
						$modelRelationTypeName,
						$relationAlias
					);
				endforeach;
			endforeach;
		}

		$code .= '}';
		return $code;
	}

	public function getPluginModelPaths() {
		$pluginModelPaths = [];
		$plugins = CakePlugin::loaded();
		foreach ($plugins as $plugin) {
			$pluginModelPaths[$plugin] = CakePlugin::path($plugin) . 'Model';
		}
		return $pluginModelPaths;
	}

	public function getModelData($options = array()) {
		$defaults = array(
			'plugins' => true,
			'filterAppModels' => true,
			'skipModels' => array(
				'AppModel'
			)
		);
		$options = Hash::merge($defaults, $options);

		$skipModels = array('AppModel');
		$modelRelationArray = array();
		$skippedClassCount = 0;
		$hasOneCount = 0;
		$hasManyCount = 0;
		$belongsToCount = 0;
		$hasAndBelongsToManyCount = 0;
		$relationCount = 0;
		$skippedRelationCount = 0;
		$relationTypes = array(
			'hasOne',
			'hasMany',
			'belongsTo',
			'hasAndBelongsToMany',
		);

		$modelPath = ROOT . DS . APP_DIR . DS . 'Model';
		$modelFiles = glob($modelPath . '/*.php', GLOB_NOSORT);

		if ($options['plugins'] === true) {
			$plugins = $this->getPluginModelPaths();
			foreach ($plugins as $plugin => $pluginPath) {
				$modelFiles = Hash::merge($modelFiles, glob($pluginPath . '/*.php', GLOB_NOSORT));
			}
			foreach ($modelFiles as $key => $file) {
				if (substr($file, -12) === 'AppModel.php' && $options['filterAppModels'] === true) {
					unset($modelFiles[$key]);
				}
			}
		}

		$classCount = count($modelFiles);

		foreach ($modelFiles as $modelFile) {
			$modelClass = basename($modelFile, '.php');
			if (in_array($modelClass, $skipModels)) {
				$skippedClassCount++;
				continue;
			}
			require_once $modelFile;
			$reflectionClass = new ReflectionClass($modelClass);

			$modelRelationArray[$modelClass] = array();
			foreach ($relationTypes as $relationType) {
				if ($reflectionClass->hasProperty($relationType)) {
					$modelRelations = $reflectionClass->getProperty($relationType)->getValue(new $modelClass);
					foreach ($modelRelations as $relationAlias => $modelRelation) {
						$relationCount++;

						if (in_array($modelRelation['className'], $options['skipModels'])) {
							$skippedRelationCount++;
							continue;
						}

						${$relationType . 'Count'} += 1;
						$modelRelationArray[$modelClass][$relationType][$relationAlias] = $modelRelation;
					}
				}
			}
		}

		return $modelRelationArray;
	}

}