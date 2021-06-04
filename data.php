<?php
ini_set('memory_limit', '1024M');
include("conectionmysql.php");

/*
inputs -> 
    typeTime -> lastData -> quant -> max 50
             -> custom -> iniDate 
                       -> endDate
                       -> resolution (endDate - iniDate) -> 3 days -> 10 min, 30 min, 1h
                                                         -> 2 days -> 5 min, 10 min, 30 min
                                                         -> 1 day -> 1 min, 2 min, 3 min
                                                         -> 0 day -> error
                       

    typeData -> all
             -> temperature -> sensor -> T1, T2, T3, T4, T5, T6, T7, T8, T9, T10
             -> flow (vazão) -> sensor -> V1
             -> velocityWind -> sensor -> AN1
             -> irradiance -> sensor -> IR1

    

*/


class InfoData {

    var $dbaccess = NULL;
    var $contentArray = NULL;

    function __construct()
    {
        $this->dbaccess = createAccessDB();
    }

    public function closeConnectionDB()
    {
        $this->dbaccess->close();
    }

    public function resolveGetData() 
    {
        if (isset($_POST["typeTime"]) && isset($_POST["typeData"]) && isset($_POST["sensor"]) ) {
            $typeTime = trim(strip_tags($_POST['typeTime']));
            $typeData = trim(strip_tags($_POST['typeData']));
        } else {
            $objJsonResult["content"] = "Error: Nonexistent Type Valor";
        }
        if (!($typeTime == "lastData" || $typeTime == "custom")) {
            $this->contentArray = array(
                "code" => NULL,
                "content" => "Error: No value correct option"
            );
            exit(); 
        }

        $sensor = NULL;

        if ($typeData == "all") {
            $option = trim(strip_tags($_POST['typeTime']));
        } 
        else if ($typeData == "temperature") {
            if ($typeTime == "lastData") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getLastData($typeData, $sensor);
            } 
            else if ($typeTime == "custom") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getCustomData($typeData, $sensor);
            } 
        } 
        else if ($typeData == "flow") {
            if ($typeTime == "lastData") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getLastData($typeData, $sensor);
            } else if ($typeTime == "custom") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getCustomData($typeData, $sensor);
            }
        } 
        else if ($typeData == "velocityWind") {
            if ($typeTime == "lastData") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getLastData($typeData, $sensor);
            } else if ($typeTime == "custom") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getCustomData($typeData, $sensor);
            }
        } 
        else if ($typeData == "irradiance") {
            if ($typeTime == "lastData") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getLastData($typeData, $sensor);
            } else if ($typeTime == "custom") {
                $sensor = $this->resolveWhichSensor($typeData);
                $this->getCustomData($typeData, $sensor);
            }
        } 
        else {
        }
        
    }

    private function resolveWhichSensor($typeData) 
    {
        $sensorInput = trim(strip_tags($_POST['sensor']));
        $arraySensor = explode(',', $sensorInput);

        $arraySensor = array_unique($arraySensor);
        
        $temperatureSensors = ["T1", "T2", "T3", "T4", "T5", "T6", "T7", "T8", "T9", "T10"];

        $arraySelectSensors = [];

        $continuaVeri = true;
        for ($i = 0; $i < count($arraySensor) && $continuaVeri; $i++){
            if ($typeData == "temperature") {
                foreach($temperatureSensors as $completeTempSensor){
                    if ($arraySensor[$i] == $completeTempSensor) {
                        array_push($arraySelectSensors, $arraySensor[$i]);
                    }
                }
            } 
            else if ($typeData == "flow") {
                if ($arraySensor[$i] == "V1") {
                    $continuaVeri = false;
                    array_push($arraySelectSensors, $arraySensor[$i]);
                }
            } 
            else if ($typeData == "velocityWind") {
                if ($arraySensor[$i] == "AN1") {
                    $continuaVeri = false;
                    array_push($arraySelectSensors, $arraySensor[$i]);
                }
            } 
            else if ($typeData == "irradiance") {
                if ($arraySensor[$i] == "IR1") {
                    $continuaVeri = false;
                    array_push($arraySelectSensors, $arraySensor[$i]);
                }
            }
        }

        if (empty($arraySelectSensors)){
            exit(); // sensores não validos
        }
        return $arraySelectSensors;
    }

    private function resolveWhichTime() 
    {
        // tipo de input esperado -> 2021-06-05
        if (!(isset($_POST["iniDate"]) || isset($_POST["endDate"]) && isset($_POST["resolution"]))) {
            exit(); // sem quantidade expecificada
        }

        $initDate = trim(strip_tags($_POST['iniDate']));
        $endDate = trim(strip_tags($_POST['endDate']));

        $datetimeInit = new DateTime($initDate); //start time
        $datetimeEnd = new DateTime($endDate); //end time

        $resolution = trim(strip_tags($_POST['resolution']));
        $interval = date_diff($datetimeEnd, $datetimeInit);

        $interval = intval($interval->format("%d"));

        $now = time(); // or your date as well
        $timestampIni = strtotime($initDate);
        $datediffInit = ceil(($now - $timestampIni) / 86400); // difference days between now and input date

        $timestampEnd = strtotime($endDate);
        $datediffEnd = ceil(($now - $timestampEnd) / 86400); // difference days between now and input date

        if (!($interval <= 3 && ($datediffInit > 0 && $datediffEnd >= 0))) {
            exit(); // fora do intervalo de tempo custom
        }

        $minutevalor = NULL;
        // interval of day
        if ($interval == 3) {
            if (!($resolution == "10min" || $resolution == "30min" || $resolution == "1h")) {
                exit(); // intervalo fora
            } 
            else {
                if ($resolution == "10min") {
                    $minutevalor = 10;
                }
                else if ($resolution == "30min") {
                    $minutevalor = 30;
                }
                else {
                    $minutevalor = 60;
                }
            }
        } else if ($interval == 2) {
            if (!($resolution == "5min" || $resolution == "10min" || $resolution == "30min")) {
                exit(); // intervalo fora
            } 
            else {
                if ($resolution == "5min") {
                    $minutevalor = 5;
                } else if ($resolution == "10min") {
                    $minutevalor = 10;
                } else {
                    $minutevalor = 30;
                }
            }
        } else {
            if (!($resolution == "1min" || $resolution == "2min" || $resolution == "3min")) {
                exit(); // intervalo fora
            } 
            else {
                if ($resolution == "1min") {
                    $minutevalor = 1;
                } 
                else if ($resolution == "2min") {
                    $minutevalor = 2;
                } 
                else {
                    $minutevalor = 3;
                }
            }
        }

        return [$resolution, $initDate, $endDate, $datetimeInit, $datetimeEnd, $interval, $timestampIni, $timestampEnd, $minutevalor];
    }

    private function getCustomData($typeData, $sensors)
    {
        $timeArray = $this->resolveWhichTime();

        $fisrtPartQuery = "SELECT id, tempo";
        $querySensors = NULL;
        foreach ($sensors as $sensor) {
            $querySensors = $querySensors . ", {$sensor}";
        }
        $tempSeconds = $timeArray[8] * 60;
        $sqlquery = $fisrtPartQuery . $querySensors . 
        " FROM tcc.dados WHERE tempo BETWEEN {$timeArray[6]} AND {$timeArray[7]} GROUP BY tempo DIV {$tempSeconds}";

        $result = $this->dbaccess->query($sqlquery);
        if ($result->num_rows > 0) {     // output data of each row   
            $this->contentArray["code"] = 200;
            while ($row = $result->fetch_assoc()) {
                if ($typeData == "temperature") {
                    $tempSensors = array();
                    foreach ($sensors as $sensor) {
                        array_push($tempSensors, $row[$sensor]);
                    }

                    $tempStemp = intval($row["tempo"]);
                    $date = new DateTime("@$tempStemp");

                    $this->contentArray["content"][] = array(array(
                        "id" => $row["id"], "tempo" => $row["tempo"], "data" =>
                        $date->format('Y-m-d H:i:s'), "temperaturas" =>
                        $tempSensors
                    ));
                } else if ($typeData == "flow") { //vazão

                } else if ($typeData == "velocityWind") { // temperatura
                    $this->contentArray["content"][] = array(array(
                        "id" => $row["id"],
                        "tempo" => array(
                            $row["tempo"],
                            $row["vento"]
                        )
                    ));
                } else if ($typeData == "irradiance") { // radiacao
                    $this->contentArray["content"][] = array(array(
                        "id" => $row["id"],
                        "tempo" => array(
                            $row["tempo"],
                            $row["radiacao"]
                        )
                    ));
                }
            }
        } else {
            $this->contentArray = array(
                "code" => 200,
                "content" => "No results"
            );
        }

    }

    private function getLastData($typeData, $sensors)
    {
        if (!isset($_POST["quant"])) {
            exit(); // sem quantidade expecificada
        }

        $quantData = trim(strip_tags($_POST['quant']));

        if (!($quantData < 30 && $quantData > 0)) {
            exit(); // fora do range especificado
        }

        $fisrtPartQuery = "SELECT id, tempo";
        $querySensors = NULL;
        foreach ($sensors as $sensor) {
            $querySensors = $querySensors . ", {$sensor}";
        }
        $sqlquery = $fisrtPartQuery.$querySensors." FROM tcc.dados ORDER BY id DESC LIMIT {$quantData}";

        $result = $this->dbaccess->query($sqlquery);
        if ($result->num_rows > 0) {     // output data of each row   
            $this->contentArray["code"] = 200;
            //$position = 1;
            while ($row = $result->fetch_assoc()) {
                if ($typeData == "temperature") {
                    $tempSensors = array();
                    foreach ($sensors as $sensor) {
                        array_push($tempSensors, $row[$sensor]);
                    }
                    $this->contentArray["content"][] = array(array(
                        "id" => $row["id"], "tempo" => $row["tempo"], "temperaturas" =>
                        $tempSensors
                    ));
                } 
                else if ($typeData == "flow") { //vazão
                    
                } 
                else if ($typeData == "velocityWind") { // temperatura
                    $this->contentArray["content"][] = array(array("id" => $row["id"], 
                    "tempo" => array($row["tempo"], 
                    $row["vento"])
                    ));
                } 
                else if ($typeData == "irradiance") { // radiacao
                    $this->contentArray["content"][] = array(array("id" => $row["id"], 
                    "tempo" => array($row["tempo"], 
                    $row["radiacao"])
                    ));
                } 
            }
        } else {
            $this->contentArray = array(
                "code" => 200,
                "content" => "No results"
            );
        }
    }
}