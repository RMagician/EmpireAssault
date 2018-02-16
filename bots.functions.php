<?php
//bot.functions.php
//function resetBots()
//function howManyBotsNeeded(&$db, &$debug, $botRatio)
//function botExplore(&$player)

require_once($root_dir.'includes/units.php');

//only call this function if you wish to delete all bots
function resetBots()
{
	$result = $db->query("SELECT * FROM empires WHERE user_id='0'");
	while($row = $result->fetch_assoc())
	{
		$db->query("DELETE FROM empire_units WHERE empire_id='{$row['empire_id']}'");
		$db->query("DELETE FROM empire_buildings WHERE empire_id='{$row['empire_id']}'");
		$db->query("DELETE FROM empire_research WHERE empire_id='{$row['empire_id']}'");
		$db->query("DELETE FROM empire_queue WHERE empire_id='{$row['empire_id']}'");
		$db->query("DELETE FROM empire_status WHERE empire_id='{$row['empire_id']}'");
		$db->query("DELETE FROM empires WHERE empire_id='{$row['empire_id']}'");
	}
	$result->free();
}

function howManyBotsNeeded(&$db, &$debug, $botRatio)
{
	$result = $db->query("SELECT * FROM empires WHERE empire_dead='0' AND user_id!='0'");
	$active_empires = $result->num_rows;
	$result->free();

	$bot_result = $db->query("SELECT * FROM empires WHERE empire_dead='0' AND user_id='0'");
	$active_bots = $bot_result->num_rows;
	$bot_result->free();

	$bots_maximum = floor($active_empires*(1/$botRatio));
	$bots_needed = $bots_maximum-$active_bots;

	$debug->add("Active Bots = $active_bots");
	$debug->add("\$active_empires = $active_empires");
	$debug->add("\$bots_maximum = $bots_maximum");
	$debug->add("Bots Needed = $bots_needed");	

	return $bots_needed;
}

function grabBotNames(&$db)
{
	$names = array();
	$result = 
	$db->query("SELECT * FROM `bot_names` WHERE `bot_name` NOT IN (select `empire_name` FROM empires WHERE			  empire_dead='0')");
	
	while($row = $result->fetch_array())
		$names[] = $row['bot_name'];
	
	$result->free();
	shuffle($names);	
	return $names;
}

function botExplore(&$player, $queuedExplore, &$debug)
{
	//$queuedExplore = $player->queue[QUEUE_TYPE_EXPLORE]['Total'];
	$exploreCostPerLand = exploreCost($player['empire_land'], $player->sector->is_growth());
	echo "exploreCost: ".$exploreCostPerLand."<br>";
	
	$result = $player['empire_money'] > (24 * $exploreCostPerLand);
	
	if ($result)
	{
		$max_explore = returnMaxExplore(
			$exploreCostPerLand, 
			$player['empire_land'], 
			$queuedExplore, 
			$player['empire_money']
		);
	
		$qty_to_explore = floor($max_explore/24)*24;	
		
		if($qty_to_explore>0)		
			exploreLand($qty_to_explore, $exploreCostPerLand, $player);
		
	}
	
	return $result;
}

function botTotalPowerOutput($player, $unitData = NULL, $buildingData = NULL, $queueData = NULL, $researchData = NULL)
{
	$power_required = calculatePowerRequired(
		$player['empire_type'], 
		$player['empire_population'], 
		$player->units, 
		$player->buildings, 
		$player->queue_data[QUEUE_TYPE_BUILD]
	);

	$power_output = 
		calculatePowerOutput($player->buildings['Nuclear Power Plant'], $player->buildings['Fusion Power Plant']);

	$power_output_expected = 
	calculatePowerOutput(
		$player->queue_data[QUEUE_TYPE_BUILD]['Nuclear Power Plant']['Total'],
		$player->queue_data[QUEUE_TYPE_BUILD]['Fusion Power Plant']['Total']
	);

	$power_output_expected += $power_output; 


	$power_bonus = 1;
	$energyCore = false; //needs to be edited to player array  

	$power_bonus = calculatePowerBonus(
		$player['empire_type'], 
		$energyCore, 
		isset($extras[$player['sector_id']]['Solar Winds']), 
		$player->research_percentage('Power Bonus'), 
		isset($extras[$player['sector_id']]['Dark Matter Plague']), 
		isset($extras[$player['sector_id']]['Super Virus'])
	);

	$power_output_expected = floor($power_output_expected*$power_bonus);
}

function botBuildBuildings(&$player, &$debug, $unit_data = NULL, $building_data = NULL, $queue_data = NULL, $research_data = NULL)
{
	$build_cost = getBuildingCosts(
		$player->get_round_bonus('Building Cost'),
		$player['empire_land'],
		$build_cost, $upgrade_cost, $convert_cost, $raze_value
	);

	$maxByMoney = floor($player['empire_money']/$build_cost);
	$maximum = ($player['empire_land_available']<$maxByMoney) ? $player['empire_land_available'] : $maxByMoney;


	$power_output_expected 
		= botTotalPowerOutput($player, $player->units, $player->buildings, $players->queue, $player->research);


	if($maximum>0)
	{
		//a better way to define how many barracks are needed is by looking at how many sols you train per tick
		//and making sure you have enough room to house those. 
		$barracks_used = $player->barracks['Used']; // does this take into account queued units ?  
		$barracks_space = $player->barracks['Capacity']; 
		$barracks_space += ($player->queue[QUEUE_TYPE_BUILD]['Barracks']['Total']*BASE_BARRACKS);
		$barracks_needed = ceil(($barracks_used*1.1-$barracks_space)/BASE_BARRACKS);
		//$build[0] = min($maximum,$barracks_needed);

		if ($player->is_researched('Fusion Technology'))
		{
			$power_plant_type =  'Fusion Power Plant';
		    	$power_plant_output = POWER_FPP;
		} else {
		    	$power_plant_type = 'Nuclear Power Plant';
		    	$power_plant_output = POWER_NPP;     
		}

		$PPsNeeded = ceil(($power_required-($power_output_expected*1.1))/$power_plant_output);
		//$build[1] = min($maximum, $PPsNeeded);  

		$training_camps = $player->buildings['Training Camp'];
		$training_camps += ($player->queue[QUEUE_TYPE_BUILD]['Training Camp']['Total']); 
		$training_camps_required = ceil($player['empire_land']*0.11); 
		$TCsNeeded = $training_camps_required-$training_camps;
		//$build[3] = min($maximum,$TCsNeeded);

		$probe_factories = $player->buildings['Probe Factory'];
		$probe_factories += ($player->queue[QUEUE_TYPE_BUILD]['Probe Factory']['Total']);
		$probe_factories_required = ceil($player['empire_land']*0.05);
		$PFsNeeded = $probe_factories_required-$probe_factories;
		//$build[4] = min($maximum,$PFsNeeded);

		$build['q'] = [$PPsNeeded, $barracks_needed, $TCsNeeded, $PFsNeeded];
		$build['name'] = [$power_plant_type, "Barracks", "Training Camp", "Probe Factory"];
		$build['debug'] =
		[
			"Power Plants Qty", 
			"Build Barracks Qty",
			"Training Camps Qty",
			"Probe Factores Qty"
		];  

		for ($i = 0; $i < sizeof($build); $i++)
		{
			$toBuild = min($maximum, $build['q'][$i]);
			construct_buildings($build['name'][$i],$build,$player->queue);
			$player['empire_money'] -= ($build_cost*$toBuild);
			$player['empire_land_available'] -= $toBuild;
			$maximum -= $toBuild;
			$debug->add($toBuild, $build['debug'][$i]);
		}



		if($maximum>=10)
		{
			//5. Residence (70% of remaining)
			$build = floor($maximum*0.7);
			construct_buildings('Residence',$build,$player->queue);

			$player['empire_money'] -= ($build_cost*$build);
			$player['empire_land_available'] -= $build;

			$maximum -= $build;

			//6. Star Mine (Remainder)
			construct_buildings('Star Mine',$maximum,$player->queue);

			$player['empire_money'] -= ($build_cost*$maximum);
			$player['empire_land_available'] -= $maximum;

			$debug->add($maximum,"Star Mine Qty");
		}
	}
	
	if($maximum>=15&&($build_cost*$maximum)>$player['empire_money'])
	{
		return false;
	}

	return true;
		
}

//mock
function decideBotBuildings(
		$buildingSpread, 
		$freeLand, 
		$totalLand, 
		$barracks_needed, 
		$PPsNeeded, 
		$FPP_RESEARCHED = false)
{	
	//still need to add in a method to account for NPP + FPP in play.
	$powerPlantsID = $FPP_RESEARCHED ? BUILDING_FPP_ID : BUILDING_NPP_ID;
	
	// this is gonna need reworking to take into account all building names.....
	$buildingNames = [BUILDING_RES_ID, BUILDING_SM_ID, BUILDING_TC_ID, BUILDING_PF_ID];
	
	$buildingSpreadTotal = array_sum($buildingSpread);
	
	echo "<br> Building Spread before Rax & PP Calculation: <br>";
	print_var($buildingSpread);
	echo"<br>";		
	
	//add 10% ish to each.
	$buildingSpread[BUILDING_RAX_ID] = 1.1 * ($barracks_needed/$totalLand)*100;
	$buildingSpread['Power Plant'] = 1.1 * ($PPsNeeded/$totalLand)*100;
	
	//assigning the unacccounted for land in the building spread, not adding any to TCs
	$removePercentage = ($buildingSpread[BUILDING_RAX_ID] + $buildingSpread['Power Plant']); 
	$extra = 100 - $buildingSpreadTotal - $removePercentage;
	//offsetting it so TCs dont get extra land
	$extra += ($extra * $buildingSpread[BUILDING_TC_ID]/100);
	
	if ($extra <> 0)
		for ($i = 0; $i < sizeof($buildingNames); $i++)
		{
			//we dont want extra TCs, Rax, PPs
			if (($buildingNames[$i] == BUILDING_TC_ID)&&($extra >0)) continue; 
			if (($buildingNames[$i] == BUILDING_RAX_ID)||($buildingNames[$i] == "Power Plant"))
				continue;
			
			$buildingName = $buildingNames[$i];
			$buildingSpread[$buildingName] += $extra * ($buildingSpread[$buildingName] / 100);
		}
	
	echo"<br><br><br> buildingSpread: <br>";
	print_var($buildingSpread);
	
	
	//now to figure out what the real amount we need is
	$buildingNames = [
			'Power Plant', 
			BUILDING_RAX_ID, 
			BUILDING_RES_ID, 
			BUILDING_SM_ID, 
			BUILDING_TC_ID, 
			BUILDING_PF_ID
		];
	
	for ($i = 0; $i < sizeof($buildingNames); $i++)
	{
		$buildingName = $buildingNames[$i];
		$buildingSpread['wanted'][$buildingName] = floor($totalLand * $buildingSpread[$buildingName]/100);
		$buildingSpread['current'][$buildingName] = 0;
		$buildingSpread['inQueue'][$buildingName] = 0;
		
		$buildingSpread['missing'][$buildingName] 
			= $buildingSpread['wanted'][$buildingName] - 
			  $buildingSpread['current'][$buildingName] -
			  $buildingSpread['inQueue'][$buildingName];
		
		//might need to setup a 1D toBuild Array here to make things easy on the sort
		$missingBuildings[$buildingName] = max(0, $buildingSpread['missing'][$buildingName]);
	}
	
	echo"<br><br><br> missingBuildings: <br>";
	print_var($missingBuildings);
	
	//prioritizing PPs & Rax
	$toBuild = array();
	
	$toBuild["Power Plant"] = ceil($missingBuildings["Power Plant"]/16)*16;
	$missingBuildings["Power Plant"] -= $toBuild["Power Plant"];
	
	$toBuild[BUILDING_RAX_ID] = ceil($missingBuildings[BUILDING_RAX_ID]/16)*16;
	$missingBuildings[BUILDING_RAX_ID] -= $toBuild[BUILDING_RAX_ID];

	$freeLand -= ($toBuild["Power Plant"] + $toBuild[BUILDING_RAX_ID]);
	
	//sorting out the rest of the buildings
	while ($freeLand >= 15)
	{
		$toBuildAmount = min($freeLand, 16);
		
		arsort($missingBuildings);
		$buildingName = key($missingBuildings);
		$toBuild[$buildingName] = $toBuild[$buildingName] + $toBuildAmount;
		
		$missingBuildings[$buildingName] -= $toBuildAmount;
		$freeLand -= $toBuildAmount;
	}

	echo"<br><br><br> toBuild: <br>";
	print_var($toBuild);
	
	echo"<br><br>free land: ".$freeLand."<br><br>";
	return $toBuild;
}

function decideMilitaryToBuild($militarySpread, $availableSoldiers, $availableMoney)
{
	//need to re-distribute scientist spread.
	
	global $units;
	
	$sciNeeded = 0; $trainCost = 0 ;
	$BOT_MAX_SOLS = 2000; //no point for bots stocking up more than 2k sols
	
	//testing purposes ***
	$land = 1000;
	$queuedSci = 0; $queuedSols = 0;
	$currentSci = $land * 
		(mt_rand(ceil($militarySpread['SciPerLand']*100/1.5), $militarySpread['SciPerLand']*100)/100); 
	$militaryDiscount = 0;
	$maxTrainableSols = 500;
	$ASBCapacity = 100;
	$trainTime = 12;
	echo "<br> Start Money Available: ".$availableMoney."<br>";
	//******
	
	/* post-testing
	$queuedSci = $player->queue_data[QUEUE_TYPE_TRAIN][SCIENTIST_ID]['Total'];
	$queuedSols = $player->queue_data[QUEUE_TYPE_TRAIN][SOLDIER_ID]['Total'];
	$currentSci = 
	$militaryDiscount = $player->getMilitaryDiscount();
	$maxTrainableSols = $player->maxSolsToTrain();
	$ASBCapacity = 100;
	$trainTime = 12;
	*****************/
	
	//could take care of soldiers here
	if ($availableSoldiers < $BOT_MAX_SOLS) 
	{
		$maxToTrain = $BOT_MAX_SOLS - $availableSoldiers - $queuedSols;
		//$maxTrainableSols = $player->maxSolsToTrain();
		$toTrain[SOLDIER_ID] = min($maxToTrain, $maxTrainableSols);
		$availableMoney -= $toTrain[SOLDIER_ID] * $units[SOLDIER_ID]['Base Cost'];
	}
	//*****
	
	echo "<br> After Sols Money Available: ".$availableMoney."<br>";
	
	//sorting out sci
	$sciNeeded = max(0, ($militarySpread['SciPerLand'] * $land - $currentSci - $queuedSci));
	$maxByMoney = floor($availableMoney / ($units[SCIENTIST_ID]['Base Cost']*(1-$militaryDiscount)));
	$maxSciToTrain = min($maxByMoney, $sciNeeded);
	$toTrain[SCIENTIST_ID] = min($availableSoldiers, $maxSciToTrain);
	
	echo "<br>Current Sci: ".$currentSci;
	echo "<br>Sci Needed: ".$sciNeeded;
	echo "<br>Sci Cost: ".$units[SCIENTIST_ID]['Base Cost'];
	echo "<br>Sci To Train: ".$toTrain[SCIENTIST_ID]."<br>";
	if ($sciNeeded > $toTrain[SCIENTIST_ID]) 
	{
		echo "<br>not enough sols or money<br>";
		return $toTrain; //not enough sols	
	}
	//$player->militaryPrice(SCIENTIST_ID, $toTrain[SCIENTIST_ID]);  || to be used after botEmpire class is implemented
	$availableMoney -= ($toTrain[SCIENTIST_ID]*($units[SCIENTIST_ID]['Base Cost']*(1-$militaryDiscount)));
	//****
	
	echo "<br> Money Available: ".$availableMoney."<br>";
	
	//fix for spreads that don't go up to 100
	$totalSpread = array_sum($militarySpread) - $militarySpread['SciPerLand'];
	$unitsArrayLength = sizeof($units);
	$extraSpread = 100 - $totalSpread;
	for ($i = 0; $i < $unitsArrayLength; $i++)
	{
		$unitName = unitIDToName($i);
		
		if ($militarySpread[$unitName] <= 0) continue;
		
		//using || so this doesnt break when the constant goes from string to int
		if ((SOLDIER_ID == $unitName)||(SOLDIER_ID == $i))
			continue; //soldiers already be taken care of elsewhere

		//scientists already sorted above, so we want to skip them
		if ((SCIENTIST_ID == $unitName)||(SCIENTIST_ID == $i))
			continue;	
		
		$militarySpread[$unitName] += $extraSpread * ($militarySpread[$unitName] / 100);
	}
	
	$initialMoney = $availableMoney;
	$initialSols = $availableSoldiers;
	$initialASB = $ASBCapacity;
	$needToSpendMoney = true;
	$iterations = 0;
	
	while ($needToSpendMoney)
	{
		$trainCost = 0;
		$soldierCost = 0;
		$ASBCost = 0;
		
		for ($i = 0; $i < $unitsArrayLength; $i++)
		{
			$unitName = unitIDToName($i);
			if ($militarySpread[$unitName] <= 0)
			{
				$trainFactor[$unitName]['MONEY_MAX'] = 0;
				$trainFactor[$unitName]['SOLDIER_MAX'] = 0;
				$trainFactor[$unitName]['MAX'] = 0;
				$toTrain[$unitName] = 0;
				continue;
			}
			
			$toTrainIteration[$unitName] = 0;
			
			/* sorting out the maximum amount to train of unit $unitName */
			$trainFactor[$unitName]['MONEY_MAX'] = 
				($militarySpread[$unitName]/100)*$availableMoney / 
				($units[$unitName]['Base Cost']*(1-$militaryDiscount));

			if ($units[$unitName]['Soldiers'] > 0)
			{
				$trainFactor[$unitName]['SOLDIER_MAX'] = ($militarySpread[$unitName]/100)*$availableSoldiers;
			} else {
				$trainFactor[$unitName]['SOLDIER_MAX'] = $ASBCapacity;
			}

			$trainFactor[$unitName]['MAX'] = 
				min($trainFactor[$unitName]['MONEY_MAX'], $trainFactor[$unitName]['SOLDIER_MAX']);

			$trainFactor[$unitName]['MAX'] = floor($trainFactor[$unitName]['MAX']);
			/* Done sorting out the maximum amount to train of unit $unitName */
			
			/* Sorting out max to train according to train Time */
			$toTrainIteration[$unitName] = floor($trainFactor[$unitName]['MAX'] / $trainTime) * $trainTime;
			$leftOver = $trainFactor[$unitName]['MAX'] - $toTrainIteration[$unitName];

			if ($leftOver == ($trainTime - 1))
				$toTrainIteration[$unitName] += $leftOver;

			/* Done Sorting out max to train according to train Time */
			
			/* Calculating Costs */
			$trainCost += $toTrainIteration[$unitName]*($units[$unitName]['Base Cost']*(1-$militaryDiscount));
			
			if ($units[$unitName]['Soldiers'] > 0)
			{
				$soldierCost += $toTrainIteration[$unitName]*$units[$unitName]['Soldiers'];
			} else {
				$ASBCost += $toTrainIteration[$unitName];
			}
			/* Done Calculating Costs */
			
			$toTrain[$unitName] += $toTrainIteration[$unitName];
		}
		
		$availableMoney -= $trainCost;
		$availableSoldiers -= $soldierCost;
		$ASBCapacity -= $ASBCost;
		$iterations++;
		
		if ($iterations >= 3) break;
		
		$needToSpendMoney = false;
		if ($availableMoney/$initialMoney < 0.9)
			if ($availableSoldiers/$initialSols < 0.9)
				if (($ASBCapacity > 0)&&($ASBCapacity/$initialASB < 0.9))
					$needToSpendMoney = true;
		
		echo "<br><b> train maxs:</b> <br>";
		print_var($trainFactor);
		
		
		if ($needToSpendMoney)
			echo "<br> going for iteration: ".$iterations;
				
	}
	
	
	
	echo "<br><b>  Military Spread:</b> <br>";
	print_var($militarySpread);
	
	
	echo "<br> Money Available Before Training: ".$initialMoney;
	echo "<br> Money Available After Training: ".$availableMoney;
	
	echo "<br><br> Soldiers available before Training: ".$initialSols;
	echo "<br> Soldier available after: ".$availableSoldiers;
	
	
	return $toTrain;			  
	
}

?>