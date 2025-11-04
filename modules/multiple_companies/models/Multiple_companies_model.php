<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Multiple_companies_model extends CI_Model
{


    public function __construct()
    {

        parent::__construct();


    }


    public function get_contact_companies( $email = '' , $customer_id = 0 )
    {

        if ( !empty( $customer_id ) )
            $this->db->where('contact.userid !=',$customer_id);

        return $this->db->select(" client.company , contact.userid , contact.id , contact.firstname , contact.lastname , contact.is_primary ")
                        ->from(db_prefix() . 'contacts contact')
                        ->join(db_prefix()."clients client"," contact.userid = client.userid ")
                        ->where('contact.email',$email)
                        ->group_by('contact.userid')
                        ->order_by('client.company')
                        ->get()
                        ->result();

    }



}

