<?php defined('_JEXEC') || die('=;)'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<jdoc:include type="head" />

		<!--
        <script src="themes/<?= $this->template ?>/js/mootools-core-1.4.2-full-nocompat.js" type="text/javascript"></script>
        <script src="themes/<?= $this->template ?>/js/mootools-more-1.4.0.1.js" type="text/javascript"></script>

		<script src="themes/<?= $this->template ?>/js/<?= $this->template ?>.js" type="text/javascript"></script>
        -->
		<link href="themes/<?= $this->template ?>/css/<?= $this->template ?>.css" media="screen" rel="stylesheet" type="text/css" />

	</head>
	<body>
		<h1>Joomla! DistroBuilder</h1>

        <jdoc:include type="message" />
		<jdoc:include type="component" name="main" />

		<div class="footer">
			&bull; Made 2012 by <a href="http://github.com/JNafu">The JNafu Team</a>
			&bull; Running on <a class="icon-joomla" href="http://github.com/joomla/joomla-platform">Joomla! Platform</a>
			<?php echo JPlatform::getShortVersion(); ?>
			&bull;
		</div>
	</body>
</html>
