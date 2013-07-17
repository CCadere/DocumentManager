<div class="name-index file span6">
	<?php
	$name = $file['name'];
	if (!empty($file['User'])) {// append owner's name only if it exists, otherwise it was a file uploaded through another way (FTP for example)
		$name .= $this->Html->tag('div', __d("document_manager", "Propriétaire %s %s", $file['User']['first_name'], $file['User']['last_name'] ), array('class' => 'owner'));
	}
	echo $this->Html->link($name, Document::getRelativePath($pathFolderNames, $file['name']), array('escape' => false, 'class' => 'file-link', 'direct' => true));
	?>
</div>
<div class="file-actions btn-group span6">
	<a href="#" class="view-extra btn"><?php echo __d("document_manager", "Détails");?></a>
	<?php echo $this->Html->link(
		__d("document_manager", "Copier l'URL"),
		$this->Html->url(Document::getRelativePath($pathFolderNames, $file['name']), true),
		array('class' => 'btn copy-contents', 'direct' => true)
	); ?>
<?php if ($this->DocumentManager->hasAdminRights() || !isset($file['Document']['user_id']) || $this->DocumentManager->fileBelongsToUser($file['Document']['user_id'])): ?>
	<?php echo $this->Html->link(
		__d("document_manager", "Renommer"),
		array_merge(
			$pathFolderNames,
			array('action' => 'rename_file')
		),
		array(
			'class' => 'btn ajax-rename',
			'filename' => $file['name'],
			'title' => __d("document_manager", "Nouveau nom de fichier :")
		)
	); ?>
	<?php echo $this->Html->link(
		__d("document_manager", "Supprimer"),
		array_merge(
			$pathFolderNames,
			array(
				'action' => 'delete_file',
				'file' => $file['name'],
			)
		),
		array('class' => 'btn btn-danger ajax-delete confirm', 'title' => __d("document_manager", "Etes-vous certain de vouloir supprimer cette entrée ?"))
	); ?>
<?php endif; ?>

</div>

<div class="clear file-extra span12">
	<div class="owner-profile user-block">
		<div class="left image-wrapper">
<?php if (!empty($file['User']['picture'])): ?>
			<?php echo $this->Html->image($file['User']['picture'], array('alt' => __d("document_manager", "Image de profil"), 'class' => 'clear')); ?>
<?php else: ?>
			<?php echo $this->Html->image('/document_manager/img/anon.jpg', array('alt' => __d("document_manager", "Image de profil"), 'class' => 'clear')); ?>
<?php endif; ?>
		</div>
<?php if (!empty($file['User']['email'])): ?>
		<div class="mailto"><?php echo $this->Html->link($file['User']['email'] , 'mailto:' . $file['User']['email']); ?></div>
<?php endif; ?>
	</div>
<?php if (!empty($file['Document']['comments'])): ?>
	<div class="file-comment file-extra column grid_6">
		<div class="content"><?php echo $file['Document']['comments']?></div>
	</div>
<?php endif; ?>
</div>

<div class="clear"></div>
