<?php
/**
 * Created by JetBrains PhpStorm.
 * User: elkuku
 * Date: 10.02.12
 * Time: 20:56
 * To change this template use File | Settings | File Templates.
 */

$cmd = (count($this->packages)) ? 'Update' : 'Build';
?>

<a class="command" href="index.php?task=build"><?= $cmd ?></a>

<?php if( ! count($this->packages)) return; ?>

<form action="index.php?task=make" method="post">

	<?php foreach ($this->packages as $repo => $packages) : ?>
	<h2><?= $repo ?></h2>

	<ul>
		<?php foreach ($packages as $package) : ?>
		<li>
			<input type="checkbox" name="packages[<?= $repo ?>][]" value="<?= $package ?>" id="<?= $repo . $package ?>"/>
			<label for="<?= $repo . $package ?>"><?= $package ?></label>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endforeach; ?>

	<input type="submit" class="buildButton" value="Build"/>

</form>

<?= $this->loadTemplate('archive') ?>
