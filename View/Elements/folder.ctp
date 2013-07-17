<div class="name-index folder span6">
	<?php echo $this->Html->link(
		$folder . '/',
		array_merge(
			$pathFolderNames,
			array($folder)
		)
	); ?>
</div>
<div class="folder-actions btn-group span6">
	<?php echo $this->Html->link(
		__d("document_manager", "Ouvrir"),
		array_merge(
			$pathFolderNames,
			array($folder)
		),
		array('class' => 'btn edit')
	); ?>
	<?php echo $this->Html->link(
		__d("document_manager", "Supprimer"),
		array_merge(
			$pathFolderNames,
			array(
				'action' => 'delete_folder',
				'folder' => $folder,
			)
		),
		array('class' => 'btn btn-danger ajax-delete confirm', 'title' => __d("document_manager", "Etes-vous certain de vouloir supprimer cette entrée ?"))
	); ?>
</div>
<div class="clear"></div>
