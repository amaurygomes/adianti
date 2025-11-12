<?php

class SystemUserExtended extends SystemUser
{
    private $cpf;
    private $vtr;

    public function __construct($id = NULL)
    {

        parent::__construct($id); 
        
        parent::addAttribute('cpf');
        parent::addAttribute('vtr');
    }

    public static function newFromCpf($cpf)
    {
        return self::where('cpf', '=', $cpf)->first();
    }
    
    public static function newFromVtr($vtr)
    {
        return self::where('vtr', '=', $vtr)->first();
    }
}