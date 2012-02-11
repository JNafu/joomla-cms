<?php
/**
 * Created by JetBrains PhpStorm.
 * User: elkuku
 * Date: 11.02.12
 * Time: 13:58
 * To change this template use File | Settings | File Templates.
 */

?>

<h2>Archive</h2>

<?php if( ! count($this->archiveFiles)) : ?>
	<p>The archive is empty.</p>
	<?php return; ?>
<?php endif; ?>

<ul>
<?php foreach($this->archiveFiles as $fileName) : ?>
	<li><a href="build/zips/<?= $fileName ?>"><?= $fileName ?></a></li>
<?php endforeach; ?>
</ul>
