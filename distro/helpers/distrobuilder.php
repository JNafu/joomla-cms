<?php
/**
 * Created by JetBrains PhpStorm.
 * User: elkuku
 * Date: 10.02.12
 * Time: 21:33
 * To change this template use File | Settings | File Templates.
 */

jimport('joomla.filesystem.folder');

class DistroBuilderHelper
{
	public static function build(SimpleXMLElement $xml, $pathBase, $pathBuild)
	{
		jimport('joomla.filesystem.file');

		$pathBarebone = $pathBuild . '/barebone';
		$pathPackages = $pathBuild . '/packages';

		JFolder::delete($pathBarebone);
		JFolder::create($pathBarebone);

		JFolder::delete($pathPackages . '/joomla');

		foreach (JFolder::files($pathBase) as $file)
		{
			JFile::copy($pathBase . '/' . $file, $pathBarebone . '/' . $file);
		}

		$excludes = array('distro', 'build');

		foreach (JFolder::folders($pathBase) as $folder)
		{
			if (in_array($folder, $excludes))
				continue;

			JFolder::copy($pathBase . '/' . $folder, $pathBarebone . '/' . $folder);
		}

		// @todo change ;)

		foreach ($xml->packages as $packages)
		{
			$repo = $packages->attributes()->group;

			/** @var $p SimpleXMLElement */
			foreach ($packages->package as $p)
			{
				$package = (string) $p->attributes()->name;

				JFolder::create($pathPackages . '/' . $repo . '/' . $package);

				foreach ($p->item as $item)
				{
					//				$this->output((string)$item.'...', false);

					JFolder::create(dirname($pathPackages . '/' . $repo . '/' . $package . '/' . $item));

					if (rename($pathBarebone . '/' . $item, $pathPackages . '/' . $repo . '/' . $package . '/' . $item))
					{
						//					$this->output('ok', true, 'green');
					}
					else
					{
						//					$this->output('FAILED', true, 'red');
					}
				}
			}
		}

		// @todo let's switch to XML instead of SQL....
		self::chopSql($xml, $pathBuild);
	}

	public static function make(array $packages, $pathBuild)
	{
		$pathBarebone = $pathBuild . '/barebone';
		$pathPackages = $pathBuild . '/packages';
		$pathTemp = $pathBuild . '/temp';

		JFolder::delete($pathTemp);

		JFolder::copy($pathBarebone, $pathTemp);

		$sql = JFile::read($pathTemp . '/installation/sql/mysql/joomla.sql');

		foreach ($packages as $repo => $rPackages)
		{
			foreach ($rPackages as $package)
			{
				$path = $pathPackages . '/' . $repo . '/' . $package;

				if (!is_dir($path))
					throw new Exception('Package not found in path: ' . $path);

				$folders = JFolder::folders($path);

				if(JFile::exists($path . '/install.sql'))
					$sql .= JFile::read($path . '/install.sql');

				foreach ($folders as $folder)
				{
					JFolder::copy($path . '/' . $folder, $pathTemp . '/' . $folder, '', true);
				}
			}
		}

		JFile::write($pathTemp . '/installation/sql/mysql/joomla.sql', $sql);

		self::zipIt($pathBuild);
	}

	public static function getPackages($pathBuild)
	{
		$pathPackages = $pathBuild . '/packages';

		$packages = array();

		if (!JFolder::exists($pathPackages))
			return $packages;

		$repos = JFolder::folders($pathPackages);

		foreach ($repos as $repo)
		{
			$packages[$repo] = JFolder::folders($pathPackages . '/' . $repo);
		}

		return $packages;
	}

	public static function getArchiveFiles()
	{
		if(!JFolder::exists(JPATH_BASE . '/build/zips'))
			return array();

		return JFolder::files(JPATH_BASE . '/build/zips');
	}

	private static function chopSql(SimpleXMLElement $xml, $pathBuild)
	{
		$pathBarebone = $pathBuild . '/barebone';
		$pathPackages = $pathBuild . '/packages';

		if( ! file_exists($pathBarebone.'/installation/sql/mysql/joomla.sql'))
			throw new Exception('Joomla! SQL not found');

		$origSql = JFile::read($pathBarebone.'/installation/sql/mysql/joomla.sql');

		$origQueries = self::splitQueries($origSql);

		$stripResult = array();

		/** @var $p SimpleXMLElement */
		foreach($xml->packages->package as $p)
		{
			$package = (string)$p->attributes()->name;

			if( ! isset($stripResult[$package])) $stripResult[$package] = array();

			/** @var $query SimpleXMLElement */
			foreach($p->query as $query)
			{
				$command = (string)$query->attributes()->type;
				$table = (string)$query;
				$item = (string)$query->attributes()->item;

				foreach($origQueries as $qNum => $query)
				{
					$query = trim($query);
					$pieces = explode("\n", $query);

					$qCommand = $pieces[0];

					$qParts = explode(' ', $qCommand);

					if(strtolower($qParts[0]) != $command)
						continue;

					if( ! preg_match('/#__'.$table.'`/', $qCommand))
						continue;

					switch($command)
					{
						case 'create':
							$stripResult[$package][] = $query;
							$origQueries[$qNum] = '';
							break;

						case 'insert':
							if( ! isset($item))
								exit('No strip set: '.$query);

							$strip = $item;

							$qNew = array();
							$qNew[] = $qCommand;

							if(strpos($qCommand, 'VALUES') === false)
								$qNew[] = 'VALUES';

							$qOld = array();
							$found = false;

							foreach($pieces as $p)
							{
								if(preg_match('/'.$strip.'/', $p))
								{
									$qNew[] = $p;
									$found = true;
								}
								else
								{
									$qOld[] = $p;
								}
							}//foreach

							$last = count($qNew) - 1;
							$qNew[$last] = rtrim($qNew[$last], ',;');
							$qNew[$last] .= ';';

							$last = count($qOld) - 1;

							if($last >= 0)
							{
								$qOld[$last] = rtrim($qOld[$last], ',;');
								$qOld[$last] .= ';';
							}

							if($found)
							{
								$stripResult[$package][] = implode("\n", $qNew);
								$origQueries[$qNum] = trim(implode("\n", $qOld));
							}

							break;

						default:
							//nodefault..
							break;
					}//switch
				}//foreach
			}//foreach
		}//foreach

		foreach($stripResult as $package => $queries)
		{
			if( ! $queries)
			{
				// The package has no queries
				continue;
			}

			$fName = $pathPackages.'/joomla/'.$package.'/install.sql';
			$buffer = implode("\n\n", $queries);

			if( ! JFile::write($fName, $buffer))
				throw new Exception('Can not open '.$fName);
		}//foreach

		$fName = $pathBarebone.'/installation/sql/mysql/joomla.sql';
		$buffer = implode("\n\n", $origQueries);

		if( ! JFile::write($fName, $buffer))
			throw new Exception('Can not open '.$fName);
	}

	/**
	 * Method to split up queries from a schema file into an array.
	 *
	 * @param string $sql SQL schema.
	 *
	 * @return array Queries to perform.
	 */
	private static function splitQueries($sql)
	{
		// Initialise variables.
		$buffer        = array();
		$queries    = array();
		$in_string    = false;

		// Trim any whitespace.
		$sql = trim($sql);

		// Remove comment lines.
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);

		// Parse the schema file to break up queries.
		for($i = 0; $i < strlen($sql) - 1; $i ++)
		{
			if($sql[$i] == ";" && ! $in_string)
			{
				$queries[] = trim(substr($sql, 0, $i + 1));
				$sql = substr($sql, $i + 1);
				$i = 0;
			}

			if($in_string
				&& ($sql[$i] == $in_string)
				&& $buffer[1] != "\\")
			{
				$in_string = false;
			}
			else if( ! $in_string
				&& ($sql[$i] == '"' || $sql[$i] == "'")
				&& ( ! isset ($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $sql[$i];
			}

			if(isset($buffer[1])) $buffer[0] = $buffer[1];

			$buffer[1] = $sql[$i];
		}//for

		// If the is anything left over, add it to the queries.
		if( ! empty($sql)) $queries[] = $sql;

		return $queries;
	}//function

	private static function zipIt($pathBuild)
	{
		$pathTemp = $pathBuild . '/temp';
		$pathZip = $pathBuild.'/zips';

		JFolder::create($pathZip);

		$fName = date('Ymd-His').'-TEST.zip';

		$files = JFolder::files($pathTemp, '.', true, true);

		$zip = new ZipArchive;

		$zip->open($pathZip.'/'.$fName, ZipArchive::CREATE);

		foreach($files as $file)
		{
			$name = str_replace($pathTemp, '', $file);
			$zip->addFile($file, $name);
		}

		$zip->close();
	}
}
