<?php
// function shouldWeShift needs to take into account current time().

/* public methods
** public function __construct($schedule = [], $activityLevel = 0, $lastScheduleShift = 0)
** public function loadFromJSON($JSON)
** public function getScheduleInfo($toJSON = true)
** public function getHourlySchedule()
** public function getActivityLevel()
** public function getLastShift()
** public function createBotSchedule()
** public function botToLogin($activityCurrentHour)
*/



class botSchedule
{     
      const INACTIVE_ODDS = 30;
      const MAX_ACTIVITY_LEVEL = 70; //100 - Human Factor - easy hit factor
      const CHILL_ADD_CHANCE = 25;
      const INSOMNIA_MAX_CHANCE = 3.57; //  = 1/8/7*2*100
      const INSOMNIA_ADD_CHANCE = 70;
      
      const MIN_WORK_TIME = 6;
      const MAX_WORK_TIME = 10;
      const MIN_SLEEP_TIME = 6;
      const MAX_SLEEP_TIME = 10;
      
      const SHIFT_SCHEDULE_MIN = 1;
      const SHIFT_SCHEDULE_MAX = 3;
      const SHIFT_ACTIVITY_MULTIPLIER = 5; 
      const SHIFT_MIN_WHEN_TO = 20; 
      const SHIFT_MAX_WHEN_TO = 28;
      
      const BOT_AT_INACTIVE = 0;
      const BOT_AT_WORK = 1;
      const BOT_AT_CHILL = 2;
      const BOT_AT_SLEEP = 3;

      private $workTimeAmount = 0;
      private $sleepTimeAmount = 0;
      private $dayStart = 0;
      
      protected $hourlySchedule = array();
      protected $activityLevel = 0;
      protected $lastScheduleShift = 0;
      
      public function __construct($schedule = [], $activityLevel = 0, $lastScheduleShift = 0)
      {
            if (sizeof($schedule) == 0)
            {
                  return $this->createBotSchedule();
            } else {
                  $this->hourlySchedule = $schedule;
                  $this->activityLevel = $activityLevel;
                  $this->lastScheduleShift = $lastScheduleShift;
                  $this->pullScheduleVariables();
                  $this->shiftBotSchedule();
            }
      }
      
      //load from & to JSON exist because I'm keeping some var internal.
      //helps to call the pullScheduleVariables function.
      public static function loadFromJSON($JSON)
      {
            $scheduleInfo; $myBotSchedule;
            
            $scheduleInfo = json_decode($JSON);
            $myBotSchedule = new botSchedule(
                                    $scheduleInfo['hourlySchedule'], 
                                    $scheduleInfo['activityLevel'], 
                                    $scheduleInfo['lastScheduleShift']
                              );
            
            return $myBotSchedule;
      }
      
      public function getScheduleInfo($toJSON = true)
      {
            $scheduleInfo = array();
            
            $scheduleInfo['hourlySchedule'] = $this->hourlySchedule;
            $scheduleInfo['activityLevel'] = $this->activityLevel;
            $scheduleInfo['lastScheduleShift'] = $this->lastScheduleShift;
            
            if ($toJSON)
            {
                  return json_encode($scheduleInfo);
            } else {
                  return $scheduleInfo;
            }
      }
      
      public function getHourlySchedule()
      {
            return $this->hourlySchedule;
      }
      
      public function getActivityLevel()
      {
            return $this->activityLevel;
      }
      
      public function getLastShift()
      {
            return $this->lastScheduleShift;
      }
      
      public function createBotSchedule()
      {
            if (sizeof($this->hourlySchedule) > 0)
                  return $this->shiftBotSchedule();
            
            $activity = array(); $chillTime = 0;
            
            //creates an inactive
            if (mt_rand(0,100) > 100-INACTIVE_ODDS)
            {
                  for($i=0;$i<24;$i++)
                        $activity[$i] = 0;
                  $this->activityLevel = 0;
            } else {

                  $this->activityLevel = mt_rand(0, MAX_ACTIVITY_LEVEL);

                  $this->sleepTimeAmount = mt_rand(MIN_SLEEP_TIME, MAX_SLEEP_TIME);
                  $this->workTimeAmount = mt_rand(MIN_WORK_TIME, MAX_WORK_TIME);

                  $this->dayStart = mt_rand(1, 24);
                  $activity = botScheduleToArray($this->dayStart, $this->workTimeAmount, $this->sleepTimeAmount);
            }
            
            $this->hourlySchedule = $activity;
            return $activity;
      }
      
      public function botToLogin($activityCurrentHour)
      {
            switch ($activityCurrentHour)
            {
                  case BOT_AT_INACTIVE: $toLogin = false; break; //inactive bot

                  //at work
                  case BOT_AT_WORK:
                        $toLogin = mt_rand(0, 100) <= $this->activityLevel;
                        break;

                  //at home
                  case BOT_AT_CHILL:
                        $toLogin = mt_rand(0, 100) <= ($this->activityLevel + CHILL_ADD_CHANCE);
                        break;

                  //sleeping
                  case BOT_AT_SLEEP:	
                        $toLogin = false;

                        //wake up in the middle of the night? 
                        $insomnia = mt_rand(0, 100) >= mt_rand(100-INSOMNIA_MAX_CHANCE, 100);
                        if ($insomnia)
                              $toLogin = mt_rand(0, 100) <= ($this->activityLevel + INSOMNIA_ADD_CHANCE);

                        break;			
            }

            return $toLogin;
      }
      
      private function pullScheduleVariables()
      {
            $lastSleep = 0; $firstWork = 0;
            
            for ($i = 0; $i < 24; $i++)
            {
                  switch ($this->hourlySchedule[$i])
                  {
                        case BOT_AT_INACTIVE:
                              $this->workTimeAmount = 0;
                              $this->sleepTimeAmount = 0;
                              $this->dayStart = 0;
                              return; //break;
                              
                        case BOT_AT_SLEEP:
                              $this->sleepTimeAmount++;
                              $lastSleep = $i;
                              break;
                              
                        case BOT_AT_WORK:
                              $this->workTimeAmount++;
                              if ($firstWork < $i) $firstWork = $i;
                              break;
                              
                        case BOT_AT_CHILL:
                              break;
                              
                  }
            }
            
            //in case it goes around the 24h mark
            if ($firstWork > $lastSleep)
            {
                  $dayStart = $firstWork;
            } else {
                  $dayStart = $lastSleep + 1;
            }
            
      }
      
      private function botScheduleToArray($dayStart, $workTime, $sleepTime)
      {
            $chillTime = 24 - $sleepTime - $workTime;
            
            //bots wake up at work, come back home and play and then go to sleep
            for ($i = 0; $i < 24; $i++)
            {
                  $h = $i + $dayStart; // $h is the bots' 24h schedule
                  if ($h > 24) { $h -= 24; }  

                  //at work
                  if ($h <= $this->workTimeAmount)
                  {
                        $activity[$i] = BOT_AT_WORK;
                        continue;
                  }

                  //at home
                  if ($h <= ($workTime + $chillTime))
                  {
                        $activity[$i] = BOT_AT_CHILL;
                        continue;
                  }

                  //sleeping
                  $activity[$i] = BOT_AT_SLEEP;
            }

            return $activity;
      }
      
      private function shouldWeShift()
      {
            //$lastShift = currentTime - $this->lastScheduleShift;
                  
            if ($this->lastScheduleShift >= mt_rand(SHIFT_MIN_WHEN_TO, SHIFT_MAX_WHEN_TO))
                  $willWeShift = (50 >= mt_rand(0, 100));
            
            if ($willWeShift)
            {
                  //$this->lastScheduleShift = now();
                  return true;
            }
            
            //no shifting today
            if ($this->lastScheduleShift > SHIFT_MAX_WHEN_TO)
            {
                  //$this->lastScheduleShift = now();
                  return false;
            }
      }
      
      private function shiftBotSchedule()
      {
            if ($this->activityLevel == 0) return $this->hourlySchedule;
            if (! shouldWeShift()) return $this->hourlySchedule; 

            $shiftLeft = mt_rand(0, 100) < 50;
            $shiftBy = mt_rand(SHIFT_SCHEDULE_MIN, SHIFT_SCHEDULE_MAX);

            if ($shiftLeft) { $this->dayStart -= $shiftBy; } else { $this->dayStart += $shiftBy; }

            $this->workTimeAmount = 
                  mt_rand
                  (
                        max($this->workTimeAmount - $shiftBy, MIN_WORK_TIME), 
                        min($this->workTimeAmount + $shiftBy, MAX_WORK_TIME)
                  );


            $this->sleepTimeAmount = 
                  mt_rand
                  (
                        max($this->sleepTimeAmount - $shiftBy, MIN_SLEEP_TIME), 
                        min($this->sleepTimeAmount + $shiftBy, MAX_SLEEP_TIME)
                  );


            $this->activityLevel =
                  mt_rand
                  (
                        max($this->activityLevel - $shiftBy * SHIFT_ACTIVITY_MULTIPLIER, 0),
                        min($this->activityLevel + $shiftBy * SHIFT_ACTIVITY_MULTIPLIER, 100)
                  );


            return botScheduleToArray($this->dayStart, $this->workTimeAmount, $this->sleepTimeAmount);
      }
}
?>