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
            $query = "INSERT INTO `tbl_users`(`username`, `email`, `password`, `firstname`, `lastname`, `gender`, `date_of_birth`, `sexual_preference`, `biography`, `address`, `token`, `salt`) VALUES"
                        ."('mkgosise', 'mpho.kgosisejo@hotmail.com', '3efd54dd0fb9340c6d02b47dfb2138ebd655f4c3c80058a7afe6c3a6df4edd80192217331657235273fb43f59aa9055b15e9f34cd6162e1c98cc5070d3ac046c', 'Mpho', 'Kgosisejo', 'male', '1994-03-12', 'female', 'About user...', '6 Boxer Street, Kensington, Johannesburg, South Africa', '', 'mBmafPGAPUcDdHI'),"
                        ."('pkaygo', 'm.k.kaygo@gmail.com', 'b9fbab3823c4d37c23d44a0c26076929763f7855d1530f131af1e319c493efca950b9c57b539fc0bc2c3d23b1ed1b2b0a59378e3e6e3a5ec2b2e1e35d130dade', 'Kaygo', 'Phobos', '', '1990-06-20', '', '', '', '', '38rvrrwqQ8d9BFY'),"
                        ."('jullian', 'mkgosise@student.wethinkcode.co.za', 'e4eb522a1a75eba7ddce63ba2bca1cfd0d11f2b46d94cc03c663105ebe9c75f7b4a67dfe34edd9e8c13eb9880fe8230bca446b6198deb3951493a9101eae90b1', 'Jullian', 'Gomez', 'female', '', '', '', '30-4 Inver Ave, Crosby, Johannesburg, South Africa', '', 'iGPeRwgWApo5RQB')";
            
            if (($check = parent::select("tbl_users", null, null, true))){
                if (!$check->rowCount){
                    if (!parent::rawQuery($query))
                        return (Config::response($res, 'response/message', "Error populating records"));
                }
            }
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