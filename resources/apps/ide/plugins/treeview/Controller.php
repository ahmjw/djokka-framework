<?php

namespace Djokka\Plugins\Treeview;

defined('DJOKKA') or die('No direct access to this file');

use Djokka\BaseController;

class Controller extends BaseController
{
	public function actionIndex()
	{
		$url = $this->getInfo('url');
		$this->asset($url.'Debugger.js');
		$this->asset($url.'ImageList.js');
		$this->asset($url.'TreeView.js');
		$this->asset($url.'treeview.css');

		$id = (int)$this->param('id');


		$current = $this->model('/AppsDocs')->find($id, array(
			'select'=>'id, title, parent_id',
		));
		$model = $this->model('/AppsDocs')->findAll(array(
			'select'=>'id, title',
			'where'=>array('parent_id=?', $id),
			'order'=>'position',
		));
		$node = '';
		if ($id > 0) {
			if($model->rowCount > 0) {
				if ($current !== null) {
					$model = $this->model('/AppsDocs')->findAll(array(
						'select'=>'id, title',
						'where'=>array('parent_id=?', $current->parent_id),
						'order'=>'position',
					));
					if($model->rowCount > 0) {
						foreach ($model->rows as $item) {
							$link = $this->link('/docs/read/'.$item->id.'-'.$this->lib('String')->slugify($item->title));
							if($item->childCount > 0) {
								$node .= "{text:'{$item->title} ({$item->childCount})', image:'folder', url:'{$link}', target: 'main', command:'parent_id={$item->id}'},";
							} else {
								$node .= "{text:'{$item->title}', image:'file', url:'{$link}', target: 'main', is_leaf: true, command:'parent_id={$item->id}'},";
							}
						}
					}
				}
			} else {
				if ($current !== null) {
					$current_parent = $this->model('/AppsDocs')->find($current->parent_id, array(
						'select'=>'id, title, parent_id',
					));
					$model = $this->model('/AppsDocs')->findAll(array(
						'select'=>'id, title',
						'where'=>array('parent_id=?', $current_parent->parent_id),
						'order'=>'position',
					));
					if($model->rowCount > 0) {
						foreach ($model->rows as $item) {
							$link = $this->link('/docs/read/'.$item->id.'-'.$this->lib('String')->slugify($item->title));
							if($item->childCount > 0) {
								$node .= "{text:'{$item->title} ({$item->childCount})', image:'folder', url:'{$link}', target: 'main', command:'parent_id={$item->id}'},";
							} else {
								$node .= "{text:'{$item->title}', image:'file', url:'{$link}', target: 'main', is_leaf: true, command:'parent_id={$item->id}'},";
							}
						}
					}
				}
			}
		} else {
			foreach ($model->rows as $item) {
				$link = $this->link('/docs/read/'.$item->id.'-'.$this->lib('String')->slugify($item->title));
				if($item->childCount > 0) {
					$node .= "{text:'{$item->title} ({$item->childCount})', image:'folder', url:'{$link}', target: 'main', command:'parent_id={$item->id}'},";
				} else {
					$node .= "{text:'{$item->title}', image:'file', url:'{$link}', target: 'main', is_leaf: true, command:'parent_id={$item->id}'},";
				}
			}
		}

$js = <<<EOD
var \$tree_docs = new TreeView();

\$tree_docs.imageList = new ImageList({
	folder: 'folder_current.png',
	collapse: 'treeRightTriangleBlack.png', 
	expand: 'treeDownTriangleBlack.png',
	file: 'resourceDocumentIconSmall.png',
	success: 'successGreenDot.png',
	warning: 'warningOrangeDot.png',
	error: 'errorRedDot.png',
	function: 'stock_effects-object-colorize.png',
	class: '1384448638_blockdevice.png',
	framework: 'document-library.png',
	module: '1392836897_file-roller.png',
	model: '1392837040_Model.png'
});
EOD;

if ($node != '') {
$js .= "\$tree_docs.node = [
	{$node},
];";
}

$js .= <<<EOD
\$tree_docs.debugger = new Debugger();
\$tree_docs.debugger.loader = '#debug-loader';
\$tree_docs.imageList.setBaseUrl('{$url}img/');
\$tree_docs.renderTo('.treeview-container');
\$tree_docs.debugger.setUrl('{$this->link('/plugin.treeview/load')}');
EOD;
		$this->js($js);
	}

	public function actionLoad()
	{
		$model = $this->model('/AppsDocs')->findAll(array(
			'select'=>'id, title, parent_id',
			'where'=>array('parent_id=?', abs((int)$_GET['parent_id'])),
			'order'=>'position',
		));
		$data = array();
		if($model->rowCount > 0) {
			foreach ($model->rows as $item) {
				$url = $this->link('/docs/read/'.$item->id.'-'.$this->lib('String')->slugify($item->title));
				if($item->childCount > 0) {
					$data[] = array(
						'text'=>$item->title . ' ('.$item->childCount.')',
						'image'=>'folder',
						'url'=>$url,
						'target'=>'main',
						'command'=>'parent_id='.$item->id,
					);
				} else {
					$data[] = array(
						'text'=>$item->title,
						'image'=>'file',
						'is_leaf'=>true,
						'url'=>$url,
						'target'=>'main',
					);
				}
			}
		}
		echo json_encode(array('data'=>$data));
	}
}