<?php

/**
 * It gets the email and token from the url,
 * then it checks if the email and token matches the database,
 * if it does, it returns the data from the database
 * @ version 1.0.1
 * @ Mohammad Prince
 * @ Contributed by Jahangir
 * @ SoftTech-IT
 */

defined('BASEPATH') or exit('No direct script access allowed'); // Exit if accessed directly

header("Access-Control-Allow-Origin: *"); // Allow cross-domain requests
header("Content-Type: application/json; charset=UTF-8"); // Set content type to json

include_once(APPPATH . 'config/app-config.php'); // Load the app config file

class Maildoll extends CI_Controller // This is the main controller for the application
{
    
    public function generate($email){
        $this->load->database();
        $this->db->query("CREATE TABLE IF NOT EXISTS `maildoll` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(50) NOT NULL,
      `token` varchar(50) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        $generated = [
        'email'=> $email,
        'token'=> uniqid('maildoll-token-', true)
        ];
    
    $query = $this->db->query("SELECT email, token FROM maildoll WHERE email = '{$generated['email']}' LIMIT 1;");
    if ($query->num_rows() == 0) {
        $insert_query = $this->db->query("INSERT INTO maildoll (email, token) VALUES ('{$generated['email']}', '{$generated['token']}');");
    }

    // You might want to retrieve the inserted data to send it back in the response
    if (isset($insert_query) && $insert_query) {
        $inserted_data = $this->db->query("SELECT * FROM maildoll WHERE email = '{$generated['email']}' LIMIT 1;");
        $data = $inserted_data->result();
    } else {
        $data = $query->result();
    }
    
    die(json_encode([
        'success' => true,
        'data' => $data[0]
    ]));
    }
    public function leads($email, $token){
       $this->load->database(); 
       $query = $this->db->query("SELECT email, token FROM maildoll WHERE email = '{$email}' AND token = '{$token}' LIMIT 1;");
        if ($query->num_rows() == 0) {
            die(json_encode([
                'success' => false,
                'message' => 'Invalid Token!'
            ]));
        } else {
            $query = $this->db->query("SELECT CONCAT(firstname,' ', lastname) AS name, email, phonenumber FROM tblcontacts");
            $data = $query->result();
            die(json_encode([
                'success' => true,
                'data' => $data,
                'message' => 'success'
            ]));
        }
        
    
    }

    // ENDS
}
