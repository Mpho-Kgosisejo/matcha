<?php
    class Setup extends Database {
        public function database(){
            $res = Config::get('response_format');

            if (($server = parent::server_connection())){
                $query = "CREATE DATABASE IF NOT EXISTS ". Config::get('server/db_name') .";";
                if (($stmt = parent::rawQuery($query, false, $server))){
                    $res = Config::response($res, 'response/state', "true");
                    return (Config::response($res, 'response/message', "Database created"));
                }
            }
            return (Config::response($res, 'response/message', "Connection error"));
        }

        public function tables(){
            new Database();
            $res = Config::get('response_format');
            $table_names = Config::get('setup_formats/table_names');
            $table_queries = Config::get('setup_formats/table_queries');
            
            $i = 0;
            foreach ($table_queries as $el){
                $query = "CREATE TABLE IF NOT EXISTS `$table_names[$i]` ($el);";
                if (!parent::rawQuery($query)){
                    return (Config::response($res, 'response/message', "Error creating table: `$table_names[$i]`)"));
                }
                $i++;
            }
            $res = Config::response($res, 'response/state', "true");
            return (Config::response($res, 'response/message', "All tables created successfully"));
        }

        public function populate_database(){
            new Database();
            $res = Config::get('response_format');
            $res = Config::get('response_format');
            
            $res = Config::response($res, 'response/state', "true");
            return (Config::response($res, 'response/message', "Population success"));
        }

        public function all(){
            $res = Config::get('response_format');
            $create_db = (object)self::database()['response'];
            
            if ($create_db->state == "true"){
                $create_tables = self::tables()['response'];

                if ($create_db->state == "true"){
                    $res = Config::response($res, 'response/state', "true");
                    return (Config::response($res, 'response/message', "Database OK"));
                }
            }
            return (Config::response($res, 'response/message', "Could not create database"));
        }
        
        public function re(){
            $res = Config::get('response_format');

            if (($server = parent::server_connection())){
                $query = "DROP DATABASE IF EXISTS ". Config::get('server/db_name') .";";

                if (parent::rawQuery($query, false, $server)){
                    $create_all = (object)self::all()['response'];

                    if ($create_all->state == "true"){
                        $res = Config::response($res, 'response/state', "true");
                        return (Config::response($res, 'response/message', "Database re-created"));
                    }
                    return ("{}");
                }
            }
            return (Config::response($res, 'response/message', "Could not re-create database"));
        }
    }
?>