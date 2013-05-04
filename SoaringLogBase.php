<?php
    class SoaringLogBase {
        /******************** Private properties ********************/
        private static $instance;

        // Name of the database to use.  Must be prefaced with 'sqlite:' to indicate this is a SQLite database
        private $dbDir = 'sqlite:/home/elijah/ad7zj/logging/myDatabase.sqlite';
        
        // name of the database table used to store the flight time information
        private $flightLogTable = "flightLog";

        // name of the database table used to store the member list in
        private $pilotTable = "pilots";
    
        /******************** Public properties ********************/
        public $dbObj;
        
        // create method for getting the singleton instance
        public static function GetInstance() {
            if(!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() { 
            // open the database
            $this->OpenDatabase();
        }

        public function OpenDatabase() {
            try {
                //create or open the database
                $this->dbObj = new PDO($this->dbDir) or die("Cannot open the database");
            }
            catch(Exception $e) {
                die("Oops, couldn't open the database :( $e");
            }
            
        }

        public function GetFlightLogTable() {
            return $this->flightLogTable;
    
        }

        public function GetPilotTable() {
            return $this->pilotTable;

        }

        public function GetAircraft() {
            return array( "", "SGS 1-26", "SGS 2-33", "SGS 1-34", "Cirrus" );

        }

        public function GetMembers() {
            $query = "SELECT * FROM pilots;";
            $result = $this->dbObj->query($query, SQLITE_BOTH, $error);

            $memberList = array( );
            while($row = $result->fetch(PDO::FETCH_BOTH)) {
                $memberList[$row['ID']] = $row['Name'];
            }
            
            return $memberList;
        }

        /**
         * returns the timestamp of when the specified pilot last flew
         *
         * @param ID of pilot to check
         */
        public function GetLastFlew($id) {
            $query = "SELECT * FROM flightLog WHERE billTo='$id' ORDER BY flightIndex DESC LIMIT 1;";
            $result = $this->dbObj->query($query, SQLITE_BOTH, $error);

            $row = $result->fetch(PDO::FETCH_BOTH);
            return $row['takeoffTime'];
        }

        public function GetInstructors() {
            return array( "", "None", "A.C. Goodwin" );

        }
   
        /**
         * Prints a drop down box of pilots to be included in an HTML form
         *
         * @param $selected 0 based index of which pilot should be initially selected
         *
         * @return String containing formatted HTML
         */ 
        public function PrintPilots($selected = 0) {
            $memberList = $this->GetMembers();
            $output = "<select name=\"billTo\">\n";
            foreach($memberList as $i => $value) {
                $output .= "<option value=\"$i\" ";
                if($i == $selected)
                    $output .= "selected=\"selected\"";
                $output .= ">$value</option>\n";
            }
            $output .= "</select>";
            return $output;
        }

        /**
         * Prints a drop down box of airplanes to be included in an HTML form
         *
         * @param $selected 0 based index of which aircraft should be initially selected
         *
         * @return String containing formatted HTML
         */ 
        public function PrintAircraft($selected = 0) {
            $aircraftList = $this->GetAircraft();
            $output = "<select name=\"aircraft\">\n";
            foreach($aircraftList as $i => $value) {
                $output .= "<option value=\"$i\" ";
                if($i == $selected)
                    $output .= "selected=\"selected\"";
                $output .= ">$value</option>\n";
            }
            $output .= "</select>";
            return $output;
        }

        /**
         * Prints a drop down box of instructors to be included in an HTML form
         *
         * @param $selected 0 based index of which instructor should be initially selected
         *
         * @return String containing formatted HTML
         */ 
        public function PrintInstructors($selected = 0) {
            $instructorList = $this->GetInstructors();
            $output = "<select name=\"instructor\">\n";
            foreach($instructorList as $i => $value) {
                $output .= "<option value=\"$i\" ";
                if($i == $selected)
                    $output .= "selected=\"selected\"";
                $output .= ">$value</option>\n";
            }
            $output .= "</select>";
            return $output;
        }
    
    }
?>
