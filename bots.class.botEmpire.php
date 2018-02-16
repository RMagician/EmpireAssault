<?php

$BONUS_POWER_ENERGYCORE = 0.2;
$BONUS_POWER_SOLARS = 0.5;
$BONUS_POWER_DMP = -0.2;
$BONUS_POWER_SUPERVIRUS = -0.1;
$BUILDINGS_BASE_MULTIPLIER = 4000;

/*
function getMilitaryTrainTime()
function maxSolsToTrain()
function militaryPrice($unitName, $quantity)
function canWeAffordMilitary($unitName, $quantity, &$cost = 0)
function trainMilitary($unitName, $quantity)
function getMilitaryDiscount() //does not account for DW goons
function getHonorMilitaryDiscount()
function getTCMilitaryDiscount()
function getHonorIncomeBonus()
function getBuildingCost($action = 'build')
function buildBuildings($buildingName, $quantity)
function calculatePowerOutput($applyBonus, $expectedNPPs = 0, $expectedFPPs = 0)
function calculatePowerBonus()
function calculatePowerRequired() //returns power required for $this || does not include shields
private function calculatePowerRequired_Buildings()
private function calculatePowerRequired_Military()
*/

class botEmpire extends Empire
{
      function getMilitaryTrainTime()
      {
            $mobi = ($this->sector->is_mobilization() || $game_config['Game Status']==GAME_STATUS_PREGAME);
            return ($mobi ? 12 : 24);
      }
      
      function maxSolsToTrain()
      {
            $maxByPop = floor($player->data['empire_population']*0.1);
            $maxByPop -= $this->queue_data[QUEUE_TYPE_TRAIN]['Soldier']['Total'];
            $maxByMoney = floor($player->data['empire_money']/150);
            
            $maxToTrain = min($maxByMoney, $maxByPop);
            return max(0, $maxToTrain);
      }
      
      function militaryPrice($unitName, $quantity)
      {
            $militaryDiscount = $this->getMilitaryDiscount();
            if ($unitName == SOLDIER_ID) { $militaryDiscount = 0;}
            
            $unitPrice = $units[$unitName]['Base Price'] * (1 - $militaryDiscount);
            
            return ($unitPrice * $quantity);
      }
      
      function canWeAffordMilitary($unitName, $quantity, &$cost = 0)
      {
            $cost = $this->militaryPrice($unitName, $quantity);
            return ($this->data['empire_money'] > $cost);
      }
      
      function trainMilitary($unitName, $quantity)
      {
            if (!$this->canWeAffordMilitary($unitName, $quantity, $cost))
                  return false;
            
            $trainTime = $this->getMilitaryTrainTime();
            $this->add_to_queue($train,QUEUE_TYPE_TRAIN, $unitName, $trainTime);
            $this->data['empire_money'] -= $cost;
            
      }
      
      function getMilitaryDiscount()
      {
            return $this->getHonorMilitaryDiscount() +  $this->getTCMilitaryDiscount();     
      }
      
      function getHonorMilitaryDiscount()
      {     
            return honorMilitaryDiscount($this->data['empire_honor']);
      }
      
      function getTCMilitaryDiscount()
      {
            $TC_PERCENTAGE = (($this->buildings['Training Camp']/$this->data['empire_land'])*100);
            return min($TC_PERCENTAGE * 0.03, 0.30);   
      }
      
      function getHonorIncomeBonus()
      {
            return honorIncomeBonus($this->data['empire_honor']) ;     
      }
      
      function getBuildingCost($action = 'build')
      {	
            switch ($action)
            {
                  case 'build': $dividor = 1; break;
                  case 'convert': $dividor = 2; break;
                  case 'upgrade': $dividor = 4; break;
                  case 'raze': $dividor = 8; break;
            }

            $cost = sqrt($this->data['empire_land']*$BUILDINGS_BASE_MULTIPLIER);
            $cost *= (1+($this->get_round_bonus('Building Cost')/100));
            $cost /= $dividor;
            $cost = floor($cost);

            return $cost;
      }      
      
      function buildBuildings($buildingName, $quantity)
      {
            $buildCost = $this->getBuildingCost();
            $totalCost = $quantity * $buildCost;
            $result = ($this->data['empire_money'] >= $totalCost) && 
                      ($this->data['empire_land_available'] >= $quantity);
            if ($result)
            {
                  $this->add_to_queue($quantity, QUEUE_TYPE_BUILD, $buildingName, 16); 
                  $this->data['empire_money'] -= $totalCost;
                  $this->data['empire_land_available'] -= $quantity;
            }
            return $result;
      }
      
      function calculatePowerOutput($applyBonus, $expectedNPPs = 0, $expectedFPPs = 0)
      {
            if ($applyBonus) 
            { $powerBonus = $this->calculatePowerBonus(); }
            else { $powerBonus = 1; }
            
            $NPPs = $this->buildings['Nuclear Power Plant'] + $expectedNPPs;
            $FPPs = $this->buildings['Fusion Power Plant'] + $expectedFPPs;
            
            return ((($NPPs*POWER_NPP)+($FPPs*POWER_FPP))*$powerBonus);
      }
      
      //calculates power bonus
      function calculatePowerBonus()
      {
            //local consts
            $C_CONDITION = 0; $C_BONUS = 1;

            $powerBonus = 1;

            ## Sorting out bonuses
            //PT Power production bonus
            $bonus[0][$C_CONDITION] = $this->get_round_bonus('Power Production') <> 0;
            $bonus[0][$C_BONUS] = $this->get_round_bonus('Power Production'); 
            //EC bonus
            $bonus[1][$C_CONDITION] = $this->is_researched('Energy Core'); 
            $bonus[1][$C_BONUS] = $BONUS_POWER_ENERGYCORE;
            //Solars Bonus
            $bonus[2][$C_CONDITION] = isset($this->sector->enhancements['Solar Winds']); 
            $bonus[3][$C_BONUS] = $BONUS_POWER_SOLARS;
            //research
            $bonus[4][$C_CONDITION] = $this->get_research_bonus('Power Bonus') > 0;
            $bonus[4][$C_BONUS] = $this->get_research_bonus('Power Bonus')/100;
            //DMP
            $bonus[5][$C_CONDITION] = isset($this->sector->weapons['Dark Matter Plague']); 
            $bonus[5][$C_BONUS] = $BONUS_POWER_DMP;
            //Super Virus
            $bonus[6][$C_CONDITION] = isset($this->sector->weapons['Super Virus']);
            $bonus[6][$C_BONUS] = $BONUS_POWER_SUPERVIRUS;

            $totalBonuses = 7;

            for ($i = 0; $i < $totalBonuses; $i++)
            {
                  if ($bonus[$i][$C_CONDITION])
                        $powerBonus += $bonus[$i][$C_BONUS];
            }

            return $powerBonus;
      }
      
      //returns power required for $this || does not include shields
      function calculatePowerRequired()
      {
            $powerRequired = 0;
            $powerRequired += $this->data['empire_population'] * POP_POWER;
            $powerRequired += $this->calculatePowerRequired_Military();
            $powerRequired += $this->calculatePowerRequired_Buildings();

            $powerRequired *= $this->get_round_bonus('Power Required')/100;
            
            return $powerRequired;
      }
      
      //returns power required for $this buildings
      private function calculatePowerRequired_Buildings()
      {
            $powerRequired = 0;
            //built buildings power upkeep
            foreach($this->buildings as $bname => $bqty)
            {
                  $powerRequired += ($buildings[$bname]['Upkeep Power']*$bqty);
            }	

            //buildings in construction power upkeep
            if (!empty($this->queue[QUEUE_TYPE_BUILD]))
            foreach($this->queue[QUEUE_TYPE_BUILD] as $bname => $qdata)
            {
                  if($buildings[$bname]['Upkeep Power']>0&&$qdata['Total']>0)
                  {
                        $powerRequired += ($buildings[$bname]['Upkeep Power']*$qdata['Total']);
                  }
            }	

            return $powerRequired;
      }
      
      //returns power required for $this military
      private function calculatePowerRequired_Military()
      {
            foreach($this->units as $uname => $udata)
            {	
                  if($udata['Total']>0 && $units[$uname]['Upkeep Power']>0)
                  {
                        $powerRequired += ($units[$uname]['Upkeep Power']*$udata['Total']);
                  }
            }

            return $powerRequired;
      }
      
      
}

?>