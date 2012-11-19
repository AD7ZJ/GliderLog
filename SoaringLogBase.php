<?php
    class SoaringLogBase {
        /******************** Private properties ********************/
        private static $instance;

        // Name of the database to use.  Must be prefaced with 'sqlite:' to indicate this is a SQLite database
        private $dbDir = 'sqlite:myDatabase.sqlite';
        
        // name of the database table used to store the flight time information
        private $flightLogTable = "flightLog";
    
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

        public function GetAircraft() {
            return array( "", "SGS 1-26", "SGS 2-33", "SGS 1-34", "Cirrus" );

        }

        public function GetMembers() {
            return array( "", "Max Denney", "Greg Berger", "Rod Clark", "Scott Boynton", "Elijah Brown", "Dana", "Fred" );

        }

        public function GetInstructors() {
            return array( "", "None", "A.C. Goodwin" );

        }

    }
?>
