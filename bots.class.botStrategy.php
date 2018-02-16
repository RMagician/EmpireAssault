<?php
/* public functions
/ public function __construct($newStrategy = true)
/ public function exportToJSON()
/ public function getBuildingSpread()
/ public function getMilitarySpread()
/ public function getResearchPriority()
/ public function getEmpireTypeID(&$empireTypeName = "")
*/

/**** TO DO
/ LTs -> LDs if research completed
/ Same for other military units
/ Researches
/***************/

class botStrategy
{
      private $empireTypeID;
      private $empireType;
      private $buildingSpread = array();
      private $militarySpread = array();
      private $reserchPriority = array();
      
      public function __construct($newStrategy = true)
      {
            $this->empireTypeID = -1;
            $this->buildingSpread['Residence'] = 0;
            $this->buildingSpread['Barrack'] = 0;
            $this->buildingSpread['Power Plant'] = 0;
            $this->buildingSpread['Star Mine'] = 0;
            $this->buildingSpread['Training Camp'] = 0;
            $this->buildingSpread['Probe Factory'] = 0;
            $this->buildingSpread['Air Support Bay'] = 0;

            $this->militarySpread[TROOPER_ID] = 0;
            $this->militarySpread[DRAGOON_ID] = 0;
            $this->militarySpread[FIGHTER_ID] = 0;
            $this->militarySpread[TACTICAL_FIGHTER_ID] = 0;
            $this->militarySpread[LASER_TROOPER_ID] = 0;
            $this->militarySpread[LASER_DRAGOON_ID] = 0;
            $this->militarySpread[TANK_ID] = 0;
            $this->militarySpread[SCIENTIST_ID] = 0; 

            $this->militarySpread['SciPerLand'] = 0;
            $this->reserchPriority = [];
      
            if ($newStrategy) $this->createBotStrategy();
      }
      
      public function exportToJSON()
      {
          return json_encode($this);
      }
      
      public function getBuildingSpread()
      {
            return $this->buildingSpread;
      }
      
      public function getMilitarySpread()
      {
            return $this->militarySpread;
      }
      
      public function getResearchPriority()
      {
            return $this->reserchPriority;
      }
      
      public function getEmpireTypeID(&$empireTypeName = "")
      {
            $empireTypeName = $this->empireType;
            return $this->empireTypeID;
      }
      
      private function createBotStrategy()
      {
            $totalStrategies = 24;
            $chooseStrategy = mt_rand(0, $totalStrategies-1);
            
            switch ($chooseStrategy)
            {
      
                  case 0:
                        ////$this->empireType = "Forest & Wildernesss";
                        $this->empireTypeID = EMPIRE_TYPE_FW;
                        $this->buildingSpread['Residence'] = 25;
                        $this->buildingSpread['Star Mine'] = 25;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 10;

                        $this->militarySpread[TROOPER_ID] = 15;
                        $this->militarySpread[LASER_TROOPER_ID] = 25;
                        $this->militarySpread[TANK_ID] = 50;
                        //$this->militarySpread[SCIENTIST_ID] = 10;

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = []; 
                  break;
                        
                  case 1:
                        //$this->empireType = "Forest & Wilderness";
                        $this->empireTypeID = EMPIRE_TYPE_FW;
                        $this->buildingSpread['Residence'] = 30;
                        $this->buildingSpread['Star Mine'] = 15;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 15;
                        
                        $this->militarySpread[TROOPER_ID] = 30;
                        $this->militarySpread[LASER_TROOPER_ID] = 40;
                        $this->militarySpread[TANK_ID] = 20;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                  
                  case 2:                 
                        //$this->empireType = "Forest & Wilderness";
                        $this->empireTypeID = EMPIRE_TYPE_FW;
                        $this->buildingSpread['Residence'] = 20;
                        $this->buildingSpread['Star Mine'] = 35;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 5;
                        
                        $this->militarySpread[TROOPER_ID] = 5;
                        $this->militarySpread[LASER_TROOPER_ID] = 40;
                        $this->militarySpread[TANK_ID] = 50;
                        //$this->militarySpread[SCIENTIST_ID] = 5; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 1;
                        $this->reserchPriority = [];      
                  break;
                  
                  case 3:
                        //$this->empireType = "Desert Wasteland";
                        $this->empireTypeID = EMPIRE_TYPE_DW;
                        $this->buildingSpread['Residence'] = 25;
                        $this->buildingSpread['Star Mine'] = 25;
                        $this->buildingSpread['Probe Factory'] = 20;

                        $this->militarySpread[DRAGOON_ID] = 35;
                        $this->militarySpread[LASER_TROOPER_ID] = 40;
                        $this->militarySpread[TANK_ID] =  15;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                                               
                  case 4:
                        //$this->empireType = "Desert Wasteland";
                        $this->empireTypeID = EMPIRE_TYPE_DW;
                        $this->buildingSpread['Residence'] = 35;
                        $this->buildingSpread['Star Mine'] = 20;
                        $this->buildingSpread['Probe Factory'] = 15;

                        $this->militarySpread[DRAGOON_ID] = 40;
                        $this->militarySpread[LASER_TROOPER_ID] = 50;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                        
                  case 5:
                        //$this->empireType = "Desert Wasteland";
                        $this->empireTypeID = EMPIRE_TYPE_DW;
                        $this->buildingSpread['Residence'] = 25;
                        $this->buildingSpread['Star Mine'] = 25;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 10;

                        $this->militarySpread[DRAGOON_ID] = 25;
                        $this->militarySpread[LASER_TROOPER_ID] = 15;
                        $this->militarySpread[TANK_ID] = 50;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;                                          
                        
                  case 6:
                        //$this->empireType = "Volcanic Inferno";
                        $this->empireTypeID = EMPIRE_TYPE_VI;
                        $this->buildingSpread['Residence'] = 35;
                        $this->buildingSpread['Star Mine'] = 15;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 17;

                        $this->militarySpread[TROOPER_ID] = 20;
                        $this->militarySpread[LASER_TROOPER_ID] = 40;
                        $this->militarySpread[TANK_ID] = 30;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                           
                  case 7:
                        //$this->empireType = "Volcanic Inferno";
                        $this->empireTypeID = EMPIRE_TYPE_VI;
                        $this->buildingSpread['Residence'] = 20;
                        $this->buildingSpread['Star Mine'] = 25;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 21;

                        $this->militarySpread[TROOPER_ID] = 5;
                        $this->militarySpread[LASER_TROOPER_ID] = 30;
                        $this->militarySpread[TANK_ID] = 55;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                       
                  case 8:
                        //$this->empireType = "Volcanic Inferno";
                        $this->empireTypeID = EMPIRE_TYPE_VI;
                        $this->buildingSpread['Residence'] = 15;
                        $this->buildingSpread['Star Mine'] = 40;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] =  11;

                        $this->militarySpread[TROOPER_ID] = 2;
                        $this->militarySpread[LASER_TROOPER_ID] = 18;
                        $this->militarySpread[TANK_ID] = 70;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;  
                        
                  case 9:
                        //$this->empireType = "Mystical Lands";
                        $this->empireTypeID = EMPIRE_TYPE_ML;
                        $this->buildingSpread['Residence'] = 15;
                        $this->buildingSpread['Star Mine'] = 35;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 13;

                        $this->militarySpread[TROOPER_ID] = 5;
                        $this->militarySpread[LASER_TROOPER_ID] = 20;
                        $this->militarySpread[TANK_ID] = 65;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;                         
                        
                  case 10:
                        //$this->empireType = "Mystical Lands";
                        $this->empireTypeID = EMPIRE_TYPE_ML;
                        $this->buildingSpread['Residence'] = 20;
                        $this->buildingSpread['Star Mine'] = 30;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 13;

                        $this->militarySpread[TROOPER_ID] = 15;
                        $this->militarySpread[LASER_TROOPER_ID] = 25;
                        $this->militarySpread[TANK_ID] = 65;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                        
                  case 11:
                        //$this->empireType = "Mystical Lands";
                        $this->empireTypeID = EMPIRE_TYPE_ML;
                        $this->buildingSpread['Residence'] = 30;
                        $this->buildingSpread['Star Mine'] = 20;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 14;

                        $this->militarySpread[TROOPER_ID] = 30;
                        $this->militarySpread[LASER_TROOPER_ID] = 40;
                        $this->militarySpread[TANK_ID] = 20;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                  
                  case 12:
                        //$this->empireType = "Mountainous";
                        $this->empireTypeID = EMPIRE_TYPE_M;
                        $this->buildingSpread['Residence'] = 30;
                        $this->buildingSpread['Star Mine'] = 20;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 5;

                        $this->militarySpread[TROOPER_ID] = 20;
                        $this->militarySpread[LASER_DRAGOON_ID] = 30;
                        $this->militarySpread[TANK_ID] = 40;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                        
                  case 13:
                        //$this->empireType = "Mountainous";
                        $this->empireTypeID = EMPIRE_TYPE_M;
                        $this->buildingSpread['Residence'] = 10;
                        $this->buildingSpread['Star Mine'] = 35;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 10;

                        $this->militarySpread[TROOPER_ID] = 5;
                        $this->militarySpread[LASER_DRAGOON_ID] = 10;
                        $this->militarySpread[TANK_ID] = 75;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                       
                  case 14:
                        //$this->empireType = "Mountainous";
                        $this->empireTypeID = EMPIRE_TYPE_M;
                        $this->buildingSpread['Residence'] = 20;
                        $this->buildingSpread['Star Mine'] = 25;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 10;

                        $this->militarySpread[TROOPER_ID] = 20;
                        $this->militarySpread[LASER_DRAGOON_ID] = 20;
                        $this->militarySpread[TANK_ID] = 50;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                  
                  case 15:
                        //$this->empireType = "Oceanic";
                        $this->empireTypeID = EMPIRE_TYPE_O; 
                        $this->buildingSpread['Residence'] = 30;
                        $this->buildingSpread['Star Mine'] = 20;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 10;

                        $this->militarySpread[TROOPER_ID] = 30;
                        $this->militarySpread[LASER_DRAGOON_ID] = 50;
                        $this->militarySpread[TANK_ID] = 10;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;   
                        
                  case 16:
                        //$this->empireType = "Oceanic";
                        $this->empireTypeID = EMPIRE_TYPE_O;
                        $this->buildingSpread['Residence'] = 25;
                        $this->buildingSpread['Star Mine'] = 20;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 15; 

                        $this->militarySpread[TROOPER_ID] = 20;
                        $this->militarySpread[LASER_DRAGOON_ID] = 40;
                        $this->militarySpread[TANK_ID] = 30;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break; 
                    
                  case 17:
                        //$this->empireType = "Oceanic";
                        $this->empireTypeID = EMPIRE_TYPE_O;
                        $this->buildingSpread['Residence'] = 20;
                        $this->buildingSpread['Star Mine'] = 35;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 5;

                        $this->militarySpread[TROOPER_ID] = 5;
                        $this->militarySpread[LASER_DRAGOON_ID] = 20;
                        $this->militarySpread[TANK_ID] = 65;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break; 
                  
                  case 18:
                        //$this->empireType = "Terra Form";
                        $this->empireTypeID = EMPIRE_TYPE_TF;
                        $this->buildingSpread['Residence'] = 50;
                        $this->buildingSpread['Star Mine'] = 0;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 10;

                        $this->militarySpread[TROOPER_ID] = 40;
                        $this->militarySpread[LASER_TROOPER_ID] = 25;
                        $this->militarySpread[TANK_ID] = 20;
                        //$this->militarySpread[SCIENTIST_ID] = 15; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 3;
                        $this->reserchPriority = [];      
                  break;
                       
                  case 19:
                        //$this->empireType = "Terra Form";
                        $this->empireTypeID = EMPIRE_TYPE_TF;
                        $this->buildingSpread['Residence'] = 40;
                        $this->buildingSpread['Star Mine'] = 0;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 20;

                        $this->militarySpread[TROOPER_ID] = 15;
                        $this->militarySpread[LASER_TROOPER_ID] = 35;
                        $this->militarySpread[TANK_ID] = 35;
                        //$this->militarySpread[SCIENTIST_ID] = 15; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 3;
                        $this->reserchPriority = [];      
                  break;      
                       
                  case 20:
                        //$this->empireType = "Terra Form";
                        $this->empireTypeID = EMPIRE_TYPE_TF;
                        $this->buildingSpread['Residence'] = 45;
                        $this->buildingSpread['Star Mine'] = 0;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 15;

                        $this->militarySpread[TROOPER_ID] = 15;
                        $this->militarySpread[LASER_TROOPER_ID] = 35;
                        $this->militarySpread[TANK_ID] = 35;
                        //$this->militarySpread[SCIENTIST_ID] = 15; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 3;
                        $this->reserchPriority = [];      
                  break;      
                        
                  case 21:
                        //$this->empireType = "Multiple Terrain";
                        $this->empireTypeID = EMPIRE_TYPE_MT;
                        $this->buildingSpread['Residence'] = 15;
                        $this->buildingSpread['Star Mine'] = 30;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 15;
                        $this->buildingSpread['Air Support Bay'] = 5; //will need to figure a diff solution for this

                        $this->militarySpread[TACTICAL_FIGHTER_ID] = 10;
                        $this->militarySpread[LASER_DRAGOON_ID] = 40;
                        $this->militarySpread[TANK_ID] = 40;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                      
                  case 22:
                        //$this->empireType = "Multiple Terrain";
                        $this->empireTypeID = EMPIRE_TYPE_MT;
                        $this->buildingSpread['Residence'] = 10;
                        $this->buildingSpread['Star Mine'] = 40;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 10;
                        $this->buildingSpread['Air Support Bay'] = 5;

                        $this->militarySpread[TACTICAL_FIGHTER_ID] = 10;
                        $this->militarySpread[LASER_DRAGOON_ID] = 10;
                        $this->militarySpread[TANK_ID] = 70;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
                    
                  case 23:
                        //$this->empireType = "Multiple Terrain";
                        $this->empireTypeID = EMPIRE_TYPE_MT;
                        $this->buildingSpread['Residence'] = 25;
                        $this->buildingSpread['Star Mine'] = 15;
                        $this->buildingSpread['Training Camp'] = 10;
                        $this->buildingSpread['Probe Factory'] = 15;
                        $this->buildingSpread['Air Support Bay'] = 7;

                        $this->militarySpread[TACTICAL_FIGHTER_ID] = 15;
                        $this->militarySpread[LASER_DRAGOON_ID] = 65;
                        $this->militarySpread[TANK_ID] = 10;
                        //$this->militarySpread[SCIENTIST_ID] = 10; //this exists just to allocate room for sci 

                        $this->militarySpread['SciPerLand'] = 2;
                        $this->reserchPriority = [];      
                  break;
      
            }
            
            $this->empireType = $empire_types[$this->empireTypeID];
     }
}

?>