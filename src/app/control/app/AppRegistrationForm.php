<?php
class AppRegistrationForm extends TPage
{
    protected $form; 
    protected $program_list;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new TModalForm('form_registration');
        $this->form->setFormTitle( _t('User registration') );
        
        $login      = new TEntry('login');
        $name       = new TEntry('name');
        $email      = new TEntry('email');
        $cpf        = new TEntry('cpf');
        $vtr        = new TEntry('vtr');
        $password   = new TPassword('password');
        $repassword = new TPassword('repassword');

        $login->placeholder = _t('User');
        $name->placeholder = _t('Name');
        $email->placeholder = _t('Email');
        $cpf->placeholder = _t('CPF');
        $vtr->placeholder = _t('VTR');
        $password->placeholder = _t('Password');
        $repassword->placeholder = _t('Password confirmation');
        
        $password->disableToggleVisibility();
        $repassword->disableToggleVisibility();
        
        $login->addValidation( _t('Login'), new TRequiredValidator);
        $name->addValidation( _t('Name'), new TRequiredValidator);
        
        $email->addValidation( _t('Email'), new TRequiredValidator);
        $email->addValidation( _t('Email'), new TEmailValidator);
        $cpf->addValidation( ('CPF'), new TRequiredValidator);
        $cpf->setMask('999.999.999-99'); 
        $vtr->addValidation( ('VTR'), new TRequiredValidator);
        $vtr->setMask('999999');

        $password->addValidation( _t('Password'), new TRequiredValidator);
        $repassword->addValidation( _t('Password confirmation'), new TRequiredValidator);
        
        $name->setSize('100%');
        $login->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $email->setSize('100%');
        
        $this->form->addRowField( _t('Login'), $login, true );
        $this->form->addRowField( _t('Name'), $name, true );
        $this->form->addRowField( _t('Email'), $email, true );
        $this->form->addRowField( ('CPF'), $cpf, true );
        $this->form->addRowField( ('VTR'), $vtr, true );
        $this->form->addRowField( _t('Password'), $password, true );
        $this->form->addRowField( _t('Password confirmation'), $repassword, true );
        
        $this->form->addAction( _t('Save'),  new TAction([$this, 'onSave']), '');
        
        parent::add($this->form);
    }
    
    public function onClear()
    {
        $this->form->clear( true );
    }
    
    public function onLoad($param)
    {
    
    }
    
    public static function onSave($param)
    {
        try
        {
            $ini = AdiantiApplicationConfig::get();
            
            if ($ini['permission']['user_register'] !== '1')
            {
                throw new Exception( _t('The user registration is disabled') );
            }
            
            TTransaction::open('permission');
            
            if( empty($param['login']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($param['name']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Name')));
            }
            
            if( empty($param['email']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Email')));
            }
            
            if( empty($param['cpf']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('CPF')));
            }

            if( empty($param['password']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
            }
            
            if( empty($param['repassword']) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password confirmation')));
            }
            
            if (isset($ini['general']['validate_strong_pass']) && $ini['general']['validate_strong_pass'] == '1')
            {
                (new TStrongPasswordValidator)->validate(_t('Password'), $param['password']);
            }
            
            if (SystemUserExtended::newFromLogin($param['login']) instanceof SystemUserExtended)
            {
                throw new Exception(_t('An user with this login is already registered'));
            }
            
            if (SystemUserExtended::newFromEmail($param['email']) instanceof SystemUserExtended)
            {
                throw new Exception(_t('An user with this e-mail is already registered'));
            }
            
            if (SystemUserExtended::newFromCpf($param['cpf']) instanceof SystemUserExtended)
            {
                throw new Exception('Um usuário com este CPF já está registrado');
            }
            
            if (SystemUserExtended::newFromVtr($param['vtr']) instanceof SystemUserExtended)
            {
                throw new Exception('Um usuário com esta VTR já está registrado');
            }
            
            if( $param['password'] !== $param['repassword'] )
            {
                throw new Exception(_t('The passwords do not match'));
            }
            
            // Preparação para salvar (VTR trim)
            $param['vtr'] = trim((string) $param['vtr']);
            
            $object = new SystemUserExtended;
            $object->active = 'Y';
            $object->fromArray( $param );
            $object->password = SystemUser::passwordHash($object->password);
            $object->frontpage_id = $ini['permission']['default_screen'];
            $object->clearParts();
            $object->store();
            
            $default_groups = explode(',', $ini['permission']['default_groups']);
            
            if( count($default_groups) > 0 )
            {
                foreach( $default_groups as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }
            
            $default_units = explode(',', $ini['permission']['default_units']);
            
            if( count($default_units) > 0 )
            {
                foreach( $default_units as $unit_id )
                {
                    $object->addSystemUserUnit( new SystemUnit($unit_id) );
                }
            }
            
            TTransaction::close(); 
            $pos_action = new TAction(['LoginForm', 'onLoad']);
            new TMessage('info', _t('Account created'), $pos_action); 
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}