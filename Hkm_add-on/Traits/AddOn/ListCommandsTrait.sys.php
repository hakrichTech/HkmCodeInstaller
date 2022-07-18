<?php
namespace Hkm_traits\AddOn;

/**
 * 
 */
use Hkm_code\CLI\CLI;

trait ListCommandsTrait
{
    /**
	 * Lists the commands with accompanying info.
	 *
	 * @param array $commands
	 */
	protected function listFull(array $commands,$addCommand = false)
	{
		// Sort into buckets by group
		$groups = [];
		$g = [];
		$c = [];



		foreach ($commands as $title => $command)
		{
			if (! isset($groups[$command['group']]))
			{
				$groups[$command['group']] = [];
			}
			
			$g[] = $command['group'];
			$c[] = $title;
			

			$groups[$command['group']][$title] = $command;
		}

		if ($addCommand) return ['groups'=>$g,'commands'=>$c];
		else{

		$length = max(array_map('strlen', array_keys($commands)));

		ksort($groups);


		// Display it all...
		foreach ($groups as $group => $commands)
		{
			
			CLI::write($group, 'yellow');
			foreach ($commands as $name => $command)
			{
				$name   = hkm_setPad($name, $length, 2, 2);
				$output = CLI::color($name, 'green');
				if (isset($command['description']))
				{
					$output .= CLI::wrap($command['description'], 125, strlen($name));
				}
				CLI::write($output);
			}

			if ($group !== array_key_last($groups))
			{
				CLI::newLine();
			}
		}
	  }
	}

	/**
	 * Lists the commands only.
	 *
	 * @param array $commands
	 */
	protected function listSimple(array $commands)
	{
		foreach (array_keys($commands) as $title)
		{
			CLI::write($title);
		}
	}
}
