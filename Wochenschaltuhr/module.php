<?
    // Klassendefinition
    class WeeklyTimer extends IPSModule
     {
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID)
            {
                // Diese Zeile nicht löschen
                parent::__construct($InstanceID);
                // Selbsterstellter Code
            }

        public function Create()
            {
                // Diese Zeile nicht löschen.
                parent::Create();

                //Variablenprofil anlegen
                $this->Var_Pro_Erstellen();  

                $this->RegisterPropertyString("Alarmname", "");
                $this->RegisterPropertyString("Alarms", "");
                $this->RegisterPropertyString("Offset", "");
                $this->RegisterVariableBoolean("ArlamState", $this->Translate("Weekly timer On / Off"), "~Switch");
		        $this->EnableAction("ArlamState");   
                $this->RegisterVariableBoolean("EditTime",$this->Translate("Take a new time"), "WT.NewTime");
		        $this->EnableAction("EditTime");
                $this->CreateTimerEvent("NewTime");
        
            }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges()
            {
                // Diese Zeile nicht löschen
               parent::ApplyChanges();
               if(($this->ReadPropertyString("Alarms") != "") AND ($this->ReadPropertyString("Offset") != "")){ 
                    #Prüfe auf doppelte Einträge
                    if($this->unique_multidim_array(json_decode($this->ReadPropertyString("Alarms"), true), "Day") == true){
                        $this->SetStatus(102);#Instanz ist inaktiv!
                        $this->SetStatus(202);}#Doppelter Wochentag                    
                    #Prüfe auf doppelte Einträge
                    elseif($this->unique_multidim_array(json_decode($this->ReadPropertyString("Offset"), true), "OffsetID") == true){
                        $this->SetStatus(102);#Instanz ist inaktiv!
                        $this->SetStatus(203);}#Doppelte Offset ID
                    #Prüfe auf doppelte Einträge
               	    elseif($this->unique_multidim_array(json_decode($this->ReadPropertyString("Offset"), true), "OffsetMin") == true){
                        $this->SetStatus(102);#Instanz ist inaktiv!
                        $this->SetStatus(204);}#Doppelte Offset Zeit
                    #Prüfe auf doppelte Einträge
               	    elseif($this->unique_multidim_array(json_decode($this->ReadPropertyString("Offset"), true), "OffsetAktion") == true){
                        $this->SetStatus(102);#Instanz ist inaktiv!                       
                        $this->SetStatus(205);}#Doplleter Offset Name
                    #Prüfe auf doppelte Einträge
               	    elseif($this->unique_multidim_array(json_decode($this->ReadPropertyString("Offset"), true), "OffsetColor") == true){
                        $this->SetStatus(102);#Instanz ist inaktiv!
                        $this->SetStatus(206);}#Dopllete Offset Farbe
                    else{
                        $this->SetStatus(102);}#Instanz ist aktiv!
                    }
                else {
                    $this->SetStatus(104);#Instanz ist inaktiv!
                }
            }
    
		public function RequestAction($Ident, $value)
		    {
			    switch($Ident) {
				    case "ArlamState":
					    $this->OpjektAktiv($value);
					    break;
				    case "EditTime":
                        SetValue($this->GetIDForIdent("EditTime"), $value);
					    $this->CreateWochenplan($value);
                        sleep(1);
                        SetValue($this->GetIDForIdent("EditTime"), false);
					    break;
				    default:
					    throw new Exception("Invalid ident");
			    }	
		    }

	
	

        protected function CreateConfigArray($Webfront)
            {
                //Alarm Zeiten aus dem Konfigurator laden 
                $ZeitKonfigurator = json_decode($this->ReadPropertyString("Alarms"),true);
                //Alarm Zeiten aus dem Wochenplan laden 
                $ZeitWochenplan = $this->WochenplanAuslesen();
                //Alarm Zeiten aus dem Webfront laden 
                $ZeitWebfront = $this->TimerAuslesen("NewTime");

                //Alarm Zeiten aus dem Konfigurator übernehmen und in $Alarms Array schreiben
                foreach ($ZeitKonfigurator as $key => $value) {
                    $time = explode(":", $ZeitKonfigurator[$key]["Time"]);
                    $Alarms[$ZeitKonfigurator[$key]["Day"]] = Array("Day" => $ZeitKonfigurator[$key]["Day"],"Houers" => $time[0],"Minutes" =>  $time[1],"Seconds" => $time[2]);
         
                }
                  
                // Wenn die Alarmzeit aktualisuerung aus dem Webfront kommt übernehme die Zeiten aus dem Wochenplan und aus dem Webfront
                if ($Webfront == true) {
                    foreach ($ZeitWochenplan as $key => $value) {
                        $time = explode(":", $ZeitWochenplan[$key]["Time"]);
                        $Alarms[$ZeitWochenplan[$key]["Day"]] = Array("Day" => $ZeitWochenplan[$key]["Day"],"Houers" => $time[0],"Minutes" =>  $time[1],"Seconds" => $time[2]); 

                    }
                    foreach ($ZeitWebfront as $key => $value) {
                        if ($value["TagAktiv"] == 1){
                            $Alarms[$value["Day"]] = Array("Day" => $value["Day"],"Houers" => $value["Houers"],"Minutes" => $value["Minutes"],"Seconds" => $value["Seconds"]); 
                        }
                    }               
                }   
    
                //Offset String auslesen
                $offset_string = json_decode($this->ReadPropertyString("Offset"));
                
                //Confg Array erstellen zum erstellen des WE-Plans 
                foreach ($Alarms as $keya =>$value)  {
                    foreach($offset_string  as $key => $value){
                        $ArrayAlarms["Groups"][$keya]= $Alarms[$keya]["Day"];
                        $ArrayAlarms["Aktion"][$key]["OffsetID"] = $value->OffsetID;
                        $ArrayAlarms["Aktion"][$key]["OffsetAktion"] = $value->OffsetAktion;
                        $ArrayAlarms["Aktion"][$key]["OffsetColor"] = $value->OffsetColor;
                        //Offset zur Alarmzeit addieren
                        $NewTime = mktime( 0, $value->OffsetMin, 0,0,0,0) + 3600 +
                        mktime( $Alarms[$keya]['Houers'] ,$Alarms[$keya]['Minutes'] , $Alarms[$keya]['Seconds'],0,0,0);
                        //Offset Zeit in Array schreieben 
                        $ArrayAlarms["Time"][$keya][$key]["OffsetID"] = $value->OffsetID; 
                        $ArrayAlarms["Time"][$keya][$key]["Houers"] = date("H",$NewTime); 
                        $ArrayAlarms["Time"][$keya][$key]["Minutes"] = date("i",$NewTime); 
                        $ArrayAlarms["Time"][$keya][$key]["Seconds"] = date("s",$NewTime);                
                    }       
                }
                $Webfront = false;
                return $ArrayAlarms;
            }
  
        public function CreateWochenplan($Webfront = false)
            {
                $Alarms = $this->CreateConfigArray($Webfront);
                $EreignisName = $this->ReadPropertyString("Alarmname");
                $EreignisIdent = "weeklyplan";     //Wochenplan Name
                $ParentIdent ="ActionScript";
                $ParentID = @IPS_GetObjectIDByIdent($ParentIdent,$this->InstanceID );
                $EreignisID = @IPS_GetObjectIDByIdent($EreignisIdent, $ParentID);  //Wochenplan ID
           
                //Alte Tage und Zeiten löschen
                if($EreignisID >0 ){
                    $EreignisInfo= IPS_GetEvent($EreignisID);
                    foreach ($EreignisInfo["ScheduleGroups"] as $key => $value ) { 
                        IPS_SetEventScheduleGroup($EreignisID, $value["ID"], 0);
                    }
                    foreach ($EreignisInfo["ScheduleActions"] as $key => $value ){
                        IPS_SetEventScheduleAction($EreignisID, $value["ID"], "", 0, "");
                    }
                };

                //Wenn kein Wochenplan vorhanden neuen erstellen
                if($EreignisID === false) {
                    $EreignisID = IPS_CreateEvent(2);  //Wochenplan
                    }
                if($Alarms["Groups"] !== ""){
                    foreach ($Alarms["Groups"] as $GruppenID => $Tage ) {              
                        IPS_SetEventScheduleGroup($EreignisID, $GruppenID, $Tage); // 1=Mo, 2=Di, 4=Mi, 8=Do, 16=Fr, 32=Sa, 64= So
                        IPS_SetParent($EreignisID, $ParentID);
                        IPS_SetName( $EreignisID, $EreignisName);
                        IPS_SetIdent( $EreignisID,$EreignisIdent);
                        IPS_SetEventActive($EreignisID, true);                  
                    }
                }
                if($Alarms["Aktion"] !== "") { // Erstelle die Aktion für den Wochenplan 
                    foreach ($Alarms["Aktion"] as $key => $value ) {
                        IPS_SetEventScheduleAction($EreignisID, $value["OffsetID"], $value["OffsetAktion"], $value["OffsetColor"], "");   
                    }
                    foreach ($Alarms["Time"] as $key => $value ){
                        for($z=0; $z < count($Alarms["Time"][$key]); $z++) { 
					        IPS_SetEventScheduleGroupPoint($EreignisID, $Alarms["Groups"][$key] /*Gruppe*/,  $value[$z]["OffsetID"] /*Schaltpunkt*/, $value[$z]["Houers"] ,$value[$z]["Minutes"]/*M*/, $value[$z]["Seconds"],  $value[$z]["OffsetID"] /*Aktion*/);
                        }
                    }
                } 
                return $EreignisID;
            }

        public function	CreateAlarmSkript()
            {
                $data = json_decode($this->ReadPropertyString("Offset"));
                $ScriptName = $this->ReadPropertyString("Alarmname");
                $ScriptIdent = "ActionScript";
                $ScriptID = @IPS_GetObjectIDByIdent($ScriptIdent, $this->InstanceID);  //Wenn kein Skript vorhanden erstelle einen neuen.
                if($ScriptID === false) {
                    $ScriptID = IPS_CreateScript(0);
                    IPS_SetName($ScriptID, $ScriptName);
                    IPS_SetIdent( $ScriptID,$ScriptIdent);
                    IPS_SetParent($ScriptID, $this->InstanceID);
                }
                if($data !== ""){
                    $Scriptdata ='<?
                    ';
                    $Scriptdata.=$this->Translate("#This script was created automatically by the module.");
                    $Scriptdata.='
                    ';                    
                    $Scriptdata.=$this->Translate("#If the script is updated through the module configurator,");
                    $Scriptdata.='
                    ';      
                    $Scriptdata.=$this->Translate("#will be overwritten !!"); 
                    $Scriptdata.='
                    ';    
                    $Scriptdata.=$this->Translate("#Please backup the contents before"); 
                    $Scriptdata.='
                        if($_IPS["SENDER"] == "TimerEvent"){
                            switch ($_IPS["ACTION"]) {';
                            foreach($data as $key => $value) {
                                $Scriptdata.= '
                                case '.$value->OffsetID.':		#'.$value->OffsetAktion.'
                                # code...
                                break;';
                            }
                    $Scriptdata.='}
                        }   
                        ?>';
                    IPS_SetScriptContent($ScriptID,$Scriptdata);
                    $this->SetStatus(102); 
                }
                return $ScriptID;
            }

        protected function	CreateTimerEvent($Ident)
            {
                $ObjectID = @IPS_GetObjectIDByIdent($Ident,  $this->InstanceID);
                if($ObjectID === false) {
                    $ObjectID = IPS_CreateEvent(1);  // “zyklisches” Ereignis
                    IPS_SetParent($ObjectID,  $this->InstanceID);
                    IPS_SetEventCyclic($ObjectID, 3 /* Wöchentlich */, 1 /* Alle 1 Wochen */, 1, 0, 0, 0);
                    IPS_SetName($ObjectID, $Ident);
                    IPS_SetIdent($ObjectID,$Ident);
                    IPS_SetEventActive($ObjectID, false);
                }
            }

        protected function WochenplanAuslesen()
            {
                $ParentIdent ="ActionScript";
                $EreignisIdent = "weeklyplan";      //Wochenplan Name
                $ParentID = @IPS_GetObjectIDByName($ParentIdent,$this->InstanceID );
                $EreignisID = @IPS_GetEventIDByName($EreignisIdent, $ParentID);  //Wochenplan ID
                if ($EreignisID === false){
                    return false;
                }
                else{
	            $EreignisInfo= IPS_GetEvent($EreignisID);
                foreach ($EreignisInfo['ScheduleGroups'] as $key => $value) {
                    $Stunde = str_pad($value['Points'][0]['Start']['Hour'],2,'0',STR_PAD_LEFT);
                    $Minute = str_pad($value['Points'][0]['Start']['Minute'],2,'0',STR_PAD_LEFT);
                    $Sekunde = str_pad($value['Points'][0]['Start']['Second'],2,'0',STR_PAD_LEFT);
                    $Time = $Stunde.":".$Minute.":".$Sekunde;
                    $Day = $value['Days'];
                    $data[$Day] =   array(
                        'Day' => $Day,
                        'Time' => $Time,
                        );
                }
                return $data;
                }
            }
        protected function TimerAuslesen($Ident)
            {
                $ObjectID = IPS_GetObjectIDByIdent($Ident,  $this->InstanceID);
                $EventInfo = IPS_GetEvent($ObjectID );
	            $ktivetage = $EventInfo['CyclicDateDay'];
	            $bin = sprintf( "%07d", decbin($ktivetage)); // Dezimalwert Der Aktiven Tage in Binär umrechnen und auf 7 stellen erweitern 
	            $Aktivetage =str_split($bin, 1); // Binär Wert trennen und in einen Array schreiben
	            $Aktivetage = array_reverse($Aktivetage); // Array umdrehen
	            $Tage = array();						//Neues Array erzeugen
	            $i=0;
	            foreach($Aktivetage as $value) {
		            $Tage[pow(2,$i)]['TagAktiv'] = $value;		//Tage als Key ins Array hinzfügen
		            $Tage[pow(2,$i)]['Day'] = pow(2,$i);
		            $Tage[pow(2,$i)]['Houers'] = $EventInfo['CyclicTimeFrom']['Hour'];
		            $Tage[pow(2,$i)]['Minutes'] = $EventInfo['CyclicTimeFrom']['Minute']; 
		            $Tage[pow(2,$i)]['Seconds'] = $EventInfo['CyclicTimeFrom']['Second'];
		            $i++;
	            }

                return $Tage;
                
            }

        // Prüfe auf doppelte einträge
        // Code von Ghanshyam von php.net
        protected function unique_multidim_array($array, $key) 
            {
                $temp_array = array();
                $i = 0;
                $key_array = array();    
                foreach($array as $val) {
                    if (!in_array($val[$key], $key_array)) {
                        $key_array[$i] = $val[$key];
                        $temp_array[$i] = $val;
                    }
                $i++;
                }
                if(count($array) == count($temp_array)){
                    return false;
                }else{
                    return true;
                }
            }

        public function OpjektAktiv($value) 
            {
                $ParentIdent ="ActionScript";
                $EreignisIdent = "weeklyplan";      //Wochenplan Name
                $ParentID = @IPS_GetObjectIDByName($ParentIdent,$this->InstanceID );
                $EreignisID = @IPS_GetEventIDByName($EreignisIdent, $ParentID);  //Wochenplan ID
                if (IPS_SetEventActive($EreignisID, $value) == true ){
                    SetValue($this->GetIDForIdent("ArlamState"), $value);
                    return $value;
                }
            } 

        ## Variablen profile erstellen  ##
        protected function Var_Pro_Erstellen()
            {
                $ProfileName = "WT.NewTime";
                if (IPS_VariableProfileExists($ProfileName) == false){
                    IPS_CreateVariableProfile($ProfileName, 0);
                    IPS_SetVariableProfileValues($ProfileName, 0, 1, 0);
                    IPS_SetVariableProfileAssociation($ProfileName, 0, $this->Translate("Reset"), "", 16711680);
                    IPS_SetVariableProfileAssociation($ProfileName, 1, $this->Translate("New time"), "", 65280);
                 }
            }
    }
?>
